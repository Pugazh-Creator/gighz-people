<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('asset/css/attendancelogs.css') ?>">


    <title>Staff Biometric Details</title>
</head>

<body>
    <?= view('navbar/sidebar') ?>
    <section class="container biometric" id="MainPageData">
        <h2 class="">Attendance Logs</h2>
        <!-- OE and Year Selection Form -->
        <form method="get" action="<?= site_url('attendance') ?>" class="search-form">
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
                <div class="col-md-3 mt-4">
                    <button type="button" id="upload-biometric" class="btn btn-primary">Upload</button>
                </div>
                <div class="col-md-3 mt-4">
                    <button type="button" id="openRefreashBtn" class="btn btn-primary">Refreach</button>
                </div>
            </div>
        </form>

        <div class="log-table-container">

            <table class="log-table">
                <thead class="log-table-head">
                    <tr>
                        <th>User ID</th>
                        <th>User Name</th>
                        <?php foreach (array_reverse($dates) as $date): ?>
                            <th><?= date('d-m-Y', strtotime($date)) ?></th>
                        <?php endforeach; ?>
                        <th>Present Days</th>
                        <th>Total Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance as $userId => $row): ?>
                        <?php if ($row['emp_id'] != '' && $row['emp_id'] != null) : ?>
                            <tr>
                                <td><?= esc($row['emp_id']) ?></td>
                                <td><?= esc($row['name']) ?></td>

                                <?php foreach (array_reverse($dates) as $date): ?>
                                    <td>
                                        <?php if (isset($row['records'][$date]['workhours'])): ?>
                                            <?php
                                            $timeRecord = $row['records'][$date]['workstatus'];
                                            $timehours = $row['records'][$date]['workhours'];
                                            $worktype = $row['records'][$date]['worktype'];
                                            $color = '';

                                            if (strpos($timeRecord, 'Absent') !== false) {
                                                $color = 'color: #fff; background:#da2442; padding:0px 4px; border-radius:5px;';
                                            } elseif (strpos($timehours, 'Error') !== false) {
                                                $color = 'color: #fff; background:orange; padding:0px 4px; border-radius:5px;';
                                            } elseif (strpos($timeRecord, 'OD') !== false) {
                                                $color = 'color: #fff; background:green; padding:0px 4px; border-radius:5px;';
                                            } elseif (strpos($timeRecord, 'WFH') !== false) {
                                                $color = 'color: #fff; background:orange; padding:0px 4px; border-radius:5px;';
                                            } elseif (strpos($worktype, 'NWM') !== false) {
                                                $color = 'color: #fff; background:black; padding:0px 4px; border-radius:5px;';
                                            } elseif (strpos($worktype, 'Watchman') !== false) {
                                                $color = 'color: black; background:aqua; padding:0px 4px; border-radius:5px;';
                                            }


                                            ?>
                                            <a href="#" class="punch-time" data-userid="<?= esc($userId) ?>"
                                                data-username="<?= esc($row['name']) ?>"
                                                data-date="<?= esc($date) ?>"
                                                data-time="<?= esc($timehours) ?>"
                                                style="<?= $color ?>">
                                                <?= esc($timehours == '00:00' ? $timeRecord : $timehours) ?>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td><?= esc($row['presentDays']); ?></td>
                                <td><?= esc($row['totalOEHours']); ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
                <tbody>
                    <?php foreach ($attendance as $userId => $row): ?>
                        <?php if ($row['emp_id'] == '' || $row['emp_id'] == null) : ?>
                            <tr>
                                <td><?= esc($row['emp_id']) ?></td>
                                <td><?= esc($row['name']) ?></td>

                                <?php foreach (array_reverse($dates) as $date): ?>
                                    <td>
                                        <?php if (isset($row['records'][$date]['workhours'])): ?>
                                            <?php
                                            $timeRecord = $row['records'][$date]['workstatus'];
                                            $timehours = $row['records'][$date]['workhours'];
                                            $worktype = $row['records'][$date]['worktype'];
                                            $color = '';

                                            if (strpos($timeRecord, 'Absent') !== false) {
                                                $color = 'color: #fff; background:#da2442; padding:0px 4px; border-radius:5px;';
                                            } elseif (strpos($timehours, 'Error') !== false) {
                                                $color = 'color: #fff; background:orange; padding:0px 4px; border-radius:5px;';
                                            } elseif (strpos($timeRecord, 'OD') !== false) {
                                                $color = 'color: #fff; background:green; padding:0px 4px; border-radius:5px;';
                                            } elseif (strpos($timeRecord, 'WFH') !== false) {
                                                $color = 'color: #fff; background:orange; padding:0px 4px; border-radius:5px;';
                                            } elseif (strpos($worktype, 'NWM') !== false) {
                                                $color = 'color: #fff; background:black; padding:0px 4px; border-radius:5px;';
                                            } elseif (strpos($worktype, 'Watchman') !== false) {
                                                $color = 'color: black; background:aqua; padding:0px 4px; border-radius:5px;';
                                            }


                                            ?>
                                            <a href="#" class="punch-time" data-userid="<?= esc($userId) ?>"
                                                data-username="<?= esc($row['name']) ?>"
                                                data-date="<?= esc($date) ?>"
                                                data-time="<?= esc($timehours) ?>"
                                                style="<?= $color ?>">
                                                <?= esc($timehours == '00:00' ? $timeRecord : $timehours) ?>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td><?= esc($row['presentDays']); ?></td>
                                <td><?= esc($row['totalOEHours']); ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Overlay -->
        <div class="overlay"></div>

        <!-- Biometric Refrech -->
        <div class="bio-refreach-container" style="display: none;">
            <div class="bio-refreach-header">
                <h2>REFREACH</h2>
            </div>
            <div class="bio-refreach-body">
                <form action="#" id="refreashform">
                    <div class="input-box">
                        <label for="refreach_startdate">Start Date</label>
                        <input type="date" name="refreach_startdate" id="refreach_startdate">
                        <span id="error_refreach_startdate"></span>
                    </div>
                    <div class="input-box">
                        <label for="refreach_enddate">End Date</label>
                        <input type="date" name="refreach_enddate" id="refreach_enddate">
                        <span id="error_refreach_enddate"></span>
                    </div>
                    <button type="submit" class="bio-refreach-btn">submit</button>
                </form>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal-box" id="punchModal">
            <span class="btn-close">✖</span>
            <h3>Punch Details</h3>
            <p class="modelUserIdABioType"><span><strong>User ID:</strong> <span id="modalUserId"></span></span><span><select id="bioType">
                        <option value="1">Normal</option>
                        <option value="2">Feild Tech</option>
                        <option value="3">Genaral</option>
                        <option value="4">Watchman</option>
                        <option value="5">Night shift</option>
                        <option value="6">OD</option>
                        <option value="7">Work From Home</option>
                    </select>
                    <button id="bioTypeBtn"><i class='bx bx-right-arrow-alt'></i></button></span></p>
            <p><strong>User Name:</strong> <span id="modalUserName"></span>

            </p>
            <p><strong>Date:</strong> <span id="modalDate"></span></p>

            <h4>Punch Records: </h4>
            <ul id="punchRecords">
                <input type="number" id="lastrecord-watchman" name="lastrecord">
            </ul>

            <!-- Manual Punch Form -->
            <form id="manualPunchForm">
                <input type="hidden" id="formUserId" name="user_id">
                <input type="hidden" id="formDate" name="date">
                <label for="manualTime">Add Manual Punch:</label>
                <input type="time" id="manualTime" name="manual_time" class="manualTime" required>
                <button type="submit" class="popup-btn">Add Punch</button>
            </form>
        </div>

        <!-- Night shif watchman -->
        <div class="modal-box" id="watchman-punchModal">
            <span class="btn-close">✖</span>
            <h3>Punch Details</h3>
            <p class="modelUserIdABioType"><span><strong>User ID:</strong> <span id="modalUserId-watchman"></span></span>
            <p><strong>User Name:</strong> <span id="modalUserName-watchman"></span>

            </p>
            <p><strong>Date:</strong> <span id="modalDate-watchman"></span></p>

            <h4>Punch Records: </h4>
            <ul id="punchRecords-watchman"></ul>

            <!-- Manual Punch Form -->
            <form id="manualPunchForm-watchman">
                <input type="hidden" id="formUserId-watchman" name="user_id">
                <input type="hidden" id="formDate-watchman" name="date">
                <label for="manualTime-watchman">Add Manual Punch:</label>
                <input type="time" id="manualTime-watchman" name="manual_time-watchman" class="manualTime" required>
                <button type="submit" class="popup-btn">Add Punch</button>
            </form>
            <button id="calculate-watchman-punch" class="popup-btn cal-btn">Calculate</button>
        </div>

        <div id="upload_bio">
            <form action="#" id="upload_bio_form">
                <i class='bx bx-x box-icon-close'></i>
                <h2>Upload Attendance</h2>
                <div class="input-box">
                    <label for="upload_bio_startdate">Start Date<span class="text-danger">*</span>:</label>
                    <input type="date" id="upload_bio_startdate">
                    <div id="error_upload_bio_startdate" class="error-text"></div>
                </div>
                <div class="input-box">
                    <label for="upload_bio_enddate">End Date<span class="text-danger">*</span>:</label>
                    <input type="date" id="upload_bio_enddate">
                    <div id="error_upload_bio_enddate" class="error-text"></div>
                </div>
                <div class="col-md-3 mt-4">
                    <button type="submit" id="upload-biometric-submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            $(document).ready(function() {
                // Open Modal
                $(".punch-time").click(function(e) {
                    e.preventDefault();
                    $(".overlay, #punchModal").fadeIn(); // Fixed typo: .overlay
                    console.log('Showing modal function working');
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
                            response.forEach(function(record, index) {
                                if (record.id && record.time) {
                                    let id = index === response.length - 1 ? `id='lastrecord'` : '';

                                    $("#punchRecords").append(`
                                    <li ${id}>
                                        ${record.time} 
                                        <button class="delete-punches" data-id="${record.id}">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </li>
                                `);
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


                // Submit Manual Punch
                $("#manualPunchForm").submit(function(e) {
                    e.preventDefault();

                    var userId = $("#formUserId").val();
                    var date = $("#formDate").val();
                    var punchTime = $("#manualTime").val();
                    console.log(`Adding manual punch...`);

                    if (!punchTime) {
                        alert("Please enter a time.");
                        return;
                    }

                    $.ajax({
                        url: "<?= site_url('attendance/addManualPunch') ?>",
                        type: "POST",
                        data: {
                            user_id: userId,
                            date: date,
                            manual_time: punchTime
                        },
                        success: function(response) {
                            alert("✅ Manual Punch Added Successfully!");
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            console.error("❌ Failed to add manual punch:", error);
                            alert("Failed to add manual punch.");
                        }
                    });
                });


                // watch man Submit Manual Punch
                $("#manualPunchForm-watchman").submit(function(e) {
                    e.preventDefault();

                    var userId = $("#formUserId-watchman").val();
                    var date = $("#formDate-watchman").val();
                    var punchTime = $("#manualTime-watchman").val();
                    console.log(`Adding manual punch...`);

                    if (!punchTime) {
                        alert("Please enter a time.");
                        return;
                    }

                    $.ajax({
                        url: "<?= site_url('attendance/addManualPunch') ?>",
                        type: "POST",
                        data: {
                            user_id: userId,
                            date: date,
                            manual_time: punchTime
                        },
                        success: function(response) {
                            alert("✅ Manual Punch Added Successfully!");
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            console.error("❌ Failed to add manual punch:", error);
                            alert("Failed to add manual punch.");
                        }
                    });
                });

                // Delete Punch
                $(document).on("click", ".delete-punches", function(e) {
                    e.preventDefault();
                    console.log("DELETE BUTTON CLICKED!");

                    var punchId = $(this).data("id");
                    // console.log("Deleting Punch ID:", punchId);

                    if (!punchId) {
                        alert("❗Invalid Punch ID!");
                        return;
                    }

                    if (confirm("❓Are you sure you want to delete this punch?")) {
                        $.ajax({
                            url: "<?= site_url('attendance/deletePunch') ?>",
                            type: "POST",
                            data: {
                                id: punchId
                            },
                            success: function(response) {
                                console.log("Delete response:", response);
                                alert("✅Punch Deleted Successfully!");
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                console.error("Delete failed:", status, error);
                                alert("❌Failed to delete punch.");
                            }
                        });
                    }
                });

                $('#bioTypeBtn').click(function() {

                    let bioType = $('#bioType').val();
                    console.log('Bio Type' + bioType)
                    var userId = $("#formUserId").val();
                    var date = $("#formDate").val();

                    if (bioType == '5') {
                        $('#watchman-punchModal').fadeIn();
                        // let punchdatabtn = $('.punch-time');

                        var userId = $("#modalUserId").text();
                        var userName = $("#modalUserName").text();
                        var date = $("#modalDate").text(); // "2025-05-26"
                        var parts = date.split('-');
                        var localDate = new Date(parts[0], parts[1] - 1, parts[2]); // Month is 0-indexed

                        // Add 1 day
                        localDate.setDate(localDate.getDate() + 1);

                        // Manually format YYYY-MM-DD using local date
                        var year = localDate.getFullYear();
                        var month = String(localDate.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
                        var day = String(localDate.getDate()).padStart(2, '0');

                        var nextDateStr = `${year}-${month}-${day}`;
                        console.log(nextDateStr);

                        $("#modalUserId-watchman").text(userId);
                        $("#modalUserName-watchman").text(userName);
                        $("#modalDate-watchman").text(nextDateStr);
                        $("#formUserId-watchman").val(userId);
                        $("#formDate-watchman").val(nextDateStr);

                        // console.log(`${userId}-${userName}-${nextDateStr}`)

                        // Fetch Punch Records
                        $.ajax({
                            url: "<?= site_url('attendance/getPunchRecords') ?>",
                            type: "GET",
                            data: {
                                user_id: userId,
                                date: nextDateStr
                            },
                            success: function(response) {
                                // console.log(response);
                                $("#punchRecords-watchman").empty();
                                console.log('Get punch records function working');

                                response.forEach(function(record, index) {

                                    let id = index == 0 ? "id='firstrecord'" : '';
                                    // Ensure record has id and time properties
                                    if (record.id && record.time) {
                                        $("#punchRecords-watchman").append(`
                                <li ${id}>
                                    ${record.time} 
                                    <button class="delete-punches" data-id="${record.id}">
                                        <i class='bx bx-trash'></i>
                                    </button>
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

                        return;
                    }

                    // console.log(`${bioType} ${userId} ${date}`);

                    $.ajax({
                        url: '<?= site_url('attendance/changeWorkType') ?> ',
                        type: "POST",
                        data: {
                            bioType: bioType,
                            userId: userId,
                            date: date
                        },
                        success: function(response) {
                            alert("✅ Work Type Changed Successfully!");
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            console.error("❌ Failed to add Work Type:", error);
                            alert("Failed to add Work Type.");
                        }
                    });

                })
            });


            // Close Modal
            $(".overlay, .btn-close, .box-icon-close").click(function() {
                $(".overlay, #punchModal, #upload_bio, #watchman-punchModal").fadeOut();
            });

            $(document).on('click', '#upload-biometric', function() {
                $('#upload_bio, .overlay').fadeIn();
            });

            $(document).on('submit', '#upload_bio_form', function(e) {
                e.preventDefault(); // Prevent form from reloading page

                var startdate = $('#upload_bio_startdate').val();
                var endDate = $('#upload_bio_enddate').val();

                let hasError = false;
                $('#error_upload_bio_startdate, #error_upload_bio_enddate').text(''); // Clear previous errors

                if (!startdate) {
                    $('#error_upload_bio_startdate').text('Please enter Start date.');
                    hasError = true;
                }

                if (!endDate) {
                    $('#error_upload_bio_enddate').text('Please enter End date.');
                    hasError = true;
                }

                if (startdate && endDate && new Date(startdate) > new Date(endDate)) {
                    alert("Start date cannot be after end date.");
                    hasError = true;
                }

                if (hasError) return false;

                $.ajax({
                    url: "<?= base_url('biometriccontroller/syncToHostinger') ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        start: startdate,
                        end: endDate
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#upload_bio, .overlay').fadeOut();
                            Swal.fire({
                                title: "Success!",
                                text: "Attendance Uploaded Successfully.",
                                icon: "success"
                            });
                        } else if (response.status === 'error') {
                            Swal.fire({
                                title: "Error!",
                                text: "Please Clear All error logs and try again.",
                                icon: "warning"
                            });
                        } else {
                            Swal.fire({
                                title: "Fail!",
                                text: "Faile to upload Attendance. Pls Try again.",
                                icon: "fail"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An unexpected error occurred: ' + error);
                        console.log(xhr.responseText);
                    }
                });
            });

            $('#calculate-watchman-punch').click(function() {
                let first_punch = $('#firstrecord').text().trim();
                let last_punch = $('#lastrecord').text().trim();
                let date = $('#modalDate').text().trim();
                let userid = $('#formUserId').val();

                console.log(first_punch + ' ' + last_punch + ' ' + date + ' ' + userid);

                $.ajax({
                    url: '<?= base_url('biometriccontroller/addOverMidnight') ?>',
                    method: 'POST',
                    data: {
                        first: first_punch,
                        second: last_punch,
                        date: date,
                        useid: userid
                    },
                    success: function(response) {
                        Swal.fire({
                            title: "Success!",
                            text: "Attendance Updated Successfully.",
                            icon: "success"
                        });
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('An unexpected error occurred: ' + error);
                        console.log(xhr.responseText);
                    }
                })
            })
            $(document).ready(function() {
                const $popup = $('.bio-refreach-container');
                const $overlay = $('.overlay');
                const $openBtn = $('#openRefreashBtn');

                // Show popup
                $openBtn.on('click', function() {
                    $overlay.show();
                    $popup.show();
                });

                // Hide popup on overlay click
                $overlay.on('click', function() {
                    $overlay.hide();
                    $popup.hide();
                });

                // Form submission and validation
                $('#refreashform').on('submit', function(e) {
                    e.preventDefault();

                    let start = $('#refreach_startdate').val();
                    let end = $('#refreach_enddate').val();

                    $('.bio-refreach-btn').hide();

                    let hasError = false;

                    // Clear previous errors
                    $('#error_refreach_startdate').text('');
                    $('#error_refreach_enddate').text('');

                    // Validation
                    if (!start) {
                        $('#error_refreach_startdate').text('Enter Start Date');
                        hasError = true;
                    }

                    if (!end) {
                        $('#error_refreach_enddate').text('Enter End Date');
                        hasError = true;
                    }

                    if (hasError) return;

                    // Proceed with AJAX
                    $.ajax({
                        url: '<?= base_url('/biometriccontroller/refreachBimetric') ?>',
                        type: 'POST',
                        dataType: 'text',
                        data: {
                            start: start,
                            end: end
                        },
                        success: function(response) {
                            console.log(response);
                            // location.reload();
                            $overlay.hide();
                            $popup.hide();
                            $('.bio-refreach-btn').show();
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                        }
                    });
                });
            });
        </script>

</body>

</html>