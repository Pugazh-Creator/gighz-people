// const mysql = require('mysql2/promise');

// async function leaveMailAndResqest() {
//     const connection = await mysql.createConnection({
//         host: 'localhost',
//         user: 'root',
//         password: 'root',
//         database: 'gighz'
//     });

//     try {
//         const [users] = await connection.execute('SELECT * FROM employees');

//         for (const user of users) {
//             let { name, emp_id, emp_status, leave_grade } = user;
//             if (emp_status == '1') {
//                 // console.log('Name : '+name);
//                 let [lastAbsentDate] = await connection.execute(
//                     'SELECT MAX(a.date) as date, a.user_id FROM attendance a JOIN attendance_users u ON a.user_id = u.user_id WHERE a.total_hours = ? AND u.emp_id = ? GROUP BY A.user_id',
//                     ['Absent', emp_id]
//                 );

//                 if (lastAbsentDate.length > 0 && lastAbsentDate[0].date) {
//                     // Convert SQL date to JavaScript Date object
//                     let jsDate = new Date(lastAbsentDate[0].date);

//                     // Get YYYY-MM-DD manually
//                     let year = jsDate.getFullYear();
//                     let month = String(jsDate.getMonth() + 1).padStart(2, '0'); // Ensure two digits
//                     let day = String(jsDate.getDate()).padStart(2, '0'); // Ensure two digits
//                     for (day; day > 0; day--) {
//                         // console.log('USER ID : '+lastAbsentDate[0].user_id);
//                         let formattedDate = `${year}-${month}-${day}`;
//                         const diff = Math.ceil(Math.abs(new Date(formattedDate) - new Date()) / (1000 * 60 * 60 * 24)); // Difference in milliseconds

//                         if (diff > 5 && diff < 2) {
//                             break;
//                         }
//                         let holiday = [];
//                         if (leave_grade <= 2) {
//                             // console.log(name);
//                             [holiday] = await connection.execute('SELECT * FROM company_holiday where date(holiday_date) = ? AND (holiday_type = ? OR holiday_type = ?) ',
//                                 [formattedDate, 'festival', 'first_saturday']);

//                         }
//                         else {
//                             [holiday] = await connection.execute('SELECT * FROM company_holiday where date(holiday_date) = ?', [formattedDate]);
//                         }

//                         if (holiday.length == 0 && new Date(formattedDate).getDay() != 0) {

//                             console.log(diff)
//                             console.log(`${emp_id} : ${formattedDate}`); // Output: '2025-03-21'
//                             // const [ckeckLeave] = await connection.execute('SELECT * FROM attendance WHERE user_id = ? AND date = ? AND total_hours = ?', [lastAbsentDate[0].user_id, formattedDate, 'Absent']);

//                             // if(ckeckLeave.length > 0){}
//                         }




//                     }

//                 } else {
//                     console.log("No absent date found.");
//                 }

//                 // console.log(new Date(Date.UTC(date)));
//                 // console.log(date);

//             }
//         }

//     } catch (error) {
//         console.error("Error updating database:", error);
//     } finally {
//         await connection.end();
//         console.log('✅ MySQL disconnected successfully...');
//     }
// }



// // Run the function

// leaveMailAndResqest()


const mysql = require('mysql2/promise');
const nodemailer = require('nodemailer'); // Import nodemailer for email sending

async function leaveMailAndRequest() {
    const connection = await mysql.createConnection({
        host: 'localhost',
        user: 'root',
        password: 'root',
        database: 'gighz'
    });

    try {
        const [users] = await connection.execute('SELECT * FROM employees');

        for (const user of users) {
            let { name, emp_id, emp_status, leave_grade, email } = user;

            if (emp_status == '1') {
                let [lastAbsentDate] = await connection.execute(
                    `SELECT MAX(a.date) AS date, a.user_id 
                    FROM attendance a 
                    JOIN attendance_users u ON a.user_id = u.user_id 
                    WHERE a.total_hours = ? AND u.emp_id = ? 
                    GROUP BY a.user_id`,
                    ['Absent', emp_id]
                );
                let leaves = [];
                if (lastAbsentDate.length > 0 && lastAbsentDate[0].date) {
                    let lastDate = new Date(lastAbsentDate[0].date);
                    const diff = Math.ceil(Math.abs(new Date(lastAbsentDate[0].date) - new Date()) / (1000 * 60 * 60 * 24));
                    console.log(diff);
                    let endOe = '';
                    for(lastDate; lastDate > 0; lastDate.setDate(lastDate.getDate() - 1))
                    {
                        console.log(`Last Date : ${lastDate}`);
                    }
                    // let jsDate = new Date(lastAbsentDate[0].date);
                    // let year = jsDate.getFullYear();
                    // let month = String(jsDate.getMonth() + 1).padStart(2, '0');
                    // let day = String(jsDate.getDate()).padStart(2, '0');

                    // let formattedDate = `${year}-${month}-${day}`;
                    // // console.log(formattedDate + ' : ' + name);
                    // const diff = Math.ceil(Math.abs(new Date(formattedDate) - new Date()) / (1000 * 60 * 60 * 24));
                    // // console.log(diff +' : '+ name);
                    // if (diff > 6) continue; // Skip if absent date is too old


                    // for (day; day > 0; day--) {
                       
                    //     formattedDate = `${year}-${month}-${day}`;

                    //     const [checkLeave] = await connection.execute(
                    //         `SELECT * FROM attendance 
                    //     WHERE user_id = ? AND DATE(date) = ? AND total_hours = ?`,
                    //         [lastAbsentDate[0].user_id, formattedDate, 'Absent']
                    //     );

                    //     console.log(formattedDate + ' : ' + name);
                    //     if (checkLeave.length == 0) break;

                    //     let holiday = [];
                    //     if (leave_grade <= 2) {
                    //         [holiday] = await connection.execute(
                    //             `SELECT * FROM company_holiday 
                    //         WHERE DATE(holiday_date) = ? 
                    //         AND (holiday_type = ? OR holiday_type = ?)`,
                    //             [formattedDate, 'festival', 'first_saturday']
                    //         );
                    //     } else {
                    //         [holiday] = await connection.execute(
                    //             `SELECT * FROM company_holiday WHERE DATE(holiday_date) = ?`,
                    //             [formattedDate]
                    //         );
                    //     }

                    //     if (holiday.length == 0 && new Date(formattedDate).getDay() != 0) {

                    //         const [lastLeaveRequest] = await connection.execute('SELECT end_date, start_date FROM leave_request WHERE emp_id = ?', [emp_id, formattedDate]);
                    //         if (lastLeaveRequest.length == 0) {
                    //             leaves.push(formattedDate);
                    //             console.log(`Not Holiday and Sunday ${formattedDate}`);
                    //         }
                    //     }
                    //     if (checkLeave.length == 0) {
                    //         let [presentDays] = await connection.execute(
                    //             `SELECT COUNT(*) AS present_days FROM attendance 
                    //             WHERE user_id = ? AND DATE(date) IN (CURDATE() - INTERVAL 1 DAY, CURDATE() - INTERVAL 2 DAY) 
                    //             AND total_hours != ?`,
                    //             [lastAbsentDate[0].user_id, 'Absent']
                    //         );
                    //         console.log(presentDays + ' : Present Days')
                    //     }
                    //     // if (presentDays[0].present_days >= 2) {
                    //     //     console.log(`📩 Sending email to ${email}: You were absent on ${formattedDate} but haven't submitted a leave request.`);
                    //     //     await sendEmail(email, name, formattedDate);
                    //     // }
                    // }
                    // console.log('Formated Date : ' + leaves);
                } else {
                    console.log("No absent date found for", name);
                }
            }
        }
    } catch (error) {
        console.error("Error updating database:", error);
    } finally {
        await connection.end();
        console.log('✅ MySQL disconnected successfully...');
    }
}

// Function to send email
async function sendEmail(email, name, absentDate) {
    let transporter = nodemailer.createTransport({
        service: 'gmail',
        auth: {
            user: 'your-email@gmail.com',
            pass: 'your-password'
        }
    });

    let mailOptions = {
        from: 'your-email@gmail.com',
        to: email,
        subject: 'Leave Request Reminder',
        text: `Hello ${name},\n\nYou were absent on ${absentDate}, but you have not submitted a leave request. 
Please submit your leave request as soon as possible.\n\nBest Regards,\nHR Team`
    };

    try {
        await transporter.sendMail(mailOptions);
        console.log(`✅ Email sent to ${email}`);
    } catch (error) {
        console.error(`❌ Error sending email to ${email}:`, error);
    }
}

// Run the function
leaveMailAndRequest();

