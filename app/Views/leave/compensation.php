<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('asset/css/leaveRequest.css') ?>">
    <title>Compensation</title>
</head>

<body>
    <?= view('navbar/sidebar') ?>

    <section class="container">
        <div class="leaverequest_header">
            <h2>compensation</h2>
        </div>

        <div class="leaverequest_tools">
            <!-- Search Form -->
            <form method="GET" action="<?= site_url('compen-request'); ?>">
                <div class="leaveRequest_search">
                    <input type="text" name="search" placeholder="Search" value="<?= esc($search); ?>">
                    <button type="submit"><ion-icon name="search-outline"></ion-icon></button>
                </div>
            </form>

            <!-- Sorting Options -->
            <form method="GET" action="<?= site_url('compen-request'); ?>">
                <div class="leaveRequest_sorting">
                    <div class="leaveRequest_sortby">
                        <select name="sort_by">
                            <option value="id" <?= ($sortBy == 'id') ? 'selected' : ''; ?>>Default</option>
                            <option value="start_date" <?= ($sortBy == 'start_date') ? 'selected' : ''; ?>>Compensation Start</option>
                            <option value="end_date" <?= ($sortBy == 'end_date') ? 'selected' : ''; ?>>Compensation End</option>
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

        <?php if (!empty($datas)): ?>
            <div class="leaverequest_table compen_table">
                <?php if ($role == 1 || $role == 10 || $role == 11) : ?>
                    <!-- Leave Requests Table -->
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <!-- <th>Designation</th> -->
                                <th>Leave Start</th>
                                <th>Leave End</th>
                                <th>No of Days</th>
                                <th class="reason">Reason</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($datas as $compen): ?>

                                <tr>
                                    <td><?= esc($compen['name']); ?></td>
                                    <td><?= esc($compen['start_date']); ?></td>
                                    <td><?= esc($compen['end_date']); ?></td>
                                    <td><?= $compen['num_of_days']; ?></td>
                                    <td class="td-reason" onclick="showFullText(this)"><?= esc($compen['reason']); ?></td>
                                    <td><?= ucfirst($compen['status']) ?></td>
                                    <td class="actions" >
                                        <a class="approve" href="<?= base_url('/update-compen-status') ?>/<?= $compen['id'] ?>/approved/<?= $compen['emp_id'] ?>/<?= $compen['num_of_days'] ?>"><i class='bx bx-check'></i></a> |
                                        <a class="reject" href="<?= base_url('/update-compen-status') ?>/<?= $compen['id'] ?>/rejected/<?= $compen['emp_id'] ?>/<?= $compen['num_of_days'] ?>"><i class='bx bx-x'></i></a> |
                                        <a class="delete" href="<?= base_url('/update-compen-status') ?>/<?= $compen['id'] ?>/delete/<?= $compen['emp_id'] ?>/<?= $compen['num_of_days'] ?>"><i class='bx bx-trash'></i></a>
                                    </td>
                                </tr>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            </div>

            </div>
        <?php else : ?>
            <h3>No Leave Requests Found</h3>
        <?php endif; ?>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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


            // $(document).ready(function(){
            //     $(".td-reason").click(function(){
            //         $(".td-reason").toggleClass('full')
            //     })
            // })
        </script>

</body>

</html>