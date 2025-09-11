// const mysql = require('mysql2/promise');
// const nodemailer = require('nodemailer');
// const cron = require('node-cron');

// const db = mysql.createPool({
//   host: 'localhost',
//   user: 'root',
//   password: 'root',
//   database: 'gighz'
// });

// const transporter = nodemailer.createTransport({
//   host: 'smtp.hostinger.com',
//   port: 587,
//   secure: false, // TLS - should be false for port 587
//   auth: {
//     user: 'itsupport@gighz.net',
//     pass: 'GigHz123#'
//   },
//   tls: {
//     rejectUnauthorized: false // <-- disables certificate validation
//   }
// });

// (async () => {

//   try {

//     console.log('started..');
//     const [absentees] = await db.query(`
//     SELECT a.user_id, a.date, e.official_mail, e.name
//     FROM attendance a
//     JOIN attendance_users u ON u.user_id = a.user_id
//     JOIN employees e ON e.emp_id = u.emp_id
//     WHERE a.work_status = 'na'
//       AND a.total_hours = '00:00'
//     AND a.date BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE() - INTERVAL 1 DAY
//       AND NOT EXISTS (
//         SELECT 1 FROM leave_request
//         WHERE emp_id = e.emp_id AND a.date BETWEEN start_date AND end_date
//       )
//   `);
//     // AND a.date BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE() - INTERVAL 1 DAY

//     console.log(absentees);
//     console.log("Query Finesh");

//     const grouped = {};

//     for (const person of absentees) {
//       // Group by user_id
      
//       if (!grouped[person.user_id]) {
//         grouped[person.user_id] = {
//           name: person.name,
//           email: person.official_mail,
//           dates: []
//         };
//       }
//       grouped[person.user_id].dates.push(person.date);
//     }

//     for (const [user_id, info] of Object.entries(grouped)) {

//       // Check if the employee is present today
//       const [presentToday] = await db.query(`
//     SELECT * FROM attendance
//     WHERE user_id = ? AND date = CURDATE() - INTERVAL 1 DAY AND work_status = 'present'
//   `, [user_id]);

//   console.log(presentToday);

//       // WHERE user_id = ? AND date = CURDATE() - INTERVAL 1 DAY AND work_status = 'present'

//       if (presentToday.length === 0) continue; // Skip if not present today

//       // Check already notified
//       const [alreadySent] = await db.query(`
//     SELECT 1 FROM unauthorized_leaves
//     WHERE user_id = ? AND date IN (?)
//   `, [user_id, info.dates]);

//       if (alreadySent.length > 0) continue; // Skip if already notified

//       // Sort dates
//       const sortedDates = info.dates.sort();

//       // Group into ranges
//       const ranges = [];
//       let start = sortedDates[0];
//       let end = sortedDates[0];

//       for (let i = 1; i < sortedDates.length; i++) {
//         const current = sortedDates[i];
//         const prev = new Date(end);
//         prev.setDate(prev.getDate() + 1);

//         if (new Date(current).toISOString().split('T')[0] === prev.toISOString().split('T')[0]) {
//           end = current;
//         } else {
//           ranges.push(start === end ? start : `${start} to ${end}`);
//           start = end = current;
//         }
//       }

//       function formatDate(dateStr) {
//         const date = new Date(dateStr);
//         const day = String(date.getDate()).padStart(2, '0');
//         const month = String(date.getMonth() + 1).padStart(2, '0');
//         const year = date.getFullYear();
//         return `${day}-${month}-${year}`;
//       }

//       ranges.push(
//         start === end
//           ? formatDate(start)
//           : `${formatDate(start)} to ${formatDate(end)}`
//       );


//       // Email body
//       const msg = {
//         // to: info.official_mail,
//         to: "hayenoj860@axcradio.com",
//         subject: "Unauthorized Leave Warning",
//         html: `<p>Dear ${info.name},<br>You were absent on: <b>${ranges.join(', ')}</b> without applying leave. Kindly apply within 2 days or it will be auto-rejected.</p>`
//       };

//       await transporter.sendMail(msg);
//       console.log(`Email sent to ${info.name}`);

//       // Insert all unauthorized dates
//       for (const date of info.dates) {
//         await db.query(`
//       INSERT IGNORE INTO unauthorized_leaves (user_id, date, email_sent, email_sent_at, resolved)
//       VALUES (?, ?, 1, NOW(), 1)
//     `, [user_id, date]);
//       }
//     }

//     // 👉 Call auto-reject function here after processing
//     await autoRejectLeaves();
//     console.log("Auto reject process done.");
//   } catch (err) {
//     console.log('ERROR : ' + err)
//   }
//   finally {
//     await db.end();
//   }
// })();


// async function autoRejectLeaves() {
//   const [rows] = await db.query(`
//     SELECT ul.*, e.emp_id FROM unauthorized_leaves ul
//     JOIN employees e ON ul.user_id = e.attendance_id
//     WHERE ul.resolved = 1 AND ul.email_sent = 1
//       AND DATE(ul.email_sent_at) <= CURDATE() - INTERVAL 2 DAY
//   `);

//   for (const row of rows) {
//     const [leaveCheck] = await db.query(`
//       SELECT * FROM leave_request
//       WHERE emp_id = ? AND ? BETWEEN start_date AND end_date
//     `, [row.emp_id, row.date]);

//     if (leaveCheck.length > 0) {
//       // Leave was applied → Mark resolved
//       await db.query(`
//     UPDATE unauthorized_leaves SET resolved = 0
//     WHERE user_id = ? AND date = ?
//   `, [row.user_id, row.date]);
//       console.log(`Leave applied later. Resolved set to 0 for user_id ${row.user_id} on ${row.date}`);
//       continue; // Skip auto-rejection
//     }

//     if (leaveCheck.length === 0) {
//       // Auto-reject
//       await db.query(`
//         INSERT INTO leave_request (emp_id, start_date, end_date, leave_type, reason, status, total_num_leaves, balence_leave, hold_balence_leave, updated_at)
//         VALUES (?, ?, ?, 'casual leave', 'Auto-rejected: No leave applied after 2 days', 'rejected', 1, 0, 0, NOW())
//       `, [row.emp_id, row.date, row.date]);

//       await db.query(`
//         INSERT IGNORE INTO tbl_lop (lop_user, lop_date, lop_total, lop_created_at)
//         VALUES (?, ?, 1, NOW())
//       `, [row.emp_id, row.date]);

//       await db.query(`
//         UPDATE unauthorized_leaves SET resolved = 0 WHERE user_id = ? AND date = ?
//       `, [row.user_id, row.date]);

//       console.log("Auto Rejected Sucessfully");
//     }
//   }
// }

// // Schedule daily at 10 AM
// // cron.schedule('0 10 * * *', async () => {
// //   await sendWarningEmails();
// //   await autoRejectLeaves();
// //   console.log("Leave processing done at", new Date());
// // });


const mysql = require('mysql2/promise');
const nodemailer = require('nodemailer');
const cron = require('node-cron');

const db = mysql.createPool({
  host: 'localhost',
  user: 'root',
  password: 'root',
  database: 'gighz'
});

const transporter = nodemailer.createTransport({
  host: 'smtp.hostinger.com',
  port: 587,
  secure: false, // TLS - should be false for port 587
  auth: {
    user: 'itsupport@gighz.net',
    pass: 'GigHz123#'
  },
  tls: {
    rejectUnauthorized: false // <-- disables certificate validation
  }
});

(async () => {

  try {

    console.log('started..');
    const [absentees] = await db.query(`
    SELECT a.user_id, a.date, e.official_mail, e.name
    FROM attendance a
    JOIN attendance_users u ON u.user_id = a.user_id
    JOIN employees e ON e.emp_id = u.emp_id
    WHERE a.work_status = 'NA'
      AND a.total_hours = '00:00'
    AND a.date BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE() - INTERVAL 1 DAY
      AND NOT EXISTS (
        SELECT 1 FROM leave_request
        WHERE emp_id = e.emp_id AND a.date BETWEEN start_date AND end_date
      )
  `);
    // AND a.date BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE() - INTERVAL 1 DAY

    console.log(absentees);
    console.log("Query Finesh");

    const grouped = {};

    for (const person of absentees) {
      // Group by user_id
      
      if (!grouped[person.user_id]) {
        grouped[person.user_id] = {
          name: person.name,
          email: person.official_mail,
          dates: []
        };
      }
      grouped[person.user_id].dates.push(person.date);
    }

    for (const [user_id, info] of Object.entries(grouped)) {
      // Check if the employee is present today
      const [presentToday] = await db.query(`
    SELECT * FROM attendance
    WHERE user_id = ? AND date = CURDATE() - INTERVAL 1 DAY AND work_status = 'present'
  `, [user_id]);

  console.log(presentToday);

      // WHERE user_id = ? AND date = CURDATE() - INTERVAL 1 DAY AND work_status = 'present'

      if (presentToday.length === 0) continue; // Skip if not present today

      // Check already notified
      const [alreadySent] = await db.query(`
    SELECT 1 FROM unauthorized_leaves
    WHERE user_id = ? AND date IN (?)
  `, [user_id, info.dates]);

      if (alreadySent.length > 0) continue; // Skip if already notified

      // Sort dates
      const sortedDates = info.dates.sort();

      // Group into ranges
      const ranges = [];
      let start = sortedDates[0];
      let end = sortedDates[0];

      for (let i = 1; i < sortedDates.length; i++) {
        const current = sortedDates[i];
        const prev = new Date(end);
        prev.setDate(prev.getDate() + 1);

        if (new Date(current).toISOString().split('T')[0] === prev.toISOString().split('T')[0]) {
          end = current;
        } else {
          ranges.push(start === end ? start : `${start} to ${end}`);
          start = end = current;
        }
      }

      function formatDate(dateStr) {
        const date = new Date(dateStr);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
      }

      ranges.push(
        start === end
          ? formatDate(start)
          : `${formatDate(start)} to ${formatDate(end)}`
      );


      // Email body
      const msg = {
         to: info.official_mail,
        // to: "hayenoj860@axcradio.com",
        subject: "Unauthorized Leave Warning",
        html: `<p>Dear ${info.name},<br>You were absent on: <b>${ranges.join(', ')}</b> without applying leave. Kindly apply within 2 days or it will be auto-rejected.</p>`
      };

      await transporter.sendMail(msg);
      console.log(`Email sent to ${info.name}`);

      // Insert all unauthorized dates
      for (const date of info.dates) {
        await db.query(`
      INSERT IGNORE INTO unauthorized_leaves (user_id, date, email_sent, email_sent_at, resolved)
      VALUES (?, ?, 1, NOW(), 1)
    `, [user_id, date]);
      }
    }

    // 👉 Call auto-reject function here after processing
    await autoRejectLeaves();
    console.log("Auto reject process done.");
  } catch (err) {
    console.log('ERROR : ' + err)
  }
  finally {
    await db.end();
  }
})();


async function autoRejectLeaves() {
  const [rows] = await db.query(`
    SELECT ul.*, e.emp_id FROM unauthorized_leaves ul
    JOIN employees e ON ul.user_id = e.attendance_id
    WHERE ul.resolved = 1 AND ul.email_sent = 1
      AND DATE(ul.email_sent_at) <= CURDATE() - INTERVAL 2 DAY
  `);

  for (const row of rows) {
    const [leaveCheck] = await db.query(`
      SELECT * FROM leave_request
      WHERE emp_id = ? AND ? BETWEEN start_date AND end_date
    `, [row.emp_id, row.date]);

    if (leaveCheck.length > 0) {
      // Leave was applied → Mark resolved
      await db.query(`
    UPDATE unauthorized_leaves SET resolved = 0
    WHERE user_id = ? AND date = ?
  `, [row.user_id, row.date]);
      console.log(`Leave applied later. Resolved set to 0 for user_id ${row.user_id} on ${row.date}`);
      continue; // Skip auto-rejection
    }

    if (leaveCheck.length === 0) {
      // Auto-reject
      await db.query(`
        INSERT INTO leave_request (emp_id, start_date, end_date, leave_type, reason, status, total_num_leaves, balence_leave, hold_balence_leave, updated_at)
        VALUES (?, ?, ?, 'casual leave', 'Auto-rejected: No leave applied after 2 days', 'rejected', 1, 0, 0, NOW())
      `, [row.emp_id, row.date, row.date]);

      await db.query(`
        INSERT IGNORE INTO tbl_lop (lop_user, lop_date, lop_total, lop_created_at)
        VALUES (?, ?, 1, NOW())
      `, [row.emp_id, row.date]);

      await db.query(`
        UPDATE unauthorized_leaves SET resolved = 0 WHERE user_id = ? AND date = ?
      `, [row.user_id, row.date]);

      console.log("Auto Rejected Sucessfully");
    }
  }
}

// Schedule daily at 10 AM
// cron.schedule('0 10 * * *', async () => {
//   await sendWarningEmails();
//   await autoRejectLeaves();
//   console.log("Leave processing done at", new Date());
// });
