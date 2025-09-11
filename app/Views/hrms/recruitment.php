<!DOCTYPE html>
<html>

<head>
    <title>Recruitment Form</title>
    <link rel="stylesheet" href="<?= base_url() ?>/asset/css/hrms.css">
</head>

<body>
    <?= view('navbar/sidebar') ?>
    <div class="container requirement-container">
        <div class="requiment-child">
            <h2>Recruitment</h2>
            <div class="require-head">
                <button id="upload-csv-btn">Upload CSV</button>
                <button id="addnewpeople-btn">Add People</button>
            </div>
            <div class="reqire-body">
                <div class="requirement-table-container">
                    <table id="requirement-table" class="table ">
                        <thead>
                            <tr>
                                <th>S. No</th>
                                <th>Name</th>
                                <th>College</th>
                                <th>Course</th>
                                <th>Mail</th>
                                <th>Phone</th>
                                <th>Deportment</th>
                                <th>Mode Of Apply</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>






        <div class="overflow"></div>
        <div class="popup requirement-add-new-popup">
            <h2>Candidate recruitment Form</h2>

            <form id="requirementForm" enctype="multipart/form-data">
                <input type="hidden" name="is_new" value="1" id="is_new">


                <!-- New Candidate Fields -->
                <div id="newFields">

                    <div class="input-box">
                        <label>First Name:</label>
                        <input type="text" name="firstname">
                        <div class="error-box"></div>
                    </div>

                    <div class="input-box">
                        <label>Last Name:</label>
                        <input type="text" name="lastname">
                        <div class="error-box"></div>
                    </div>

                    <div class="input-box">
                        <label>Initial:</label>
                        <input type="text" name="initial" maxlength="1">
                        <div class="error-box"></div>
                    </div>

                    <div class="input-box">
                        <label>Date Of Birth:</label>
                        <input type="date" name="dob">
                        <div class="error-box"></div>
                    </div>

                    <div class="input-box">
                        <label>College:</label>
                        <input type="text" name="college">
                        <div class="error-box"></div>
                    </div>

                    <div class="input-box">
                        <label>Course:</label>
                        <input type="text" name="course">
                        <div class="error-box"></div>
                    </div>

                    <div class="input-box">
                        <label>Mail ID:</label>
                        <input type="email" name="mail_id">
                        <div class="error-box"></div>
                    </div>

                    <div class="input-box">
                        <label>Phone No:</label>
                        <input type="tel" name="phone_no">
                        <div class="error-box"></div>
                    </div>

                    <div class="input-box">
                        <label>Resume:</label>
                        <input type="file" name="resume">
                        <div class="error-box"></div>
                    </div>

                    <div class="input-box">
                        <label>Department:</label>
                        <select name="department">
                            <option value="" selected disabled>--Select--</option>
                            <option value="1">Engineering</option>
                            <option value="2">ISP</option>
                            <option value="4">Marketing</option>
                            <option value="5">HR & Operations</option>
                            <option value="6">IT Administration</option>
                            <option value="7">Software</option>
                            <option value="8">General Administration</option>
                            <option value="9">FPGA</option>
                            <option value="10">General</option>
                        </select>
                        <div class="error-box"></div>
                    </div>

                    <div class="input-box">
                        <label>Gender:</label>
                        <select name="gender">
                            <option value="">--Select--</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <div class="error-box"></div>
                    </div>

                    <div class="input-box">
                        <label>Experience:</label>
                        <input type="text" name="experience">
                        <div class="error-box"></div>
                    </div>

                    <div class="input-box">
                        <label>Mode of Apply:</label>
                        <select name="mode_of_apply">
                            <option value="">-- Select --</option>
                            <option value="college">College</option>
                            <option value="referral">Referral</option>
                            <option value="website">Website</option>
                        </select>
                        <div class="error-box"></div>
                    </div>

                    <div class="input-box">
                        <label>Status:</label>
                        <select name="status">
                            <option value="">-- Select Status --</option>
                            <option value="applied">Applied</option>
                            <option value="no_vacancy">No Vacancy</option>
                            <option value="aptitude">Aptitude</option>
                            <option value="dept_question">Department Question</option>
                            <option value="round_1">Round 1</option>
                            <option value="round_2">Round 2</option>
                            <option value="round_3">Round 3</option>
                            <option value="selected">Selected</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <div class="error-box"></div>
                    </div>
                    <div class="input-box">
                        <label>Joining Type:</label>
                        <select name="employement_type">
                            <option value="">-- Select Employeement Type --</option>
                            <option value="employee">Employee</option>
                            <option value="unpaid intern">Unpaid Intern</option>
                            <option value="paid intern">Paid Intern</option>
                        </select>
                        <div class="error-box"></div>
                    </div>

                </div>

                <!-- Toggle New/Existing -->
                <!-- <button type="button" id="toggleMode">Switch to Existing User</button><br><br> -->
                <button type="submit">Submit</button>
            </form>
        </div>

        <!-- <div id="response" style="margin-top: 20px;">hello</div> -->

    </div>
    <script>
        //   $('#toggleMode').on('click', function () {
        //     const isNew = $('#is_new').val();
        //     if (isNew == "1") {
        //       $('#is_new').val("0");
        //       $('#newFields').hide();
        //       $('#updateFields').show();
        //       $(this).text("Switch to New Candidate");
        //     } else {
        //       $('#is_new').val("1");
        //       $('#newFields').show();
        //       $('#updateFields').hide();
        //       $(this).text("Switch to Existing User");
        //     }
        //   });
        function validateForm() {
            let isValid = true;

            $('#newFields .input-box').each(function() {
                const input = $(this).find('input, select');
                const errorBox = $(this).find('.error-box');
                const name = input.attr('name');

                // Skip validation for 'lastname' field
                if (name === 'lastname') {
                    errorBox.text('');
                    return true; // continue to next iteration
                }

                if ((input.attr('type') === 'file' && input.get(0).files.length === 0) ||
                    (!input.val() || input.val().trim() === '')) {
                    errorBox.text('This field is required.');
                    isValid = false;
                } else {
                    errorBox.text('');
                }
            });

            return isValid;
        }

        $('#requirementForm').on('submit', function(e) {
            e.preventDefault();
            $('.error-box').text('');
            if (!validateForm()) {
                return;
            }

            let formData = new FormData(this);
            console.log(formData);

            $.ajax({
                url: '<?= base_url('hrms/saveRequirement') ?>',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#response').html('<p style="color: green; background: rgba(167, 255, 178, 1);">' + response.message + '</p>');
                        $('#requirementForm')[0].reset();
                        $('.error-box').text('');
                        getRequirementsdata();
                    } else {
                        $('#response').html('<p style="color: red; background: rgb(255, 176, 176);">' + response.message + '</p>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#response').html('<p style="color: red; background: rgb(255, 176, 176);">AJAX error: ' + error + '</p>');
                }
            });
        });

        getRequirementsdata()

        function getRequirementsdata() {

            $.ajax({
                url: '<?= base_url() ?>/hrms/getRquirementDetails',
                method: 'GET',
                success: function(data) {
                    console.log(data);

                    // Destroy and reinitialize DataTable if it already exists
                    if ($.fn.DataTable.isDataTable('#requirement-table')) {
                        $('#requirement-table').DataTable().clear().destroy();
                    }

                    // Initialize DataTable
                    const table = $('#requirement-table').DataTable({
                        pageLength: 10, // Set to a number, not false
                        searching: true,
                        ordering: true
                    });

                    // Populate rows
                    let sno = 1;
                    data.forEach(function(item) {
                        table.row.add([
                            sno++,
                            item.name || '',
                            item.college || '',
                            item.course_name || '',
                            item.personal_mail || '',
                            item.phone_no || '',
                            item.department || '',
                            item.mode_of_apply || '',
                            `<a href="<?= base_url() ?>/hrms/studentdetails/${item.recruitment_id}" id="status-${item.recruitment_id || sno}" class="status-link">${item.requirment_status || ''}</a>`
                        ]);
                    });

                    table.draw();
                }

            })
        }

        $(document).ready(function() {
            $('#addnewpeople-btn').on('click', function() {
                $('.error-box').text('');
                $('.requirement-add-new-popup, .overflow').fadeIn(); // or .show();
            });
        });

        $('.overflow').on('click', function() {
            $('.requirement-add-new-popup, .overflow').fadeOut();
        })
    </script>

</body>

</html>



<!-- ["Code of Conduct.pdf","fff1.pdf","10th Marksheet.pdf","12th Marksheet.pdf"] -->