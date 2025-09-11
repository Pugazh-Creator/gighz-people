<!DOCTYPE html>
<html>

<head>
    <title>Appraisal Details</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            max-width: 800px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f4f4f4;
        }

        h2 {
            margin-bottom: 20px;
        }

        .overflow {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 100%;
            background: #a0a0a063;
            z-index: 2;
        }

        .model {
            position: fixed;
            display: none;
            z-index: 3;
            top: 10%;
            left: 35%;
            background: #fff;
            padding: 40px;
            border-radius: 20px;
        }

        ion-icon {
            font-size: 16px;
        }

        ion-icon[name="checkmark-circle-outline"] {
            color: green;
        }

        ion-icon[name="close-circle-outline"] {
            color: red;
        }
    </style>
</head>

<body>
    <?= view('navbar/sidebar') ?>
    <div class="container appraisal_details_container">
        <div class="appraisal_head">
            <h2>Appraisal Details</h2>
        </div>
        <div class="appraisal_boay">
            <?php

            use PhpParser\Node\Stmt\Echo_;

            if (!empty($data)) : ?>
                <?php foreach ($data as $r): ?>
                    <div class="body_cont con1">
                        <div>
                            <h2><?= esc($r['name']) ?></h2>
                            <input type="hidden" name="appraisal_id" id="appraisal_id" value="<?= esc($r['id']) ?>">
                            <input type="hidden" name="employee_id" id="employee_id" value="<?= esc($r['emp_id']) ?>">
                            <h3><?= esc($r['dept_name']) ?></h3>
                            <div style="display: flex;">
                                <?php
                                $stage = $r['stages'];
                                $disabled = $stage == 'completed' ? 'disabled' : '';
                                $color = $stage != 'completed' ? 'background:red; color:white;' : 'background:green; color:white;';
                                echo "<strong>Stage: </strong><p style='padding:7px 15px; display:inline; border-radius:10px;" . $color . "'>$stage</p>"
                                ?>
                            </div>
                            <div>
                                <label for="last_appraisal">Last Appraisal</label>
                                <select name="last_appraisal" id="last_appraisal"></select>
                            </div>
                        </div>
                        <div>
                            <button id="update_stage_btn" <?= $disabled ?>>Update Stage</button>
                        </div>
                    </div>
                    <div class="body_cont con2">
                        <div>
                            <h4>stages</h4>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Stages</th>
                                        <th>Feed Back</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Initiated</td>
                                        <td>-</td>
                                        <td><?= esc($r['date']) ?? '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td>Employee <br> Feedback</td>
                                        <td><?= esc($r['emp_feedback']) ?? '-' ?></td>
                                        <td><?= esc($r['emp_feedback_date']) ?? '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td>360 Feedback</td>
                                        <td><?= esc($r['feedback_360']) ?? '-' ?></td>
                                        <td><?= esc($r['feedback_360_date']) ?? '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td>MD discussion</td>
                                        <td><?= esc($r['md_discussion']) ?? '-' ?></td>
                                        <td><?= esc($r['md_discussion_date']) ?? '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td>Document Stage</td>
                                        <td>-</td>
                                        <td><?= esc($r['document_stage_date']) ?? '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td>Completed</td>
                                        <td>-</td>
                                        <td><?= esc($r['completed_date']) ?? '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td>Hold</td>
                                        <td><?= esc($r['hold_feedback']) ?? '-' ?></td>
                                        <td><?= esc($r['hold_date']) ?? '-' ?></td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                        <div>
                            <form action="#" id="swot_form">
                                <h4>SWOT Analysis</h4>
                                <div class="input-box">
                                    <label for="strength">Strengths</label>
                                    <textarea name="strength" id="strength" <?= $disabled ?>><?= esc($r['strength']) ?? '' ?></textarea>
                                </div>
                                <div class="input-box">
                                    <label for="weakness">Weaknesses</label>
                                    <textarea name="weakness" id="weakness" <?= $disabled ?>><?= esc($r['weakness']) ?? '' ?></textarea>
                                </div>
                                <div class="input-box">
                                    <label for="opportunity">Opportunities</label>
                                    <textarea name="opportunity" id="opportunity" <?= $disabled ?>><?= esc($r['opportunity']) ?? '' ?></textarea>
                                </div>
                                <div class="input-box">
                                    <label for="threat">Threats</label>
                                    <textarea name="threat" id="threat" <?= $disabled ?>><?= esc($r['threat']) ?? '' ?></textarea>
                                </div>
                                <div class="input-box">
                                    <label for="other_notes">Other Feedback / Notes</label>
                                    <textarea name="other_notes" id="other_notes" <?= $disabled ?>><?= esc($r['other_notes']) ?? '' ?></textarea>
                                </div>
                                <button type="submit" <?= $disabled ?>>Update</button>
                            </form>
                            <!-- SALARY DETAILS -->
                            <form id="salary_form">
                                <h4>Salary</h4>

                                <div class="input-box">
                                    <label for="salary">Salary:</label>
                                    <input type="number" name="salary" id="salary" value="<?= $salary ?>" <?= $disabled ?>>
                                </div>

                                <div class="input-box">
                                    <label for="mom">MOM:</label>
                                    <textarea name="mom" id="mom" <?= $disabled ?>><?= esc($r['mom']) ?? '' ?></textarea>
                                </div>

                                <input type="hidden" name="emp_id" id="emp_id" value="<?= $r['emp_id']; ?>">

                                <button type="submit" <?= $disabled ?>>Update Salary</button>
                            </form>
                        </div>
                        <div>
                            <h4>Documents</h4>
                            <div class="document_cont">
                                <div>
                                    NDA <?php echo $r['nda_doc'] != '' ?  "<ion-icon name='checkmark-circle-outline'></ion-icon> 
                                                    <a href='" . base_url() . "uploads/employee_doc/" . $r['emp_id'] . "/serving/appraisal/" . $r['nda_doc'] . "' target='_blank'>view</a>" :  "<ion-icon name='close-circle-outline'></ion-icon> "   ?>
                                    <button id="nda_download">Download</button>
                                    <button <?= $disabled ?> class="doc_upload_btn" data-doc="nda_doc">Upload</button>
                                </div>
                                <div>
                                    Agreement <?php echo $r['agreement_doc'] != '' ?  "<ion-icon name='checkmark-circle-outline'></ion-icon> <a href='" . base_url() . "uploads/employee_doc/" . $r['emp_id'] . "/serving/appraisal/" . $r['agreement_doc'] . "' target='_blank'>view</a>" :  "<ion-icon name='close-circle-outline'></ion-icon> "   ?>
                                    <!-- <button id="Agreement_download">Download</button> -->
                                    <button <?= $disabled ?> class="doc_upload_btn" data-doc="agreement_doc">Upload</button>
                                </div>
                                <div>
                                    R&R <?php echo $r['r_and_r_doc'] != '' ?  "<ion-icon name='checkmark-circle-outline'></ion-icon> <a href='" . base_url() . "uploads/employee_doc/" . $r['emp_id'] . "/serving/appraisal/" . $r['r_and_r_doc'] . "' target='_blank'>view</a>" :  "<ion-icon name='close-circle-outline'></ion-icon> "   ?>
                                    <button <?= $disabled ?> class="doc_upload_btn" data-doc="r_and_r_doc">Upload</button>
                                </div>
                                <div>
                                    Employee Feedback <?php echo $r['emp_feedback_doc'] != '' ?   "<ion-icon name='checkmark-circle-outline'></ion-icon> <a href='" . base_url() . "uploads/employee_doc/" . $r['emp_id'] . "/serving/appraisal/" . $r['emp_feedback_doc'] . "' target='_blank'>view</a>" :  "<ion-icon name='close-circle-outline'></ion-icon> "   ?>
                                    <button <?= $disabled ?> class="doc_upload_btn" data-doc="emp_feedback_doc">Upload</button>
                                </div>
                                <div>
                                    Other Document
                                    <button id="openOtherDocModal">Other Documents</button>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>

            <?php endif; ?>
        </div>

        <!-- model Documents -->
        <div class="overflow"></div>
        <div class="model" id="documets_model">
            <form action="#" id="documents_form">
                <div class="input-box">
                    <label for="document_input">Document</label>
                    <input type="file" id="document_input" name="document_input" accept="application/pdf">
                    <input type="hidden" id="document_type" name="document_type">
                    <div class="error" id="error_document_input"></div>
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>

        <div class="model" id="change_stage_popup">
            <form action="#" id="update_stages_form">
                <div class="input-box">
                    <label for="stage_select">Stage</label>
                    <select name="stage_select" id="stage_select">

                    </select>
                </div>
                <div class="input-box" id="textarea_box" style="display:none;">
                    <label for="feedback">Feedback</label>
                    <textarea name="feedback" id="feedback"></textarea>
                </div>
                <div class="input-box">
                    <label for="stages_date">Date</label>
                    <input type="date" name="stages_date" id="stages_date">
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <!-- OTHER DOCUMENT MODEL -->
    <div class="model" id="otherDocModal" style="display:none;">
        <h3>Upload Other Documents</h3>
        <input type="file" id="other_doc_input" name="other_doc_input[]" multiple <?php $disabled ?>>
        <button id="uploadOtherDocsBtn" <?= $disabled ?>>Upload</button>

        <h4>Uploaded Files</h4>
        <ul id="otherDocList"></ul>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script>
        $(document).ready(function() {
            const baseurl = "<?= base_url() ?>";
            const appraisal_id = $('#appraisal_id').val();
            const emp_id = $('#employee_id').val();

            function getToday() {
                let d = new Date();
                return d.getFullYear() + ('0' + (d.getMonth() + 1)).slice(-2) + ('0' + d.getDate()).slice(-2);
            }

            const stages = [
                'employee feedback', '360 feedback', 'md disscussion', 'document stage', 'completed', 'hold'
            ];

            // Allowed stages for feedback
            const feedbackStages = [
                'employee feedback',
                '360 feedback',
                'md disscussion',
                'hold'
            ];

            $('#swot_form').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this); // Now 'this' is the form element

                $.ajax({
                    url: baseurl + '/hrms/updateswot/' + appraisal_id,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status == "success") {
                            console.log(response.msg);
                            location.reload();
                        } else {
                            console.log(response.msg);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                    }
                })
            })

            $('.doc_upload_btn').on('click', function() {
                $('.overflow, #documets_model').fadeIn();
                let type = $(this).data('doc');
                $('#document_type').val(type);
            });

            $('.overflow').on('click', function() {
                $('.overflow, #documets_model, #change_stage_popup, #otherDocModal').fadeOut();
            })

            $("#documents_form").on('submit', function(e) {
                e.preventDefault();
                $('.error').text('');

                if (!$('#document_input').val()) {
                    $('#error_document_input').text('Upload File');
                    return;
                }

                const formData = new FormData(this);

                // Debug: log FormData entries
                for (let [key, value] of formData.entries()) {
                    console.log(key, value);
                }

                $.ajax({
                    url: baseurl + "hrms/uploadappraisaldocuments/" + appraisal_id + '/' + emp_id,
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        console.log(res);
                        showPopup(res.msg, res.status)
                        location.reload();
                    },
                    error: function(xhr, status, err) {
                        console.log(err);
                    }
                });
            });

            // --------------------------------------------------------------------------------------------------

            // STAGES UPDATE

            function toUpperCamelCase(str) {
                return str
                    .split(' ')
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                    .join(' ');
            }

            $('#update_stage_btn').on('click', function() {
                let stageSelect = $('#stage_select'); // Target the select element

                // Clear existing options
                stageSelect.empty();

                // Add default option
                stageSelect.append("<option value='' selected disabled>Select Stage</option>");

                // Add stages as options with upper camel case display text
                stages.forEach((stage) => {
                    stageSelect.append(`<option value='${stage}'>${toUpperCamelCase(stage)}</option>`);
                });

                // Show the popup
                $('.overflow, #change_stage_popup').fadeIn();
            });

            // When stage changes
            $('#stage_select').on('change', function() {
                const selectedStage = $(this).val();

                if (feedbackStages.includes(selectedStage)) {
                    $('#textarea_box').show(); // Show feedback input
                } else {
                    $('#textarea_box').hide(); // Hide feedback input
                    $('#feedback').val(''); // Clear the feedback field
                }
            });

            // Trigger check on load (in case it's pre-filled)
            $('#stage_select').trigger('change');

            // Handle form submit
            $('#update_stages_form').on('submit', function(e) {
                e.preventDefault();

                let button = $(this).find('button[type="submit"]');
                button.attr('disabled', true);
                button.text('...')

                let stage_type = $('#stage_select').val();

                if (!stage_type) {
                    button.attr('disabled', false);
                    button.text('Submit');
                    alert('Please select a stage.');
                    return;
                }

                let formData = new FormData(this);

                $.ajax({
                    url: baseurl + 'hrms/changeAppraisalstage/' + appraisal_id,
                    method: 'POST', // ✅ fixed typo
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        console.log('Server Response:', res);

                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        button.attr('disabled', false);
                        button.text('Submit')
                        console.error('AJAX Error:', status, error, xhr.responseText);
                    }
                });
            });

            // -------------------------------------------------------------------------------

            // OTHER DOCUMENTS

            // Open modal
            $('#openOtherDocModal').on('click', function() {
                $('#otherDocModal,.overflow').fadeIn();
                loadOtherDocs();
            });

            // Load all files from DB
            function loadOtherDocs() {
                $.get(baseurl + 'hrms/getOtherDocuments/' + appraisal_id + '/' + emp_id, function(res) {
                    let html = '';
                    if (res.length > 0) {
                        res.forEach(file => {
                            html += `<li>
                                        <a href="${baseurl}uploads/employee_doc/${emp_id}/serving/appraisal/${file}" target="_blank">${file}</a>
                                        <button class="deleteOtherDoc" data-file="${file}">Delete</button>
                                    </li>`;
                        });
                    } else {
                        html = '<li>No files uploaded</li>';
                    }
                    $('#otherDocList').html(html);
                });
            }

            // Upload with custom name
            $('#uploadOtherDocsBtn').on('click', function() {
                let files = $('#other_doc_input')[0].files;
                if (files.length === 0) {
                    alert('Please select at least one file');
                    return;
                }

                let formData = new FormData();
                for (let file of files) {
                    let customName = prompt(`Enter filename for ${file.name} (without extension):`);
                    if (!customName) continue;
                    formData.append('files[]', file, customName + '_' + getToday() + '.' + file.name.split('.').pop());
                }

                $.ajax({
                    url: baseurl + 'hrms/uploadOtherDocuments/' + appraisal_id + '/' + emp_id,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        loadOtherDocs();
                        $('#other_doc_input').val('');

                    }
                });
            });

            // Delete file
            $(document).on('click', '.deleteOtherDoc', function() {
                let file = $(this).data('file');
                $.post(baseurl + 'hrms/deleteOtherDocument/' + appraisal_id, {
                    file
                }, function() {
                    loadOtherDocs();
                });
            });



            loadLastAppraisalselect();

            // ----------------------------------------------------------------------------------------------------------------

            function loadLastAppraisalselect() {
                let selection = $('#last_appraisal');
                selection.empty();

                $.ajax({
                    url: baseurl + 'hrms/getlastappraisal/' + emp_id,
                    method: 'GET',
                    success: function(res) {
                        let option = "<option value='' selected disabled>Select Last Appraisal</option>";
                        res.forEach(row => {
                            option += `<option value='${row.id}'>${row.date}</option>`
                        });
                        selection.append(option);
                    }
                })
            }

            $('#last_appraisal').on('change', function() {
                let selectedId = $(this).val();
                if (!selectedId) return;

                window.location.href = baseurl + 'hrms/appraisal_details/' + selectedId;
            });

            $('#nda_download').on('click', function() {
                window.location.href = baseurl + 'hrms/downloadNDA/' + emp_id;
            });

            // ---------------------------------
            // SALARY DETAILS

            $('#salary_form').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: baseurl + 'hrms/updateSalary/' + appraisal_id, // Auto-routed to controller method
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            showPopup(res.status, res.msg)
                            alert('Salary updated successfully!');
                        } else {
                            showPopup('error', res.msg);
                            alert('Error: ' + res.message);
                        }
                    },
                    error: function(err) {
                        alert('Something went wrong!');
                    }
                });
            });

        })
    </script>
</body>

</html>