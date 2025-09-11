<!DOCTYPE html>
<html>

<head>
    <title>Employee Appraisal</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <style>
        /* Basic modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: white;
            width: 400px;
            margin: 10% auto;
            padding: 20px;
            border-radius: 5px;
            position: relative;
        }

        .modal-header {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .close-btn {
            position: absolute;
            right: 10px;
            top: 5px;
            cursor: pointer;
            font-size: 20px;
        }

        .form-group {
            margin-bottom: 10px;
        }

        label {
            font-weight: bold;
            display: block;
        }

        select,
        input[type="date"],
        button,
        input[type="text"] {
            width: 100%;
            padding: 7px;
            margin-top: 4px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .btn {
            padding: 7px 12px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }
    </style>
</head>

<body>
        <?= view('navbar/sidebar')?>
    <div class="container all_appraisal_container">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3>Employee Appraisal List</h3>
            <button class="btn btn-primary" id="openModal">Add People</button>
            <a href="<?=base_url('hrms/downloadAppraisalquestion/')?>"><button class="btn btn-primary" id="download_appraisal_qes">Appraisal Question</button></a>
        </div>

        <table id="appraisalTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Stage</th>
                </tr>
            </thead>
            <tbody id="appraisalBody"></tbody>
        </table>

        <!-- Custom Modal -->
        <div class="modal" id="addModal">
            <div class="modal-content">
                <span class="close-btn" id="closeModal">&times;</span>
                <div class="modal-header">Add Appraisal</div>
                <form id="addForm">
                    <div class="form-group">
                        <label>Department</label>
                        <select name="department" id="departmentSelect" required>
                            <option value="">Select Department</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Employee</label>
                        <select name="emp_id" id="employeeSelect" required>
                            <option value="">Select Employee</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="review_date" required>
                    </div>
                    <div style="text-align:right;">
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            loadAppraisalData();

            function loadAppraisalData() {
                let baseDetailUrl = '<?= base_url("hrms/appraisal_details/") ?>';
                $.ajax({
                    url: '<?= base_url("hrms/getAppraisalData") ?>',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        let tableHTML = '';

                        $.each(response.data, function(index, row) {
                            tableHTML += `
                        <tr>
                            <td>${row.dept_name}</td>
                            <td>${row.name}</td>
                            <td>${row.date}</td>
                            <td><a href="${baseDetailUrl}${row.id}">${row.stages}</a></td>
                        </tr>
                    `;
                        });

                        $('#appraisalBody').html(tableHTML);

                        // Initialize DataTable after data load
                        $('#appraisalTable').DataTable();
                    },
                    error: function(xhr, status, error) {
                        console.error("Error loading data:", error);
                    }
                });
            }

            // Open modal
            $('#openModal').click(function() {
                $('#addModal').fadeIn();
                loadDepartments();
            });

            // Close modal
            $('#closeModal').click(function() {
                $('#addModal').fadeOut();
            });

            // Close modal on click outside content
            $(window).click(function(e) {
                if ($(e.target).is('#addModal')) {
                    $('#addModal').fadeOut();
                }
            });

            // Load departments
            function loadDepartments() {
                $.getJSON('<?= base_url("hrms/getDepartments") ?>', function(data) {
                    var deptSelect = $('#departmentSelect');
                    deptSelect.empty().append('<option value="">Select Department</option>');
                    $.each(data, function(i, dept) {
                        deptSelect.append('<option value="' + dept.dept_id + '">' + dept.dept_name + '</option>');
                    });
                });
            }

            // Load employees when department changes
            $('#departmentSelect').on('change', function() {
                var deptId = $(this).val();
                var empSelect = $('#employeeSelect');
                empSelect.empty().append('<option value="">Select Employee</option>');

                if (deptId) {
                    $.getJSON('<?= base_url("hrms/getEmployeesByDepartment") ?>/' + deptId, function(data) {
                        $.each(data, function(i, emp) {
                            empSelect.append('<option value="' + emp.emp_id + '">' + emp.name + '</option>');
                        });
                    });
                }
            });

            // Submit form
            $('#addForm').on('submit', function(e) {
                e.preventDefault();
                $.post('<?= base_url("hrms/storeappraisal") ?>', $(this).serialize(), function(res) {
                    if (res.status === 'success') {
                        $('#addModal').fadeOut();
                        $('#addForm')[0].reset();
                        loadAppraisalData();
                    }
                }, 'json');
            });
        });
    </script>

</body>

</html>