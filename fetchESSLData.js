const ZKLib = require("node-zklib");

(async () => {
    const zkInstance = new ZKLib("192.168.0.150", 4370, 10000, 4000); // Change IP

    try {
        // Connect to the eSSL device
        await zkInstance.createSocket();
        console.log("✅ Connected to eSSL Biometric Device!");

        // Fetch employee details
        let users = await zkInstance.getUsers();
        console.log("📌 Employee List:", users);

        // Fetch attendance logs
        let datas = await zkInstance.getAttendances();

        // Map attendance logs to user names
        let attendanceData = datas.map(log => {
            let user = users[log.uid] || { name: "Unknown User" };
            return {
                id: log.id,
                name: user.name,
                timestamp: log.timestamp,
                status: log.type // 0 = Check-in, 1 = Check-out
            };
        });

        console.log("📌 Attendance Data with Names:", attendanceData);

        // Disconnect after fetching data
        await zkInstance.disconnect();
    } catch (error) {
        console.error("❌ Error:", error);
    }
})();
