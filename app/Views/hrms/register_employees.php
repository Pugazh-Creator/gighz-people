<!DOCTYPE html>
<html>

<head>
    <title>Employee Register</title>
    <style>
        label {
            display: block;
            margin-top: 10px;
        }

        select,
        input,
        textarea,
        button {
            margin-top: 5px;
            padding: 6px;
            width: 200px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            margin: 5% auto;
            width: 90%;
            max-width: 600px;
            border-radius: 5px;
            position: relative;
            overflow-y: auto;
            max-height: 80vh;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            cursor: pointer;
            font-size: 20px;
        }
    </style>
</head>

<body>

    <?= view('navbar/sidebar') ?>
    <div class="container register_employees">
        <h2>Employee Register</h2>

        <label>Select Type:</label>
        <select id="user_type">
            <option value="">-- Select --</option>
            <option value="intern">Intern</option>
            <option value="employee">Employee</option>
        </select>

        <!-- Intern Select -->
        <div id="intern_select_div" style="display:none;">
            <label>Select Intern:</label>
            <select id="intern_id">
                <option value="">-- Select Intern --</option>
                <?php foreach ($interns as $intern): ?>
                    <option value="<?= $intern['id'] ?>"><?= esc($intern['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Intern Details -->
        <div id="intern_details" style="margin-top:20px; display:none;">
            <h3>Intern Details</h3>
            <div id="details_box" style="border:1px solid #ccc; padding:10px;"></div>
            <button id="openAddModal">Add</button>
        </div>



        <!-- Intern Upload Modal -->
        <div class="modal" id="addModal">
            <div class="modal-content">
                <span class="close-btn" id="closeModal">&times;</span>
                <h3>Upload Documents</h3>
                <form id="uploadForm" enctype="multipart/form-data">
                    <input type="hidden" name="intern_id" id="modal_intern_id">

                    <label>Start Date:</label>
                    <input type="date" name="start_date" id="intern_start_date" required>

                    <label>End Date:</label>
                    <input type="date" name="end_date" id="intern_end_date" required>

                    <label>Select Documents:</label>
                    <input type="file" name="intern_documents[]" id="intern_documents" accept="application/pdf" multiple>

                    <br><br>
                    <button type="submit">Upload</button>
                    <button id="downloadListDocBtn" type="button">Download List Docx</button>
                </form>
                <ul id="fileList"></ul>
            </div>
        </div>

        <!-- ✅ Only ONE employee_details block -->
        <div id="employee_section" style="display:none; margin-top: 20px;">
            <label>Select Department:</label>
            <select id="department_id"></select>

            <label>Select Employee:</label>
            <select id="employee_id"></select>

            <!-- ✅ Employee Details Block -->
            <div id="employee_details" style="margin-top: 15px; display:none; border: 1px solid #ccc; padding: 15px;">
                <h3>Employee Details</h3>
                <div id="employee_info">
                    <!-- Populated via JS -->
                </div>
                <br>
                <button type="button" id="openEmployeeModal">Update</button>
                <button type="button" id="openEmployeeDocModal">Add Documents</button>
            </div>
        </div>
        <!-- Employee Update Modal -->
        <div class="modal" id="employeeModal">
            <div class="modal-content">
                <span class="close-btn" id="closeEmployeeModal">&times;</span>
                <h3>Update Employee Information</h3>

                <!-- Nav -->
                <div id="formStepsNav" style="margin-bottom: 15px;">
                    <button type="button" class="step-tab" data-step="1">General</button>
                    <button type="button" class="step-tab" data-step="2">Contact</button>
                    <button type="button" class="step-tab" data-step="3">Job</button>
                    <button type="button" class="step-tab" data-step="4">Personal</button>
                    <button type="button" class="step-tab" data-step="5">Education</button>
                    <button type="button" class="step-tab" data-step="6">Family</button>
                </div>

                <form id="employeeModalForm">
                    <input type="hidden" name="no" id="no">

                    <!-- Step 1 -->
                    <div class="form-step" data-step="1">
                        <h4>General Info</h4>
                        <label>First Name:</label><input type="text" name="first_name" id="first_name">
                        <label>Last Name:</label><input type="text" name="last_name" id="last_name">
                        <label>Initial:</label><input type="text" name="initial" id="initial">
                        <label>Name:</label><input type="text" name="name" id="name">
                        <label>DOB:</label><input type="date" name="dob" id="dob">
                        <label>DOJ:</label><input type="date" name="doj" id="doj">
                        <label>Gender:</label>
                        <select name="gender" id="gender">
                            <option value="">-- Select --</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <br><br>
                        <button type="button" class="next-step">Next</button>
                    </div>

                    <!-- Step 2 -->
                    <div class="form-step" data-step="2" style="display: none;">
                        <h4>Contact</h4>
                        <label>Official Mail:</label><input type="email" name="official_mail" id="official_mail">
                        <label>Personal Mail:</label><input type="email" name="personal_mail" id="personal_mail">
                        <label>Phone:</label><input type="text" name="phone_no" id="phone_no">
                        <label>Alternate No:</label><input type="text" name="alternate_no" id="alternate_no">
                        <label>Emergency Contact:</label><input type="text" name="emergency_contact" id="emergency_contact">
                        <label>Alter Contact:</label><input type="text" name="alter_contact" id="alter_contact">
                        <br><br>
                        <button type="button" class="prev-step">Back</button>
                        <button type="button" class="next-step">Next</button>
                    </div>

                    <!-- Step 3: Job Info -->
                    <div class="form-step" data-step="3" style="display: none;">
                        <h4>Job Info</h4>
                        <!-- <label>Role:</label><input type="text" name="role" id="role"> -->
                        <label>Role:</label>
                        <select name="role" id="role">
                            <option value="">-- Select Role --</option>
                        </select>
                        <label>Emp ID:</label><input type="text" name="emp_id" id="emp_id">
                        <label>Attendance ID:</label><input type="number" name="attendance_id" id="attendance_id">

                        <label>Designation:</label>
                        <select name="designation" id="designation">
                            <option value="">-- Select Designation --</option>
                        </select>

                        <label>Department:</label>
                        <select name="dept" id="dept">
                            <option value="">-- Select Department --</option>
                        </select>
                        <label>Grade:</label><input type="text" name="grade" id="grade">
                        <label>Source Hire:</label><input type="text" name="source_hire" id="source_hire">
                        <label>Experience:</label><input type="number" name="exprience" id="exprience">
                        <br><br>
                        <button type="button" class="prev-step">Back</button>
                        <button type="button" class="next-step">Next</button>
                    </div>

                    <!-- Step 4: Personal -->
                    <div class="form-step" data-step="4" style="display: none;">
                        <h4>Personal</h4>
                        <label>Blood Group:</label><input type="text" name="blood_group" id="blood_group">
                        <label>Marriage:</label><input type="text" name="marriage" id="marriage">
                        <label>Aadhaar:</label><input type="text" name="aadhaar" id="aadhaar">
                        <label>PAN:</label><input type="text" name="pan" id="pan">
                        <label>City:</label><input type="text" name="city" id="city">
                        <label>Pincode:</label><input type="text" name="pincode" id="pincode">
                        <label>Residential Address:</label><textarea name="residential_address" id="residential_address"></textarea>
                        <label>Permanent Address:</label><textarea name="permanent_address" id="permanent_address"></textarea>
                        <br><br>
                        <button type="button" class="prev-step">Back</button>
                        <button type="button" class="next-step">Next</button>
                    </div>

                    <!-- Step 5: Education -->
                    <div class="form-step" data-step="5" style="display: none;">
                        <h4>Education</h4>
                        <label>Course Name:</label><input type="text" name="course_name" id="course_name">
                        <label>Institute:</label><input type="text" name="institute" id="institute">
                        <label>Completed Date:</label><input type="text" name="course_completed_date" id="course_completed_date">
                        <label>Course Grade:</label><input type="text" name="course_grade" id="course_grade">
                        <br><br>
                        <button type="button" class="prev-step">Back</button>
                        <button type="button" class="next-step">Next</button>
                    </div>

                    <!-- Step 6: Family -->
                    <div class="form-step" data-step="6" style="display: none;">
                        <h4>Family</h4>
                        <label>Guardian Name:</label><input type="text" name="guradian_name" id="guradian_name">
                        <input type="hidden" name="emp_status" id="emp_status">
                        <input type="hidden" name="image" id="image">
                        <input type="hidden" name="additional_dept" id="additional_dept">
                        <br><br>
                        <button type="button" class="prev-step">Back</button>
                        <button type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- employee documents -->

        <div class="modal" id="employeeDocModal">
            <div class="modal-content">
                <span class="close-btn" id="closeEmployeeDocModal">&times;</span>
                <h3>Upload Employee Documents</h3>
                <form id="employeeDocForm" enctype="multipart/form-data">
                    <input type="hidden" name="employee_id" id="doc_employee_id">
                    <input type="hidden" name="employee_name" id="doc_employee_name">

                    <label>Select Documents:</label>
                    <input type="file" name="emp_documents[]" accept="application/pdf" multiple required>

                    <br><br>
                    <button type="submit">Upload</button>
                    <button type="button" id="generateDocListBtn">Generate Document List</button>
                </form>

                <div id="employeeDocsList">

                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Employee & Intern JS Logic -->
    <script>
        const baseurl = '<?= base_url() ?>';
        $(function() {
            const internSelect = $('#intern_select_div');
            const internDetails = $('#intern_details');
            const employeeSection = $('#employee_section');
            const employeeDetails = $('#employee_details');
            const addModal = $('#addModal');
            const employeeModal = $('#employeeModal');
            let selectedEmployeeData = null;
            let currentStep = 1;


            // Show specific modal section
            function showStep(step) {
                $('.form-step').hide();
                $(`.form-step[data-step="${step}"]`).show();
                currentStep = step;
            }

            // Handle user type switch
            $('#user_type').on('change', function() {
                const type = $(this).val();

                if (type === 'intern') {
                    internSelect.show();
                    internDetails.hide();
                    employeeSection.hide();
                } else if (type === 'employee') {
                    internSelect.hide();
                    internDetails.hide();
                    employeeSection.show();
                    employeeDetails.hide();

                    $.getJSON("<?= site_url('hrms/getdepartments') ?>", function(data) {
                        $('#department_id').empty().append('<option value="">-- Select Department --</option>');
                        $.each(data, function(i, dept) {
                            $('#department_id').append(`<option value="${dept.dept_id}">${dept.dept_name}</option>`);
                        });
                    });
                } else {
                    internSelect.hide();
                    internDetails.hide();
                    employeeSection.hide();
                }
            });

            // Intern selection
            $('#intern_id').on('change', function() {
                const internId = $(this).val();
                if (!internId) {
                    internDetails.hide();
                    return;
                }

                $.getJSON('<?= site_url('hrms/getinterndetails') ?>/' + internId, function(data) {
                    const html = `
                    <strong>Name:</strong> ${data.name}<br>
                    <strong>Email:</strong> ${data.personal_mail}<br>
                    <strong>Phone:</strong> ${data.phone_no}<br>
                    <strong>College:</strong> ${data.college}<br>
                    <strong>Course:</strong> ${data.course}<br>
                    <strong>Gender:</strong> ${data.gender}<br>
                    <strong>DOB:</strong> ${data.dob}
                `;
                    $('#details_box').html(html);
                    internDetails.show();
                    $('#modal_intern_id').val(data.intern_id);
                    $('#intern_start_date').val(data.join_date || '');
                    $('#intern_end_date').val(data.exit_date || '');
                    $('#downloadListDocBtn').data('intern', data.intern_id);
                });
            });

            // Open intern document modal
            $('#openAddModal').on('click', function() {
                addModal.fadeIn();
            });

            $('#closeModal').on('click', function() {
                addModal.fadeOut();
            });

            // Intern document upload
            // $('#uploadForm').on('submit', function(e) {
            //     e.preventDefault();
            //     const formData = new FormData(this);

            //     $.ajax({
            //         url: '<?php // site_url('hrms/uploaddocuments') 
                                ?>',
            //         method: 'POST',
            //         data: formData,
            //         contentType: false,
            //         processData: false,
            //         success: function() {
            //             alert("Documents uploaded successfully.");
            //             addModal.fadeOut();
            //         },
            //         error: function() {
            //             alert("Upload failed.");
            //         }
            //     });
            // });

            // ---------------------------------------------------------------------------------------

            let selectedFiles = [];

            // Track selected files & allow rename
            $('#intern_documents').on('change', function() {
                selectedFiles = [];

                // $('#fileList').empty();

                Array.from(this.files).forEach(file => {
                    let newName = prompt("Enter a new name for: " + file.name, file.name);

                    // Ensure it keeps the extension
                    const ext = file.name.split('.').pop();
                    if (!newName.endsWith('.' + ext)) {
                        newName = newName.replace(/\.[^/.]+$/, "") + '.' + ext;
                    }

                    selectedFiles.push({
                        file: file,
                        newName: newName
                    });
                    // $('#fileList').append(`<li>${newName}</li>`);
                });
            });


            //     // Function to render file list with delete buttons
            //     function renderFileList() {
            //         $('#fileList').empty();
            //         selectedFiles.forEach((file, index) => {
            //             $('#fileList').append(`
            //     <li>
            //         ${file.name} 
            //         <button type="button" onclick="removeFile(${index})">❌</button>
            //     </li>
            // `);
            //         });
            //     }

            //     // Remove particular file
            //     function removeFile(index) {
            //         selectedFiles.splice(index, 1);
            //         renderFileList();
            //     }

            // DONLOAD LIST OF DOCUMENTS

            $('#downloadListDocBtn').on('click', function() {
                const internId = $(this).data('intern');
                // console.log('intern : '+internId);
                // return;
                window.location.href = `<?= site_url() ?>hrms/downloadInternDocx/${internId}`;
            });

            function getInternFiles() {
                let internId = $('#modal_intern_id').val();

                $('#fileList').empty(); // Clear old list

                $.ajax({
                    url: '<?= base_url("hrms/getFiles") ?>',
                    type: 'GET',
                    data: {
                        intern_id: internId
                    },
                    success: function(res) {
                        if (res.files && res.files.length > 0) {
                            res.files.forEach(file => {
                                $('#fileList').append(`
                        <li>
                            <a href="<?= site_url() ?>/uploads/intern_doc/${internId}/joining/${file}" target="_blank">${file}</a>
                            <button class="deleteFileBtn btn btn-sm btn-danger" data-file="${file}" data-intern="${internId}">❌</button>
                        </li>
                    `);
                            });
                        } else {
                            $('#fileList').append('<li>No files uploaded yet.</li>');
                        }
                    }
                });
            }
            $('#openAddModal').on('click', function() {
                getInternFiles();
            });

            // Handle delete click
            $(document).on('click', '.deleteFileBtn', function() {
                const fileName = $(this).data('file');
                const internId = $(this).data('intern');
                // console.log(fileName +' '+ internId);
                // return;

                if (!confirm('want to Delete this')) return;
                console.log('after');

                $.ajax({
                    url: '<?= site_url('hrms/deleteFile') ?>',
                    type: 'POST',
                    data: {
                        intern_id: internId,
                        file: fileName
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            // Remove <li> from DOM
                            $(`button[data-file="${fileName}"]`).closest('li').remove();
                        } else {
                            alert(res.message);
                        }
                    }
                });
            });

            $('#uploadForm').on('submit', function(e) {
                e.preventDefault();
                $('#fileList').empty();
                const formData = new FormData();

                console.log('Intern Document Submitted');

                formData.append('intern_id', $('#modal_intern_id').val());
                formData.append('start_date', $('input[name=start_date]').val());
                formData.append('end_date', $('input[name=end_date]').val());
                formData.append('note', $('#note').val() || '');

                // Attach files with renamed filenames
                selectedFiles.forEach(({
                    file,
                    newName
                }) => {
                    formData.append('intern_documents[]', file, newName);
                });

                $.ajax({
                    url: '<?= site_url('hrms/uploadDocuments') ?>',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        console.log(res);
                        if (res.status === 'success') {
                            showPopup(res.message);

                            $('#fileList').empty();
                            //         res.files.forEach(file => {
                            //             $('#fileList').append(`
                            //     <li>
                            //         <a href="/uploads/intern_doc/${$('#modal_intern_id').val()}/joining/${file}" target="_blank">${file}</a>
                            //         <button class="deleteFileBtn" data-file="${file}" data-intern="${$('#modal_intern_id').val()}">❌</button>
                            //     </li>
                            // `);
                            //         });
                            getInternFiles();

                            $('#uploadForm')[0].reset();
                            // selectedFiles = [];
                        } else {
                            alert(res.message);
                        }
                    },
                    error: function() {
                        alert("Upload failed.");
                    }
                });
            });

            // ---------------------------------------------------------------------------------------

            // Department select → load employees
            $('#department_id').on('change', function() {
                const deptId = $(this).val();
                if (!deptId) return;

                $.getJSON("<?= site_url('hrms/getemployeesbydepartment') ?>/" + deptId, function(data) {
                    $('#employee_id').empty().append('<option value="">-- Select Employee --</option>');
                    $.each(data, function(i, emp) {
                        $('#employee_id').append(`<option value="${emp.emp_id}">${emp.name}</option>`);
                    });
                });
            });

            // Employee selected
            $('#employee_id').on('change', function() {
                const empId = $(this).val();
                if (!empId) return;

                $.getJSON("<?= site_url('hrms/getemployeedetails') ?>/" + empId, function(emp) {
                    selectedEmployeeData = emp;
                    employeeDetails.show();

                    const info = `
                    <strong>Name:</strong> ${emp.name}<br>
                    <strong>Email:</strong> ${emp.official_mail}<br>
                    <strong>Phone:</strong> ${emp.phone_no}<br>
                    <strong>Department:</strong> ${emp.dept}<br>
                    <strong>Designation:</strong> ${emp.designation}<br>
                    <strong>DOB:</strong> ${emp.dob}<br>
                    <strong>Gender:</strong> ${emp.gender}
                `;
                    $('#employee_info').html(info);

                    // Open modal
                    $('#openEmployeeModal').off('click').on('click', function() {
                        $('#employeeModal').fadeIn();
                        showStep(1);

                        // Load dropdowns
                        $.getJSON("<?= site_url('hrms/getroles') ?>", function(roles) {
                            $('#role').empty().append('<option value="">-- Select Role --</option>');
                            roles.forEach(function(role) {
                                const selected = (selectedEmployeeData.role == role.id) ? 'selected' : '';
                                $('#role').append(`<option value="${role.id}" ${selected}>${role.user_name}</option>`);
                            });
                        });

                        $.getJSON("<?= site_url('hrms/getdesignations') ?>", function(designations) {
                            $('#designation').empty().append('<option value="">-- Select Designation --</option>');
                            designations.forEach(function(des) {
                                const selected = (selectedEmployeeData.designation == des.position_id) ? 'selected' : '';
                                $('#designation').append(`<option value="${des.position_id}" ${selected}>${des.position_name}</option>`);
                            });
                        });

                        $.getJSON("<?= site_url('hrms/getdepartments') ?>", function(depts) {
                            $('#dept').empty().append('<option value="">-- Select Department --</option>');
                            depts.forEach(function(dept) {
                                const selected = (selectedEmployeeData.dept == dept.dept_id) ? 'selected' : '';
                                $('#dept').append(`<option value="${dept.dept_id}" ${selected}>${dept.dept_name}</option>`);
                            });
                        });

                        // Fill all other inputs
                        for (const key in emp) {
                            if (['role', 'designation', 'dept'].includes(key)) continue;
                            if ($('#' + key).length) {
                                $('#' + key).val(emp[key]);
                            }
                        }
                    });
                });
            });

            // Close modal
            $('#closeEmployeeModal').on('click', function() {
                employeeModal.fadeOut();
            });

            // Save employee update
            $('#employeeModalForm').on('submit', function(e) {
                e.preventDefault();

                const formData = $(this).serialize();

                $.ajax({
                    url: "<?= site_url('hrms/updateemployee') ?>",
                    method: "POST",
                    data: formData,
                    success: function(res) {
                        if (res.status === 'success') {
                            alert("Employee updated successfully.");
                            employeeModal.fadeOut();
                        } else {
                            alert("Update failed.");
                        }
                    },
                    error: function() {
                        alert("An error occurred.");
                    }
                });
            });

            // Modal step navigation
            $('.next-step').on('click', function() {
                if (currentStep < 6) showStep(currentStep + 1);
            });

            $('.prev-step').on('click', function() {
                if (currentStep > 1) showStep(currentStep - 1);
            });

            $('.step-tab').on('click', function() {
                const step = $(this).data('step');
                showStep(step);
            });

            // Close modals on outside click
            $(window).on('click', function(e) {
                $('.modal:visible').each(function() {
                    const modalContent = $(this).find('.modal-content')[0];
                    if (e.target === this) {
                        $(this).fadeOut();
                    }
                });
            });


            // Open Employee Document Modal
            $('#openEmployeeDocModal').on('click', function() {

                $('#doc_employee_id').val(selectedEmployeeData.emp_id);
                $('#doc_employee_name').val(selectedEmployeeData.name.replace(/\s+/g, '_')); // sanitize
                $('#employeeDocModal').fadeIn();
                loadEmployeeDocs(selectedEmployeeData.emp_id)
            });
        });

        // Close Modal
        $('#closeEmployeeDocModal').on('click', function() {
            $('#employeeDocModal').fadeOut();
        });

        // Handle document form submit
        $('#employeeDocForm').on('submit', function(e) {
            e.preventDefault();
            console.log('upload doc');
            $('.overglow').fadeIn();

            const files = $('input[name="emp_documents[]"]')[0].files;
            if (files.length === 0) {
                alert("Please select at least one PDF");
                $('.overglow').fadeOut();
                return;
            }

            let formData = new FormData();

            // Append hidden fields too
            formData.append('employee_id', $('#doc_employee_id').val());
            formData.append('emp_name', $('#doc_employee_name').val());

            for (let file of files) {
                if (file.type !== "application/pdf") {
                    alert("Only PDF files are allowed.");
                    $('.overglow').fadeOut();
                    return;
                }

                // Ask user for custom name
                let customName = prompt(`Enter filename for ${file.name} (without extension):`);
                if (!customName) continue;

                let ext = file.name.split('.').pop();
                let newFileName = customName + "." + ext;

                // Create renamed file and append
                let renamedFile = new File([file], newFileName, {
                    type: file.type
                });
                formData.append('documents[]', renamedFile);
            }

            $.ajax({
                url: "<?= site_url('hrms/uploademployeedocs') ?>",
                method: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    console.log(res)
                    $('.overglow').fadeOut();
                    if (res.status === 'success') {
                        // alert("Documents uploaded successfully.");
                        showPopup('Documents uploaded successfully.')
                        $('#employeeDocModal').fadeOut();
                        $('#employeeDocForm')[0].reset();
                        loadEmployeeDocs(res.emp_id);
                    } else {
                        console.log(res.msg || "Upload failed.");
                        showPopup(res.msg, 'error');
                        console.log('Error: ' + res.msg);
                    }
                },
                error: function(xhr, status, err) {
                    alert("Upload failed.");
                    $('.overglow').fadeOut();
                    showPopup('Upload failed', 'error')
                    console.log(err)
                }
            });
        });

        // Helper function for today's date (YYYYMMDD)
        function getToday() {
            let d = new Date();
            return d.getFullYear() +
                ("0" + (d.getMonth() + 1)).slice(-2) +
                ("0" + d.getDate()).slice(-2);
        }


        function loadEmployeeDocs(emp_id) {
            $.ajax({
                url: baseurl + "hrms/getEmployeeDocs/" + emp_id,
                method: "GET",
                dataType: "json",
                success: function(res) {
                    if (res.status === "success") {
                        let html = "<ul>";
                        res.docs.forEach(function(doc, index) {
                            // Construct file URL
                            let fileUrl = baseurl + "/uploads/employee_doc/" + emp_id + "/joining/" + doc;

                            html += `<li>
                        ${index + 1}. ${doc}
                        <a href="${fileUrl}" target="_blank" class="view-doc">View</a>
                        <button class="delete-doc" data-doc="${doc}" data-emp="${emp_id}">Delete</button>
                    </li>`;
                        });
                        html += "</ul>";
                        $("#employeeDocsList").html(html);
                    } else {
                        $("#employeeDocsList").html("<li>No documents uploaded.</li>");
                    }
                }
            });
        }




        $(document).on('click', '.delete-doc', function() {
            let filename = $(this).data('doc');
            let emp_id = $(this).data('emp');
            $('.overglow').fadeIn();
            console.log(filename + emp_id);

            $.post("<?= site_url('hrms/deleteemployeedoc/') ?>/" + emp_id + '/' + filename, function(res) {
                if (res.status === 'success') {
                    showPopup('Deleted successfully')
                    alert('Deleted successfully');
                    loadEmployeeDocs(emp_id);
                    $('.overglow').fadeOut();
                } else {
                    alert(res.msg || 'Delete failed');
                    $('.overglow').fadeOut();
                }
            });
        });


        // GENERATE DOCUMENTS
        $('#generateDocListBtn').on('click', function() {
            const emp_id = $('#doc_employee_id').val();
            if (!emp_id) {
                alert('Employee ID not found.');
                return;
            }

            const $btn = $(this);
            $btn.prop('disabled', true).text('Generating...');

            $.ajax({
                url: baseurl + 'hrms/generateEmployeeDocList/' + encodeURIComponent(emp_id),
                method: 'GET',
                xhrFields: {
                    responseType: 'blob'
                }, // get binary
                success: function(data, status, xhr) {
                    // If server returned JSON (error), show it
                    const ct = xhr.getResponseHeader('Content-Type') || '';
                    if (ct.indexOf('application/json') !== -1) {
                        const reader = new FileReader();
                        reader.onload = function() {
                            try {
                                const json = JSON.parse(reader.result);
                                alert(json.msg || 'Failed to generate document.');
                            } catch {
                                alert('Failed to generate document.');
                            }
                        };
                        reader.readAsText(data);
                        return;
                    }

                    // Try to extract filename from Content-Disposition
                    let filename = (function() {
                        const dispo = xhr.getResponseHeader('Content-Disposition') || '';
                        const match = dispo.match(/filename\*=UTF-8''([^;]+)|filename="?([^"]+)"?/i);
                        if (match) return decodeURIComponent(match[1] || match[2]);
                        return emp_id + '_Document_List.docx';
                    })();

                    const blob = new Blob(
                        [data], {
                            type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                        }
                    );

                    // Trigger download
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    URL.revokeObjectURL(url);
                },
                error: function() {
                    alert('Failed to generate document.');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Generate Document List');
                }
            });
        });
    </script>

</body>

</html>