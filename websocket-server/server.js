const express = require("express");
const http = require("http");
const socketIo = require("socket.io");
const cors = require("cors");

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: {
        origin: "*",  // Allow all origins
        methods: ["GET", "POST"]
    }
});

app.use(cors());

io.on("connection", (socket) => {
    console.log("A user connected:", socket.id);

    socket.on("send_leave_request", (data) => {
        io.emit("notify_hr", data); // Notify HR
    });

    socket.on("leave_status_update", (data) => {
        io.emit("notify_employee", data); // Notify Employee
    });

    socket.on("disconnect", () => {
        console.log("User disconnected:", socket.id);
    });
});

server.listen(3000, () => {
    console.log("WebSocket Server running on port 3000");
});