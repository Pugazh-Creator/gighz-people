<div>
    <div class="applyheader">
        <div class="toggleBtn">
            <button class='leave'>Leave</button>
            <button class='compensation'>Compensation</button>
            <button class='permission'>Permission</button>
        </div>
        <div>
            <button id="apply-btn">Apply</button>
        </div>
    </div>
    <div class="application-table-cont">
        <table id="leaveTable" class="display table table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>Leave Type</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th style="display:none;">Created At</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data loaded dynamically -->
            </tbody>
        </table>
        <table id="compenTable" class="display table table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>Start</th>
                    <th>End</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th style="display:none;">Created At</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data loaded dynamically -->
            </tbody>
        </table>
        <table id="permissionTable" class="display table table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Permission</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th style="display:none;">Created At</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data loaded dynamically -->
            </tbody>
        </table>

    </div>
</div>
<div class="form-box">

    <div class="model" id="leave_form_container">
        <!-- Leave form -->
        <form class="leaveForm applyforms" id="leaveapply-form" action="#" method="post">
            <div class="cls-btn">✕</div>
            <h2>Leave Form</h2>
            <div class="input-box">
                <label for="leaveType">Leave Type</label>
                <select name="leaveType" id="leaveType">
                    <option value="" selected disabled>-Select Leave Type--</option>
                    <option value="casual leave">Casual Leave</option>
                    <option value="sick leave">Sick Leave</option>
                    <option value="emergency leave">Emergency Leave</option>
                </select>
                <div class="error-txt" id="error-leaveType"></div>
            </div>
            <div class="input-box">
                <label for="leave_start_date">Start Date</label>
                <input type="date" name="leave_start_date" id="leave_start_date">
                <div class="error-txt" id="error-leave_start_date"></div>
            </div>
            <div class="input-box">
                <label for="leave_end_date">End Date</label>
                <input type="date" name="leave_end_date" id="leave_end_date">
                <div class="error-txt" id="error-leave_end_date"></div>
            </div>
            <div class="input-box  ">
                <label for="leave_reason">Reason</label>
                <textarea name="leave_reason" id="leave_reason"></textarea>
                <div class="error-txt" id="error-leave_reason"></div>
            </div>
            <div>
                <button class="submit-btn" type="submit">Submit</button>
            </div>
        </form>
    </div>

    <div class="model" id="compensation_apply_box">
        <!-- Compensation form -->
        <form id="compensationapply-form" action="#" class="compensationForm applyforms" method="post">
            <div class="cls-btn">✕</div>
            <h2>Compensation Form</h2>
            <div class="input-box">
                <label for="start_date">Start Date</label>
                <input type="date" name="compen_start_date" id="compen_start_date">
                <div class="error-txt" id="error-compen_start_date"></div>
            </div>
            <div class="input-box">
                <label for="compen_end_date">End Date</label>
                <input type="date" name="compen_end_date" id="compen_end_date">
                <div class="error-txt" id="error-compen_end_date"></div>
            </div>
            <div class="input-box  ">
                <label for="compen_reason">Reason</label>
                <textarea name="compen_reason" id="compen_reason"></textarea>
                <div class="error-txt" id="error-compen_reason"></div>
            </div>
            <div>
                <button class="submit-btn" type="submit">Submit</button>
            </div>
        </form>
    </div>

    <div class="model" id="permission_apply_box">
        <!-- Permission form -->
        <form action="#" class="permission-form applyforms" id="permission-form">
            <div class="cls-btn">✕</div>
            <h2>Permission</h2>
            <div class="input-box">
                <label for="permission_date">Date</label>
                <input type="date" name="start_date" id="permission_date">
                <div class="error-txt" id="error_permission_date">

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
                <div class="error-txt" id="error_permission_reason">

                </div>
            </div>
            <div>
                <button class="submit-btn" type="submit">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
    const active_design = "background:none; border:2px solid #da2442; color: #da2442;"
    const inactive_design = "background:da2442; border:none; color:#fff;"

    // $(document).ready(function() {
    //     $('.submit-btn').click(function() {
    //         var btn = $(this); // Store the clicked button
    //         btn.hide(); // Hide the button

    //         // Show the button again after 10 seconds (10000 milliseconds)
    //         setTimeout(function() {
    //             btn.show();
    //         }, 10000);
    //     });
    // });


    $(document).ready(function() {

        loadLeaveRequest();

        // hide only these two wrappers on load
        $('#compenTable_wrapper, #permissionTable_wrapper, .dataTables_wrapper, #leaveTable_wrapper').hide();


        $('#compenTable').hide();
        $('#permissionTable').hide();
        $('#leaveTable').show();

        $(".dataTables_wrapper").hide();
        $('#leaveTable_wrapper').show();

        $('.leave').attr('style', active_design);
        $('.compensation, .permission').attr('style', inactive_design);

        $('#apply-btn').attr('data-btn', 'leave')


    });

    $('.leave').on('click', function() {
        loadLeaveRequest();
        $('#apply-btn').attr('data-btn', 'leave')

        $('#compenTable').hide();
        $('#permissionTable').hide();
        $('#leaveTable').show();

        $(".dataTables_wrapper").hide();
        $('#leaveTable_wrapper').show();

        $('.leave').attr('style', active_design);
        $('.compensation, .permission').attr('style', inactive_design);
    })

    $('.compensation').on('click', function() {
        loadCompensationRequest();
        $('#apply-btn').attr('data-btn', 'compensation')

        $('#compenTable').show();
        $('#leaveTable').hide();
        $('#permissionTable').hide();

        $(".dataTables_wrapper").hide();
        $('#compenTable_wrapper').show();

        $('.compensation').attr('style', active_design);
        $('.leave, .permission').attr('style', inactive_design);

    })

    $('.permission').on('click', function() {
        loadPermissionRequest();
        $('#apply-btn').attr('data-btn', 'permission')

        $('#permissionTable').show();
        $('#leaveTable').hide();
        $('#compenTable').hide();

        $(".dataTables_wrapper").hide();
        $('#permissionTable_wrapper').show();

        $('.permission').attr('style', active_design);
        $('.leave, .compensation').attr('style', inactive_design);
    })


    $('#apply-btn').on('click', function(e) {
        e.preventDefault();

        let thisData = $(this).attr('data-btn'); // ✅ always lates
        $('.overlay, .model').fadeOut();
        if (thisData == 'leave') {
            $('#leave_form_container, .overlay').fadeIn();
        } else if (thisData == 'permission') {
            $('#permission_apply_box, .overlay').fadeIn();
        } else if (thisData == 'compensation') {
            $('#compensation_apply_box, .overlay').fadeIn();
        }
    })

    $('.overlay, .cls-btn').on('click', function() {
        $('.overlay, .model').fadeOut();
    })


    // ------------------------------------------------------------------------
    // LEAVE REQUEST

    function loadLeaveRequest() {
        $.ajax({
            url: "<?= base_url('employeecontroller/getMyLeaves') ?>",
            method: "GET",
            success: function(res) {

                if ($.fn.DataTable.isDataTable('#leaveTable')) {
                    $('#leaveTable').DataTable().destroy();
                }
                $('#leaveTable tbody').empty();

                let rows = "";
                $.each(res, function(index, item) {
                    rows += `
                        <tr>
                        <td>${item.leave_type}</td>
                        <td>${item.start_date}</td>
                        <td>${item.end_date}</td>
                        <td>${item.total_num_leaves}</td>
                        <td>${item.reason}</td>
                            <td>${item.status}</td>
                             <td style="display:none;">${item.created_at}</td>
                        </tr>
                    `;
                });
                $("#leaveTable tbody").html(rows);

                // Initialize / Reinitialize
                $('#leaveTable').DataTable({
                    searching: true,
                    paging: true,
                    lengthChange: false, // hides "Show entries"
                    pageLength: 10,
                    order: [
                        [6, "desc"]
                    ], // 6 = created_at column index
                    columnDefs: [{
                            targets: [6],
                            visible: false
                        } // hide created_at column
                    ]
                });
            }
        });
    }

    function loadCompensationRequest() {
        console.log('compen called');
        $.ajax({
            url: "<?= base_url('employeecontroller/showMyCompensation') ?>",
            method: "GET",
            success: function(res) {
                // Initialize / Reinitialize
                if ($.fn.DataTable.isDataTable('#compenTable')) {
                    $('#compenTable').DataTable().destroy();
                }

                $('#compenTable tbody').empty();

                let rows = "";

                $.each(res, function(index, item) {
                    rows += `
                        <tr>
                        <td>${item.start_date}</td>
                        <td>${item.end_date}</td>
                        <td>${item.num_of_days}</td>
                        <td>${item.reason}</td>
                            <td>${item.status}</td>
                            <td style="display:none;">${item.created_at}</td>
                        </tr>
                    `;
                });
                $("#compenTable tbody").html(rows);

                $('#compenTable').DataTable({
                    searching: true,
                    paging: true,
                    lengthChange: false, // hides "Show entries"
                    pageLength: 10,
                    order: [
                        [5, "desc"]
                    ], // 6 = created_at column index
                    columnDefs: [{
                            targets: [5],
                            visible: false
                        } // hide created_at column
                    ]
                });
            }
        });
    }

    function loadPermissionRequest() {
        $.ajax({
            url: "<?= base_url('employeecontroller/getmypermission') ?>",
            method: "GET",
            success: function(res) {
                if ($.fn.DataTable.isDataTable('#permissionTable')) {
                    $('#permissionTable').DataTable().destroy();
                }
                // console.log(res)
                $('#permissionTable tbody').empty();
                // return;
                let rows = "";
                $.each(res, function(index, item) {
                    rows += `
                        <tr>
                        <td>${item.permission_date}</td>
                        <td>${item.permission_time}</td>
                        <td>${item.permission_reason}</td>
                            <td>${item.permission_status}</td>
                            <td style="display:none;">${item.created_at}</td>
                        </tr>
                    `;
                });
                $("#permissionTable tbody").html(rows);

                // Initialize / Reinitialize
                $('#permissionTable').DataTable({
                    searching: true,
                    paging: true,
                    lengthChange: false, // hides "Show entries"
                    pageLength: 10,
                    order: [
                        [4, "desc"]
                    ], // 6 = created_at column index
                    columnDefs: [{
                            targets: [4],
                            visible: false
                        } // hide created_at column
                    ]
                });
            }
        });
    }


    // --------------------------------------------------------------------------
    // -------------------------------LEAVE REQUEST ---------------------------------

    $('#leaveapply-form').on('submit', function(e) {
        e.preventDefault(); // prevent default form submission

        // Clear previous errors
        $('.error-txt').text('');

        // Get form values
        let leaveType = $('#leaveType').val();
        let startDate = $('#leave_start_date').val();
        let endDate = $('#leave_end_date').val();
        let reason = $('#leave_reason').val();

        // Basic validation
        let hasError = false;
        if (!leaveType) {
            $('#error-leaveType').text('Please select a leave type.');
            hasError = true;
        }
        if (!startDate) {
            $('#error-leave_start_date').text('Please select start date.');
            hasError = true;
        }
        if (!endDate) {
            $('#error-leave_end_date').text('Please select end date.');
            hasError = true;
        }
        if (!reason) {
            $('#error-leave_reason').text('Please enter reason.');
            hasError = true;
        }

        if (hasError) return; // stop if validation fails

        // Find the submit button inside this form
        let $btn = $('.submit-btn');
        let originalText = "Submit";

        // Disable button and show "Submitting..."
        $btn.text('Submitting...').prop('disabled', true);

        // Prepare FormData
        let formData = new FormData(this);

        // Send AJAX request
        $.ajax({
            url: baseurl + 'employeecontroller/leaveapplysubmit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    loadLeaveRequest();
                    $('.cls-btn').click();
                    showPopup('Leave applied successfully!', 'success');
                    $('#leaveapply-form')[0].reset(); // reset form

                } else {
                    showPopup(res.message || 'Failed to apply leave!', 'error');
                    $btn.text('');
                    $btn.text(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                showPopup('An error occurred! Please try again.', 'error');
                console.error(error);
            },
            complete: function() {
                // Reset button text and enable it
                $btn.text('');
                $btn.text(originalText).prop('disabled', false);
            }
        });

    });

    // -------------------------- COMPENSATION -----------------------------------
    $('#compensationapply-form').on('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        $('.error-txt').text('');

        // Get form values
        let startDate = $('#compen_start_date').val();
        let endDate = $('#compen_end_date').val();
        let reason = $('#compen_reason').val();

        // Basic validation
        let hasError = false;
        if (!startDate) {
            $('#error-compen_start_date').text('Please select start date.');
            hasError = true;
        }
        if (!endDate) {
            $('#error-compen_end_date').text('Please select end date.');
            hasError = true;
        }
        if (!reason) {
            $('#error-compen_reason').text('Please enter reason.');
            hasError = true;
        }
        if (hasError) return;

        // Change button text and disable it while processing
        let $btn = $('.submit-btn');
        let originalText = "Submit";
        $btn.text('Processing...').prop('disabled', true);

        // Prepare FormData
        let formData = new FormData(this);

        // AJAX request
        $.ajax({
            url: baseurl + 'employeecontroller/applyCompensation',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.status === 'success') {
                    loadCompensationRequest();
                    $('.cls-btn').click();
                    showPopup('Compensation request submitted successfully!', 'success');
                    $('.compensationForm')[0].reset();
                } else {
                    showPopup(res.message || 'Failed to submit compensation request!', 'error');
                    $btn.text('');
                    $btn.text(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                showPopup('An error occurred! Please try again.', 'error');
                console.error(error);
            },
            complete: function() {
                // Reset button text and enable it
                $btn.text('');
                $btn.text(originalText).prop('disabled', false);
            }
        });
    });

    // ---------------------------------------- PERMISSION -------------------------------------- 

    $(document).on('submit', '#permission-form', function(e) {
        e.preventDefault();
        $('#error_permission_date').text('');
        $('#error_permission_time').text('');
        $('#error_permission_reason').text('');
        $('.submit-btn').text('Submiting...').prop('disabled', false);
        let date = $('#permission_date').val();
        // let time = $('#permission_time').val();
        let reason = $('#permission_reason').val();
        let flag = false;
        if (date == '' || date == null) {
            $('#error_permission_date').text('Enter Date of Permission');
            flag = true;
        }
        if (reason == '' || reason == null) {
            $('#error_permission_reason').text('Enter Reason...');
            flag = true;
        }
        if (flag) {
            $('.submit-btn').text('Submit').prop('disabled', true);
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
                if (response.status == 'success') {
                    loadPermissionRequest();
                    $('.cls-btn').click();
                    showPopup(response.message, response.status);
                }
                $('#permission-form').trigger('reset');
                $('.submit-btn').text('Submit').prop('disabled', true);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        })

    })
</script>