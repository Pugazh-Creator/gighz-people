<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <script src="https://cdn.socket.io/4.5.0/socket.io.min.js"></script>
    <script>
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    console.log('Notification permission granted.');
                }
            });
        }
        const socket = io('http://localhost:3000'); // Replace with your server URL

        // Listen for notifications
        socket.on('notifyHR', (data) => {
            console.log('Notification for HR:', data);
            new Notification(data.message, {
                body: data.details,
                icon: '<?= base_url('asset/images/gighzlogo.jpg') ?>'
            });
        });

        socket.on('notifyEmployee', (data) => {
            console.log('Notification for Employee:', data);
            new Notification(data.message, {
                body: data.details,
                icon: '<?= base_url('asset/images/gighzlogo.jpg') ?>'
            });
        });
    </script>
</body>

</html>