const mysql = require('mysql2/promise');

async function updateDatabase() {
    const connection = await mysql.createConnection({
        host: 'localhost',
        user: 'root',
        password: 'root',
        database: 'gighz'
    });

    try {
        // Get current OE period (Assuming a function fetches this from LeaveRequestModel)
        console.log('start')
        const oePeriod = getCurrentOE();
        // Example usage

        // Get all employees
        const [employees] = await connection.execute('SELECT emp_id, remaining_leaves FROM employees');
        const [rows] = await connection.execute('SELECT * FROM function_updates WHERE id = 1');

        const date2 = rows[0].updated_at;

        const date1 = new Date();

        const diffTime = Math.abs(date2 - date1); // Difference in milliseconds
        let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); // Convert to days

        console.log(`difference between the date is ${diffDays}`);

        if (diffDays < 24) {
            console.log('OE is not ended');
            return
        }

        for (const employee of employees) {
            let availableLeave = employee.remaining_leaves;
            let newLeave;
            if (new Date().toISOString().slice(5, 10) === '12-24') {
                // If it's December 25, save leave history

                // console.log('Year Update '+newLeave);
                const year = new Date().getFullYear();
                await connection.execute(
                    'INSERT INTO leave_history (emp_id, balence, year) VALUES (?, ?, ?)',
                    [employee.emp_id, availableLeave, year]
                );
                getSaturdays(year + 1);
                newLeave = 1; // Reset leave at year-end
            } else if (oePeriod % 3 === 0) {
                newLeave = availableLeave + 2; // Add 2 leave every 3rd month
                console.log('3rd month Update ' + newLeave);
            } else {
                newLeave = availableLeave + 1; // Add 1 leave normally
                console.log('casual  Update ' + newLeave);
            }

            // Update the employee's available leave
            await connection.execute(
                'UPDATE employees SET remaining_leaves = ? WHERE emp_id = ?',
                [newLeave, employee.emp_id]
            );
            await connection.execute(
                'UPDATE function_updates SET updated_at = ? WHERE id = 1',
                [date1]
            );
        }

        console.log("Database updated successfully!");





    } catch (error) {
        console.error("Error updating database:", error);
    } finally {
        await connection.end();
        console.log('✅ MySQL disconnected successfully...');
    }
}

function getCurrentOE() {

    const today = new Date();

    const currentYear = today.getFullYear();
    const currentMonth = today.getMonth() + 1; // JavaScript months are 0-based, so add 1

    // Define the start date (25th of the current month) and end date (24th of the next month)
    const startDate = new Date(currentYear, currentMonth - 1, 25); // 25th of current month
    const endDate = new Date(currentYear, currentMonth, 24); // 24th of next month

    // Special case: If today is December 25th (any year), return OE period as 1
    if (today.getMonth() === 11 && today.getDate() >= 25) {
        return 1; // OE period is 1 on December 25th
    }

    // if (today.getMonth() === 1 && today.getDate() <= 24) {
    //     return 1; // OE period is 1 on December 25th
    // }


    // Check if today's date is within the range (25th of current month to 24th of next month)
    if (today >= startDate && today <= endDate) {
        return currentMonth + 1; // Return the next month (OE period)
    }
    // If today is before the 25th of the current month, return the current month as OE period
    return currentMonth;
}



async function getSaturdays(year) {

    const connection = await mysql.createConnection({
        host: 'localhost',
        user: 'root',
        password: 'root',
        database: 'gighz'
    });

    try {

        for (let month = 0; month < 12; month++) {
            let saturdays = [];

            for (let day = 1; day <= 31; day++) {
                let date = new Date(year, month, day);
                if (date.getMonth() !== month) break; // Stop if next month starts
                if (date.getDay() === 6) { // Saturday
                    saturdays.push(day);
                }
            }

            let thirdSaturday = saturdays[2]; // Correct Saturday date
            let thirdDate = new Date(Date.UTC(year, month, thirdSaturday)).toISOString().split('T')[0];

            let selectedSaturdayIndex = saturdays.length >= 5 ? 4 : 3;
            let selectedSaturday = saturdays[selectedSaturdayIndex];
            let selectdate = new Date(Date.UTC(year, month, selectedSaturday)).toISOString().split('T')[0];

            const weekday = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
            const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

            await connection.execute('INSERT INTO company_holiday (holiday_date, holiday_name, month, day, holiday_type) VALUES (?, ?, ?, ?, ?)',
                [thirdDate, 'other_saturday', months[new Date(thirdDate).getMonth()], weekday[new Date(thirdDate).getDay()], 'other_saturday']
            );

            await connection.execute('INSERT INTO company_holiday (holiday_date, holiday_name, month, day, holiday_type) VALUES (?, ?, ?, ?, ?)',
                [selectdate, 'other_saturday', months[new Date(selectdate).getMonth()], weekday[new Date(selectdate).getDay()], 'other_saturday']
            );

            console.log('✅ Satuday Leaves Updated Successfully...');


            // result[month + 1] = [firstDaterday, thirdSaturday, selectedSaturday];
        }
    }
    catch (error) {
        console.log('❌ Saturday Leave Updated Fail : ' + error);
    }

}


// Run the function

updateDatabase()



