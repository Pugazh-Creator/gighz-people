<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('asset/css/hr.css') ?>">
    <title>Human Resource</title>
    <style>
        .staff-leaves {
            width: 100%;
            padding: 10px 10px;
        }

        .staff-leaves table {
            background: transparent;
            backdrop-filter: blur(10px);
            border-collapse: collapse;
            max-height: 100%;
        }

        .staff-leaves table th {
            padding: 5px;
            font-size: 18px;
            font-weight: 700;
            max-width: 150px;
            color: var(--white-color--);
            background: #da2442;
            text-align: center;
            font-weight: 500;
            letter-spacing: 1px;
        }

        .staff-leaves table td {
            color: black;
            font-weight: 400;
            font-size: 16px;
            text-align: center;
            padding: 5px;
            border-bottom: 1px solid gray;
        }

        .staff-leaves table tbody tr:nth-last-of-type(even) {
            background-color: rgba(218, 36, 66, 0.22);
        }

        .staff-leaves table tbody tr:last-of-type {
            border-bottom: 2px solid var(--brand-color--);
        }
    </style>
</head>

<body>
    <?= view('navbar/sidebar') ?>

    <section class="container">
        <div class="error-box">
            <?php
            if (!empty(session()->getFlashdata('success'))) {
            ?>
                <div class="msg"><?= session()->getFlashdata('success') ?></div>
            <?php
            } else if (!empty(session()->getFlashdata('fail'))) {
            ?>
                <div class="msg"><?= session()->getFlashdata('fail') ?></div>
            <?php
            }
            ?>
        </div>
        <div class="hr-nav" style="overflow-x: auto; padding: 10px 5px;">
            <a href="<?= base_url('leaveRequests2') ?>">
                <div class="leave-request">
                    <span style="font-size:12px">Leave <br /> Requests</span>
                    <h1><?= esc($pending) ?>/<?= esc($total) ?></h1>
                </div>
            </a>
            </a>
            <a href="<?= base_url('/showCompen') ?>">
                <div class="leave-request ">
                    <p style="font-size:12px">Compensation <br /> Request</p>
                    <h1><?= esc($pendingCompen) ?>/<?= esc($totalCompen) ?></h1>
                </div>
            </a>
            <a href="<?= base_url('/hrcontroller/getallpermission/') ?>">
                <div class="leave-request ">
                    <p style="font-size:12px">Permission</p>
                    <h1><?= esc($per_pending) ?>/<?= esc($per_total) ?></h1>
                    <!-- <ion-icon name="finger-print-outline"></ion-icon> -->
                    <!-- <i class='bx bx-fingerprint' style="font-size: 40px;"></i> -->
                </div>
            </a>
            <a href="<?= base_url('/attendance') ?>">
                <div class="leave-request ">
                    <p style="font-size:12px">Staff</br>Attendance</p>
                    <!-- <ion-icon name="finger-print-outline"></ion-icon> -->
                    <i class='bx bx-fingerprint' style="font-size: 40px;"></i>
                </div>
            </a>
            <a href="<?= base_url('/payrole/employeeAccounting/') ?>">
                <div class="leave-request ">
                    <p style="font-size:12px">Accounting</p>
                    <!-- <ion-icon name="finger-print-outline"></ion-icon> -->
                    <!-- <i class='bx bx-fingerprint' style="font-size: 40px;"></i> -->
                </div>
            </a>
            <a href="<?= base_url('/hrms/appraisal_index/') ?>">
                <div class="leave-request ">
                    <p style="font-size:12px">Appraisal</p>
                    <!-- <ion-icon name="finger-print-outline"></ion-icon> -->
                    <!-- <i class='bx bx-fingerprint' style="font-size: 40px;"></i> -->
                </div>
            </a>
            <a href="<?= base_url('/hrms/employeeRegister/') ?>">
                <div class="leave-request ">
                    <p style="font-size:12px">Employee Register</p>
                    <!-- <ion-icon name="finger-print-outline"></ion-icon> -->
                    <!-- <i class='bx bx-fingerprint' style="font-size: 40px;"></i> -->
                </div>
            </a>
            <a href="<?= base_url('/hrms/career/') ?>">
                <div class="leave-request ">
                    <p style="font-size:12px">Career</p>
                    <!-- <ion-icon name="finger-print-outline"></ion-icon> -->
                    <!-- <i class='bx bx-fingerprint' style="font-size: 40px;"></i> -->
                </div>
            </a>
            <a href="<?= base_url('/hrms/disciplinary/') ?>">
                <div class="leave-request ">
                    <p style="font-size:12px">disciplinary Action</p>
                    <!-- <ion-icon name="finger-print-outline"></ion-icon> -->
                    <!-- <i class='bx bx-fingerprint' style="font-size: 40px;"></i> -->
                </div>
            </a>
            <a href="<?= base_url('/hrms/policyManagementPage/') ?>">
                <div class="leave-request ">
                    <p style="font-size:12px">Policy</p>
                    <!-- <ion-icon name="finger-print-outline"></ion-icon> -->
                    <!-- <i class='bx bx-fingerprint' style="font-size: 40px;"></i> -->
                </div>
            </a>
        </div>

        <div class="staff-leaves">
            <h2 class="slc-heading">Staff Leaves | Compensations</h2>
            <table>
                <thead>
                    <tr>

                        <th>ID</th>
                        <th>Name</th>
                        <?php
                        foreach (array_reverse($oe) as $period => $status):
                        ?>
                            <th><?= $period ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data)): ?>
                        <?php foreach ($data as $empId => $details): ?>
                            <tr>
                                <td><?= htmlspecialchars($empId) ?></td>
                                <td><?= htmlspecialchars($details['name']) ?></td>
                                <?php foreach (array_reverse($oe) as $period => $status): ?>
                                    <td>
                                        <?php
                                        if (isset($details['records'][$period])):
                                            echo $details['records'][$period]['leaves'] . " | " . $details['records'][$period]['compensation'];
                                        else:
                                            echo "-";
                                        endif;
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= count($oe) + 1 ?>">No data available</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>


    <?= view('notification') ?>





    <script src="https://cdnjs.cloudflare.com/ajax/libs/push.js/1.0.6/push.min.js"></script>
    <script>
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

        $(document).ready(function() {
            $('.update-emp-leave').click(function() {
                <?php
                if (!empty(session()->getFlashdata('success'))) {
                ?>
                    alert('<?= session()->getFlashdata('success') ?>')
                <?php
                } else if (!empty(session()->getFlashdata('fail'))) {
                ?>
                    alert('<?= session()->getFlashdata('fail') ?>')
                <?php
                }
                ?>

            })
        })
    </script>
</body>

</html>