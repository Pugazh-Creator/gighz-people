const xlsx = require('xlsx');
const mysql = require('mysql2/promise');

(async () => {
  const workbook = xlsx.readFile('C:/xampp/htdocs/gighz/websocket-server/node_scripts/tasks.xlsx');
  const sheet = workbook.Sheets[workbook.SheetNames[0]];
  const rows = xlsx.utils.sheet_to_json(sheet);

  const connection = await mysql.createConnection({
    // host: 'srv827.hstgr.io',
    // user: 'u853418576_Test',
    // password: 'Gighz@_1',
    // database: 'u853418576_Test'
    // host: 'srv827.hstgr.io',
    // user: 'u853418576_timesheet',
    // password: 'Gighz@_1',
    // database: 'u853418576_timesheet'
  });

  let currentMainTask = null;
  let maintask_id;

  for (const row of rows) {
    const mainTask = row['Main Tasks']?.trim();
    const subTask = row['Sub Tasks']?.trim();

    // console.log(mainTask);
    // console.log(subTask);

    if (!subTask) continue;

    // Update current main task if present
    if (mainTask) {
      currentMainTask = mainTask;


      // Check if main task already exists
      const [existing] = await connection.execute(
        'SELECT main_task_id FROM tbl_maintask WHERE main_task_name = ? AND dept_main_task = ?',
        [currentMainTask, "8"]
      );
      if (existing.length > 0) {
        console.log(existing[0].main_task_id)
        maintask_id = existing[0].main_task_id;
      }
      if (existing.length === 0) {
        await connection.execute(
          `INSERT INTO tbl_maintask 
          (main_task_name, dept_main_task, task_status, task_project_category, maintask_isactive, task_project_name) 
          VALUES (?, ?, ?, ?, ?, ?)`,
          [currentMainTask, "8", 1, "1", "1", ""]
        );
        console.log(subTask + " " + maintask_id);

        const [existing] = await connection.execute(
          'SELECT main_task_id FROM tbl_maintask WHERE main_task_name = ? AND dept_main_task = ?',
          [currentMainTask, "8"]
        );

        if (existing.length > 0) {
          console.log(existing[0].main_task_id)
          maintask_id = existing[0].main_task_id;
        }

      }
    }

    // Insert subtask
    if (currentMainTask) {
      await connection.execute(
        'INSERT INTO tbl_subtask (maintask_name, subtask_name, subtask_status) VALUES (?, ?, ?)',
        [maintask_id, subTask, "1"]
      );
      console.log(subTask)
    }
  }

  await connection.end();
  console.log('✅ Import completed successfully.');
})();
