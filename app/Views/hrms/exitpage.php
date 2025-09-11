<!DOCTYPE html>
<html>

<head>
    <title>HR Exit Management</title>
    <style>
        #exitModal .modal-content,
        #editModal .modal-content {
            background: #fff;
            padding: 20px;
            width: 500px;
            max-height: 80vh;
            /* limit modal height */
            overflow-y: auto;
            /* enable vertical scroll */
            margin: 80px auto;
            position: relative;
            border-radius: 6px;
        }

        #exitModal .close-btn,
        #editModal .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 5px;
            border: 1px solid #ccc;
        }

        th {
            background-color: #da2442;
            color: white;
        }

        .btn {
            padding: 6px 12px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #0056b3;
        }

        .deleteBtn {
            background: red;
        }

        input,
        select,
        textarea {
            padding: 6px;
            width: 100%;
        }
    </style>
</head>

<body>
    <?= view('navbar/sidebar') ?>

    <div class="container requirement-container">
        <h2>HR Exit Management</h2>

        <div class="">
            <button class="btn" onclick="$('#exitModal').show();">+ Add Exit</button>
            <a href="<?= base_url('hrms/downloadExitClearenceForm')?>"><button>Exit Clearence</button></a>
            <a href="<?= base_url('hrms/downloadExitInterviewForm')?>"><button>Exit Interview</button></a>
        </div>


        <div id="exitSection"></div>

        <!-- Exit Form Modal -->
        <div id="exitModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6);">
            <div class="modal-content ">
                <span style="position:absolute; top:10px; right:15px; cursor:pointer;" onclick="$('#exitModal').hide();">X</span>
                <h3>Add Exit</h3>
                <form id="exitForm" enctype="multipart/form-data">

                    <div>
                        <label>Date:</label>
                        <input type="date" name="exit_date" required>
                    </div>

                    <div>
                        <label>Department:</label>
                        <select name="department_id" id="departmentDropdown" required>
                            <option value="">-- Select Department --</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['dept_id'] ?>"><?= $dept['dept_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Employee Name:</label>
                        <select name="emp_id" id="employeeDropdown" required>
                            <option value="">-- Select Employee --</option>
                        </select>
                    </div>

                    <div>
                        <label>Status:</label>
                        <select name="status" required>
                            <option value="Under Notice Period">Under Notice Period</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Completed">Completed</option>
                            <option value="F&F">F&F Setlled</option>
                            <option value="Exited">Exited</option>
                        </select>
                    </div>

                    <div>
                        <label>Reason:</label>
                        <textarea name="reason"></textarea>
                    </div>

                    <div>
                        <label>Mail Doc:</label>
                        <input type="file" name="mail_doc">
                    </div>

                    <div>
                        <label>Relieve Date:</label>
                        <input type="date" name="relieve_date">
                    </div>

                    <div>
                        <label>Exit Doc:</label>
                        <input type="file" name="exit_doc">
                    </div>

                    <div>
                        <label>Exit Interview Notes:</label>
                        <textarea name="exit_notes"></textarea>
                    </div>

                    <div>
                        <label>Clearance Form:</label>
                        <input type="file" name="clearance_doc">
                    </div>

                    <div style="margin-top:10px;">
                        <button type="submit" class="btn">Add Exit</button>
                    </div>
                </form>

            </div>
        </div>

        <!-- Edit Exit Modal -->
        <div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6);">
            <div class="modal-content">
                <span style="position:absolute; top:10px; right:15px; cursor:pointer;" onclick="$('#editModal').hide();">X</span>
                <h3>Edit Exit</h3>
                <form id="editExitForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">

                    <div>
                        <label>Date:</label>
                        <input type="date" name="exit_date" id="edit_exit_date" required>
                    </div>

                    <!-- <div>
                        <label>Department:</label>
                        <select name="department_id" id="edit_departmentDropdown" required>
                            <option value="">-- Select Department --</option>
 ?>
                        </select>
                    </div> -->

                    <div>
                        <label>Employee ID:</label>
                        <input name="emp_id" id="emp_name" readonly />
                        <!-- <select name="emp_id" id="edit_employeeDropdown" >
                            <option value="">-- Select Employee --</option>
                        </select> -->
                    </div>

                    <div>
                        <label>Status:</label>
                        <select name="status" id="edit_status" required>
                            <option value="Under Notice Period">Under Notice Period</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Completed">Completed</option>
                            <option value="F&F">F&F Setlled</option>
                            <option value="Exited">Exited</option>
                        </select>
                    </div>

                    <div>
                        <label>Reason:</label>
                        <textarea name="reason" id="edit_reason"></textarea>
                    </div>

                    <div>
                        <label>Mail Doc:</label>
                        <input type="file" name="mail_doc">
                    </div>

                    <div>
                        <label>Relieve Date:</label>
                        <input type="date" name="relieve_date" id="edit_relieve_date">
                    </div>

                    <div>
                        <label>Exit Doc:</label>
                        <input type="file" name="exit_doc">
                    </div>

                    <div>
                        <label>Exit Interview Notes:</label>
                        <textarea name="exit_notes" id="edit_exit_notes"></textarea>
                    </div>

                    <div>
                        <label>Clearance Form:</label>
                        <input type="file" name="clearance_doc">
                    </div>

                    <div style="margin-top:10px;">
                        <button type="submit" class="btn">Update Exit</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function loadExits() {
            $.get("<?= base_url('hrms/getExits') ?>", function(data) {
                // console.log(data);
                if (data.length > 0) {
                    let html = `<table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Mail Doc</th>
                        <th>Relieve Date</th>
                        
                        <th>Exit Doc</th>
                        <th>Clearence Doc</th>
                        <th>Actions</th>
                    </tr>
                </thead><tbody>`;
                    data.forEach(row => {
                        let actions = '';
                        if (row.status !== 'F&F') {
                            actions = `
            <button class="btn editBtn" data-id="${row.id}">Edit</button>
            <button class="btn deleteBtn" data-id="${row.id}">Delete</button>
        `;
                        } else {
                            actions = `<span style="color: gray;">Locked</span>`;
                        }

                        html += `<tr>
                        <td>${row.exit_date}</td>
                        <td>${row.name}</td>
                        <td>${row.status}</td>
                        <td>${row.reason}</td>
                        <td>${row.mail_doc ? `<a href="${"<?= base_url() ?>"}uploads/employee_doc/${row.emp_id}/relive/${row.mail_doc}" target="_blank">View</a>` : ''}</td>
                        <td>${row.relieve_date}</td>
                        <td>${row.exit_doc ? `<a href="${"<?= base_url() ?>"}uploads/employee_doc/${row.emp_id}/relive/${row.exit_doc}" target="_blank">View</a>` : ''}</td>
                        <td>${row.clearance_doc ? `<a href="${"<?= base_url() ?>"}uploads/employee_doc/${row.emp_id}/relive/${row.clearance_doc}" target="_blank">View</a>` : ''}</td>
                        <td>${actions}</td>
                        </tr>`;
                    });

                    html += `</tbody></table>`;
                    $('#exitSection').html(html);
                } else {
                    $('#exitSection').html('<p>No exits found.</p>');
                }
            });
        }

        $(document).ready(function() {
            loadExits();

            $('#exitForm').submit(function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                // for (let [key, value] of formData.entries()) {
                //     console.log(key, value);
                // }

                $.ajax({
                    url: "<?= base_url('hrms/addExit') ?>",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // alert(res.message);
                        showPopup(response.message,response.status);
                        $('#exitModal').hide();
                        loadExits();
                        $('#exitForm')[0].reset();
                    }
                });
            });

            $(document).on('click', '.editBtn', function() {
                let id = $(this).data('id');
                $.get(`<?= base_url('hrms/getExitById/') ?>${id}`, {
                    id: id
                }, function(row) {
                    $('#edit_id').val(row.id);
                    $('#edit_exit_date').val(row.exit_date);
                    $('#emp_name').val(row.emp_id);
                    $('#edit_status').val(row.status);
                    $('#edit_reason').val(row.reason);
                    $('#edit_relieve_date').val(row.relieve_date);
                    $('#edit_exit_notes').val(row.exit_notes);

                    $('#editModal').show();
                });
            });

            $('#editExitForm').submit(function(e) {
                e.preventDefault();
                let formData = new FormData(this);

                $.ajax({
                    url: "<?= base_url('hrms/updateExit') ?>",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        showPopup(response.message,response.status);
                        // alert(res.message);
                        $('#editModal').hide();
                        loadExits();
                    }
                });
            });



            $(document).on('change', '#departmentDropdown', function() {
                let deptId = $(this).val();
                $('#employeeDropdown').html('<option value="">-- Select Employee --</option>');

                if (deptId) {
                    $.get("<?= base_url('hrms/EmployeesByDepartment') ?>", {
                        department: deptId
                    }, function(res) {
                        res.forEach(emp => {
                            $('#employeeDropdown').append(`<option value="${emp.emp_id}" data-name="${emp.name}">${emp.name}</option>`);
                        });
                    });
                }
            });


            $(document).on('click', '.deleteBtn', function() {
                if (confirm('Delete this exit?')) {
                    $.post("<?= base_url('hrms/deleteExit') ?>", {
                        id: $(this).data('id')
                    }, function(response) {
                         showPopup(response.message,response.status);
                        // alert(res.message);
                        loadExits();
                    });
                }
            });
        });
    </script>
</body>

</html>