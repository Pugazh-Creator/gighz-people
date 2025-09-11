const { exec } = require('child_process');
const path = require('path');
const fs = require('fs');
const cron = require('node-cron');

// ================== CONFIGURATION ==================
const dbConfigs = [
    {
        name: 'u853418576_Test',
        user: 'u853418576_Test',
        pass: 'Gighz@_1',
        host: 'srv827.hstgr.io',
        backupDir: 'C:\\Users\\magendiran\\Downloads\\backup\\timesheet',
        MyName: 'TimeSheet'
    },
    {
        name: 'gighz',
        user: 'root',
        pass: 'root',
        host: 'localhost',
        backupDir: 'C:\\Users\\magendiran\\Downloads\\backup\\webapp',
        MyName: 'LocalWebApp'
    }
];

const lastRunFile = path.join(__dirname, 'last_backup.txt');
// ===================================================

function hasAlreadyRunToday() {
    if (!fs.existsSync(lastRunFile)) return false;

    const lastRunDate = fs.readFileSync(lastRunFile, 'utf-8').trim();
    const today = new Date().toISOString().split('T')[0];

    return lastRunDate === today;
}

function markTodayAsRun() {

    const today = new Date().toISOString().split('T')[0];
    fs.writeFileSync(lastRunFile, today, 'utf-8');
}

function takeBackup() {
    if (hasAlreadyRunToday()) {
        console.log('⏳ Backup already taken today. Skipping...');
        return;
    }
    try {

        console.log("🚀 Starting backup process...");
        const now = new Date();
        const formattedTime = now.toISOString().replace(/[:.]/g, '-');

        dbConfigs.forEach(config => {
            const { name, user, pass, host, backupDir, MyName } = config;

            if (!fs.existsSync(backupDir)) {
                fs.mkdirSync(backupDir, { recursive: true });
                console.log(`📁 Created directory: ${backupDir}`);
            }

            const fileName = `backup-${MyName}-${formattedTime}.sql`;
            const filePath = path.join(backupDir, fileName);

            const command = `mysqldump -h ${host} -u ${user} -p${pass} ${name} > "${filePath}"`;

            exec(command, (error) => {
                if (error) {
                    console.error(`❌ Backup failed for ${name}: ${error.message}`);
                    return;
                }
                console.log(`✅ Backup successful for ${name}: ${filePath}`);
            });
        });

        // Mark backup as done for today
        markTodayAsRun();
    } catch (error) {
        console.log(error);
    }finally{
        
    }
}

// Run backup immediately when script starts
takeBackup();

// Optional: Keep checking every hour — backup will only trigger once a day
// cron.schedule('0 * * * *', () => {
//     console.log(`[${new Date().toLocaleString()}] ⏰ Checking if backup needed...`);
//     takeBackup();
// });
