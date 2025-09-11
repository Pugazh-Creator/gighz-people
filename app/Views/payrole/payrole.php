<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Role Processing</title>
    <link rel="stylesheet" href="<?= base_url('asset/css/payroll.css') ?>">
</head>

<body class="payrole_body">
    <?= view('navbar/sidebar') ?>
    <div class="container payrole-container">
        <div class="header payrole-header">
            <h3>PayRole</h3>
        </div>
        <div class="body payrole-body">
            <div class="body-table-header">
                <div id='date_selection_container'>
                    <div class="input-box">
                        <label for="oe-month-selecting">Month</label>
                        <select name="oe-month-selecting" id="oe-month-selecting">

                        </select>
                    </div>
                    <div class="input-box">
                        <label for="oe-year-selecting">Year</label>
                        <select name="oe-year-selecting" id="oe-year-selecting">

                        </select>
                    </div>
                    <button class="h-btn" id="date_selection_submit-btn">Search</button>
                </div>
                <button class="h-btn" id="showSelectedBtn">Show Selected Only</button>
                <button class="h-btn" id="showAllBtn">Show All</button>

                <!-- take it for manulal button -->
                <button class="h-btn" id="addManualUserBtn">Add Manual User</button>

                <p>Total: <strong class="total-selected-amount">0</strong></p>
            </div>
            <div class="body table-body" style="overflow-x: auto;">
                <table id="attendanceTable">
                    <thead>
                        <tr>
                            <th class="sticky-col"><input type="checkbox" id="checkAll" /></th>
                            <th class="sticky-col">ID</th>
                            <th class="sticky-col">Name</th>
                            <th class="sticky-col">dept</th>
                            <!-- <th>Total work </br> Minutes</th> -->
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Compensation</th>
                            <th>LOP</th>
                            <!-- <th>OD</th> -->
                            <!-- <th>WFH</th> -->
                            <!-- <th>otherSaterday</th> -->
                            <!-- <th>Not Applied</th> -->
                            <!-- <th>Rejected Leave</th> -->
                            <!-- <th>Approved Leave</th> -->
                            <th>Sortfall</th>
                            <th>Actual hours</th>
                            <th>Expected hours</th>
                            <!-- <th>perday Salary</th> -->
                            <th>Permission Hours</th>
                            <th>Current Salery</th>
                            <th>Additional <br> Pay</th>
                            <th>Dedection</th>
                            <th>Payable Days</th>
                            <th>Total Salary</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>

                <!-- For Manual Table  here   -->
                <!-- <div class="body table-body">
                    <h4>Manual Users</h4> -->
                <br>
                <table id="manualUserTable" class="payroll-table">
                    <thead>
                        <tr>
                            <th class="sticky-col"><input type="checkbox" id="checkAll" disabled /></th>
                            <th class="sticky-col">ID</th>
                            <th class="sticky-col">Name</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Compensation</th>
                            <th>LOP</th>
                            <th>Shortfall</th>
                            <th>Total OE Hours</th>
                            <th>Actual Present</th>
                            <th>Permission Hours</th>
                            <th>Current Salary</th>
                            <th>Additional Pay</th>
                            <th>Deduction Pay</th>
                            <th>Paid Days</th>
                            <th>Net Pay</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <!-- </div> -->


                <!--  -->
                <button class="btn file_generation" id="generateNEFTBtn">Generate NEFT</button>
                <button class="btn file_generation" id="send_payslip">Send Pay Slip</button>
            </div>
        </div>
    </div>

    <!-- POPUP -->
    <div class="overflow"></div>
    <div class="pop-up pop-additionalpay-container">
        <div class="popup-cont">
            <h4 id="popup-header"></h4>
            <input type="hidden" name="popup_transaction_type" id="popup_transaction_type">
            <form action="#" class="popup-details popup-amount-form" id="popup-amount-form">
                <input type="hidden" name="popup-emp-id" id="popup-emp-id">
                <div class="popup-empname">Name : <strong><span id="nameoftheemployee"></span></strong></div>
                <div class="popup-paytype-select">
                    <label for="popup-pay-type">Type Of Pay</label>
                    <select name="popup-pay-type" id="popup-pay-type">

                    </select>
                    <div class="error" id="error_popup-pay-type"></div>
                </div>
                <div class="popup-amount-container">
                    <label for="popup-amout-input">Amount</label>
                    <input type="number" name="popup-amout-input" id="popup-amout-input">
                    <input type="number" name="popup-balence-amout-input" id="popup-balence-amout-input" disabled>
                    <div class="error" id='error-popup-amount-input'></div>
                </div>
                <div class="popup-description-container">
                    <label for="popup-additional-pay-description">Description</label>
                    <textarea name="popup-additional-pay-description" id="popup-additional-pay-description"></textarea>
                    <div class="error" id='error_popup-pay-decription'></div>
                </div>
                <button class="popup-additional-submit-btn" id='popup_add_amount_submit_btn'>submit</button>
            </form>
        </div>
    </div>

    <!-- addition data details -->
    <div class="pop-up additional-pays-showing">
        <ul id="additional-datas-details">

        </ul>
    </div>
    <div id="popup-message" class="popup-box" style="display:none;"></div>


    <!-- For Manual User Form          -->
    <div class="overflows" id="manualUserOverlay" style="display: none;"></div>
    <div class="pop-ups" id="manualUserForm" style="display: none;">
        <div class="popup-cont">
            <h4>Add Manual User</h4>
            <form id="manualUserEntryForm" class="popup-details">
                <div class="input-box">
                    <label for="manual_name">Name</label>
                    <select id="manual_name" required>
                        <option value="">Select Employee</option>
                        <!-- Options will be populated by JS -->
                    </select>
                </div>
                <div class="input-box">
                    <label for="manual_salary">Salary</label>
                    <input type="number" id="manual_salary" required>
                </div>
                <div class="input-box">
                    <label for="manual_account">A/C No</label>
                    <input type="text" id="manual_account" required>
                </div>
                <div class="input-box">
                    <label for="manual_ifsc">IFSC Code</label>
                    <input type="text" id="manual_ifsc" required>
                </div>
                <div class="input-box">
                    <label for="manual_lop">LOP</label>
                    <input type="text" id="manual_lop" required>
                </div><br>
                <button class="h-btn" type="submit">Submit</button>
            </form>
        </div>
    </div>

    <!--  ----------------------  -->

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- jQuery (needed for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        // loadmonthandyear();

        $(document).ready(function() {

            $.ajax({
                url: '<?= base_url() ?>/payrole/loadmonthandyear',
                method: 'GET',
                success: function(responce) {
                    $('#oe-month-selecting').empty();
                    $('#oe-year-selecting').empty();

                    const month = responce.months;
                    const year = responce.years;

                    month.forEach((month, index) => {
                        let selected = responce.selectedmonth == index + 1 ? 'selected' : '';
                        $('#oe-month-selecting').append(`
                            <option value='${index+1}' ${selected}>${month}</option>
                        `)
                    })
                    year.forEach((y, index) => {
                        let selected = responce.selectedyear == y ? 'selected' : '';
                        $('#oe-year-selecting').append(`
                            <option  value='${y}' ${selected}>${y}</option>
                        `)
                    })
                    loadpayroletable();
                    loadManualUserTable();

                    // console.log($('#oe-month-selecting').val());
                    // console.log($('#oe-year-selecting').val());

                }
            })
        })


        $(document).on('click', '#date_selection_submit-btn', function(e) {
            e.preventDefault();
            loadpayroletable();
            loadManualUserTable();
        })


        // setInterval(function(){
        //     loadpayroletable();
        // }, 2000)

        function loadpayroletable() {
            $('#attendanceTable tbody').empty()

            let month = $('#oe-month-selecting').val();
            let year = $('#oe-year-selecting').val();

            // console.log($('#oe-month-selecting').val());
            // console.log($('#oe-year-selecting').val());
            // console.log('method Called...')
            $.ajax({
                url: `<?= base_url() ?>/payrole/getattendance/${month}/${year}`,
                method: 'GET',
                success: function(responce) {
                    // console.log(typeof(responce));
                    console.log(responce);
                    $('#manualUserTable').show();
                    // return;

                    $('#attendanceTable').data('datas', responce);

                    if ($.fn.DataTable.isDataTable('#attendanceTable')) {
                        $('#attendanceTable').DataTable().destroy();
                    }

                    // console.log(responce);
                    const tableBody = $('#attendanceTable tbody');
                    tableBody.empty();
                    const data = Object.values(responce.data);
                    const month = responce.monthdays;

                    let rowsHtml = '';

                    if (responce.access == 1) {
                        console.log('it is comes inside');
                        $('.file_generation').hide();
                        $('#manualUserTable').hide();
                        data.forEach((row, index) => {
                            console.log('emp_id is ' + row.emp_id + ' DEPT - ' + row.dept);
                            rowsHtml += `
                            <tr>
                                <td class="sticky-col">${index+1}</td>
                                <td class="sticky-col">${row.emp_id}</td>
                                <td class="sticky-col">${row.name}</td>
                                <td>${row.dept}</td>
                                <td>${row.presentDays}</td>
                                <td>${row.absentDays}</td>
                                <td>${row.compensation}</td>
                                <td>${row.lop_days}</td>
                                <td>${row.sortfall}</td>
                                <td>${row.totalOEHours}</td>
                                <td>${row.actual_present}</td>
                                <td>${row.permission_hours}</td>
                                <td>${row.salary}</td>
                                <td>${row.additional}</td>
                                <td>${row.deduction}</td>
                                <td>${row.payable_days}</td>
                                <td>${Math.round((parseFloat(row.salary) + parseFloat(row.additional)) - parseFloat(row.deduction))}</td>
                             </tr>
                             `;
                        })
                        tableBody.empty().append(rowsHtml);
                        // ✅ Reinitialize DataTable after all rows are in place
                        if ($.fn.DataTable.isDataTable('#attendanceTable')) {
                            $('#attendanceTable').DataTable().destroy();
                        }
                        $('#attendanceTable').DataTable({

                            columnDefs: [{
                                targets: 3, // index of the dept column (0-based)
                                visible: false, // hides the column
                                searchable: false // optional: removes it from search
                            }],
                            order: [
                                [3, 'asc']
                            ], // sort by hidden column
                            paging: false,
                            info: false,
                            lengthChange: false,
                            searching: true,
                        });
                        // console.log(rowsHtml);
                        return;
                        console.log('after return');
                    }

                    data.forEach((row, index) => {
                        const safeJson = JSON.stringify(row).replace(/"/g, '&quot;');
                        const safeadditional = JSON.stringify(row.additional_details).replace(/"/g, '&quot;');
                        const safededuction = JSON.stringify(row.deduction_details).replace(/"/g, '&quot;');
                        rowsHtml += `
            <tr>
                <td class="sticky-col"><input type="checkbox" class="rowCheckbox" data-empid="${row.emp_id}" data-name="${row.name}" data-netpay="${row.netpay+row.additional-row.deduction}" data-ac="${row.account_no}" data-ifsc="${row.ifsc}" 
                        data-items="${safeJson}" data-paiddays="${row.compensation > row.absentDays ? (month + (row.compensation - row.absentDays)) - row.lop_days : month - row.lop_days}"/></td>
                <td class="sticky-col">${row.emp_id}</td>
                <td class="sticky-col">${row.name}</td>
                <td>${row.dept}</td>
                <td>${row.presentDays}</td>
                <td>${row.absentDays}</td>
                <td>${row.compensation}</td>
                <td>${row.lop_days}</td>
                <td>${row.sortfall}</td>
                <td>${row.totalOEHours}</td>
                <td>${row.actual_present}</td>
                <td>${row.permission_hours}</td>
                <td>₹${row.currecnt_salary}</td>
                <td>
                    <a href="#" class="payroll-addition-pay-btn" data-id="${row.emp_id}" data-name="${row.name}" data-additional = "${safeadditional}">
                        ₹${row.additional || '0.00'}
                    </a>
                    <div class="hover-popup">
                        ${row.additional_details?.map(item => 
                            `<strong>${item.type}:</strong> ₹${item.payed || '0.00'}`
                        ).join('<br>') || 'No data'}
                    </div>
                </td>
                <td>
                    <a href='#' class='payroll-dedection-btn' data-id='${row.emp_id}' data-name='${row.name}' data-additional = "${safededuction}">
                        ₹${row.deduction || 0}
                    </a>
                    <div class="hover-popup">
                        ${row.deduction_details?.map(item => 
                            `<strong>${item.type}:</strong> ₹${item.payed || '0.00'}`
                        ).join('<br>') || 'No data'}
                    </div>    
                </td>
                <td>${row.compensation > row.absentDays ? (month + (row.compensation - row.absentDays)) - row.lop_days : month - row.lop_days}</td>
                <td>₹${Math.round(row.netpay+row.additional-row.deduction)}</td>
            </tr>
        `;
                    });

                    tableBody.empty().append(rowsHtml); // ✅ now append all at once

                    // ✅ Reinitialize DataTable after all rows are in place
                    if ($.fn.DataTable.isDataTable('#attendanceTable')) {
                        $('#attendanceTable').DataTable().destroy();
                    }
                    $('#attendanceTable').DataTable({

                        columnDefs: [{
                            targets: 3, // index of the dept column (0-based)
                            visible: false, // hides the column
                            searchable: false // optional: removes it from search
                        }],
                        order: [
                            [3, 'asc']
                        ], // sort by hidden column
                        paging: false,
                        info: false,
                        lengthChange: false,
                        searching: true,
                    });
                    $('.file_generation').show();
                }
            });

        }



        // showing popup --------->Chagefully
        $(document).on('click', '.payroll-addition-pay-btn, .payroll-dedection-btn', function() {
            $('#additional-datas-details').empty();
            $('#popup-amout-input').prop('disabled', true);
            $('#popup-amount-form').trigger('reset');
            $('.pop-up').fadeIn();
            $('.overflow').fadeIn();

            const currentClass = $(this).attr('class');
            let id = $(this).data('id');
            let name = $(this).data('name');

            // ✅ Fix: Parse JSON if it's a string
            let datas = $(this).data('additional');
            if (typeof datas === 'string') {
                try {
                    datas = JSON.parse(datas);
                } catch (e) {
                    datas = [];
                }
            }

            // Render additional/deduction data
            if (!Array.isArray(datas) || datas.length === 0) {
                $('#additional-datas-details').append(`<li>No data</li>`);
            } else {
                console.log(datas);
                datas.forEach(item => {
                    const type = item.type || item.pay_type || item.description || 'Unknown';
                    const payed = item.payed || item.pay_total_amount || item.deduction_amount || item.amount || '0.00';
                    const itemId = item.id || item.pay_id || ''; // fallback for undefined ids

                    $('#additional-datas-details').append(`
                <li>
                    <div class="popup-default-value-container" data-id="${itemId}">
                        ${type} : ${payed}
                    </div>
                    <div class="edit-additional-detail-input-container" data-id="${itemId}" style="display:none;">
                        ${type} :
                        <input type='number' class="edit-additional-detail-input" data-hold='${payed}' value="${payed}" data-id="${itemId}" />
                    </div>
                    <div>
                        <button style="display:none" class='edit-additional-details' data-id='${itemId}'><i class='bx bx-message-square-edit'></i></button>
                        <button class='edit-additional-details-save' data-id='${itemId}' style="display:none;"><i class='bx bx-navigation'></i></button>
                        <button data-id='${itemId}' class='delete-additional-details'><i class='bx bx-trash'></i></button>
                    </div>
                </li>
            `);
                });
            }

            $('#popup-header').text('');
            $('#nameoftheemployee').text('');
            $('#popup-emp-id').val('');
            $('#popup-balence-amout-input').val('');
            $('#popup_transaction_type').val('');

            $('#nameoftheemployee').text(name);
            $('#popup-emp-id').val(id);

            let trasn_type = 1;
            if (currentClass.includes('payroll-addition-pay-btn')) {
                $('#popup-header').text('Additional Pay');
                $('#popup_transaction_type').val('0');
                trasn_type = 0;
            } else {
                $('#popup-header').text('Deduction Pay');
                $('#popup_transaction_type').val('1');
                trasn_type = 1;
            }

            // Load dropdown types
            $.ajax({
                url: '<?= base_url() ?>/payrole/loadtypeofpays/' + trasn_type,
                method: 'GET',
                success: function(response) {
                    let select = $('#popup-pay-type');
                    select.empty();
                    select.append('<option value="" selected disabled>Select Payment Type</option>');
                    $.each(response, function(value, text) {
                        select.append(`<option value="${value}">${text}</option>`);
                    });
                },
                error: function() {
                    alert('Failed to load payment types.');
                }
            });
        });


        $(document).on('click', '.edit-additional-details', function() {
            let id = $(this).data('id');

            // First hide all open input boxes
            $('.popup-default-value-container').fadeIn();
            $('.edit-additional-detail-input-container').fadeOut();
            $('.edit-additional-details').fadeIn();
            $('.edit-additional-details-save').fadeOut();

            // Now show only the clicked item's input box
            $(`.popup-default-value-container[data-id="${id}"]`).fadeOut();
            $(`.edit-additional-details[data-id="${id}"]`).fadeOut();
            $(`.edit-additional-details-save[data-id="${id}"]`).fadeIn();
            $(`.edit-additional-detail-input-container[data-id="${id}"]`).fadeIn();
        });

        // Save updated value
        $(document).on('click', '.edit-additional-details-save', function() {
            let id = $(this).data('id');
            let updatedValue = $(`.edit-additional-detail-input[data-id="${id}"]`).val();

            console.log(updatedValue);

            $.ajax({
                url: '<?= base_url() ?>/payrole/editadditionaldata/' + id + '/' + updatedValue,
                method: 'GET',
                success: function(response) {
                    $('.popup-default-value-container').fadeIn();
                    $('.edit-additional-detail-input-container').fadeOut();
                    $('.edit-additional-details').fadeIn();
                    $('.edit-additional-details-save').fadeOut();
                    console.log(response);
                    loadpayroletable();
                    loadManualUserTable();
                    // $('.payroll-addition-pay-btn').click();
                    // $('.payroll-dedection-btn').click();
                    // Optionally reload or update the UI
                }
            });
        });

        // Delete entry
        $(document).on('click', '.delete-additional-details', function() {
            let id = $(this).data('id');
            $.ajax({
                url: '<?= base_url() ?>/payrole/deleteadditionaldata/' + id,
                type: 'GET',
                success: function(response) {
                    console.log(response);
                    // return;
                    loadpayroletable();
                    loadManualUserTable();
                    // $('.payroll-addition-pay-btn, .payroll-dedection-btn').click();
                    // Optionally remove the item from UI
                }
            });
        });

        // On change: Reset to held value if blank
        $(document).on('change', '.edit-additional-detail-input', function() {
            if ($(this).val() == '') {
                $(this).val($(this).data('hold'));
            }
        });


        // load pay type 
        $(document).on('change', '#popup-pay-type', function() {
            $('#popup-amout-input').val('')
            let type = $(this).val();
            if (!type) {
                $('#popup-amout-input').prop('disabled', true);
            }
            $('#popup-amout-input').prop('disabled', false);

            let emp_id = $('#popup-emp-id').val();
            $('#popup-balence-amout-input').val('');
            let transaction_type = $('#popup_transaction_type').val();

            $.ajax({
                url: '<?= base_url() ?>/payrole/get_additional_pay_details',
                method: 'POST',
                data: {
                    type: type,
                    emp_id: emp_id
                },
                success: function(responce) {

                    if (responce.status == 'error') {
                        alert('❗Somthing went Rong : Datas not sended.')
                    }

                    if (responce.status == 'success') {
                        let amount = responce.data != null ? responce.data : 0;
                        $('#popup-balence-amout-input').val(amount);
                        $('#popup-balence-amout-input').attr('data-original', amount);
                    }
                }
            })
        })

        // live calculation in exist amount and entered amount
        $(document).ready(function() {
            $('#popup-amout-input').on('input', function() {
                let inputVal = $(this).val();
                let type = $('#popup-pay-type').val();
                console.log(type);

                let entered_val = parseFloat(inputVal);
                let exist_val = parseFloat($('#popup-balence-amout-input').attr('data-original')) || 0; // store original value
                let trans = $('#popup_transaction_type').val();

                $('.error-popup-amount-input').text(''); // Clear previous error

                if (inputVal === '') {
                    // Input is empty – reset to original value
                    $('#popup-balence-amout-input').val(exist_val);
                    return;
                }

                if (trans === '0') {
                    // Addition
                    if (type === 'Arrear') {
                        if (exist_val >= entered_val) { // Subtraction
                            $('#popup-balence-amout-input').val(exist_val - entered_val);
                        } else {
                            $('.error-popup-amount-input').text('Invalid Amount, Please Check');
                            $(this).val('');
                            $('#popup-balence-amout-input').val(exist_val); // Reset to original
                        }
                    } else {
                        $('#popup-balence-amout-input').val(exist_val + entered_val);
                    }
                } else {
                    if (type === 'Arrear' || type === 'Non_Performance') {
                        $('#popup-balence-amout-input').val(exist_val + entered_val);
                    } else if (exist_val >= entered_val) { // Subtraction
                        $('#popup-balence-amout-input').val(exist_val - entered_val);
                    } else {
                        $('.error-popup-amount-input').text('Invalid Amount, Please Check');
                        $(this).val('');
                        $('#popup-balence-amout-input').val(exist_val); // Reset to original
                    }
                }
            });

            // Store original value when modal/input loads
            $('#popup-balence-amout-input').each(function() {
                $(this).attr('data-original', $(this).val());
            });
        });



        // submiting additional pay
        $(document).on('click', '#popup_add_amount_submit_btn', function(e) {
            e.preventDefault();

            let emp_id = $('#popup-emp-id').val();
            let type = $('#popup-pay-type').val();
            let amount = parseFloat($('#popup-amout-input').val()) || 0; // ensure it's a number
            let note = $('#popup-additional-pay-description').val();
            let trans_type = $('#popup_transaction_type').val();
            let trans = trans_type == 1 && type == 'Arrear' ? 'Hold' :
                (trans_type == 0 ? 'Credit' : 'Debit');


            let isempty = false;

            if (!amount) {
                $('#error-popup-amount-input').text('Enter Amount.');
                isempty = true;
            }
            if (!note) {
                $('#error_popup-pay-decription').text('Enter Description.');
                isempty = true;
            }
            if (!type) {
                $('#error_popup-pay-type').text('Select Type.');
                isempty = true;
            }
            if (isempty) return;

            // console.log(`${emp_id} , ${type}, ${amount}, ${note}, ${trans}`)
            // return;

            $.ajax({
                url: '<?= base_url('/payrole/savepayments') ?>',
                type: 'POST',
                data: {
                    empid: emp_id,
                    type: type,
                    amount: amount,
                    note: note,
                    trans: trans,
                    payment: 'salary'
                },
                // contentType: 'application/x-www-form-urlencoded; charset=UTF-8', // 👈 Add this
                // processData: true, // 👈 Ensures jQuery formats it correctly
                success: function(response) {
                    // console.log(response);
                    // return;
                    if (response.status === 'success') {
                        loadpayroletable();
                        loadManualUserTable();
                        showPopup('Payment saved successfully', 'success');
                        $('#popup-amount-form').trigger('reset');

                    } else if (response.status === 'no_open_balance') {
                        showPopup('No open balance found for deduction.', 'error');
                    } else {
                        showPopup('Error saving payment. Please try again.', 'error');
                    }
                },
                error: function(err) {
                    console.log(err);
                    showPopup('Server error occurred.', 'error');
                }
            });
        });



        function showPopup(message, type = 'success') {
            const popup = $('#popup-message');
            popup.removeClass('error').removeClass('success'); // reset
            popup.addClass(type === 'error' ? 'error' : 'success'); // apply class
            popup.text(message).fadeIn();

            setTimeout(() => {
                popup.fadeOut();
            }, 3000); // hide after 3 seconds
        }

        $(document).on('click', '.overflow, .popup-close-btn', function() {
            $('.pop-up').fadeOut();
            $('.overflow').fadeOut();
        })

        // check Box
        $(document).on('change', '#checkAll', function() {
            $('.rowCheckbox').prop('checked', $(this).prop('checked'));
        });

        $('#showSelectedBtn').on('click', function() {
            $('#attendanceTable tbody tr').each(function() {
                const checkbox = $(this).find('.rowCheckbox');
                if (!checkbox.is(':checked')) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        });

        $('#showAllBtn').on('click', function() {
            $('#attendanceTable tbody tr').show();
        });



        // generate NEFT
        $(document).ready(function() {
            $('#generateNEFTBtn').on('click', function() {
                sendRequest('generateNeftDocument', 'NEFT_Report.docx');
                sendRequest('generateNeftExcel', 'NEFT_Report.xlsx');
                savepayroll();
            });
        })

        $(document).on('change', '.rowCheckbox, #checkAll', function() {
            $('.total-selected-amount').text('');
            let total = 0;
            $('.rowCheckbox:checked').each(function() {
                // total += $(this).data('netpay');
                // --------Convert to parse
                total += parseFloat($(this).data('netpay'));
                // console.log($(this).data('netpay')+"check box data");

            });
            $('.total-selected-amount').text('₹' + Math.round(total).toLocaleString('en-IN'));

        })

        function sendRequest(endpoint, filename) {
            const selectedEmployees = [];

            $('.rowCheckbox:checked').each(function() {
                selectedEmployees.push({
                    emp_id: $(this).data('empid'),
                    name: $(this).data('name'),
                    netpay: $(this).data('netpay'),
                    account: $(this).data('ac'),
                    ifsc: $(this).data('ifsc')
                });
            });

            if (selectedEmployees.length === 0) {
                alert("Please select at least one employee.");
                return;
            }

            $.ajax({
                url: `<?= base_url() ?>/payrole/${endpoint}`,
                method: 'POST',
                data: {
                    data: selectedEmployees
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(blob, status, xhr) {
                    const disposition = xhr.getResponseHeader('Content-Disposition');
                    let finalFilename = filename;

                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        finalFilename = disposition.split('filename=')[1].replace(/['"]/g, '');
                    }

                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = finalFilename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },
                error: function(xhr, status, error) {
                    console.error('Download failed:', error);
                }
            });
        }

        $('#send_payslip').click(function() {

            $('.overflow').fadeIn();
            const selectedEmployees = [];

            $('.rowCheckbox:checked').each(function() {
                selectedEmployees.push({
                    datas: $(this).data('items'),
                    paiddays: $(this).data('paiddays')
                });
            });

            if (selectedEmployees.length === 0) {
                alert("Please select at least one employee.");
                return;
            }

            $.ajax({
                url: `<?= base_url() ?>/payrole/sendPayslipToEmail`,
                method: 'POST',
                // contentType: 'application/json', // 🔥 tell server it's JSON
                data: {
                    data: selectedEmployees
                },
                // xhrFields: {
                //     responseType: 'blob' // 👈 Important: treat the response as binary (Blob)
                // },
                // success: function(blob, status, xhr) {
                //     const disposition = xhr.getResponseHeader('Content-Disposition');
                //     let finalFilename = "payslip.pdf";

                //     if (disposition && disposition.indexOf('filename=') !== -1) {
                //         finalFilename = disposition.split('filename=')[1].replace(/['"]/g, '');
                //     }

                //     const url = window.URL.createObjectURL(blob);
                //     const link = document.createElement('a');
                //     link.href = url;
                //     link.download = finalFilename;
                //     document.body.appendChild(link);
                //     link.click();
                //     document.body.removeChild(link);
                //     window.URL.revokeObjectURL(url);
                // },
                success: function(response) {
                    console.log(response)
                    return;
                    $('.overflow').fadeOut();
                    if (response.status == 'success') {
                        showPopup('Payslips sended to all successfully.', 'success');
                    }
                },
                error: function(xhr, status, error) {
                    alert("Failed to generate payslip.");
                    console.log('Error: '+error);
                    // console.error(xhr.responseText);
                }
            });

        })


        // -----------------------------------------------------

        // --------------------------------------------------- here 

        // Show/hide the manual form
        $('#addManualUserBtn').click(function() {

            loadEmployeeNames(); // load the names every time the form opens
            $('#manualUserOverlay').fadeIn();
            $('#manualUserForm').fadeIn();

        });

        // Close manual user form on clicking outside
        $('#manualUserOverlay').click(function() {

            $('#manualUserOverlay').fadeOut();
            $('#manualUserForm').fadeOut();
        });


        // Handle form submission
        $('#manualUserEntryForm').submit(function(e) {
            e.preventDefault();

            $('#manualUserOverlay').fadeOut();
            $('#manualUserForm').fadeOut();

            const emp_id = $('#manual_name').val();
            const name = $('#manual_name option:selected').text(); // get selected name
            const salary = parseFloat($('#manual_salary').val());
            const account = $('#manual_account').val();
            const ifsc = $('#manual_ifsc').val();
            const lop = $('#manual_lop').val();

            console.log(`${emp_id} - ${name} - ${salary} - ${account} - ${ifsc} - ${lop}`);


            $.ajax({
                url: '<?= base_url() ?>/payrole/addManualUser', // ✅ your controller route
                type: 'POST',
                data: {
                    emp_id: emp_id,
                    name: name,
                    salary: salary,
                    account: account,
                    ifsc: ifsc,
                    lop: lop
                },
                success: function(response) {
                    console.log(response);
                    if (response.status === 'success') {
                        showPopup('Manual user added successfully');
                        $('#manualUserEntryForm')[0].reset();
                        $('#manualUserOverlay').fadeOut();
                        $('#manualUserForm').fadeOut();
                        loadManualUserTable(); // ✅ Refresh table to show new entry
                    } else {
                        showPopup('Failed to add user', 'error');
                    }
                },
                error: function() {
                    showPopup('Server error occurred.', 'error');
                }
            });
        });

        function loadManualUserTable() {
            $('#manualUserTable tbody').empty();

            let month = parseInt($('#oe-month-selecting').val());
            let year = parseInt($('#oe-year-selecting').val());

            month = month + 1 == 13 ? 1 : month + 1;
            year = month + 1 == 13 ? year + 1 : year;
            console.log(month + ' ' + year);

            $.ajax({
                url: `<?= base_url() ?>/payrole/getManualUsers/${month}/${year}`,
                method: 'GET',
                success: function(response) {
                    // console.log(typeof(response));
                    console.log(response);

                    $('#manualUserTable').data('datas', response);

                    const tableBody = $('#manualUserTable tbody');
                    tableBody.empty(); // ✅ Clear before appending

                    // ✅ Check if response is valid
                    if (!response.success || !Array.isArray(response.data)) {
                        console.error("Invalid manual user data format:", response);
                        return;
                    }

                    let rows = '';
                    response.data.forEach(user => {
                        const additionalData = user.additional_data || [];
                        const deductionData = user.deduction_data || [];
                        const netpays = parseFloat(user.salary);
                        const additionals = parseFloat(user.additional);
                        const deductions = parseFloat(user.deduction);
                        // console.log(netpays)
                        // console.log(deductionData);
                        // const netPay = Math.round((user.salary || 0) + (user.additional || 0) - (user.deduction || 0));

                        rows += `
            <tr>
                <td class="sticky-col">
                    <input type="checkbox" class="rowCheckbox" 
                        data-id="${user.id}" 
                        data-empid="${user.emp_id}" 
                        data-name="${user.name}" 
                        data-netpay="${netpays+additionals-deductions}" 
                        data-ac="${user.account_no}" 
                        data-ifsc="${user.ifsc_code}" 
                        data-items="[]" />
                </td>
                <td class="sticky-col">${user.emp_id}</td>
                <td class="sticky-col">${user.name}</td>
                <td>${user.presentDays || '-'}</td>
                <td>${user.absentDays || '-'}</td>
                <td>${user.compensation || '-'}</td>
                <td>${user.lop_days || '-'}</td>
                <td>${user.sortfall || '-'}</td>
                <td>${user.totalOEHours || '-'}</td>
                <td>${user.actual_present || '-'}</td>
                <td>${user.permission_hours || '-'}</td>
                <td>₹${user.salary || 0}</td>

                <td>
                    <a href="#" class="payroll-addition-pay-btn"
                        data-id="${user.emp_id}"
                        data-name="${user.name}"
                        data-additional='${JSON.stringify(additionalData)}'>
                        ₹${user.additional || '0.00'}
                    </a>
                    <div class="hover-popup">
                        ${additionalData.length > 0
                            ? additionalData.map(item =>
                                `<strong>${item.description || item.pay_type || 'Type'}:</strong> ₹${item.payed || item.amount || '0.00'}`
                              ).join('<br>')
                            : 'No data'}
                    </div>
                </td>

                <td>
                    <a href="#" class="payroll-dedection-btn"
                        data-id="${user.emp_id}"
                        data-name="${user.name}"
                        data-additional='${JSON.stringify(deductionData)}'>
                        ₹${user.deduction || '0.00'}
                    </a>
                    <div class="hover-popup">
                        ${deductionData.length > 0
                            ? deductionData.map(item =>
                                `<strong>${item.description || item.pay_type || 'Type'}:</strong> ₹${
                                item.payed || item.amount || '0.00'
                                }`
                              ).join('<br>')
                            : 'No data'}
                    </div>
                </td>

                <td>-</td>
                <td>₹${Math.round(netpays+additionals-deductions)}</td>
            </tr>`;
                        // console.log(" " + netpays + " " + additionals + " " + deductions + ' dataas');
                    });

                    tableBody.append(rows);
                },
                error: function(xhr, status, error) {
                    console.error("Failed to fetch manual users:", error);
                }
            });

        }
        // Call this when the form is opened
        function loadEmployeeNames() {
            $.ajax({
                url: '<?= base_url() ?>/payrole/getemplyeename', // ✅ adjust route to your controller method
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        const $dropdown = $('#manual_name');
                        $dropdown.empty();
                        $dropdown.append('<option value="">Select Employee</option>');
                        response.data.forEach(emp => {
                            $dropdown.append(`<option value="${emp.emp_id}">${emp.name}</option>`);
                        });
                    } else {
                        alert("No employees found.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading employees:', error);
                }
            });
        }

        function savepayroll() {
            let manual_user_data = $('#manualUserTable').data('datas');
            let employee_data = $('#attendanceTable').data('datas');

            let merged = {
                manual_user_data,
                employee_data
            }

            $.ajax({
                url: `<?= base_url() ?>/payrole/savepayroll`,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    data: merged
                }),
                success: function(response) {
                    console.log(response);
                    return;
                    if (response.status === 'success') {
                        console.log('Payroll Saved successfully');
                    } else {
                        console.log('Payroll Saved Failed');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('ERROR: ' + error);
                }
            })
        }
    </script>

</body>

</html>