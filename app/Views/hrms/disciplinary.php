<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<style>
    #discModal form{
        position: relative;
    }
    .closemodel{
        position: absolute;
        right: 10px;
        top: 7px;
    }
</style>

<body>
    <?= view('navbar/sidebar')?>
    <div class="container">
        <button id="addBtn" class="btn btn-primary">Add Disciplinary Action</button>
        <table id="discTable" class="display">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Reason</th>
                    <th>Apology Doc</th>
                    <th>Action Taken</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <!-- Modal Form -->
        <div id="discModal" class="modal">
            <form id="discForm" enctype="multipart/form-data">
                <button class="closemodel">✕</button>
                <input type="hidden" name="id" id="discId">
                <input type="hidden" name="existing_doc" id="existingDoc">
                <div><label>Date</label><input type="date" name="action_date" id="actionDate" required></div>
                <div><label>Name</label><select name="emp_id" id="employeeSelect" required></select></div>
                <div><label>Reason</label><textarea name="reason" id="reason" required></textarea></div>
                <div><label>Apology Doc (mail or letter)</label><input type="file" name="apology_doc" id="apologyDoc"></div>
                <div><label>Action Taken</label><input type="text" name="action_taken" id="actionTaken" required></div>
                <button type="button" id="saveBtn">Save</button>
                <button type="button" id="cancelBtn">Cancel</button>
            </form>
        </div>

    </div>
    <style>
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
        }
    </style>

    <script>
        $(document).ready(function() {
            let empMap = {};

            function loadUsers(callback) {
                $.getJSON('<?= base_url('hrms/getUsers') ?>', function(employees) {
                    $('#employeeSelect').empty();
                    employees.forEach(emp => {
                        empMap[emp.emp_id] = emp.name;
                        $('#employeeSelect').append(`<option value="${emp.emp_id}">${emp.name}</option>`);
                    });
                    callback();
                });
            }

            function loadTable() {
                if ($.fn.DataTable.isDataTable('#discTable')) {
                    $('#discTable').DataTable().destroy();
                }
                $.getJSON('<?= base_url('hrms/listDisciplinary') ?>', function(data) {
                    // console.log(data);
                    
                    $('#discTable tbody').html('');
                    data.forEach(r => {
                        $('#discTable tbody').append(`
          <tr data-id="${r.id}">
            <td>${r.action_date}</td>
            <td>${empMap[r.name] || r.name}</td>
            <td class="reason">${r.reason}</td>
            <td data-apology_doc ="${r.apology_doc ? r.apology_doc : ''}">${r.apology_doc ? `<a href="<?= base_url('uploads/employee_doc') ?>/${r.emp_id}/serving/disciplinary/${r.apology_doc}" target="_blank">View</a>` : '—'}</td>
            <td class="action">${r.action_taken}</td>
            <td><button class="inlineEdit">Edit</button></td>
          </tr>`);
                    });
                    $('#discTable').DataTable();
                });
            }

            function openModal(edit = false, data = {}) {
                $('#discForm')[0].reset();
                $('#discId').val(edit ? data.id : '');
                $('#existingDoc').val(data.apology_doc);
            
                    $('#existingDoc').data('exist_doc', data.apology_doc ? data.apology_doc : '');

                // console.log($('#existingDoc').val());
                $('#actionDate').val(edit ? data.action_date : '');
                loadDeptRole(data.emp_id);
                $('#reason').val(edit ? data.reason : '');
                $('#actionTaken').val(edit ? data.action_taken : '');
                $('#saveBtn').text(edit ? 'Update' : 'Add');
                $('#discModal').css('display', 'flex');
            }

            function closeModal() {
                $('#discModal').hide();
            }

            $('#addBtn').click(() => openModal());

            $('#discTable').on('click', '.inlineEdit', function() {
                let tr = $(this).closest('tr');
                const id = tr.data('id');
                // allow editing by showing modal
                let existing = {
                    id,
                    action_date: tr.children().eq(0).text(),
                    emp_id: Object.keys(empMap).find(k => empMap[k] === tr.children().eq(1).text()),
                    reason: tr.find('.reason').text(),
                    apology_doc: tr.children().eq(3).data('apology_doc') || "" ,
                    action_taken: tr.find('.action').text()
                };
                openModal(true, existing);
            });

            $('#cancelBtn').click(closeModal);

            $('#saveBtn').click(function() {
                let form = $('#discForm')[0];

                let formData = new FormData(form);
                // $('#discId').val() ? $('#existingDoc').hide() : 
                let url = $('#discId').val() ? '<?= base_url('hrms/editDisciplinary') ?>' : '<?= base_url('hrms/addDisciplinary') ?>';
                $.ajax({
                    url,
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success(res) {
                        if (res.status) {
                            showPopup('Updated Successfully')
                            closeModal();
                            loadTable();
                        }
                    },
                    error:function(xhr, status, err){
                        showPopup('Update Failed', 'error');
                    }
                });
            });

            loadUsers(loadTable);
        });

        function loadDeptRole(selectedDept = '') {
            $.getJSON('<?= base_url('hrms/getUsers') ?>', function(emp) {
                $('#employeeSelect').empty();
                $.each(emp, function(_, e) {
                    $('#employeeSelect').append(
                        $('<option>', {
                            value: e.emp_id,
                            text: e.name,
                            selected: e.emp_id == selectedDept
                        })
                    );
                });
            });
        }

        $('.closemodel').on('click', function(e){
            e.preventDefault();
            $('#discModal').fadeOut();
        })
    </script>

</body>

</html>