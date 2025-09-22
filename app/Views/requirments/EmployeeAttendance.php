<link rel="stylesheet" href="<?= base_url('asset/css/attendancelogs.css') ?>">


<div class="emp_attendance">
    <div class="emp_att-header">
        <div>
            <form method="get" action="<?= site_url('employee-attendance') ?>" class="search-form emp-attendance-search-form">
                <div class="row">
                    <div class="col-md-3">
                        <label for="month" class="form-label">Select OE Month:</label>
                        <select name="month" id="month" class="form-control">
                            <?php foreach ($months as $key => $name) : ?>
                                <option value="<?= $key ?>" <?= ($selectedMonth == $key) ? 'selected' : '' ?>>
                                    <?= $name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="year" class="form-label">Select Year:</label>
                        <select name="year" id="year" class="form-control">
                            <?php foreach ($years as $year) : ?>
                                <option value="<?= $year ?>" <?= ($selectedYear == $year) ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mt-4 ">
                        <button type="submit" class="btn btn-primary gb-btn">View</button>
                    </div>
                </div>
            </form>
        </div>
        <div>
            <?php foreach ($attendance as $userId => $row): ?>
                <div>
                    Worked Hours
                    <span><?= $row['totalOEHours'] ?></span>
                </div>
                |
                <div>
                    Actual Hrs
                    <span><?= $row['actual_work_hrs'] ?></span>
                </div>
        </div>
    </div>
    <div class="attendance-container">
        <div class="attendace-data">

            <div class="attendance-details">
                <div class="eff1">
                    <img src="<?= base_url() ?>asset/icons/monitoring.png" alt="">
                    <div class="">
                        <p>Present Days</p>
                        <p><?= $row['presentDays'] ?></p>
                    </div>
                </div>
                <div class="eff1">
                    <img src="<?= base_url() ?>asset/icons/productivity.png" alt="">
                    <div class="">
                        <p>Compensation</p>
                        <p><?= $row['compensation'] ?></p>
                    </div>
                </div>
                <div class="eff1">
                    <img src="<?= base_url() ?>asset/icons/curriculum-vitae.png" alt="">
                    <div>
                        <p>Leaves Not Applied</p>
                        <p><?= $row['not_applied'] ?></p>
                    </div>
                </div>
                <div class="eff1">
                    <img src="<?= base_url() ?>asset/icons/accepted.png" alt="">
                    <div>
                        <p>Approved Leaves</p>
                        <p><?= $row['approved_leave'] ?></p>
                    </div>
                </div>
                <div class="eff1">
                    <img src="<?= base_url() ?>asset/icons/rejected.png" alt="">
                    <div>
                        <p>Rejected Leaves</p>
                        <p><?= $row['rejected_leave'] ?></p>
                    </div>
                </div>
                <div class="eff1">
                    <img src="<?= base_url() ?>asset/icons/calendar.png" alt="">
                    <div>
                        <p>Other Saturdays</p>
                        <p><?= $row['otherSaterday'] ?></p>
                    </div>
                </div>
                <div class="eff1">
                    <img src="<?= base_url() ?>asset/icons/office.png" alt="">
                    <div>
                        <p>Work From Home</p>
                        <p><?= $row['wfh'] ?></p>
                    </div>
                </div>
                <div class="attendance-last-cont">
                    <div class="alc-1 eff1">
                        <p>Short Fall</p> |
                        <p><?= $row['od'] ?></p>
                    </div>
                    <div class="alc-1 eff1">
                        <p>On Duty</p> |
                        <p><?= $row['od'] ?></p>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
        </div>
        <div class="employee-attendance-section">
            <div class="employee-attendance">
                <table class="log-table ">
                    <thead class="log-table-head">
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Hours</th>
                        </tr>
                    </thead>
                    <tbody class="employee-attendance-tbody">
                        <?php foreach ($attendance as $userId => $row): ?>
                            <?php foreach (array_reverse($dates) as $date): ?>
                                <tr>
                                    <td><?= date('d-m-Y', strtotime($date)) ?></td>
                                    <td><?= $row['records'][$date]['status'] ?></td>
                                    <td>
                                        <?php if (isset($row['records'][$date])): ?>
                                            <?php
                                            $timeRecord = $row['records'][$date]['total'];
                                            ?>
                                            <a href="#" class="punch-time" data-userid="<?= esc($userId) ?>"
                                                data-username="<?= esc($row['name']) ?>"
                                                data-date="<?= esc($date) ?>"
                                                data-time="<?= esc($timeRecord) ?>">
                                                <?= esc($timeRecord) ?>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="punch-detail-box">
                <div id="punch-logs-box">
                    <!-- <span class="btn-close">✖</span> -->
                    <h3>Punch Details</h3>
                    <div>
                        <p class="modelUserIdABioType"><span><strong>Attendance ID:</strong> <span id="modalUserId"></span></span></p>
                        <p><strong>User Name:</strong> <span id="modalUserName"></span></p>
                        <p><strong>Date:</strong> <span id="modalDate"></span></p>

                        <h4>Punch Records: </h4>
                        <ul id="punchRecords">

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Overlay -->
<div class="overlay"></div>

<!-- Modal
<div class="modal-box" id="punchModal">
    <span class="btn-close">✖</span>
    <h3>Punch Details</h3>
    <p class="modelUserIdABioType"><span><strong>User ID:</strong> <span id="modalUserId"></span></span></p>
    <p><strong>User Name:</strong> <span id="modalUserName"></span></p>
    <p><strong>Date:</strong> <span id="modalDate"></span></p>

    <h4>Punch Records: </h4>
    <ul id="punchRecords"></ul>
</div> -->

<script>
    $(document).ready(function() {
        // Open Modal
        $(".punch-time").click(function(e) {
            e.preventDefault();
            // $('.befor-punch-details').fadeOut();
            // $('#punch-logs-box').fadeIn();
            // console.log('Showing modal function working');
            let text = $(this).text();

            var userId = $(this).data("userid");
            var userName = $(this).data("username");
            var date = $(this).data("date");

            $("#modalUserId").text(userId);
            $("#modalUserName").text(userName);
            $("#modalDate").text(date);
            $("#formUserId").val(userId);
            $("#formDate").val(date);

            // Fetch Punch Records
            $.ajax({
                url: "<?= site_url('attendance/getPunchRecords') ?>",
                type: "GET",
                data: {
                    user_id: userId,
                    date: date
                },
                success: function(response) {
                    $("#punchRecords").empty();
                    // console.log('Get punch records function working');

                    response.forEach(function(record) {
                        // Ensure record has id and time properties
                        if (record.id && record.time) {
                            $("#punchRecords").append(`
                                <li>
                                    ${record.time} 
                                </li>
                            `);
                            // console.log(record.time + " " + record.id);
                        } else {
                            console.error("❌ Invalid record:", record);
                        }
                    });

                    // Debugging: Check if buttons are being added
                    // console.log("Delete buttons count:", $(".delete-punches").length);
                },
                error: function(xhr, status, error) {
                    console.error("Failed to fetch punch records:", error);
                }
            });
        });
    });
    // Close Modal
    // $(".overlay, .btn-close").click(function() {
    //     $(".overlay, #punchModal").fadeOut();
    // });
</script>