<?php
$present_days = '';
$absent_days = '';
$sortfall = '';
foreach ($attendance as $userId => $row) {
    $present_days = $row['presentDays'];
    $absent_days = $row['absentDays'];
    $sortfall = $row['sortfall'];
}
?>
<div class="dashboard">
    <div class="top">
        <div class="attendance">
            <div class="attendance-child ac1">
                <div class="eff1">
                    <span>
                        <h5>Present</h5>
                        <span id="attendance-present" class="attendance-values values">00</span>
                    </span>
                    <span>
                        <img src="<?= base_url("asset/icons/workplace-icon.png") ?>" alt="" width="40%">
                    </span>
                </div>
                <div class="eff1">
                    <span>
                        <h5>Absent</h5> <span id="attendance-absent" class="attendance-values values">00</span>
                    </span>
                    <span>
                        <img src="<?= base_url("asset/icons/leave-notice.png") ?>" alt="">
                    </span>
                </div>
                <div class="eff1">
                    <span>
                        <h5>Last Working Hrs</h5><span id="attendance-last-worked-hrs" class="attendance-values values">00:00</span>
                    </span>
                </div>
            </div>
            <div class="attendance-child ac2">
                <div class="eff1">
                    <h5>Short Fall</h5>
                    <span id="attendance-shoet-fall" class="attendance-values values">00:00</span>
                    <span>
                        <img src="<?= base_url("asset/icons/soil-clack.png") ?>" alt="">
                    </span>
                </div>
                <div class="eff1"><a href="<?= base_url()?>dashboard/getEmployeesAttendance">View More</a></div>
            </div>
        </div>
        <div class="dashboard-reports">
            <div class="apply-status">
                <div class="eff1 leave-box">
                    <img src="<?= base_url("asset/icons/absent.png") ?>" alt="">
                    <h5>Leave</h5>
                </div>
                <div class="eff1 compensation-box">
                    <img src="<?= base_url("asset/icons/compensation.png") ?>" alt="">
                    <h5>Compensation</h5>
                </div>
                <div class="eff1 Permission-box">
                    <img src="<?= base_url("asset/icons/permission.png") ?>" alt="">
                    <h5>Permission</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-section2">
        <div class="dashboard-notification">
            <div class="notification-header">
                <h4>Notifications</h4>
            </div>
            <div class="notification-body">
                <img src="<?= base_url("asset/icons/comming-soon.png") ?>" alt="" width="250">
                <p>Comming Soon</p>
            </div>
        </div>
        <div class="dashboard-version">
            <h4>What's we Updated</h4>
            <div class="versions" id="version-cont">

            </div>
        </div>

        <div class="dashbord-work-report">
            <div class="eff1">
                <img src="<?= base_url("asset/icons/working.png") ?>" alt="" width="250">
                <div>
                    <span>Worked Hrs</span>
                    <div id="total-worked-hrs" class="report-value values ">00:00</div>
                </div>
            </div>
            <div class="eff1">
                <img src="<?= base_url("asset/icons/rr.png") ?>" alt="" width="250">
                <div>
                    <span>R&R</span>
                    <div id="total-rr-hrs" class="report-value values ">00:00</div>
                </div>
            </div>
            <div class="eff1">
                <img src="<?= base_url("asset/icons/general.png") ?>" alt="" width="250">
                <div>
                    <span>General</span>
                    <div id="total-general-hrs" class="report-value values ">00:00</div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $.ajax({
        url: baseurl + "dashboard/dashboardDatas",
        method: 'POST',
        success: function(res) {
            console.log(res)

            const attendance = res.Attendance;
            const Permission = res.Permission;
            const compensation = res.compensation;
            console.log(compensation);
            const leave = res.leave;
            const versions = res.versions;


            // $('.values').text('');

            $('#attendance-present').text(attendance.presentDays);
            $('#attendance-absent').text(attendance.absentDays);
            $('#attendance-shoet-fall').text(attendance.sortfall);
            $('#attendance-last-worked-hrs').text(res.records);

            $('#total-worked-hrs').text(attendance.totalOEHours);
            $('#total-rr-hrs').text(res.rr);
            $('#total-general-hrs').text(res.general);

            $("#version-cont").empty();

            let version = '';
            versions.forEach(v => {
                version += `
                     <div class="eff1">
                    <span>${v.version}</span>
                    <p>${v.version_details}</p>
                </div>
                `;

            });
            $("#version-cont").append(version);

            if (leave != null) {
                if (leave.status == "approved") {
                    $('.leave-box').css({
                        'background': '#7ad798ff',
                        // 'color': '#fff',
                        // 'font-weight': 'bold'
                    });
                } else if (leave.status == "rejected") {
                    $('.leave-box').css({
                        'background': '#dc7086ff',
                        // 'color': '#fff',
                        // 'font-weight': 'bold'
                    });
                } else if (leave.status == "pending") {
                    $('.leave-box').css({
                        'background': '#d7ce82ff',
                        // 'color': '#fff',
                        // 'font-weight': 'bold'
                    });

                } else {
                    $('.leave-box').css({
                        'background': '#ebebeb',

                        // 'font-weight': 'bold'
                    });

                }
            }
            if (compensation != null) {
                if (compensation.status == "approved") {
                    $('.compensation-box').css({
                        'background': '#7ad798ff',
                        // 'color': '#fff',
                        // 'font-weight': 'bold'
                    });
                } else if (compensation.status == "rejected") {
                    $('.compensation-box').css({
                        'background': '#dc7086ff',
                        // 'color': '#fff',
                        // 'font-weight': 'bold'
                    });
                } else if (compensation.status == "pending") {
                    $('.compensation-box').css({
                        'background': '#d7ce82ff',
                        // 'color': '#fff',
                        // 'font-weight': 'bold'
                    });

                } else {
                    $('.compensation-box').css({
                        'background': '#ebebeb',

                        // 'font-weight': 'bold'
                    });

                }
            }
            if (Permission != null) {
                if (Permission.permission_status == "approved") {
                    $('.Permission-box').css({
                        'background': '#7ad798ff',
                        // 'color': '#fff',
                        // 'font-weight': 'bold'
                    });
                } else if (Permission.permission_status == "rejected") {
                    $('.Permission-box').css({
                        'background': '#dc7086ff',
                        // 'color': '#fff',
                        // 'font-weight': 'bold'
                    });
                } else if (Permission.permission_status == "pending") {
                    $('.Permission-box').css({
                        'background': '#d7ce82ff',
                        // 'color': '#fff',
                        // 'font-weight': 'bold'
                    });

                } else {
                    $('.Permission-box').css({
                        'background': '#ebebeb',

                        // 'font-weight': 'bold'
                    });

                }
            }

        },
        error: function(err) {
            console.log(err);
        }
    })
</script>