<div class="hrdasbord_container">
    <div class="hrdasboard_header">
        <div class="navigations eff1">
            <a href="<?= base_url() ?>hrcontroller/ShowingLeaveRequests">
                <div>Leave<br>Requests</div>
                <div class="value" id="leave_requests">0/0</div>
            </a>
        </div>
        <div class="navigations eff1">
            <a href="<?= base_url() ?>hrcontroller/showAllCompensation">
                <div>Compensation<br> Requests</div>
                <div class="value" id="compensation_requests">0/0</div>
            </a>
        </div>
        <div class="navigations eff1">
            <a href="<?= base_url() ?>hrcontroller/getallpermission">
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
                    <h2 id="employeeName"></h2>
                    <div>
                        <div id="leaveBalence"></div>
                        <div id="shortFall"></div>
                    </div>
                </div>
                <div class="plan-sec">
                    <h4>OE Plan</h4>
                    <div>
                        <div id="leavePlans">
                            <h4 class="">
                                Leave
                                <span id="leavePlanCount">0</span>
                            </h4>
                            <div class="" id="leavePlan">
                                <p></p>

                            </div>
                        </div>
                        <div class="compen">
                            <h4>
                                Compensation
                                <span id="compenPlanCount">0</span>
                            </h4>
                            <div class="">
                                <p id="compenPlan"></p>
                            </div>
                        </div>
                        <div class="permission">
                            <h4>Permission</h4>
                            <p id="permissionPlan"></p>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="">
                        <h4>After 8pm</h4>
                        <div class="after8pm-cont">
                        </div>
                    </div>
                    <div class="">

                    </div>
                </div>
            </div>
            <div class="nodata">
                <img src="https://i.pinimg.com/originals/8d/b8/e6/8db8e6f39203f657ee8efad634cacad1.gif" alt="no Data">
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
        $('.userDetails').fadeIn();
        $('.nodata').fadeOut();

        let user_id = $(this).data('id');
        $.ajax({
            url: baseurl + 'dashboard/dashboardDatas/' + user_id,
            method: 'GET',
            success: function(res) {
                console.log(res)
                const after8 = res.affter8pm;
                let after8Pm = '';
                if (after8.length > 0) {
                    after8.forEach(e => {
                        let parts = e.timestamp.split(" ");
                        let datePart = parts[0]; // "2025-09-01"
                        let timePart = parts[1]; // "20:07:24"

                        // Format date as "12 Aug"
                        let d = new Date(datePart);
                        let options = {
                            day: "2-digit",
                            month: "short"
                        };
                        let formattedDate = d.toLocaleDateString("en-GB", options);

                        // Remove seconds from time
                        let formattedTime = timePart.slice(0, 5); // "20:07"

                        after8Pm += `<div>
                                        <div>${formattedDate}</div>
                                        <div>${formattedTime}</div>
                                    </div>`
                    });
                } else {
                    after8Pm += 'No Data'
                }

                $('.after8pm-cont').empty();
                $('#employeeName').text('');
                $('#leaveBalence').text('');
                $('#shortFall').text('');
                $('#leavePlan').text('');
                $('#leavePlanCount').text('');
                $('#compenPlan').text('');
                $('#compenPlanCount').text('');
                $('#permissionPlan').text('');

                $('.after8pm-cont').append(after8Pm);
                $('#employeeName').text(res.employeeData.name);
                $('#leaveBalence').text(`Leave Balence [${res.employeeData.remaining_leaves}]`);
                $('#shortFall').text(`Short Fall [${res.Attendance.sortfall}]`);
                if (res.planLeave != null) {
                    $('#leavePlan').text(res.planLeave.tentative || '');
                    $('#leavePlanCount').text(res.planLeave.tentative_leave_count || 0);
                    $('#compenPlan').text(res.planLeave.tentative_leave_compensation || '');
                    $('#compenPlanCount').text(res.planLeave.tentative_leave_compensation_count || 0);
                    $('#permissionPlan').text(res.planLeave.tentative_leave_permission || '');
                }
            }
        })
    })
</script>