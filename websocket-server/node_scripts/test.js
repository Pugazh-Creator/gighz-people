// const express = require('express');
// const ZKLib = require('node-zklib');
// const cors = require('cors');
// const app = express();

// app.use(cors());

// app.get('/fetch', async (req, res) => {
//     const { start, end } = req.query;

//     const zk = new ZKLib('192.168.1.201', 4370, 10000, 4000); // Replace with your IP

//     try {
//         await zk.createSocket();
//         const logs = await zk.getAttendances();
//         await zk.disconnect();

//         // Optionally filter by date here
//         const filtered = logs.data.filter(log => {
//             const logDate = new Date(log.timestamp);
//             return (!start || logDate >= new Date(start)) &&
//                 (!end || logDate <= new Date(end));
//         });

//         res.json(filtered);
//     } catch (err) {
//         res.status(500).json({ error: 'Device not reachable' });
//     }
// });

// app.listen(3000, () => console.log('Biometric server running on http://localhost:3000'));


const ZKLib = require("node-zklib");
const mysql = require("mysql2/promise");

const zkInstance = new ZKLib("192.168.0.150", 4370, 10000, 4000);

// Database Configuration
const dbConfig = {
    host: "localhost",
    user: "root",
    password: "root",
    database: "gighz"
};

(async () => {
    let connection;
    var flag = false;
    let deviceconnected = false;
    try {
        // MySQL Connection
        connection = await mysql.createConnection(dbConfig);
        console.log("✅ Connected to MySQL!");

        // Biometric Device Connection
        await zkInstance.createSocket();
        deviceconnected = true;
        console.log("✅ Connected to eSSL Biometric Device!");

        // --- Fetch Users from Device ---
        let users = await zkInstance.getUsers();
        if (!Array.isArray(users)) users = [users];
        users = users[0]?.data || [];
        console.log(users)

        for (const user of users) {
            const { uid, role, name, userId } = user;
            const [existing] = await connection.execute('SELECT * FROM attendance_users WHERE user_id = ?', [userId]);

            if (existing.length > 0) {
                const [logCheck] = await connection.execute('SELECT MAX(timestamp) as last_punch FROM attendance_logs WHERE employee_id = ?', [userId]);
                const lastPunch = logCheck[0]?.last_punch ? new Date(logCheck[0].last_punch) : null;
                const today = new Date();
                await connection.execute("UPDATE attendance_users au JOIN employees e ON e.name LIKE CONCAT(au.name, '%') SET au.emp_id = e.emp_id WHERE au.emp_id IS NULL OR au.user_id = ?", [userId])

                if (existing[0].employee_status == '1' && lastPunch) {
                    const dayDiff = (today - lastPunch) / (1000 * 60 * 60 * 24);
                    if (dayDiff >= 30) {
                        await connection.execute('UPDATE attendance_users SET employee_status = ? WHERE user_id = ?', ['0', userId]);
                        await connection.execute('UPDATE user SET status = ? WHERE emp_id = ?', ['0', existing[0].emp_id]);
                        await connection.execute('UPDATE employees SET emp_status = ? WHERE emp_id = ?', ['0', existing[0].emp_id]);
                    }
                }
            } else {
                await connection.execute(
                    "INSERT INTO attendance_users (uid, role, name, user_id, employee_status) VALUES (?, ?, ?, ?, ?)",
                    [uid, role, name, userId, '1']
                );
                console.log('new User...')
            }
        }

        // --- Fetch Attendance Logs ---
        let logs = await zkInstance.getAttendances();
        if (!Array.isArray(logs)) logs = [logs];
        const logData = logs[0]?.data || [];

        const [lastLogRow] = await connection.execute("SELECT timestamp FROM attendance_logs ORDER BY timestamp DESC LIMIT 1");
        const lastLog = lastLogRow.length > 0 ? new Date(lastLogRow[0].timestamp) : new Date('2024-12-24');
        const today = new Date();

        for (const log of logData) {
            const logDate = new Date(log.recordTime);
            if (logDate > lastLog && logDate <= today) {
                await connection.execute(

                    "INSERT INTO attendance_logs (employee_id, timestamp, status) VALUES (?, ?, ?)",
                    [log.deviceUserId, log.recordTime, 0]
                );
                flag = true;
                console.log(`✅ Log inserted: ${log.deviceUserId} at ${log.recordTime}`);
            }
        }

        console.log("✅ Attendance Data Stored in MySQL!");

        // --- Calculate Employee Hours ---
        if (flag) {
            console.log('colculation Start...');
            await calculateEmployeeHours(connection);
        }


    } catch (error) {
        console.error("❌ Error:", error);
    } finally {
        try { await zkInstance.disconnect(); } catch (e) { }
        if (connection) {
            try { await connection.end(); } catch (e) { }
        }
        console.log("✅ Disconnected from Biometric Device and MySQL.");
    }
})();

async function calculateEmployeeHours(connection) {
    try {
        const [users] = await connection.execute('SELECT * FROM attendance_users');
        const [holidays] = await connection.execute('SELECT holiday_date FROM company_holiday');

        let holiday = [];
        for (let row of holidays) {
            let dateObj = new Date(row.holiday_date);
            let date = `${dateObj.getFullYear()}-${String(dateObj.getMonth() + 1).padStart(2, '0')}-${String(dateObj.getDate()).padStart(2, '0')}`;
            holiday.push(date);
        }
        // console.log('new Holiday is : ' + holiday);

        const defaultStartDate = new Date('2024-12-24');

        for (const user of users) {
            const { user_id, name, emp_id, employee_status, emp_attendance_type } = user;
            if (employee_status == '1' && (emp_id != null || emp_attendance_type == 3 || emp_attendance_type == 4)) {

                // const [empData] = await connection.execute('SELECT doj FROM employees WHERE emp_id = ?', [emp_id]);
                // let doj = empData[0].doj ? new Date(empData[0].doj) : defaultStartDate;

                const [lastRecord] = await connection.execute('SELECT MAX(date) as last_date FROM attendance WHERE user_id = ?', [user_id]);
                let startDate = lastRecord[0].last_date ? new Date(lastRecord[0].last_date) : defaultStartDate;
                startDate = new Date(startDate.setDate(startDate.getDate() + 1)).toISOString().split('T')[0];

                let endDate = new Date();

                endDate = new Date(endDate.setDate(endDate.getDate() - 1)).toISOString().split('T')[0];

                const leaverequests = getThisOELeaves(connection, emp_id, startDate, endDate)
                // console.log(`Start Date : ${startDate} \n End Date : ${endDate}`)

                while (startDate <= endDate) {
                    // console.log('hai')
                    const dateStr = startDate;
                    const [logs] = await connection.execute(
                        'SELECT timestamp FROM attendance_logs WHERE employee_id = ? AND DATE(timestamp) = ? ORDER BY time(timestamp)',
                        [user_id, dateStr]
                    );

                    let totalHours = "00:00";
                    let workType = 'Absent';

                    let worked = emp_attendance_type == 2 ? 'Feildtech' : (emp_attendance_type == 3 ? 'General' :
                        (emp_attendance_type == 4 ? 'Watchman' : 'Normal')
                    );

                    if (logs.length % 2 === 1) {


                        totalHours = "Error";
                        workType = 'Present';
                    } else if (logs.length > 0) {
                        let totalWorkTime = 0;
                        workType = 'Present';

                        if (emp_attendance_type == 1) {
                            for (let i = 0; i < logs.length - 1; i += 2) {
                                let inTime = new Date(logs[i].timestamp);
                                let outTime = new Date(logs[i + 1].timestamp);
                                totalWorkTime += (outTime - inTime) / (1000 * 60 * 60);
                            }
                            worked = 'Normal';
                            if (totalWorkTime < 5) {
                                workType = 'Offday';
                            } else if (totalWorkTime < 7) {
                                workType = 'Permission';
                            } else {
                                workType = 'Present';
                            }

                        } else if (emp_attendance_type == 2) {
                            let inTime = new Date(logs[0].timestamp);
                            let outTime = new Date(logs[logs.length - 1].timestamp);
                            let hours = (outTime - inTime) / (1000 * 60 * 60);

                            if (hours < 2) totalWorkTime = hours;
                            else if (hours < 5) totalWorkTime = hours - 0.167;
                            else if (hours < 6.67) totalWorkTime = hours - 0.833;
                            else totalWorkTime = hours - 1;

                            worked = 'Feildtech';
                        }
                        else if (emp_attendance_type == 3 || emp_attendance_type == 4) {
                            let inTime = new Date(logs[0].timestamp);
                            let outTime = new Date(logs[logs.length - 1].timestamp);
                            let hours = (outTime - inTime) / (1000 * 60 * 60);
                            totalWorkTime = hours;
                            worked = emp_attendance_type == 3 ? 'General' : 'Watchman';
                        }

                        let h = Math.floor(totalWorkTime);
                        let m = Math.round((totalWorkTime - h) * 60);
                        totalHours = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
                    }

                    if (workType === 'Absent' && emp_attendance_type != 4 && emp_attendance_type != 3) {
                        let issunday = new Date(dateStr);
                        if (issunday.getDay() === 0 || holiday.includes(dateStr)) {
                            // console.log(dateStr + ' is a Holiday');
                            workType = 'Absent';
                        }
                        else {

                            if ((await leaverequests).approved.includes(dateStr)) {
                                workType = 'APL';
                            }
                            else if ((await leaverequests).rejected.includes(dateStr)) {
                                workType = 'RL';
                            }
                            else {
                                workType = 'NA';
                            }
                        }
                    }

                    const [existing] = await connection.execute('SELECT * FROM attendance WHERE user_id = ? AND date = ?', [user_id, dateStr]);
                    if (existing.length > 0) {
                        await connection.execute('UPDATE attendance SET total_hours = ?, work_status = ?, work_type = ? WHERE user_id = ? AND date = ?', [totalHours, workType, worked, user_id, dateStr]);
                    } else {
                        await connection.execute('INSERT INTO attendance (user_id, date, total_hours, work_status, work_type) VALUES (?, ?, ?, ?, ?)', [user_id, dateStr, totalHours, workType, worked]);
                        console.log(`✅ Processed: ${name} (${user_id}) - ${dateStr} on ${totalHours} - Hours: ${workType}`);
                    }

                    startDate = new Date(startDate)
                    startDate = new Date(startDate.setDate(startDate.getDate() + 1)).toISOString().split('T')[0];
                }
            }
        }

        // console.log("✅ Employee Hours Calculated Successfully!");
    } catch (error) {
        console.error("❌ Error calculating hours:", error);
    }
}

async function getThisOELeaves(connection, empID, startDate, endDate) {
    const [rows] = await connection.execute(
        `SELECT start_date, end_date, reason, total_num_leaves, emp_id, status 
         FROM leave_request 
         WHERE start_date >= ? AND end_date <= ? AND emp_id = ?`,
        [startDate, endDate, empID]
    );

    const leaveRequests = {
        approved: [],
        rejected: [],
        pending: []
    };

    for (const row of rows) {
        const status = row.status.toLowerCase();
        let current = new Date(row.start_date);
        const end = new Date(row.end_date);

        while (current <= end) {
            // const dateStr = current.toISOString().split('T')[0];
            const dateStr = `${current.getFullYear()}-${String(current.getMonth() + 1).padStart(2, '0')}-${String(current.getDate()).padStart(2, '0')}`;


            if (status === 'approved') {
                leaveRequests.approved.push(dateStr);
            } else if (status === 'rejected') {
                leaveRequests.rejected.push(dateStr);
            } else if (status === 'pending') {
                leaveRequests.pending.push(dateStr);
            }

            current.setDate(current.getDate() + 1); // increment by 1 day
        }
    }

    return leaveRequests;
}