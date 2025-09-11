<!DOCTYPE html>
<html>

<head>
    <title>Employee Asset Management</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 3px;
            border: 1px solid #ccc;
        }

        th {
            background-color: #da2442;
        }

        select,
        input[type="text"],
        textarea {
            padding: 6px;
            width: 200px;
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

        .form-row {
            margin-bottom: 10px;
        }

        #addAssetForm {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
        }

        #addAssetForm1   {
            display: block;
            margin-right:10px ;

        }

        .deleteBtn {
            background-color: red;
        }

        #assetSection input[type="text"],
        textarea {
            border: none;
            background-color: #CCC5B9;
        }
    </style>
</head>

<body>
    <?= view('navbar/sidebar') ?>

    <div class="container requirement-container">
        <div class="requiment-child">
            <h2>Employee Asset Management</h2>

            <div class="form-row">
                <label>Department:</label>
                <select id="departmentDropdown">
                    <option value="">-- All Departments --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['dept_id'] ?>"><?= $dept['dept_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <label>Employee:</label>
                <select id="employeeDropdown">
                    <option value="">-- Select Employee --</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['emp_id'] ?>"><?= $emp['name'] ?> (<?= $emp['emp_id'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="addAssetForm1" style="margin-top: 20px;">
                <button class="btn" onclick="$('#assetModal').show();">+ Add New Asset</button>
            </div>

            <div id="assetSection">
                <!-- Asset list will load here -->
            </div>


            <!-- Add Asset Modal -->
            <div id="assetModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
                <div style="background:#fff; padding:20px; width:400px; margin:80px auto; position:relative;">
                    <span style="position:absolute; top:10px; right:15px; cursor:pointer;" onclick="$('#assetModal').hide();">X</span>
                    <h3>Add New Asset</h3>
                    <form id="newAssetForm">
                        <input type="hidden" name="emp_id" id="new_emp_id" />
                        <div class="form-row">
                            <label>Asset ID:</label>
                            <input type="text" name="asset_id" required />
                        </div>
                        <div class="form-row">
                            <label>Asset Name:</label>
                            <input type="text" name="asset_name" required />
                        </div>
                        <div class="form-row">
                            <label>Date:</label>
                            <input type="date" name="date" required />
                        </div>
                        <div class="form-row">
                            <label>Description:</label>
                            <textarea name="description" required></textarea>
                        </div>
                        <div class="form-row">
                            <button type="submit" class="btn">Add Asset</button>
                        </div>
                    </form>
                </div>
            </div>


        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function renderAssets(empId) {
            $('#assetSection').html('');
            if (empId) {
                $.get("<?= base_url('hrms/getEmployeeAssets') ?>", {
                    emp_id: empId
                }, function(assets) {
                    if (assets.length > 0) {
                        let html = `<table>
                            <thead>
                                <tr>
                                    <th>Asset ID</th>
                                    <th>Asset Name</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead><tbody>`;

                        assets.forEach(asset => {
                            html += `
                                <tr>
                                    <td><input type="text" class="asset_id" value="${asset.asset_id}" readonly/></td>
                                    <td><input type="text" class="asset_name" value="${asset.asset_name}" readonly/></td>
                                    <td><input type="text" class="asset_date" value="${asset.date}" readonly/></td>
                                    <td><textarea class="description" readonly>${asset.description}</textarea></td>
                                    <td>
                                        <button class="btn updateBtn" data-id="${asset.id}">Update</button>
                                         <button class="btn deleteBtn" data-id="${asset.id}">Delete</button>
                                    </td>
                                </tr>
                            `;
                        });

                        html += `</tbody></table>`;
                        $('#assetSection').html(html);
                    } else {
                        $('#assetSection').html('<p>No assets found for this employee.</p>');
                    }
                });
            }
        }

        $(document).ready(function() {
            // On department change
            $('#departmentDropdown').change(function() {
                const deptId = $(this).val();
                $('#employeeDropdown').html('<option value="">-- Select Employee --</option>');
                $('#assetSection').html('');
                $('#addAssetForm').hide();

                $.get("<?= base_url('hrms/EmployeesByDepartment') ?>", {
                    department: deptId
                }, function(res) {
                    res.forEach(emp => {
                        $('#employeeDropdown').append(`<option value="${emp.emp_id}">${emp.name} (${emp.emp_id})</option>`);
                    });
                });
            });

            // On employee change
            $('#employeeDropdown').change(function() {
                const empId = $(this).val();
                console.log(empId);
                $('#new_emp_id').val(empId);

                if (empId) {
                    renderAssets(empId);
                    $('#addAssetForm').show();
                } else {
                    $('#assetSection').html('');
                    $('#addAssetForm').hide();
                }
            });

            // Add asset manually (no serialize)
            $('#newAssetForm').submit(function(e) {
                e.preventDefault();

                const asset_id = $('input[name="asset_id"]').val();
                const asset_name = $('input[name="asset_name"]').val();
                const date = $('input[name="date"]').val();
                const description = $('textarea[name="description"]').val();
                const emp_id = $('#new_emp_id').val();


                $.post("<?= base_url('hrms/saveAsset') ?>", {
                    asset_id: asset_id,
                    asset_name: asset_name,
                    date: date,
                    description: description,
                    emp_id: emp_id
                }, function(response) {

                    // alert(response.message || 'Asset added successfully');
                    showPopup(response.message,response.status);
                    renderAssets(emp_id);
                    $('#newAssetForm')[0].reset();
                    $('#assetModal').hide();
                });
            });

            // Update asset
            $(document).on('click', '.updateBtn', function() {
                const row = $(this).closest('tr');
                const id = $(this).data('id');

                if ($(this).text() === 'Update') {
                    // Make fields editable
                    row.find('input, textarea').removeAttr('readonly').css('background', '#eef');
                    $(this).text('Submit');
                } else {
                    // Get updated values
                    row.find('input, textarea').css('background-color', '#FDFFFC')
                    const asset_id = row.find('.asset_id').val();
                    const asset_name = row.find('.asset_name').val();
                    const date = row.find('.asset_date').val();
                    const description = row.find('.description').val();

                    $.post("<?= base_url('hrms/updateAsset') ?>", {
                        id,
                        asset_id,
                        asset_name,
                        description
                    }, function(response) {
                        showPopup(response.message,response.status);
                        // alert(response.message || 'Asset updated successfully');
                        // Make fields readonly again
                        row.find('input, textarea').attr('readonly', true).css('background', '');
                        row.find('.updateBtn').text('Update');
                    });
                }
            });

            //delete assets
            $(document).on('click', '.deleteBtn', function() {
                const id = $(this).data('id');

                if (confirm('Are you sure you want to delete this asset?')) {
                    $.post("<?= base_url('hrms/deleteAsset') ?>", {
                        id
                    }, function(response) {
                        // alert(response.message || 'Asset deleted');
                        showPopup(response.message,response.status);

                        $('#employeeDropdown').change(); // reload asset list
                    });
                }
            });


            // Initial load all employees (first one selected)
            const firstEmpId = $('#employeeDropdown option:eq(1)').val();
            if (firstEmpId) {
                $('#employeeDropdown').val(firstEmpId).change();
            }
        });
    </script>

</body>

</html>