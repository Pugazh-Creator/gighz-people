<link rel="stylesheet" href="<?= base_url('asset/css/attendancelogs.css') ?>">


<?php /// view('navbar/sidebar') 
?>
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

        <div class="col-md-3 mt-4">
            <button type="submit" class="btn btn-primary">View</button>
        </div>
    </div>
</form>
<div class="attendance-container">
    <div class="log-table-container employee-attendance">

        <table class="log-table ">
            <thead class="log-table-head">
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Hours</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance as $userId => $row): ?>
                    <?php foreach (array_reverse($dates) as $date): ?>
                        <tr>
                            <td style="background :none; color:black;"><?= date('d-m-Y', strtotime($date)) ?></td>
                            <td style="background :none; color:black;"><?= $row['records'][$date]['status'] ?></td>
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
    <div class="attendace-data">
        <?php foreach ($attendance as $userId => $row): ?>
            <div class="employee-total-hours">
                <h3>Total Working Hours</h3> |
                <p><?= $row['totalOEHours'] ?></p>
            </div>
            <div class="attendance-details">
                <div>
                    <h3>Present Days</h3> |
                    <p><?= $row['presentDays'] ?></p>
                </div>
                <div>
                    <h3>Compensation</h3> |
                    <p><?= $row['compensation'] ?></p>
                </div>
                <div>
                    <h3>Leaves Not Applied</h3> |
                    <p><?= $row['not_applied'] ?></p>
                </div>
                <div>
                    <h3>Approved Leaves</h3> |
                    <p><?= $row['approved_leave'] ?></p>
                </div>
                <div>
                    <h3>Rejected Leaves</h3> |
                    <p><?= $row['rejected_leave'] ?></p>
                </div>
                <div>
                    <h3>Other Saturdays</h3> |
                    <p><?= $row['otherSaterday'] ?></p>
                </div>
                <div>
                    <h3>On Duty</h3> |
                    <p><?= $row['od'] ?></p>
                </div>
                <div>
                    <h3>Work From Home</h3> |
                    <p><?= $row['wfh'] ?></p>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
</div>
<!-- Overlay -->
<div class="overlay"></div>

<!-- Modal -->
<div class="modal-box" id="punchModal">
    <span class="btn-close">✖</span>
    <h3>Punch Details</h3>
    <p class="modelUserIdABioType"><span><strong>User ID:</strong> <span id="modalUserId"></span></span></p>
    <p><strong>User Name:</strong> <span id="modalUserName"></span></p>
    <p><strong>Date:</strong> <span id="modalDate"></span></p>

    <h4>Punch Records: </h4>
    <ul id="punchRecords"></ul>
</div>

<script>
    $(document).ready(function() {
        // Open Modal
        $(".punch-time").click(function(e) {
            e.preventDefault();
            $(".overlay, #punchModal").fadeIn(); // Fixed typo: .overlay
            console.log('Showing modal function working');
            let text = $(this).text();
            if (text.includes("Absent")) {
                $(this).css("color", "#fff");
            } else if (text.includes("Error")) {
                $(this).css("color", "#fff");
            }

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
                    console.log('Get punch records function working');

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
    $(".overlay, .btn-close").click(function() {
        $(".overlay, #punchModal").fadeOut();
    });
</script>