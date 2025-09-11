// const express = require('express');
// const ZKLib = require('node-zklib');
// const cors = require('cors');
// const app = express();

// app.use(cors());

// app.get('/fetch', async (req, res) => {
//   const zk = new ZKLib('192.168.0.150', 4370, 10000, 4000); // Replace with your IP

//   try {
//     await zk.createSocket();
//     const logs = await zk.getAttendances();
//     await zk.disconnect();

//     const logData = Array.isArray(logs) ? logs : logs.data || [];

//     const groupedLogs = {};

//     for (const log of logData) {

//       const dateKey = new Date(log.recordTime).toISOString().split('T')[0];

//       if (!groupedLogs[dateKey]) {
//         groupedLogs[dateKey] = [];
//       }

//       groupedLogs[dateKey].push({
//         userSn: log.userSn || null,
//         deviceUserId: log.deviceUserId || null,
//         recordTime: log.recordTime,
//       });
//     }

//     res.json(groupedLogs);
//   } catch (err) {
//     console.error('Biometric fetch error:', err);
//     res.status(500).json({ error: 'Device not reachable or communication failed' });
//   }
// });

// app.listen(3000, () => console.log('Biometric server running on http://localhost:3000'));


const ZKLib = require('node-zklib');

(async () => {
  const zk = new ZKLib('192.168.0.150', 4370, 10000, 4000);
  try {
    await zk.createSocket();
    let logs = await zk.getAttendances(); // changed from const to let

    await zk.disconnect();
    
    // ✅ Print logs as JSON
    console.log(JSON.stringify(logs));
  } catch (err) {
    console.error(JSON.stringify({ error: 'Biometric device is not connected : ' + err }));
    process.exit(1);
  }
})();


