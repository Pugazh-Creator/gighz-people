<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('asset/css/sidebar.css') ?>">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="<?= base_url('asset/images/favicon.png') ?>">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <title>Document</title>
    <style>
        .popup-message {
            display: none;
            position: fixed;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            padding: 15px 25px;
            border-radius: 6px;
            font-size: 16px;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
            min-width: 300px;
            text-align: center;
        }

        .popup-message.show {
            display: block;
            opacity: 1;
        }

        .popup-success {
            background-color: #28a745;
            /* Bootstrap success green */
        }

        .popup-error {
            background-color: #da2442;
            /* Custom error red */
        }

        textarea {
            resize: vertical;
            padding: 5px 10px;
        }

        .overglow {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: #cacaca4f;
            z-index: 100001;
        }
    </style>

</head>

<body>

    <nav class="sidebar">
        <header>
            <span class="image_logo">
                <img src="<?= base_url('asset/images/favicon.png') ?>" alt="gighz the PCB design company">
            </span>
            <div class="cnt-btn">
                <button id="toggleSidebar"><i class='bx bx-chevron-left'></i></button>
                <button id="show-btn" style="display: none;"><ion-icon name="chevron-forward"></ion-icon></button>
            </div>
        </header>
        <ul class="main-menu">
            <a class="menu a5" href="<?= site_url('/dashboard') ?>">
                <li>
                    <i class='bx bxs-dashboard'></i>
                    <span>Dashboard</span>
                </li>
            </a>
            <?php if (session()->get('role') == 1) : ?>
                <a class="menu a2" href="<?= site_url('dashboard/hr') ?>">
                    <li>
                        <i class='bx bxs-user'></i>
                        <span>HR</span>
                    </li>
                </a>
            <?php elseif (session()->get('role') == 11) : ?>
                <a class="menu a2" href="<?= site_url('dashboard/hr') ?>">
                    <li>
                        <i class='bx bxs-user'></i>
                        <span>HR</span>
                    </li>
                </a>
            <?php endif; ?>
            <a class="menu a3" href="<?= site_url('dashboard/applyLeave') ?>">
                <li>
                    <i class='bx bxs-send'></i>
                    <span>Apply</span>
                </li>
            </a>
            <a class="menu a4" href="<?= site_url('empleaves') ?>">
                <li>
                    <i class='bx bxs-pie-chart-alt-2'></i>
                    <span>My leaves</span>
                </li>
            </a>
            <a class="menu a4" href="<?= site_url('/compensation') ?>">
                <li>
                    <i class='bx bxs-customize'></i>
                    <span>Compensation</span>
                </li>
            </a>
            <a class="menu a4" href="<?= site_url('/employeecontroller/getmypermission') ?>">
                <li>
                    <i class='bx bxs-customize'></i>
                    <span>Permission</span>
                </li>
            </a>
            <a class="menu a4" href="<?= site_url('/companyHoliday') ?>">
                <li>
                    <i class='bx bxs-calendar'></i>
                    <span>Holidays</span>
                </li>
            </a>
            <a class="menu a4" href="http://192.168.0.29:8081/demos/dashboard">
                <li>
                    <i class='bx bxs-calendar'></i>
                    <span>Timesheet</span>
                </li>
            </a>
            <a class="menu a4" href="<?= base_url() ?>/hrms/index">
                <li>
                    <i class='bx bxs-calendar'></i>
                    <span>HRMS</span>
                </li>
            </a>

            <!-- ----------------------- 11-08-2025----------------------------- -->
            <a class="menu a4" href="<?= site_url('/hrms/assets') ?>">
                <li>
                    <i class="bi bi-archive"></i>
                    <span>Assets</span>
                </li>
            </a>

            <a class="menu a4" href="<?= site_url('/hrms/exitpage') ?>">
                <li>
                    <i class="bi bi-escape"></i>
                    <span>Exit Interview</span>
                </li>
            </a>
            <!-- ---------------------------------------------------- -->

            <?php
            $today = date('Y-m-d');
            $startDate = date('Y-m') . "-02";
            $enddate = date('Y-m') . "-15";

            if ($today >= $startDate && $today <= $enddate):
            ?>
                <a class="menu a4" href="http://192.168.0.29:8081/demos/dashboard">
                    <li>
                        <i class='bx bxs-calendar'></i>
                        <span>pay Roll</span>
                    </li>
                </a>
            <?php endif; ?>
        </ul>
        <ul class="logout_container">
            <a class="menu" href="<?= site_url('auth/logout') ?>">
                <li class="logout">
                    <i class='bx bxs-exit'></i>
                    <span>Log Out</span>
                </li>
            </a>
        </ul>
        <ul class="version">
            <li class="verion-a">App Version <?= session('version') ?></li>
            <span class="version-b">v<?= session('version') ?></span>
        </ul>
    </nav>

    <header class="sidebar-header">

        <div class="log-name">
            <h3><?= session()->get('name') ?></h3>
        </div>
    </header>

    <div id="statusPopup" class="popup-message">
        <span id="popupText"></span>
    </div>

    <div class="overglow"></div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#toggleSidebar').click(function(e) {
                e.preventDefault();
                $('.bx-chevron-left').toggleClass('bx-chevron-right');
                $('.sidebar').toggleClass('expanded');
            });
        });

        // notification popup
        function showPopup(message, type = 'success') {
            const popup = $('#statusPopup');

            // Clear previous state
            popup.removeClass('popup-success popup-error');

            // Add correct class
            if (type === 'error') {
                popup.addClass('popup-error');
            } else {
                popup.addClass('popup-success');
            }

            // Show message
            $('#popupText').text(message);
            popup.addClass('show');

            // Auto-hide after 3 seconds
            setTimeout(() => {
                popup.removeClass('show');
            }, 3000);
        }
    </script>

</body>

</html>