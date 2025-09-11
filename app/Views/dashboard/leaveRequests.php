<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url('asset/css/leaveRequest.css') ?>">
    <!-- <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'> -->
    <title>Leave Requests</title>
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
</head>

<body>
    <?= view('navbar/sidebar') ?>
    <section class="container">
        <?php
        $emp_id = '';
        $leave_id = "";
        ?>
        <div class="leaverequest_header">
            <h1>leave Requests</h1>
        </div>
        <?php if (!empty($leaveRequests)): ?>
            <div class="leaverequest_tools">
                <!-- Search Form -->
                <form method="GET" action="<?= site_url('leave-request'); ?>">
                    <div class="leaveRequest_search">
                        <input type="text" name="search" placeholder="Search by employee name" value="<?= esc($search); ?>">
                        <button type="submit"><ion-icon name="search-outline"></ion-icon></button>
                    </div>
                </form>

                <!-- Sorting Options -->
                <form method="GET" action="<?= site_url('leave-request'); ?>">
                    <div class="leaveRequest_sorting">
                        <div class="leaveRequest_sortby">
                            <select name="sort_by">
                                <option value="id" <?= ($sortBy == 'id') ? 'selected' : ''; ?>>Default</option>
                                <option value="start_date" <?= ($sortBy == 'start_date') ? 'selected' : ''; ?>>Leave Start</option>
                                <option value="end_date" <?= ($sortBy == 'end_date') ? 'selected' : ''; ?>>Leave End</option>
                                <option value="name" <?= ($sortBy == 'name') ? 'selected' : ''; ?>>Employee Name</option>
                            </select>
                        </div>

                        <div class="leaveRequest_orderby">
                            <select name="sort_order">
                                <option value="asc" <?= ($sortOrder == 'asc') ? 'selected' : ''; ?>>Descending</option>
                                <option value="desc" <?= ($sortOrder == 'desc') ? 'selected' : ''; ?>>Ascending</option>
                            </select>
                        </div>

                        <button type="submit"><ion-icon name="funnel-outline"></ion-icon></button>
                    </div>
                </form>
                <div class="pager-container">
                    <p>Page <?= esc($currentPage); ?> of <?= esc($totalPages); ?></p>

                    <div>
                        <?php if ($currentPage > 1): ?>
                            <a href="<?= site_url('leave-request?page=' . ($currentPage - 1) . '&search=' . esc($search) . '&sort_by=' . esc($sortBy) . '&sort_order=' . esc($sortOrder)); ?>"><button class="pagerbtn previes">Previous</button></a>
                        <?php endif; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= site_url('leave-request?page=' . ($currentPage + 1) . '&search=' . esc($search) . '&sort_by=' . esc($sortBy) . '&sort_order=' . esc($sortOrder)); ?>"><button class="pagerbtn next">Next</button></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="leaverequest_table">
                <!-- Leave Requests Table -->
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <!-- <th>Designation</th> -->
                            <th>Leave Type</th>
                            <th>Leave Start</th>
                            <th>Leave End</th>
                            <th>No of Days</th>
                            <th class="leabe-balence">Availabel Leaves</th>
                            <th class="reason">Reason</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leaveRequests as $leave): ?>
                            <tr>
                                <td><?= esc($leave['name']); ?></td>
                                <td><?= esc($leave['leave_type']); ?></td>
                                <td><?= esc($leave['start_date']); ?></td>
                                <td><?= esc($leave['end_date']); ?></td>
                                <td><?= $leave['num_leave_days']; ?></td>

                                <td>
                                    <span class="available-leave" id="available-leave-<?= $leave['id'] ?>"><?= esc($leave['balence_leave']); ?> | <?= esc($leave['remaining_leaves']); ?></span>

                                    <input class="edit-input" type="number" id="edit-input-<?= $leave['id'] ?>" style="display: none;">

                                    <button class="edit-btn" data-id="<?= $leave['id'] ?>"><i class='bx bx-pencil'></i></button>
                                    <button class="save-btn" style="display: none;" data-id="<?= $leave['id'] ?>"><i class='bx bx-check'></i></button>
                                </td>
                                <td class="td-reason" onclick="showFullText(this)"><?= esc($leave['reason']); ?></td>
                                <td><?= ucfirst($leave['status']) ?></td>
                                <td class="actions">
                                    <?php $leave_id = $leave['id'];
                                    $emp_id = $leave['emp_id'];
                                    ?>
                                    <a class="approve" href="<?= base_url('/hr/change-status') ?>/<?= $leave['id'] ?>/approved/<?= $leave['emp_id'] ?>/<?= $leave['num_leave_days'] ?>"><i class='bx bx-check'></i></a> |
                                    <button class="reject reject_leave_request" data-id="<?= $leave['id'] ?>" data-emp="<?= $leave['emp_id'] ?>" data-leave="<?= $leave['num_leave_days'] ?>"><i class='bx bx-x'></i></button>
                                    <a class="delete" href="<?= base_url('/hr/change-status') ?>/<?= $leave['id'] ?>/delete/<?= $leave['emp_id'] ?>/<?= $leave['num_leave_days'] ?>"><i class='bx bx-trash'></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div>
        <?php else : ?>
            <h3>No Leave Requests Found</h3>
        <?php endif; ?>
        <!-- Pagination Controls -->
        <div class="fade" style="display: none;"></div>

        <div class="reject_reason_container" style="display: none;">
            <form action="#" id="reject_reason_form">
                <button class="reject_reason_container_close">X</button>
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
                    <input type='number' id='popup_reject_leave_actual_count'>
                    <span class="error_popup_counts"></span>
                </div>
                <input type='number' id='nom_of_dates' style='display: none;'>
                <button type="button" id="reject_reason_send_btn">Send</button>
            </form>
        </div>

    </section>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
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
            $('.edit-btn').click(function(event) {
                event.stopPropagation(); // Prevent the click event from bubbling up
                var id = $(this).data('id');
                $('#available-leave-' + id).hide();
                $('#edit-input-' + id).show();
                $(this).hide();
                $('.save-btn[data-id="' + id + '"]').show();
            });
            // Save data when save button is clicked
            $('.save-btn').click(function(event) {
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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

            $('.fade').fadeIn();
            $('.reject_reason_container').fadeIn();
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
                url: `<?= base_url() ?>/hr/change-status/${encodeURIComponent(id)}/${encodeURIComponent(status)}/${encodeURIComponent(emp_id)}/${encodeURIComponent(leave_days)}`,
                type: 'POST',
                data: {
                    reason: reason,
                    apc : approved_count,
                    rlc : reject_count
                },
                success: function(result) {
                    console.log(result);
                    if (result.status === 'success') {
                        Swal.fire({
                            title: "Success!",
                            text: "Leave status updated successfully.",
                            icon: "success"
                        }).then(() => location.reload());
                    } else {
                        Swal.fire("Error!", "Leave status update failed.", "error");
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire("Error", "Something went wrong while updating leave.", "error");
                }
            });
        });


        $(".fade, .reject_reason_container_close").click(function() {
            $('.fade').fadeOut();
            $('.reject_reason_container').fadeOut();
        })
    </script>

</body>

</html>