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
        // console.log(users)

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
        }
        await calculateEmployeeHours(connection);


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

        // Get all holidays in YYYY-MM-DD format (local time)
        const holiday = holidays.map(row => {
            const dateObj = new Date(row.holiday_date);
            return dateObj.toLocaleDateString('en-CA');
        });

        for (const user of users) {
            const { user_id, name, emp_id, employee_status, emp_attendance_type } = user;

            if (employee_status == '1' && (emp_id != null || emp_attendance_type == 3 || emp_attendance_type == 4)) {

                let defaultStartDate = new Date('2024-12-24'); // fallback

                const [empData] = await connection.execute('SELECT doj FROM employees WHERE emp_id = ?', [emp_id]);
                if (empData.length !== 0) {
                    defaultStartDate = new Date(empData[0].doj);
                }

                const [lastRecord] = await connection.execute('SELECT MAX(date) as last_date FROM attendance WHERE user_id = ?', [user_id]);

                let startDate = lastRecord[0].last_date != '' && lastRecord[0].last_date != null
                    ? new Date(lastRecord[0].last_date)
                    : defaultStartDate;

                // Move to the next date
                startDate.setDate(startDate.getDate() + 1);

                const endDate = new Date(); // Today
                endDate.setDate(endDate.getDate() - 1); // Yesterday

                while (startDate <= endDate) {
                    const dateStr = startDate.toLocaleDateString('en-CA'); // local date in YYYY-MM-DD

                    const [logs] = await connection.execute(
                        'SELECT timestamp FROM attendance_logs WHERE employee_id = ? AND DATE(timestamp) = ? ORDER BY time(timestamp)',
                        [user_id, dateStr]
                    );

                    let totalHours = "00:00";
                    let workType = 'Absent';
                    let worked = emp_attendance_type == 2 ? 'Feildtech' :
                        emp_attendance_type == 3 ? 'General' :
                            emp_attendance_type == 4 ? 'Watchman' : 'Normal';

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

                            if (totalWorkTime < 5) {
                                workType = 'Offday';
                            } else if (totalWorkTime < 7) {
                                workType = 'Permission';
                            }

                        } else if (emp_attendance_type == 2) {
                            let inTime = new Date(logs[0].timestamp);
                            let outTime = new Date(logs[logs.length - 1].timestamp);
                            let hours = (outTime - inTime) / (1000 * 60 * 60);

                            if (hours < 2) totalWorkTime = hours;
                            else if (hours < 5) totalWorkTime = hours - 0.167;
                            else if (hours < 6.67) totalWorkTime = hours - 0.833;
                            else totalWorkTime = hours - 1;

                        } else if (emp_attendance_type == 3 || emp_attendance_type == 4) {
                            let inTime = new Date(logs[0].timestamp);
                            let outTime = new Date(logs[logs.length - 1].timestamp);
                            totalWorkTime = (outTime - inTime) / (1000 * 60 * 60);
                        }

                        let h = Math.floor(totalWorkTime);
                        let m = Math.round((totalWorkTime - h) * 60);
                        totalHours = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
                    }

                    if (workType === 'Absent' && emp_attendance_type != 4 && emp_attendance_type != 3) {
                        let isSunday = startDate.getDay() === 0;
                        if (isSunday || holiday.includes(dateStr)) {
                            workType = 'Absent';
                        } else {
                            let [leavebals] = await connection.execute('SELECT remaining_leaves FROM employees WHERE emp_id = ?', [emp_id]);
                            let leaveBalance = leavebals[0].remaining_leaves ?? 0;
                            let newLeaveBalance = leaveBalance;
                            let [leaveRequests] = await connection.execute(
                                "SELECT * FROM leave_request WHERE start_date <= ? AND end_date >= ? AND emp_id = ?",
                                [dateStr, dateStr, emp_id]
                            );

                            let leaveStatus = leaveRequests[0]?.status ?? null;
                            let leaveId = leaveRequests[0]?.id;

                            let lop = false;

                            if (leaveStatus === 'approved') {
                                workType = 'APL';
                                newLeaveBalance = Math.max(0, leaveBalance - 1);
                                if (leaveBalance <= 0) lop = true;
                            } else if (leaveStatus === 'rejected') {
                                workType = 'RL';
                                newLeaveBalance = Math.max(0, leaveBalance - 1);
                                lop = true;
                            } else {
                                workType = 'NA';
                                // newLeaveBalance = Math.max(0, leaveBalance - 1);
                                // lop = true;
                            }

                            await connection.execute(
                                'UPDATE employees SET remaining_leaves = ? WHERE emp_id = ?',
                                [newLeaveBalance, emp_id]
                            );

                            if (lop) {
                                console.log(`✅ LOP writed to ${emp_id} ${dateStr}`);
                                const [rows] = await connection.execute(
                                    'SELECT * FROM tbl_lop WHERE lop_user = ? AND lop_date = ?',
                                    [emp_id, dateStr]
                                );
                                if (rows.length > 0) {
                                    await connection.execute(
                                        'UPDATE tbl_lop SET lop_total = ? WHERE lop_user = ? AND lop_date = ?',
                                        [1, emp_id, dateStr]
                                    );
                                } else {
                                    await connection.execute(
                                        'INSERT INTO tbl_lop (lop_user, lop_date, lop_total) VALUES (?, ?, ?)',
                                        [emp_id, dateStr, 1]
                                    );
                                }
                            }
                        }
                    }

                    const [existing] = await connection.execute(
                        'SELECT * FROM attendance WHERE user_id = ? AND date = ?',
                        [user_id, dateStr]
                    );

                    if (existing.length > 0) {
                        await connection.execute(
                            'UPDATE attendance SET total_hours = ?, work_status = ?, work_type = ? WHERE user_id = ? AND date = ?',
                            [totalHours, workType, worked, user_id, dateStr]
                        );
                    } else {
                        await connection.execute(
                            'INSERT INTO attendance (user_id, date, total_hours, work_status, work_type) VALUES (?, ?, ?, ?, ?)',
                            [user_id, dateStr, totalHours, workType, worked]
                        );
                        console.log(`✅ Processed: ${name} (${user_id}) - ${dateStr} on ${totalHours} - Hours: ${workType}`);
                    }

                    startDate.setDate(startDate.getDate() + 1); // increment to next day
                }
            }
        }
    } catch (error) {
        console.error("❌ Error calculating hours:", error);
    }
}


