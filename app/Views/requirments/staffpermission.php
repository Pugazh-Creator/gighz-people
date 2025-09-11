<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('asset/css/leaveRequest.css') ?>">
    <title>My Permissions</title>
</head>

<body>

    <?= view('navbar/sidebar') ?>

    <section class="container Permission">
        <div class="availabel-leave">
            <h1>Permission
        </div>

        <div class="ourLeave">
            <?php if (!empty($data)) : ?>
                <div class="leaverequest_tools">
                    <!-- Search Form -->
                    <form method="GET" action="<?= site_url('/employeecontroller/getmypermission'); ?>">
                        <div class="leaveRequest_search">
                            <input type="text" name="search" placeholder="Search " value="<?= esc($search); ?>">
                            <button type="submit"><ion-icon name="search-outline"></ion-icon></button>
                        </div>
                    </form>

                    <!-- Sorting Options -->
                    <form method="GET" action="<?= site_url('/employeecontroller/getmypermission'); ?>">
                        <div class="leaveRequest_sorting">
                            <div class="leaveRequest_sortby">
                                <select name="sort_by">
                                    <option value="permission_id" <?= ($sortBy == 'permission_id') ? 'selected' : ''; ?>>Default</option>
                                    <option value="permission_date" <?= ($sortBy == 'permission_date') ? 'selected' : ''; ?>>Date</option>
                                    <option value="permission_time" <?= ($sortBy == 'permission_time') ? 'selected' : ''; ?>>Time</option>
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
                    <!-- Pagination Controls -->
                    <div class="pager-container">
                        <p>Page <?= esc($currentPage); ?> of <?= esc($totalPages); ?></p>

                        <div>
                            <?php if ($currentPage > 1): ?>
                                <a href="<?= site_url('empleaves?page=' . ($currentPage - 1) . '&search=' . esc($search) . '&sort_by=' . esc($sortBy) . '&sort_order=' . esc($sortOrder)); ?>"><span class="pagerbtn previes">Previous</span></a>
                            <?php endif; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="<?= site_url('empleaves?page=' . ($currentPage + 1) . '&search=' . esc($search) . '&sort_by=' . esc($sortBy) . '&sort_order=' . esc($sortOrder)); ?>"><span class="pagerbtn next">Next</span></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="leaverequest_table">

                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th class="reason">Reason</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $compen): ?>
                                <tr>
                                    <td> <?= esc($compen['name']); ?></td>
                                    <td><?= esc($compen['permission_date']); ?></td>
                                    <td><?= esc($compen['permission_time']); ?></td>
                                    <td class="td-reason" onclick="showFullText(this)"><?= esc($compen['permission_reason']); ?></td>
                                    <td><?= esc($compen['permission_status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <h3>You not Appled Any leaves</h3>
                <?php endif;  ?>
                </div>
        </div>
    </section>
    <?= view('notification') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function showFullText(element) {
            $(".td-reason").removeClass("full"); // Hide any open text
            $(element).addClass("full"); // Show the clicked text
        }

        $(document).click(function(event) {
            if (!$(event.target).closest(".td-reason").length) {
                $(".td-reason").removeClass("full"); // Hide full content when clicking outside
            }
        });
    </script>
</body>

</html>