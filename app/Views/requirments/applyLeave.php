<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('asset/css/applyleave.css') ?>">
    <link rel="icon" href="<?= base_url('asset/images/favicon.png') ?>">

    <title>Apply Leave</title>

</head>

<body>
    <?= view('navbar/sidebar') ?>
    <section class="container applyleave" style="display: flex;">
        <div>
            <div class="toggleBtn">
                <button class='leave'>Leave</button>
                <button class='compensation'>Compensation</button>
                <button class='permission'>Permission</button>
            </div>

            <div class="form-box">
                <!-- Leave form -->
                <form class="leaveForm" action="<?= base_url('/leaveapplysubmit') ?>" method="post">
                    <h2>Leave Form</h2>
                    <div class="input-box">
                        <label for="leaveType">Leave Type</label>
                        <select name="leaveType" id="leaveType" required>
                            <option value="casual leave">Casual Leave</option>
                            <option value="sick leave">Sick Leave</option>
                            <option value="emergency leave">Emergency Leave</option>
                        </select>
                    </div>
                    <div class="input-box">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date">
                        <div class="error-text">
                            <?= isset($validation) ? dispaly_form_error($validation, 'start_date') : '' ?>
                        </div>
                    </div>
                    <div class="input-box">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date">
                        <div class="error-text">
                            <?= isset($validation) ? dispaly_form_error($validation, 'end_date') : "" ?>
                        </div>
                    </div>
                    <div class="input-box  ">
                        <label for="reason">Reason</label>
                        <textarea name="reason" id="reason"></textarea>
                        <div class="error-text">
                            <?= isset($validation) ? dispaly_form_error($validation, 'reason') : '' ?>
                        </div>
                    </div>
                    <div>
                        <button class="submit-btn" type="submit">Submit</button>
                    </div>
                </form>

                <!-- Compensation form -->
                <form action="<?= base_url('/compensation-request') ?>" class="compensationForm" method="post">
                    <h2>Compensation Form</h2>
                    <div class="input-box">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date">
                        <div class="error-text">
                            <?= isset($validation) ? dispaly_form_error($validation, 'start_date') : '' ?>
                        </div>
                    </div>
                    <div class="input-box">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date">
                        <div class="error-text">
                            <?= isset($validation) ? dispaly_form_error($validation, 'end_date') : '' ?>
                        </div>
                    </div>
                    <div class="input-box  ">
                        <label for="reason">Reason</label>
                        <textarea name="reason" id="reason"></textarea>
                        <div class="error-text">
                            <?= isset($validation) ? dispaly_form_error($validation, 'reason') : '' ?>
                        </div>
                    </div>
                    <div>
                        <button class="submit-btn" type="submit">Submit</button>
                    </div>
                </form>

                <!-- Permission form -->
                <form action="#" class="permission-form" id="permission-form">
                    <h2>Permission</h2>
                    <div class="input-box">
                        <label for="permission_date">Date</label>
                        <input type="date" name="start_date" id="permission_date">
                        <div class="error-text" id="error_permission_date">

                        </div>
                    </div>
                    <!-- <div class="input-box">
                    <label for="permission_time">Time</label>
                    <input type="time" name="permission_time" id="permission_time" min="00:30" max="02:00">
                    <div class="error-text" id="error_permission_time">

                    </div>
                </div> -->
                    <div class="input-box  ">
                        <label for="reason">Reason</label>
                        <textarea name="reason" id="permission_reason"></textarea>
                        <div class="error-text" id="error_permission_reason">

                        </div>
                    </div>
                    <div>
                        <button class="submit-btn" type="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
        <div>
            <table id="leaveTable" class="display table table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Leave Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data loaded dynamically -->
                </tbody>
            </table>
        </div>
    </section>
    <?= view('notification') ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.submit-btn').click(function() {
                var btn = $(this); // Store the clicked button
                btn.hide(); // Hide the button

                // Show the button again after 10 seconds (10000 milliseconds)
                setTimeout(function() {
                    btn.show();
                }, 10000);
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            <?php if (session()->getFlashdata('success')): ?>
                Swal.fire({
                    title: "Success!",
                    text: "<?= session()->getFlashdata('success'); ?>",
                    icon: "success"
                });
            <?php endif; ?>

            <?php if (session()->getFlashdata('fail')): ?>
                Swal.fire({
                    title: "Error!",
                    text: "<?= session()->getFlashdata('fail'); ?>",
                    icon: "error"
                });
            <?php endif; ?>
        });

        $(document).ready(function() {

            let active_design = "background:none; border:2px solid #da2442; color: #da2442;"
            let inactive_design = "background:da2442; border:none; color:#fff;"
            // Hide both forms initially
            loadLeaveRequest();
            $('.leaveForm').show();
            $('.compensationForm').hide();
            $('.permission-form').hide();

            $('.leave').attr('style', active_design);
            $('.compensation, .permission').attr('style', inactive_design);

            // Toggle form display on button click
            $('.leave').click(function() {
                $('.leaveForm').show();
                $('.compensationForm').hide();
                $('.permission-form').hide();

                $('.leave').attr('style', active_design);
                $('.compensation, .permission').attr('style', inactive_design);
            });

            $('.compensation').click(function() {
                $('.compensationForm').show();
                $('.permission-form').hide();
                $('.leaveForm').hide();

                $('.compensation').attr('style', active_design);
                $('.leave, .permission').attr('style', inactive_design);
            });


            $('.permission').click(function() {
                $('.compensationForm').hide();
                $('.permission-form').show();
                $('.leaveForm').hide();

                $('.permission').attr('style', active_design);
                $('.leave, .compensation').attr('style', inactive_design);
            });
        });

        // ------------------------------------------------------------------------
        // LEAVE REQUEST

        function loadLeaveRequest() {
            $.ajax({
                url: "<?= base_url('employeecontroller/getMyLeaves') ?>",
                method: "GET",
                success: function(res) {
                    console.log(res)
                    let rows = "";

                    $.each(res, function(index, item) {
                        rows += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.leave_type}</td>
                        <td>${item.start_date}</td>
                        <td>${item.end_date}</td>
                        <td>${item.total_num_leaves}</td>
                        <td>${item.status}</td>
                    </tr>
                `;
                    });

                    // inject into table body
                    $("#leaveTable tbody").html(rows);

                    // initialize or reinitialize datatable
                    $('#leaveTable').DataTable({
                        destroy: true // ensures no duplicate initialization
                    });
                }
            });
        }

        // --------------------------------------------------------------------------


        // Permission 

        $(document).on('submit', '#permission-form', function(e) {
            e.preventDefault();

            $('#error_permission_date').text('');
            $('#error_permission_time').text('');
            $('#error_permission_reason').text('');

            let date = $('#permission_date').val();
            // let time = $('#permission_time').val();
            let reason = $('#permission_reason').val();



            let flag = false;

            if (date == '' || date == null) {
                $('#error_permission_date').text('Enter Date of Permission');
                flag = true;
            }
            // if (time == '' || time == null) {
            //     $('#error_permission_time').text('Enter Total Time');
            //     flag = true;
            // }
            if (reason == '' || reason == null) {
                $('#error_permission_reason').text('Enter Reason...');
                flag = true;
            }

            if (flag) {
                $('.submit-btn').show();
                return;
            }

            $.ajax({
                url: '<?= base_url('/employeecontroller/applypermission/') ?>/' + date,
                method: 'POST',
                data: {
                    date: date,
                    time: '02:00',
                    reason: reason
                },
                success: function(response) {
                    // console.log(response);
                    // return;

                    if (response.status == 'success') {
                        Swal.fire({
                            title: "Success!",
                            text: "Permission submited successfully.",
                            icon: "success"
                        });
                    } else {
                        Swal.fire({
                            title: "Error!",
                            text: "Permission submited successfully.",
                            icon: "error"
                        });
                    }
                    $('#permission-form').trigger('reset');
                    $('.submit-btn').show();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            })

        })
    </script>

</body>

</html>