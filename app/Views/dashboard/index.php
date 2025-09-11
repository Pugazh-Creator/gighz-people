
    <link rel="stylesheet" href="<?= base_url('asset/css/index.css') ?>">
    <link rel="icon" href="<?= base_url('asset/images/favicon.png') ?>">
    <style>
        .work-status {
            width: 50%;
            height: 70px;
            display: flex;
            justify-content: start;
            gap: 20px;
            /* background: red; */
            transition: all .2s;
            margin-top: 30px;
            
        }

        .work-status-feilds {
            text-align: center;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: calc(100% / 4);
            padding: 10px 10px 10px 40px;
            border-radius: 10px;
            background: linear-gradient(#da2442a7, #da2442);
            color: rgb(255, 255, 255);
            position: relative;
        }

        .work-status a {
            background: linear-gradient(#da2442a7, #da2442);
            padding: 10px;
            border-radius: 10px;
            color: #fff;
            font-weight: 500;
            height: 30px;
            margin: auto 0; 
        }
        .work-status a:hover{
            background:  linear-gradient(rgba(203, 76, 97, 0.66),rgb(203, 83, 103));
        }

        .versions {
            width: 50%;
        }

        .version-box {
            width: 500px;
            background: linear-gradient(#da2442a7, #da2442);
            border-radius: 10px;
            margin: 10px;
            padding: 10px;
            display: flex;
            justify-content: left;
            gap: 20px;

        }

        .dashboard-body {
            display: flex;
            justify-content: space-between;
            padding: 20px 0;

        }

        .version-box h4 {
            color: rgb(249, 249, 249);
            letter-spacing: 1px;
        }
    </style>

    <!-- <?php // view('navbar/sidebar') ?> -->
        <?php 
            $present_days ='';
            $absent_days = '';
            $sortfall = '';
            foreach ($attendance as $userId => $row){
              $present_days = $row['presentDays'];
              $absent_days = $row['absentDays'];
              $sortfall = $row['sortfall'];
              
            }
        ?>
    <section class="container">
        <h1>Dashboard</h1>
        <div class="work-status">
                <div class="work-status-feilds f1">
                    <p>Present </br><span><?= $present_days ?></span> </p>
                    <!-- <i class='bx bx-briefcase-alt-2'></i> -->
                    <img src="https://cdn3d.iconscout.com/3d/premium/thumb/employees-working-in-business-workspace-3d-illustration-download-png-blend-fbx-gltf-file-formats--analytics-logo-office-desk-data-analysis-pack-illustrations-9627684.png?f=webp" alt="Present" height="70px">

                </div>
                <div class="work-status-feilds f2">
                    <p>Absent </br> <span><?= $absent_days?></span></p>
                    <!-- <i class='bx bx-block'></i> -->
                    <img src="https://cdn3d.iconscout.com/3d/premium/thumb/not-approved-3d-icon-download-in-png-blend-fbx-gltf-file-formats--cross-remove-cancel-business-pack-icons-8858756.png?f=webp" alt="Absent" height="60px">
                </div>
                <div class="work-status-feilds f2">
                    <p>Short fall </br> <span><?= $sortfall?></span></p>
                    <!-- <i class='bx bx-block'></i> -->
                    <!-- <img src="https://cdn3d.iconscout.com/3d/premium/thumb/not-approved-3d-icon-download-in-png-blend-fbx-gltf-file-formats--cross-remove-cancel-business-pack-icons-8858756.png?f=webp" alt="Absent" height="60px"> -->
                </div>
                <a href="employeesAttendanceDetails">View More</a>
        </div>
        <div class="dashboard-body">
            <div class="versions">
                <h3>New Updates</h3>
                <?php foreach ($versions as $version) : ?>
                    <?php
                    $role = session()->get('role');
                    $scope = $version['visible_level'] != 0;
                    $display = '';

                    if ($role == 3 && $scope) {
                        $display = 'display:none';
                    }
                    ?>
                    <div class="version-box" style="<?= $display ?>">
                        <h4><?= $version['version'] ?></h4>
                        <p><?= $version['version_details'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="upcomming">

            </div>
        </div>

    </section>