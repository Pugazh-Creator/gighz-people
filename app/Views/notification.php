<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/push.js/1.0.6/push.min.js"></script>
    <script>
        <?php if(session()->get('role')==1 ||session()->get('role')== 11 || session()->get('role')==10 ) :?>
        // Request permission for notifications
        if (Notification.permission !== "granted") {
            Notification.requestPermission();
        }

        // Function to show notification
        function showNotification(title, message) {
            if (Notification.permission === "granted") {
                new Notification(title, {
                    body: message,
                    icon: '<?= base_url('asset/images/gighzlogo.jpg') ?>'
                });
            }
        }

        // Set an interval to check for new leave requests every minute
        setInterval(function() {
             fetch('<?= base_url('/hr/check_new_leave_requests')?>')
                .then(response => response.json())
                .then(data => {
                    if (data.new_leave_request) {
                        showNotification("New Leave Request", "You have a new leave request from " + data.name);
                    }
                });
        }, 60000); // Check every minute
        <?php endif ;?>
    </script>
</body>

</html>