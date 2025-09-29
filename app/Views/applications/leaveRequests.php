<style>
    .fade {
        width: 100%;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 999;
        background: rgba(123, 123, 123, 0.46);
    }

    .reject_reason_container {
        width: 40%;
        height: 500px;
        position: fixed;
        top: 20%;
        left: 40%;
        z-index: 1000;
        background: #fff;
        border-radius: 10px;

    }
</style>

<div class="requestTableContainer">
    <table id="leaveRequestTable" class="display">
        <thead>
            <tr>
                <th>Name</th>
                <th style='display:none'>Created At</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Total</th>
                <th>Balance</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="leaveRequestTableBody">
            <!-- rows will be injected here -->
        </tbody>
    </table>
</div>

<!-- Pagination Controls -->

<div class="model" id="reject_reason_container" style="display: none;">
    <div class="cls-btn">✕</div>
    <form action="#" id="reject_reason_form">
        <div class="input_box">
            <label for="reject_reason">Reason For Reject</label>
            <textarea name="reject_reason" id="reject_reason"></textarea>
            <input type="hidden" id="popup_reject_leave_id">
            <input type="hidden" id="popup_reject_leave_empid">
            <input type="hidden" id="popup_reject_leave_leavedays">
        </div>
        <div class="input_box">
            <label for="show-dates">Reject Perticular leave</label>
            <input type="checkbox" id="show-dates">
        </div>
        <div class="input_box" id="dates_input_boxes" style="display: none;">
            <label for='popup_reject_leave_count'>Enter Approve Leave</label>
            <input type='number' id='popup_reject_leave_approve_count'>
            <input type='number' id='popup_reject_leave_reject_count'>
            <input type='number' id='popup_reject_leave_actual_count' disabled>
            <span class="error-txt error_popup_counts"></span>
        </div>
        <input type='number' id='nom_of_dates' style='display: none;'>
        <button type="button" id="reject_reason_send_btn">Send</button>
    </form>
</div>
<script>
    function getLeaveReqest() {
        $('#leaveRequestTableBody').html('');
        $.ajax({
            url: baseurl + 'hrcontroller/getEmployeeLeaveRequests',
            method: 'GET',
            success: function(res) {
                // console.log(res);

                // Destroy old DataTable if exists
                if ($.fn.DataTable.isDataTable('#leaveRequestTable')) {
                    $('#leaveRequestTable').DataTable().clear().destroy();
                }

                // Build table rows
                let tbody = '';
                res.forEach(e => {
                    tbody += `<tr>
                    <td>${e.name}</td>
                    <td style='display:none'>${e.created_at}</td>
                    <td>${e.leave_type}</td>
                    <td>${e.start_date}</td>
                    <td>${e.end_date}</td>
                    <td>${e.total_num_leaves}</td>
                    <td>
                        <span class="available-leave" id="available-leave-${e.id}">${e.balence_leave} | ${e.remaining_leaves}</span>
                        <input class="edit-input" type="number" id="edit-input-${e.id}" style="display: none;">
                        <a href="#" class="edit-btn" data-id="${e.id}"><i class='bx bx-pencil'></i></a>
                        <a href="#" class="save-btn" style="display: none;" data-id="${e.id}"><i class='bx bx-check'></i></a>
                    </td>
                    <td class="td-reason">${e.reason}</td>
                    <td>${e.status} ${e.status == 'pending' ? '' : (e.status != 'rejected' ? '' : (e.leave_approve_count == '' ? [0] : [e.leave_approve_count]))}</td>
                    <td class="actions">
                        <a class="tbl-action approve" href="#" data-url = "<?= base_url('hrcontroller/change_status') ?>/${e.id}/approved/${e.emp_id}/${e.total_num_leaves}"><i class='bx bx-check'></i></a> |
                        <a href="#" class="reject reject_leave_request" data-id="${e.id}" data-emp="${e.emp_id}" data-leave="${e.total_num_leaves}"><i class='bx bx-x'></i></a>
                        <a class="tbl-action delete" href="#" data-url = "<?= base_url('hrcontroller/change_status') ?>/${e.id}/delete/${e.emp_id}/${e.total_num_leaves}"><i class='bx bx-trash'></i></a>
                    </td>
                </tr>`;
                });

                // Append new rows
                $('#leaveRequestTableBody').html(tbody);

                // Initialize DataTable on table
                $('#leaveRequestTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    order: [
                        [1, 'desc'] // first column in descending order
                    ], // sort by Name ASC
                    searching: true,
                    stripeClasses: [],
                    scrollX: true,
                    autoWidth: false
                });
            }
        });
    }

    $(document).on('click', '.td-reason', function() {
        $(".td-reason").removeClass("full"); // Hide any open text
        $(this).addClass("full"); // Show the clicked text
    });

    $(document).ready(function() {
        getLeaveReqest();
        $('.leabe-balence').select(function() {
            var id = $(this).data('id');
            $('#available-leave-' + id).show();
            $('#edit-input-' + id).hide();
            $(this).show();
            $('.save-btn[data-id="' + id + '"]').hide();
        })
    })

    $(document).ready(function() {
        // Show input box and hide text when edit button is clicked
        $(document).on('click', '.edit-btn', function(event) {
            console.log('edit button clicke');

            event.stopPropagation(); // Prevent the click event from bubbling up
            var id = $(this).data('id');
            $('#available-leave-' + id).hide();
            $('#edit-input-' + id).show();
            $(this).hide();
            $('.save-btn[data-id="' + id + '"]').show();
        });
        // Save data when save button is clicked
        $(document).on('click', '.save-btn', function(event) {
            event.stopPropagation(); // Prevent the click event from bubbling up
            var id = $(this).data('id');
            var availableLeave = $('#edit-input-' + id).val();
            console.log(id);

            $.ajax({
                url: '<?= base_url('leave/updateLeave') ?>',
                type: 'POST',
                data: {
                    id: id,
                    available_leave: availableLeave
                },
                success: function(response) {
                    alert('✅ leave Updated SuccessFully...');
                    location.reload();
                    getLeaveReqest()
                },
                error: function(xhr, status, error) {
                    alert('Failed to update leave.');
                }
            });
        });
        $(document).click(function(event) {
            if (!$(event.target).closest('.edit-input, .save-btn').length) {
                $('.edit-input').each(function() {
                    var id = $(this).attr('id').replace('edit-input-', '');
                    $('#available-leave-' + id).show();
                    $(this).hide();
                    $('.save-btn[data-id="' + id + '"]').hide();
                    $('.edit-btn[data-id="' + id + '"]').show();
                });
            }
        });

        // Prevent hiding the input box when clicking inside it
        $('.edit-input').click(function(event) {
            event.stopPropagation(); // Prevent the click event from bubbling up
        });
    });

    function showFullText(element) {
        $(".td-reason").removeClass("full"); // Hide any open text
        $(element).addClass("full"); // Show the clicked text
    }

    $(document).click(function(event) {
        if (!$(event.target).closest(".td-reason").length) {
            $(".td-reason").removeClass("full"); // Hide full content when clicking outside
        }
    });

    $('#reject_reason_form').on('change', 'input[type="checkbox"]', function() {

        if ($(this).is(':checked')) {
            $('#dates_input_boxes').show();
        } else {
            $('#dates_input_boxes').hide();
        }
    })

    $(document).ready(function() {
        $('#popup_reject_leave_approve_count').on('input', function() {
            $('.error_popup_counts').text('');
            var approved = parseInt($(this).val()) || 0;

            // Example: total leave was 10
            let totalLeave = $('#popup_reject_leave_leavedays').val();

            if (approved > totalLeave) {
                $('#popup_reject_leave_approve_count').val('');
                $('.error_popup_counts').text('Incorrect Approved count.');
                return;
            }

            var rejected = totalLeave - approved;

            approved <= 0 ? $('#popup_reject_leave_reject_count').val(totalLeave) : $('#popup_reject_leave_reject_count').val(rejected);

            $('#popup_reject_leave_actual_count').val(approved);
        });
    });


    $(document).on('click', '.reject_leave_request', function() {

        var id = $(this).data('id');
        var emp_id = $(this).data('emp');
        var leave_days = $(this).data('leave');

        console.log(id + ' ' + emp_id + ' ' + leave_days);


        $('#popup_reject_leave_id').val('');
        $('#popup_reject_leave_empid').val('');
        $('#popup_reject_leave_leavedays').val('');


        $('#popup_reject_leave_id').val(id);
        $('#popup_reject_leave_empid').val(emp_id);
        $('#popup_reject_leave_leavedays').val(leave_days);

        $('#reject_reason_send_btn').data('id', id);
        $('#reject_reason_send_btn').data('emp', emp_id);
        $('#reject_reason_send_btn').data('leave', leave_days);

        $('#popup_reject_leave_reject_count').val('');
        $('#popup_reject_leave_reject_count').val(leave_days);

        $('.overlay').fadeIn();
        $('#reject_reason_container').fadeIn();
        $('#reject_reason_form').trigger('reset');
    });

    $(document).on('click', '#reject_reason_send_btn', function() {
        var reason = $("#reject_reason").val().trim();
        if (!reason) {
            Swal.fire("Validation Error", "Please enter a rejection reason.", "warning");
            return;
        }

        var id = $('#popup_reject_leave_id').val();
        var emp_id = $('#popup_reject_leave_empid').val();
        var leave_days = $('#popup_reject_leave_leavedays').val();

        let status = 'rejected';

        let approved_count = $('#popup_reject_leave_approve_count').val() || 0
        let reject_count = $('#popup_reject_leave_reject_count').val() || leave_days;

        console.log(approved_count + ' ' + reject_count);

        $.ajax({
            url: `<?= base_url() ?>/hrcontroller/change_status/${encodeURIComponent(id)}/${encodeURIComponent(status)}/${encodeURIComponent(emp_id)}/${encodeURIComponent(leave_days)}`,
            type: 'POST',
            data: {
                reason: reason,
                apc: approved_count,
                rlc: reject_count
            },
            success: function(result) {
                console.log(result);
                if (result.status === 'success') {
                    $('#reject_reason_form').trigger('reset');
                    showPopup('Leave Rejected Successfully.')
                    getLeaveReqest();
                    $('.cls-btn').click();
                } else {
                    Swal.fire("Error!", "Leave status update failed.", "error");
                }
            },
            error: function(xhr, status, error) {
                Swal.fire("Error", "Something went wrong while updating leave.", "error");
            }
        });
    });

    $(document).on('click', '.tbl-action', function(e) {
        e.preventDefault();
        let url = $(this).data('url');
        $.ajax({
            url: url,
            method: 'GET',
            success: function(res) {
                if (res.status == 'success') {} else {
                    showPopup(res.message, res.status)
                    getLeaveReqest();
                }
            },
            error: function(err) {
                showPopup(err, 'error');
            }
        })
    })


    $(".overlay, .cls-btn").on('click', function() {
        $('.overlay, .model').fadeOut();
    })
</script>