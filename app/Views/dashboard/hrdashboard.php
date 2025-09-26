<div class="hrdasbord_container">
    <div class="hrdasboard_header">
        <div class="navigations eff1">
            <a href="">
                <div>Leave<br>Requests</div>
                <div class="value" id="leave_requests">0/0</div>
            </a>
        </div>
        <div class="navigations eff1">
            <a href="">
                <div>Compensation<br> Requests</div>
                <div class="value" id="compensation_requests">0/0</div>
            </a>
        </div>
        <div class="navigations eff1">
            <a href="">
                <div>Permission<br>Requests</div>
                <div class="value" id="permission_requests">0/0</div>
            </a>
        </div>
        <div class="navigations eff1">
            <a href="">
                <div>Staff<br>Attendance</div>
                <div class="value"><img src="<?= base_url() ?>asset/icons/fingerprint.png" alt=""></div>
            </a>
        </div>
    </div>
    <div class="hrdasboard_body">
        <div class="">
            <div>
                <table id="recordsTable" class="display">
                    <thead>
                        <tr id="tableHeader"></tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>
        </div>
        <div class="user_detail_container">
            <div class="userDetails">
                <div>
                    <h2 class="name">Demo</h2>
                    <div>
                        <div id="leaveBalence">Leavebalence [3]</div>
                        <div id="shortFall">Shortfall [00:00]</div>
                    </div>
                </div>
                <div class="plan-sec">
                    <h4>OE Plan</h4>
                    <div>
                        <div class="leave">
                            <h4 class="">
                                Leave
                                <span>2</span>
                            </h4>
                            <div class="">
                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ab, eum!</p>

                            </div>
                        </div>
                        <div class="compen">
                            <h4 class="">
                                Compensation
                                <span>2</span>
                            </h4>
                            <div class="">
                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ab, eum!</p>
                            </div>
                        </div>
                        <div class="permission">
                            <h4 class="">Permission</h4>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ab, eum!</p>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="">
                        <h4>After 8pm</h4>
                        <div class="after8pm-cont">
                            <div>
                                <div>12 Aug</div>
                                <div>20:12</div>
                            </div>
                            <div>
                                <div>12 Aug</div>
                                <div>20:12</div>
                            </div>
                            <div>
                                <div>12 Aug</div>
                                <div>20:12</div>
                            </div>
                            <div>
                                <div>12 Aug</div>
                                <div>20:12</div>
                            </div>
                        </div>
                    </div>
                    <div class="">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $.ajax({
            url: baseurl + 'hrcontroller/getStaffDetails',
            method: "GET",
            success: function(res) {
                const response = res.data;
                // return;
                console.log(res);

                $('#leave_requests').text(``);
                $('#leave_requests').text(`${res.pending}/${res.total}`);
                
                $('#compensation_requests').text(``);
                $('#compensation_requests').text(`${res.pendingCompen}/${res.totalCompen}`);
                
                $('#permission_requests').text(``);
                $('#permission_requests').text(`${res.per_pending}/${res.per_total}`);

                // Get months from "oe" object (latest first)
                let months = Object.keys(res.oe).reverse();

                // Build header
                let headerHtml = "<th>ID</th> <th> Name </th><th style='display:none'> Dept </th>";
                $.each(months, function(i, month) {
                    headerHtml += "<th>" + month + "</th>";
                });
                $("#tableHeader").html(headerHtml);

                // Build body
                let bodyHtml = "";
                $.each(response, function(id, emp) {
                    bodyHtml += "<tr>";
                    bodyHtml += "<td>" + id + "</td>";
                    bodyHtml += "<td><a href='#' class = 'user-data' data-id = '" + id + "'>" + emp.name + "</td>";
                    bodyHtml += "<td style='display:none'>" + emp.dept + "</td>";

                    $.each(months, function(i, month) {
                        let rec = emp.records[month] || {
                            compensation: 0,
                            leaves: 0
                        };
                        bodyHtml += "<td>" + rec.leaves + "|" + rec.compensation + "</td>";
                    });

                    bodyHtml += "</tr>";
                });
                $("#tableBody").html(bodyHtml);

                // Initialize DataTable
                $('#recordsTable').DataTable({
                    pageLength: 10,
                    stripeClasses: [], // ← disable automatic odd/even classes
                    ordering: true,
                    searching: true,
                    scrollX: true,
                    order: [
                        [2, 'asc']
                    ],
                });
            }
        })

    });
    $(document).on('click', '.user-data', function(e) {
        e.preventDefault();
        let user_id = $(this).data('id');
        $.ajax({
            url: baseurl + '' + user_id,
            method: 'GET',
            success: function(res) {

            }
        })
    })
</script>