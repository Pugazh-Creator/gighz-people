<!DOCTYPE html>
<html>

<head>
    <title>Policy Management</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            width: 90%;
            max-width: 700px;
            max-height: 80vh;
            overflow-y: auto;
            border-radius: 8px;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 18px;
            cursor: pointer;
        }

        textarea {
            width: 100%;
            height: 100px;
        }
    </style>
</head>

<body>
        <?=view('navbar/sidebar')?>
    <div class="container">
        <h2>Policy Management</h2>
        <button id="addPolicyBtn">Add New Policy</button>
        <table id="policyTable" class="display">
            <thead>
                <tr>
                    <th>Version</th>
                    <th>Policy Name</th>
                    <th>Status</th>
                    <th>Short Note</th>
                    <th>Content</th>
                    <th>Document</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <!-- Content View Modal -->
        <div class="modal" id="contentModal">
            <div class="modal-content">
                <span class="close-btn" id="closeContentModal">&times;</span>
                <h3>Full Content</h3>
                <div id="fullContent" style="white-space: pre-wrap;"></div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div class="modal" id="addPolicyModal">
            <div class="modal-content">
                <span class="close-btn" id="closeAddPolicyModal">&times;</span>
                <h3>Add New Policy</h3>
                <form id="policyForm">

                    <div class="input-box">
                        <label>Policy Name:</label>
                        <select id="policy_name" name="policy_name"></select>
                    </div>

                    <div class="input-box" id="radio-box">
                        <label>Version Control:</label><br>
                        <label><input type="radio" name="version_radio" value="max"> Max</label>
                        <label><input type="radio" name="version_radio" value="min"> Min</label>
                        <label><input type="radio" name="version_radio" value="in-min"> In-Min</label>
                    </div>

                    <div class="input-box">
                        <label>Version:</label>
                        <input type="text" name="version" id="version" disabled>
                        <input type="hidden" name="version" id="version_hidden">
                    </div>
                    <div class="input-box">
                        <label>Status:</label>
                        <select name="status">
                            <option value="Draft">Draft</option>
                            <option value="Published">Published</option>
                            <option value="Wait for Approval">Wait for Approval</option>
                        </select>
                    </div>
                    <div class="input-box">
                        <label>Short Note:</label>
                        <input type="text" name="short_note">
                    </div>
                    <div class="input-box">
                        <label>Content:</label>
                        <textarea name="content"></textarea>
                    </div>
                    <div class="input-box" id="document-box">
                        <label>Policy PDF:</label>
                        <input type="file" name="document" accept="application/pdf">
                    </div>
                    <br><br>
                    <button type="submit">Save</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        let policyTable;

        function loadPolicies() {
            // console.log("function");
            $.getJSON('<?= base_url() ?>/hrms/policies', function(data) {
                let tableRows = '';
                const policies = Array.isArray(data) ? data : data.data || [];

                if (policies.length === 0) {
                    console.log('no data');
                }

                // console.log(policies);
                // console.log('policies');

                policies.forEach(row => {
                    const safeJson = JSON.stringify(row).replace(/"/g, '&quot;');

                    const contentPreview = row.content.substring(0, 50) + '...';
                    tableRows += `
                    <tr>
                        <td>${row.version}</td>
                        <td>${row.policy_name}</td>
                        <td>${row.status}</td>
                        <td>${row.short_note}</td>
                        <td><span class="content-preview" style="cursor:pointer; color:blue">${contentPreview}</span></td>
                        <td><a href="${row.document_path}" target="_blank">View PDF</a></td>
                        <td><button data-data="${safeJson}" class="edit-btn">Edit</button> <button data-id="${row.id}" class="delete-btn">Delete</button></td>
                    </tr>
                `;
                });

                if ($.fn.DataTable.isDataTable('#policyTable')) {
                    policyTable.clear().destroy(); // Clear and destroy safely
                }

                $('#policyTable tbody').html(tableRows);

                // Reinitialize
                policyTable = $('#policyTable').DataTable();

                $('#policyTable tbody').off('click').on('click', '.content-preview', function() {
                    const rowIdx = policyTable.row($(this).closest('tr')).index();
                    const rowData = policies[rowIdx];
                    $('#fullContent').text(rowData.content);
                    $('#contentModal').fadeIn();
                });
                $('#policyTable tbody').on('click', '.edit-btn', function() {
                    const rowData = JSON.parse($(this).attr('data-data').replace(/&quot;/g, '"'));

                    $('#policyForm')[0].reset();
                    $('#addPolicyModal').fadeIn();

                    $('#document-box').hide()
                    $('#radio-box').hide();

                    // Set fields
                    $('#policy_name').html(`<option>${rowData.policy_name}</option>`).prop('disabled', true);
                    $('#version').val(rowData.version).prop('disabled', true);
                    $('#version_hidden').val(rowData.version);
                    $('select[name="status"]').val(rowData.status);
                    $('input[name="short_note"]').val(rowData.short_note);
                    $('textarea[name="content"]').val(rowData.content);

                    // Flag mode
                    $('#policyForm').data('edit-id', rowData.id);
                });

                $('#policyTable tbody').on('click', '.delete-btn', function() {
                    const id = $(this).data('id');
                    if (!confirm('Are you sure you want to delete this policy?')) return;

                    $.ajax({
                        url: '<?= base_url() ?>/hrms/deletePolicy/' + id,
                        method: 'POST', // or 'DELETE' depending on your API
                        success: function() {
                            alert('Policy deleted');
                            loadPolicies();
                            // console.log("helo")
                        },
                        error: function() {
                            alert('Delete failed');
                        }
                    });
                });
            });
        }

        $(document).ready(function() {
            loadPolicies();
            // console.log("hello")

            $('#closeContentModal').on('click', () => $('#contentModal').fadeOut());
            $('#closeAddPolicyModal').on('click', () => $('#addPolicyModal').fadeOut());

            $('#addPolicyBtn').on('click', function() {
                $('#policyForm')[0].reset();
                $('#version').val('');
                $('#version_hidden').val('');
                $('#addPolicyModal').fadeIn();
                $('input[name="version_radio"]').show()
                $('#policy_name').prop('disabled', false);


                $('#document-box').show()
                $('#radio-box').show();

                $.getJSON('<?= base_url() ?>/hrms/policyNames', function(names) {
                    const select = $('#policy_name').empty();
                    names.forEach(name => {
                        select.append(`<option value="${name}">${name}</option>`);
                    });
                });
            });

            $('input[name="version_radio"]').on('change', function() {
                const selectedValue = this.value;
                const policy = $('#policy_name').val();
                if (!policy) return;

                $.ajax({
                    url: `<?= base_url() ?>/hrms/policyLastVersion/${policy}`,
                    method: 'get',
                    success: function(response) {
                        // console.log(response)

                        version = response.data;

                        // if (!version || typeof version !== 'string' || version.trim() === '' || version === 'null') {

                        //     version = '0.0.0';
                        // }

                        const parts = version.split('.').map(n => parseInt(n));
                        let newVersion;

                        if (selectedValue === 'max') {
                            newVersion = `${parts[0] + 1}.0.0`;
                        } else if (selectedValue === 'min') {
                            newVersion = `${parts[0]}.${parts[1] + 1}.0`;
                        } else {
                            newVersion = `${parts[0]}.${parts[1]}.${parts[2] + 1}`;
                        }

                        $('#version').val(newVersion);
                        $('#version_hidden').val(newVersion);
                    },
                    error: function(error) {
                        // console.log('Error fetching version:', error);
                    }
                });
            });

            $('#policyForm').on('submit', function(e) {
                e.preventDefault();

                const isEdit = $(this).data('edit-id');
                const formData = new FormData(this);
                formData.set('version', $('#version_hidden').val());

                const url = isEdit ?
                    '<?= base_url() ?>/hrms/updatePolicy/' + isEdit :
                    '<?= base_url() ?>/hrms/savePolicy';



                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function() {
                        alert('Policy ' + (isEdit ? 'updated' : 'saved'));
                        $('#addPolicyModal').fadeOut();
                        $('#policyForm').removeData('edit-id');
                        $('#policy_name').prop('disabled', false);
                        loadPolicies();
                        // location.reload();
                        // console.log('hello')
                    },
                    error: function() {
                        alert(isEdit ? 'Update failed' : 'Save failed');
                    }
                });
            });
        });
    </script>

</body>

</html>