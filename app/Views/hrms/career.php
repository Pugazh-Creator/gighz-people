<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        /* Modal backdrop */
        #jobModal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        /* Modal content box */
        #jobModal form {
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            width: 100%;
            z-index: 1001;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 500px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        /* Form group spacing */
        #jobModal form>div {
            margin-bottom: 15px;
        }

        /* Input and select styling */
        #jobModal label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        #jobModal input[type="text"],
        #jobModal input[type="number"],
        #jobModal textarea,
        #jobModal select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* Button styling */
        #saveBtn {
            padding: 10px 20px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        #saveBtn:hover {
            background-color: #0b5ed7;
        }

        .close-btn{
            position: absolute;
            top: 5px;
            right: 5px;

        }
    </style>

</head>

<body>
    <?= view('navbar/sidebar') ?>
    <div class="container career_container">
        <h2>Career Management</h2>
        <button id="addBtn" class="btn btn-primary">Add New Job</button>
        <table id="jobsTable" class="display">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Role</th>
                    <th>Description</th>
                    <th>Count</th>
                    <th>Package</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>


        <!-- Modal -->
        <div id="jobModal" style="display:none;">
            <form id="jobForm">
                <button class="close-btn">✕</button>
                <input type="hidden" name="id" id="jobId">
                <div>
                    <label>Department</label>
                    <select name="department" id="departmentSelect"></select>
                </div>
                <div>
                    <label>Role</label>
                    <select name="role" id="roleSelect"></select>
                </div>
                <div>
                    <label>Description</label>
                    <textarea name="description" id="description"></textarea>
                </div>
                <div>
                    <label>Count</label>
                    <input type="number" name="count" id="count">
                </div>
                <div>
                    <label>Package</label>
                    <input type="text" name="package" id="package">
                </div>
                <div>
                    <label>Active</label>
                    <input type="checkbox" name="active" id="active">
                </div>
                <button type="button" id="saveBtn">Add</button>
            </form>
        </div>
    </div>

    <script>
        loadcareertable();

        function loadcareertable() {
            $.ajax({
                url: '<?= base_url('hrms/listCareers') ?>',
                method: "GET",
                success: function(data) {
                    console.log(data);

                    if ($.fn.DataTable.isDataTable('#jobsTable')) {
                        $('#jobsTable').DataTable().destroy();
                    }

                    let tr = '';
                    data.data.forEach(row => {
                        tr += `
                        <tr>
                            <td>${row.dept_name}</td>
                            <td>${row.position_name}</td>
                            <td>${row.description}</td>
                            <td>${row.count}</td>
                            <td>${row.package}</td>
                            <td>${row.created}</td>
                            <td><button class="editBtn" data-id="${row.id}">Edit</button></td>
                        </tr>
                    `;
                    });

                    $('#jobsTable tbody').html(tr);
                    $('#jobsTable').DataTable({
                        order: [
                            [5, 'desc']
                        ], // column index starts from 0 (so 4 = 5th column)
                        searching: true // enable search box
                    });
                },
                error: function(error) {
                    console.log('Error: ' + error);
                }
            });
        }

        $(document).ready(function() {
            // Add new job
            $('#addBtn').on('click', function() {
                $('#jobForm')[0].reset();
                $('#jobId').val('');
                $('#saveBtn').text('Add');
                loadDeptRole();
                openModal();
            });

            // Edit button action
            $('#jobsTable').on('click', '.editBtn', function() {
                const id = $(this).data('id');

                $.get('<?= base_url('hrms/listCareers') ?>', function(resp) {
                    const job = resp.data.find(j => j.id == id);

                    if (!job) return;

                    $('#jobId').val(job.id);
                    $('#description').val(job.description);
                    $('#count').val(job.count);
                    $('#package').val(job.package);
                    $('#active').prop('checked', job.active == 1);
                    loadDeptRole(job.dept_id, job.role_id);
                    $('#saveBtn').text('Update');
                    openModal();
                });
            });

            // Save or Update job
            $('#saveBtn').on('click', function() {
                const url = $('#jobId').val() ?
                    '<?= base_url('hrms/editCareer') ?>' :
                    '<?= base_url('hrms/addCareer') ?>';

                $.post(url, $('#jobForm').serialize(), function(res) {
                    if (res.status) {
                        loadcareertable(); // reload the updated table
                        closeModal();
                    }
                }, 'json');
            });

            // Load departments and roles
            function loadDeptRole(selectedDept = '', selectedRole = '') {
                $.getJSON('<?= base_url('hrms/getDepartments') ?>', function(departments) {
                    $('#departmentSelect').empty();
                    $.each(departments, function(_, dept) {
                        $('#departmentSelect').append(
                            $('<option>', {
                                value: dept.dept_id,
                                text: dept.dept_name,
                                selected: dept.dept_id == selectedDept
                            })
                        );
                    });

                    $.getJSON('<?= base_url('hrms/getDesignations') ?>', function(positions) {
                        $('#roleSelect').empty();
                        $.each(positions, function(_, pos) {
                            $('#roleSelect').append(
                                $('<option>', {
                                    value: pos.position_id,
                                    text: pos.position_name,
                                    selected: pos.position_id == selectedRole
                                })
                            );
                        });
                    });
                });
            }

            function openModal() {
                $('#jobModal').fadeIn();
            }

            function closeModal() {
                $('#jobModal').fadeOut();
            }

            $('.close-btn').on('click', function(e){
                e.preventDefault();
                closeModal();
            })
        });
    </script>


</body>

</html>