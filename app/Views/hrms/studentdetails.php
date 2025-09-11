<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url() ?>/asset/css/hrms.css">
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"> -->

    <!-- DataTables JS -->
    <!-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script> -->
    <title>Student details</title>
</head>

<body>
    <?= view('navbar/sidebar') ?>
    <div class="container std-container">
        <div class="std-header">
            <h2>Student Details</h2>
            <div>
                <?php if (!empty(esc($data[0]['gighz_id']))): ?>
                    <?php if (esc($data[0]['employment_type']) != 'unpaid intern'): ?>
                        <a href="<?= base_url('hrms/downloadEmployeeForm/') . esc($data[0]['gighz_id']) ?>">
                            <button type="button">Employee Form</button>
                        </a>
                        <a href="<?= base_url('hrms/downloadNDA/') . esc($data[0]['gighz_id']) ?>"><button>Download NDA</button></a>
                    <?php else : ?>
                        <a href="<?= base_url('hrms/downloadUnpaiedInternForm/') . esc($data[0]['gighz_id']) ?>">
                            <button type="button">Intern Form</button>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                <button id="Update-dtails">Status Update</button>
                <button id="edit-dtails">Edit</button>
            </div>
        </div>
        <div class="std-body">
            <div class="std-details">
                <div class="std-chaild">
                    <h2>Name: <span id="std-name"><?= esc($data[0]['name']) ?></span></h2>
                    <p>Type: <span id="std-employee-typr"><?= esc($data[0]['employment_type']) ?></span></p>
                    <p>Date Of Birth: <span id="std-phone"><?= esc($data[0]['dob']) ?></span></p>
                    <p>College: <span id="std-college"><?= esc($data[0]['college']) ?></span></p>
                    <p>Course: <span id="std-course"><?= esc($data[0]['course_name']) ?></span></p>
                    <p>Mail: <span id="std-mail"><?= esc($data[0]['personal_mail']) ?></span></p>
                    <p>Phone: <span id="std-phone"><?= esc($data[0]['phone_no']) ?></span></p>
                    <p>Department: <span id="std-department"><?= esc($data[0]['dept_name']) ?></span></p>
                    <p>Mode Of Apply: <span id="std-moa"><?= esc($data[0]['mode_of_apply']) ?></span></p>
                </div>
                <div class="std-chaild schedule-container">
                    <p>Next Round: <span id="std-moa"><?= esc($data[0]['next_round']) ?></span></p>
                    <p>Next Schedule: <span id="std-moa"><?= esc($data[0]['scheduled_date']) ?></span></p>
                </div>
            </div>
            <div class="std-status">
                <h2>Status: <span id="require-status"><?= esc($data[0]['requirment_status']) ?></span></h2>
                <div class="status-container-parent">
                    <div class="status-container">
                        <p>Aptitude:</p>
                        <p>Score: <span id="aptitude-score"><?= esc($data[0]['aptitude_score']) ?></span></p>
                        <p>Date: <span id="aptitude-date"><?= esc($data[0]['aptitude_date']) ?></span></p>
                    </div>
                    <div class="status-container">
                        <p>Department QA:</p>
                        <p>Score: <span id="dept-score"><?= esc($data[0]['dept_question_score']) ?></span></p>
                        <p>Date: <span id="dept-date"><?= esc($data[0]['dept_ques_date']) ?></span></p>
                    </div>
                    <div class="status-container">
                        <p>Round 1:</p>
                        <p>Note: <span id="round1-note"><?= esc($data[0]['round_1_note']) ?></span></p>
                        <p>Date: <span id="round1-date"><?= esc($data[0]['round_1_date']) ?></span></p>
                    </div>
                    <div class="status-container">
                        <p>Round 2:</p>
                        <p>Note: <span id="round2-note"><?= esc($data[0]['round_2_note']) ?></span></p>
                        <p>Date: <span id="round2-date"><?= esc($data[0]['round_2_date']) ?></span></p>
                    </div>
                    <div class="status-container">
                        <p>Round 3:</p>
                        <p>Note: <span id="round3-note"><?= esc($data[0]['round_3_note']) ?></span></p>
                        <p>Date: <span id="round3-date"><?= esc($data[0]['round_3_date']) ?></span></p>
                    </div>
                    <div class="status-container">
                        <p>Round 3:</p>
                        <p>Note: <span id="round4-note"><?= esc($data[0]['round_4_note']) ?></span></p>
                        <p>Date: <span id="round4-date"><?= esc($data[0]['round_4_date']) ?></span></p>
                    </div>
                    <div class="status-container">
                        <p>Round 5:</p>
                        <p>Note: <span id="round5-note"><?= esc($data[0]['round_5_note']) ?></span></p>
                        <p>Date: <span id="round5-date"><?= esc($data[0]['round_5_date']) ?></span></p>
                    </div>
                    <div class="status-container">
                        <p>Final Status:</p>
                        <p>Note: <span id="final-status-note"><?= esc($data[0]['requirment_status'] == 'selected' ? $data[0]['selected_note'] : $data[0]['rejected_note']) ?></span></p>
                        <p>Date: <span id="final-status-date"><?= esc($data[0]['last_action_date']) ?></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- pop-up -->

    <div class="overflow"></div>

    <!-- update status -->
    <div class=" popuop employee-details-update-container">
        <h2>Update Status</h2>
        <form action="#" id="update-employee-details-form">
            <input type="hidden" name="is_new" value="0" id="is_new">
            <input type="hidden" name="id" value="<?= esc($id) ?>">
            <div>
                <div class="input-box">
                    <label>Status:</label>
                    <select name="status" id="popup-requirement-status">
                        <option value="" selected disabled>-- Select Status --</option>
                        <option value="no_vacancy">No Vacancy</option>
                        <option value="aptitude">Aptitude</option>
                        <option value="dept_question">Department Question</option>
                        <option value="round_1">Round 1</option>
                        <option value="round_2">Round 2</option>
                        <option value="round_3">Round 3</option>
                        <option value="round_4">Round 4</option>
                        <option value="round_5">Round 5</option>
                        <option value="selected">Selected</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <div class="error-box" id="error-popuptype"></div>
                </div>
                <div class="input-box" id="popup-note">

                </div>
                <div class="input-box" id="popup-date">

                </div>
                <div class="input-box" id="popup-scheduled-type">

                </div>
                <div class="input-box" id="popup-scheduled-date">

                </div>
                <div class="input-box" id="popup-role">

                </div>
            </div>
            <button id="submit-button" type="submit">Submit</button>
        </form>
    </div>

    <!-- edit user details -->

    <div class="popup edituserpopup-container">
        <div class="edit-body">
            <h2>Edit User Details</h2>
            <form action="#" id="edit-user-details-form">
                <div class="input-container">


                    <div class="input-box">
                        <label for="">First Name</label>
                        <input type="text" name="edit-firstname" id="edit-firstname">
                    </div>
                    <div class="input-box">
                        <label for="">Last Name</label>
                        <input type="text" name="edit-lastname" id="edit-lastname">
                    </div>
                    <div class="input-box">
                        <label for="">Initial</label>
                        <input type="text" name="edit-initial" id="edit-initial" maxlength="1">
                    </div>
                    <div class="input-box">
                        <label for="">DOB</label>
                        <input type="date" name="edit-dob" id="edit-dob">
                    </div>
                    <div class="input-box">
                        <label for="">College</label>
                        <input type="text" name="edit-college" id="edit-college">
                    </div>
                    <div class="input-box">
                        <label for="">Course</label>
                        <input type="text" name="edit-course" id="edit-course">
                    </div>
                    <div class="input-box">
                        <label for="">Mail</label>
                        <input type="text" name="edit-mail" id="edit-mail">
                    </div>
                    <div class="input-box">
                        <label for="">Phone</label>
                        <input type="text" name="edit-phone" id="edit-phone">
                    </div>
                    <div class="input-box">
                        <label for="">Experience</label>
                        <input type="number" name="edit-exp" id="edit-exp">
                    </div>
                    <div class="input-box">
                        <label for="">Gender</label>
                        <select name="edit-gender" id="edit-gender">

                        </select>
                    </div>
                    <div class="input-box">
                        <label for="">Department</label>
                        <select name="edit-dept" id="edit-dept">

                        </select>
                    </div>
                    <div class="input-box">
                        <label for="">Mode Of Apply</label>
                        <select name="edit-moa" id="edit-moa">

                        </select>
                    </div>
                    <div class="input-box">
                        <label for="edit-employment-type">Employment Type</label>
                        <select name="edit-employment-type" id="edit-employment-type"></select>
                    </div>
                </div>
                <button type="submit">Update</button>
            </form>
        </div>
    </div>

    <!-- offer leter -->
    <div class="popup askpermisiontosendoffetrletter">
        <div class="popup-childs">
            <h2>You want to send an offer letter?</h2>
        </div>
        <div class="popup-childs">
            <button id="offer-send">Yes</button>
            <button id="offer-send-cancel">No</button>
        </div>
        <div class="send-offer-letter-container" style="display: none;">
            <div>
                <h2>Send Offer Letter</h2>
            </div>
            <div>
                <form action="#" id="send-offer-letter-form">
                    <input type="hidden" name="offer-emp_id" id="offer-emp_id">
                    <input type="hidden" name="offer-type" id="offer-type">
                    <div class="input-cont">
                        <div class="input-box" id="salary-input-box">
                            <label for="">Salary</label>
                            <input type="number" name="offer-salary" id="offer-salary">
                            <div class="err" id="error-offer-salary"></div>
                        </div>
                        <div class="input-box">
                            <label for="">Doj</label>
                            <input type="date" name="offer-doj" id="offer-doj">
                            <div class="err" id="error-offer-doj"></div>
                        </div>
                        <div class="input-box">
                            <label for="offer-note">Description</label>
                            <textarea name="offer-note" id="offer-note"></textarea>
                            <div class="err" id="error-offer-note"></div>
                        </div>
                    </div>
                    <button type="submit">Send</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function validateForm() {
            let isValid = true;

            // Clear all error messages first
            $('.error-box').text('');

            let status = $('#popup-requirement-status').val();
            let note = $('[name="scoreornote"]').val();
            let std_role = $('#std-role').val();
            let scheduledType = $('#popup-next-schdule-type').val();
            let scheduledDate = $('#scheduled-date').val();
            let date = $('#dateofupdate').val();

            if (!status) {
                $('#error-popuptype').text("This field is required.");
                isValid = false;
            }

            if (!note) {
                $('#error-popup-note').text("This field is required.");
                isValid = false;
            }

            if (!date) {
                $('#error-date').text("This field is required.");
                isValid = false;
            }

            // if (!scheduledType) {
            //     $('#error-schedule_type').text("This field is required.");
            //     isValid = false;
            // }

            // if (!scheduledDate) {
            //     $('#error-scheduled_date').text("This field is required.");
            //     isValid = false;
            // }

            if (status === 'selected' && !std_role) {
                $('#error-popup-role').text("This field is required.");
                isValid = false;
            }

            return isValid;
        }

        $('#update-employee-details-form').on('submit', function(e) {
            e.preventDefault();
            let status = $('#popup-requirement-status').val();
            let id = window.location.pathname.split('/').filter(Boolean).pop();

            if (!validateForm()) return;

            let formData = new FormData(this);

            // Optional: disable button
            $('#submit-button').prop('disabled', true).text('Submitting...');

            $.ajax({
                url: '<?= base_url('hrms/saveRequirement') ?>',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    // In case response is a stringified JSON
                    console.log(response);

                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }


                    if (response.status === 'success') {
                        showPopup(response.message, 'success')
                        $('#update-employee-details-form')[0].reset();
                        $('.error-box').text('');
                        console.log()
                        if (status == 'selected') {
                            sendofferletter(id);
                        } else {
                            location.reload();
                        }

                    } else {
                        showPopup(response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showPopup(error, 'error');
                },
                complete: function() {
                    $('#submit-button').prop('disabled', false).text('Submit');
                }
            });
        });

        // Status change dynamic field injection
        $(document).on('change', '#popup-requirement-status', function() {
            let status = $(this).val();

            let id = window.location.pathname.split('/').filter(Boolean).pop();

            let note = $('#popup-note');
            let date = $('#popup-date');
            let scheduledType = $('#popup-scheduled-type');
            let scheduledDate = $('#popup-scheduled-date');
            let role = $('#popup-role');

            note.empty();
            date.empty();
            scheduledType.empty();
            scheduledDate.empty();
            role.empty();

            if (status === 'aptitude' || status === 'dept_question') {
                note.append(`
            <label>Score</label>
            <input type="number" name="scoreornote">
            <div class="error-box" id="error-popup-note"></div>
        `);
            } else if (
                ['round_1', 'round_2', 'round_3', 'round_4', 'round_5', 'rejected'].includes(status)
            ) {
                note.append(`
            <label>Note</label>
            <textarea name="scoreornote"></textarea>
            <div class="error-box" id="error-popup-note"></div>
        `);
            } else if (status === 'selected') {
                note.append(`
            <label>Final Comment</label>
            <textarea name="scoreornote"></textarea>
            <div class="error-box" id="error-popup-note"></div>
        `);

                // Get roles via AJAX
                $.ajax({
                    url: '<?= base_url() ?>/hrms/getpositions/' + id,
                    method: 'get',
                    success: function(data) {
                        if (data.status === 'success') {
                            let result = data.data;
                            let roleHTML = `
                        <label>Role:</label>
                        <select name="std-role" id="std-role">
                            <option value="" selected disabled>-- Select Role --</option>
                    `;
                            result.forEach(row => {
                                roleHTML += `<option value="${row.position_id}">${row.position_name}</option>`;
                            });
                            roleHTML += `</select><div class="error-box" id="error-popup-role"></div>`;
                            role.html(roleHTML);
                        } else {
                            console.log('Unable to fetch positions');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX error: ' + error);
                    }
                });
            }

            date.append(`
        <label>Date</label>
        <input type="date" name="dateofupdate" id="dateofupdate">
        <div class="error-box" id="error-date"></div>
    `);

            if (status !== 'rejected' && status !== 'no_vacancy' && status !== 'selected') {
                console.log("hellos")
                scheduledType.append(`
                <label>Schedule Type:</label>
                <select name="scheduledtype" id="popup-next-schdule-type">
                    <option value="" selected disabled>-- Select Status --</option>
                    <option value="aptitude">Aptitude</option>
                    <option value="dept_question">Department Question</option>
                    <option value="round_1">Round 1</option>
                    <option value="round_2">Round 2</option>
                    <option value="round_3">Round 3</option>
                    <option value="round_4">Round 4</option>
                    <option value="round_5">Round 5</option>
                </select>
                <div class="error-box" id="error-schedule_type"></div>
                `);

                scheduledDate.append(`
                <label>Next Schedule</label>
                <input type="date" name="scheduleddate" id="scheduled-date">
                <div class="error-box" id="error-scheduled_date"></div>
                `);
            }

        });

        $('#Update-dtails').on('click', function() {
            $('#update-employee-details-form')[0].reset();
            $('.overflow, .employee-details-update-container').fadeIn();
        });

        $('.overflow').on('click', function() {
            $('.overflow, .employee-details-update-container, .edituserpopup-container, .askpermisiontosendoffetrletter').fadeOut();
        });

        $(document).on('click', '#edit-dtails', function() {

            $('#edit-user-details-form')[0].reset();
            let id = window.location.pathname.split('/').filter(Boolean).pop();
            $('.overflow, .edituserpopup-container').fadeIn();

            const modes = ['college', 'referral', 'website'];
            const genders = ['male', 'female', 'other'];

            const types = ['employee', 'unpaid intern', 'paid intern'];

            $.ajax({
                url: `<?= base_url() ?>/hrms/getstudeteditdetails/${id}`,
                type: 'get',
                success: function(result) {
                    console.log(result);
                    
                    const user = result.user;
                    const dept = result.department;



                    user.forEach(row => {
                        $('#edit-firstname').val(row.first_name);
                        $('#edit-lastname').val(row.last_name);
                        $('#edit-initial').val(row.initial);
                        $('#edit-dob').val(row.dob);
                        $('#edit-college').val(row.college);
                        $('#edit-course').val(row.course_name);
                        $('#edit-mail').val(row.personal_mail);
                        $('#edit-phone').val(row.phone_no);
                        $('#edit-exp').val(row.experience);

                        let user_dept = row.department;

                        $('#edit-dept').empty();

                        dept.forEach(dpt => {
                            let selected = user_dept === dpt.dept_id ? 'selected' : '';
                            $('#edit-dept').append(`<option value="${dpt.dept_id}" ${selected}>${dpt.dept_name}</option>`)
                        })

                        let moa = row.mode_of_apply;
                        $('#edit-moa').empty();
                        $.each(modes, function(index, value) {
                            let isSelected = moa == value ? 'selected' : '';

                            console.log(`${moa} - ${value}`)
                            $('#edit-moa').append(
                                `<option value="${value}" ${isSelected}>${value.charAt(0).toUpperCase() + value.slice(1)}</option>`
                            );
                        });

                        let user_gender = row.gender;
                        $('#edit-gender').empty();
                        $.each(genders, function(index, value) {
                            let isSelected = user_gender == value ? 'selected' : '';

                            console.log(`${user_gender} - ${value}`)
                            $('#edit-gender').append(
                                `<option value="${value}" ${isSelected}>${value.charAt(0).toUpperCase() + value.slice(1)}</option>`
                            );
                        });
                        $('#edit-employment-type').empty();

                        let emp_type = row.employment_type;
                        $.each(types, function(index, value) {
                            let isSelected = emp_type == value ? 'selected' : '';
                            $('#edit-employment-type').append(
                                `<option value="${value}" ${isSelected}>${value.charAt(0).toUpperCase() + value.slice(1)}</option>`
                            );
                        });

                    })
                },
                error: function(err) {
                    console.log('Error: ' + err);

                }
            })
        })

        $('#edit-user-details-form').on('submit', function(e) {
            e.preventDefault(); // Prevent default submit
            let id = window.location.pathname.split('/').filter(Boolean).pop();


            // Basic Validation
            let isValid = true;
            let errorMsg = '';

            const firstName = $('#edit-firstname').val().trim();
            const lastName = $('#edit-lastname').val().trim();
            const email = $('#edit-mail').val().trim();
            const phone = $('#edit-phone').val().trim();
            const dob = $('#edit-dob').val().trim();
            const exp = $('#edit-exp').val().trim();

            // Required fields
            if (firstName === '') {
                isValid = false;
                errorMsg += 'First name is required.\n';
            }

            // if (lastName === '') {
            //     isValid = false;
            //     errorMsg += 'Last name is required.\n';
            // }

            if (dob === '') {
                isValid = false;
                errorMsg += 'Date of birth is required.\n';
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email === '' || !emailRegex.test(email)) {
                isValid = false;
                errorMsg += 'Valid email is required.\n';
            }

            // Phone number validation (digits only, min 10 digits)
            const phoneRegex = /^[0-9]{10,}$/;
            if (!phoneRegex.test(phone)) {
                isValid = false;
                errorMsg += 'Valid phone number is required (at least 10 digits).\n';
            }

            // Experience validation
            if (isNaN(exp) || Number(exp) < 0) {
                isValid = false;
                errorMsg += 'Experience must be a non-negative number.\n';
            }

            if (!isValid) {
                alert(errorMsg);
                return; // Stop if form is invalid
            }

            // If valid, send form with AJAX
            const formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: '<?= base_url("hrms/updatestudentdetails") ?>/' + id, // Adjust for your route
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    if (response.status === 'success') {
                        showPopup(response.msg, 'success');
                    } else {
                        showPopup("Faile to Update User details", 'error');
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert('Something went wrong!');
                }
            });
        });

        function sendofferletter(id) {
            $('.send-offer-letter-container').hide();
            $('.popup-childs').show();
            $('.askpermisiontosendoffetrletter, .overflow').fadeIn();
            $('#offer-emp_id').val('');
            $('#offer-type').val('');

            $('#offer-send').click(function() {
                $('.popup-childs').hide();
                $('.send-offer-letter-container').show();

                $.ajax({
                    url: `<?= base_url() ?>/hrms/getstudeteditdetails/` + id,
                    type: 'get',
                    success: function(result) {
                        console.log(result);
                        const user = result.user;
                        $('#salary-input-box').show()
                        if (user[0].employment_type === 'unpaid intern') {
                            $('#salary-input-box').hide()
                            $('#offer-emp_id').val(user[0].gighz_id)
                            $('#offer-type').val('0');

                        } else {
                            $('#salary-input-box').show()
                            $('#offer-emp_id').val(user[0].gighz_id)
                            $('#offer-type').val('1');

                        }
                    }
                })
            })

            $('#offer-send-cancel').click(function() {
                $('#send-offer-letter-form')[0].reset();
                $('.askpermisiontosendoffetrletter, .overflow').fadeOut();
            })
        }

        $(document).on('submit', '#send-offer-letter-form', function(e) {
            e.preventDefault();
            $('#send-offer-letter-form button[type="submit"]').text('⌛Sending...');
            // Clear previous errors
            $('#error-offer-salary').text('');
            $('#error-offer-doj').text('');
            $('#error-offer-note').text('');

            let type = $('#offer-type').val();
            let sal = $('#offer-salary').val().trim();
            let doj = $('#offer-doj').val().trim();
            let des = $('#offer-note').val().trim();

            let flag = false;

            if (type === '1') {
                if (!sal) {
                    $('#error-offer-salary').text('Enter Salary');
                    flag = true;
                }
            }

            if (!doj) {
                $('#error-offer-doj').text('Enter Date Of Joining');
                flag = true;
            }

            if (!des) {
                $('#error-offer-note').text('Enter Description');
                flag = true;
            }

            if (flag) return;

            // console.log("I'm here");
            // return;

            let formData = $(this).serialize();

            $.ajax({
                url: '<?= base_url() ?>/hrms/sendofferletter',
                method: 'Post',
                data: formData,
                dataType: 'json',
                success: function(response) {
                console.log(response);
                    if (response.status === 'success') {
                        $('#send-offer-letter-form button[type="submit"]').text('Sended');
                        showPopup(response.msg, 'success')
                        location.reload();
                    } else {
                        showPopup(response.msg, 'error');
                        $('#send-offer-letter-form button[type="submit"]').text('❌Faild');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                    $('#send-offer-letter-form button[type="submit"]').text('❌Faild');
                }

            })
        })
    </script>
</body>

</html>