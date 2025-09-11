
const mysql = require('mysql2/promise');

// Database connection
const dbConfig = {
    host: 'localhost',
    user: 'root',
    password: 'root',
    database: 'gighz'
};

async function calculateEmployeeHours() {
    const connection = await mysql.createConnection(dbConfig);

    try {


        // Fetch all employees
        const [users] = await connection.execute('SELECT * FROM attendance_users');




        for (const user of users) {
            const { user_id, name, emp_id, employee_status, emp_attendance_type } = user;
            // console.log(employee_status)
            let getElseDate = new Date('2024-12-24'); // if user not has last proccesed date it will start before 10 days

            if (employee_status === '1' && emp_id != null) {

                // fetch employee detail from employees table.
                const [employees] = await connection.execute('SELECT dept FROM employees WHERE emp_id = ?', [emp_id]);
                // console.log(employees[0].dept, emp_id);

                // Get last processed date from attendance table
                const [lastRecord] = await connection.execute(
                    'SELECT MAX(date) as last_date FROM attendance WHERE user_id = ?',
                    [user_id]
                );
                // console.log(lastRecord[0].last_date)
                let lastDate = lastRecord[0].last_date ? new Date(lastRecord[0].last_date) : null;
                let startDate = lastDate ? new Date(lastDate) : getElseDate;
                startDate.setDate(startDate.getDate() + 1); // Ensure it starts from the next day
                let yesterday = new Date();
                yesterday.setDate(yesterday.getDate() - 1); // Process until yesterday


                while (startDate <= yesterday || yesterday == startDate) {
                    let formattedDate = startDate.toISOString().split('T')[0];
                    // console.log("FORMATED DATE " + formattedDate);

                    // console.log(`YESTERDAY : ${yesterday} -> FORMATED DATE : ${formattedDate} -> START dATE : ${startDate}`);
                    // Fetch logs for this employee on this date
                    const [logs] = await connection.execute(
                        'SELECT timestamp FROM attendance_logs WHERE employee_id = ? and DATE(timestamp) = ? ORDER BY timestamp',
                        [user_id, formattedDate]
                    );

                    let totalHours = "Absent";
                    // console.log(logs);
                    if (logs.length % 2 === 1) {
                        // console.log(`Lenth Of Log is ${logs.length}`)
                        totalHours = "Error";
                    }
                    else if (logs.length >= 1) {
                        let totalWorkTime = 0;
                        // console.log(`USER ID IS ${user_id}`)

                        if (emp_attendance_type == 1) {

                            for (let i = 0; i < logs.length - 1; i += 2) {

                                // console.log(`Normal Employee ${user_id} -> ${name}`);
                                let checkIn = new Date(logs[i].timestamp);
                                // let checkIn = new Date(logs[i].timestamp).toLocaleTimeString('en-GB', { hour12: false });

                                let checkOut = logs[i + 1] ? new Date(logs[i + 1].timestamp) : null;
                                // let checkOut =logs[i + 1] ? new Date(logs[i + 1].timestamp).toLocaleTimeString('en-GB', { hour12: false }): null;

                                if (!checkOut) break; // Prevents calculating work time if there's no check-out


                                totalWorkTime += (checkOut - checkIn) / (1000 * 60 * 60); // Convert ms to hours

                                // console.log(checkIn+' : '+checkOut+' = '+totalWorkTime);
                                // console.log(`CHECK IN = ${checkIn} CHECK OUT = ${checkOut} TOTAL  = ${totalWorkTime}`);
                            }

                        }
                        else if (emp_attendance_type == 2) {
                            // console.error(`Feild Technition ${user_id} -> ${name}`);
                            let feildin = new Date(logs[0].timestamp);
                            let feildOut = logs[logs.length - 1] ? new Date(logs[logs.length - 1].timestamp) : null;
                            if (!feildOut) break;

                            let diffHours = (feildOut - feildin) / (1000 * 60 * 60);

                            // Apply deductions based on working hours
                            if (diffHours < 2) {
                                totalWorkTime = diffHours.toFixed(2);
                            } else if (diffHours < 5) {
                                totalWorkTime = (diffHours - 0.167).toFixed(2); // Deduct 10 minutes
                            } else if (diffHours < 6.67) {
                                totalWorkTime = (diffHours - 0.833).toFixed(2); // Deduct 50 minutes
                            } else {
                                totalWorkTime = (diffHours - 1).toFixed(2); // Deduct 1 hour
                            }
                        }

                        let hours = Math.floor(totalWorkTime);
                        let minutes = Math.round((totalWorkTime - hours) * 60);
                        totalHours = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`; // Format as HH:MM
                    }

                    let result = await connection.execute('SELECT date, user_id FROM attendance WHERE user_id = ? and date = ?', [user_id, formattedDate]);
                    let datas = result[0].date;
                    // console.log(datas)
                    if (datas) {
                        // console.log(`${user_id} AND ${formattedDate} ARE DUPLLICATE ENTRY...`);
                        await connection.execute('UPDATE attendance SET total_hours = ? where user_id = ? and date = ?', [totalHours, user_id, formattedDate]);
                    }
                    else {
                        // Insert or update attendance record
                        // await connection.execute(
                        //     'INSERT INTO attendance (user_id, date, total_hours) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE total_hours = ?',
                        //     [user_id, formattedDate, totalHours, totalHours]
                        // );
                    }
                    console.log(`✅ Processed: ${name} (${user_id}) on ${formattedDate} - Hours: ${totalHours}`);

                    // Move to the next day
                    startDate.setDate(startDate.getDate() + 1);

                }
            }

        }
        console.log('✅ ALL DATA CALCULATED SUCCESSFULLY...')
    } catch (error) {
        console.error("❌ Error processing attendance:", error);
    } finally {
        await connection.end();
    }

}

// Run the function
calculateEmployeeHours();