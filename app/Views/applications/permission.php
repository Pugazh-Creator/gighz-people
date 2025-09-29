<div class="requestTableContainer">
    <table id="permissionRequestTable" class="display">
        <thead>
            <tr>
                <th>Name</th>
                <th style='display:none'>Created At</th>
                <th>Date</th>
                <th>Hours</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="permissionRequestTableBody">
            <!-- rows will be injected here -->
        </tbody>
    </table>
</div>

<script>
    function getPermission() {
        $.ajax({
            url: baseurl + 'hrcontroller/getPermission',
            method: "GET",
            success: function(res) {
                console.log(res)


                // Destroy old DataTable if exists
                if ($.fn.DataTable.isDataTable('#permissionRequestTable')) {
                    $('#permissionRequestTable').DataTable().clear().destroy();
                }

                let tbody = '';
                if (res.length > 0) {
                    res.forEach(e => {
                        tbody += `<tr>
                                        <td>${e.name}</td>
                                        <td style='display:none'>${e.permission_created}</td>
                                        <td>${e.permission_date}</td>
                                        <td>${e.permission_time}</td>
                                        <td class="td-reason">${e.permission_reason}</td>
                                        <td>${e.permission_status}</td>
                                        <td class="actions">
                                            <a class="tbl-action approve" href="#"  data-url="<?= base_url('/hrcontroller/changepermissionstatus') ?>/${e.permission_id}/approved/${e.permission_user_id}/"><i class='bx bx-check'></i></a> |
                                            <a class="tbl-action reject" href="#" data-url="<?= base_url('/hrcontroller/changepermissionstatus') ?>/${e.permission_id}/rejected/${e.permission_user_id}/"><i class='bx bx-x'></i></a> |
                                            <a class="tbl-action delete" href="#" data-url="<?= base_url('/hrcontroller/changepermissionstatus') ?>/${e.permission_id}/delete/${e.permission_user_id}/"><i class='bx bx-trash'></i></a>
                                        </td>
                                    </tr>`;
                    });
                } else {
                    tbody = "<tr><td colspan = '7'></td></tr>";
                }

                $('#permissionRequestTableBody').html(tbody);

                $('#permissionRequestTable').DataTable({
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
            },
            error: function(err) {
                console.log(err);
            }
        })
    }

    $(document).ready(function() {
        $(document).on('click', '.td-reason', function() {
            $(".td-reason").removeClass("full"); // Hide any open text
            $(this).addClass("full"); // Show the clicked text
        });
        getPermission();

        $(document).on('click', '.tbl-action', function(e) {
            e.preventDefault();
            let url = $(this).data('url');
            console.log(url);

            $.ajax({
                url: url,
                method: "GET",
                success: function(res) {
                    if (res.status == 'success') {
                        showPopup(res.message, res.status);
                        getPermission();
                    } else {
                        showPopup(res.message, res.status);
                    }
                },
                error: function(err) {
                    console.log(err);
                    showPopup('Failed to Update', "error");
                }
            })
        })
    })
</script>