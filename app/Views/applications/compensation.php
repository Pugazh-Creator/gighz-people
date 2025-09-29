<div class="requestTableContainer">
    <table id="compenRequestTable" class="display">
        <thead>
            <tr>
                <th>Name</th>
                <th style='display:none'>Created At</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Total</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="compenRequestTableBody">
            <!-- rows will be injected here -->
        </tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        getCompensation();


        $(document).on('click', '.tbl-action', function() {
            let url = $(this).data('url');
            $.ajax({
                url: url,
                method: 'GET',
                success: function(res) {
                    if (res.status == "success") {
                        showPopup(res.message, res.status);
                        getCompensation();
                    } else {
                        showPopup(res.message, res.status);
                    }
                },
                error: function(err) {
                    console.log(err);
                    showPopup('Failed to change Status', 'error');
                }
            })
        })

        $(document).on('click', '.td-reason', function() {
            $(".td-reason").removeClass("full"); // Hide any open text
            $(this).addClass("full"); // Show the clicked text
        });
        
    })

    function getCompensation() {
        $.ajax({
            url: baseurl + 'hrcontroller/getAllCompensationRequests',
            method: 'GET',
            success: function(res) {

                // Destroy old DataTable if exists
                if ($.fn.DataTable.isDataTable('#compenRequestTable')) {
                    $('#compenRequestTable').DataTable().clear().destroy();
                }
                let tbody = '';

                if (res.length > 0) {
                    res.forEach(e => {
                        tbody += `<tr>
                                    <td>${e.name}</td>
                                    <td style="display:none">${e.created_at}</td>
                                    <td>${e.start_date}</td>
                                    <td>${e.end_date}</td>
                                    <td>${e.num_of_days}</td>
                                    <td class="td-reason" >${e.reason}</td>
                                    <td>${e.status}</td>
                                    <td class="actions">
                                        <a class="tbl-action approve" href ="#" data-url ="<?= base_url('hrcontroller/changeCompenStatus') ?>/${e.id}/approved/${e.emp_id}/${e.num_of_days}"><i class='bx bx-check'></i></a> |
                                        <a class="tbl-action reject" href ="#" data-url="<?= base_url('hrcontroller/changeCompenStatus') ?>/${e.id}/rejected/${e.emp_id}/${e.num_of_days}"><i class='bx bx-x'></i></a> |
                                        <a class="tbl-action delete" href ="#" data-url="<?= base_url('hrcontroller/changeCompenStatus') ?>/${e.id}/delete/${e.emp_id}/${e.num_of_days}"><i class='bx bx-trash'></i></a>
                                    </td>
                                </tr>`;
                    });
                } else {
                    tbody = "<tr><td colspan='8'>No Data</td><tr>";
                }

                $('#compenRequestTableBody').html(tbody);

                $('#compenRequestTable').DataTable({
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
        })

    }
</script>