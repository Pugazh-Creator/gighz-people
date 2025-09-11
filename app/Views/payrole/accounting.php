<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('asset/css/payroll.css') ?>">
    <title>Paymet Management</title>
</head>

<body class="payrole_body">
    <?= view('navbar/sidebar') ?>
    <div class="container loan-container">
        <div class="loan-header">
            <div class="heading">
                <h2>Payments</h2>
            </div>
            <div class="addition-deduction-btn">
                <h4 data-empid='<?= $emp_id ?>' id='accounted-name'><?= $employeename ?></h4>
                <button class="btn functional-btn add-loan-button" id="add-load-button" data-paytype='0'>Pay</button>
                <button class="btn functional-btn add-deduct-button" id='deduct-loan-button' data-paytype='1'>Recover</button>
            </div>
            <div class="payments-tables-body">
                <div class="payment-details-head">

                </div>
                <div class="payment-detail-table">
                    <div id="credit-totals" style="margin-bottom: 10px; font-weight: bold;"></div>
                    <div id="debit-totals" style="margin-top: 20px; margin-bottom: 10px; font-weight: bold;"></div>
                    <div class="table-container">
                        <div class="table-box">
                            <h3>Account Payable</h3>
                            <table class="payroll-table" id="addition-common-amont-table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="table-box">
                            <h3>Account Recoverable</h3>
                            <br>
                            <table class="payroll-table" id="deduct-common-amont-table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Deducted Date</th>
                                        <th>Deducted Amount</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="overflow"></div>
    <div class="popup popup-container loanpopup" style="display: none;">
        <div class="popup-header">
            <h3 id="popup-heading"></h3>
        </div>
        <form action="#" id="popup_add_amount_submit_form">
            <div class="popup-body">
                <input type="hidden" id='popup-employee-id' name="popup-employee-id">
                <input type="hidden" id='popup_transaction_type' name="popup_transaction_type">
                <div class="input-box loan-input-box">
                    <label for="popup-type-selection">Type Of Pay</label>
                    <select name="popup-type-selection" id="popup-type-selection">
                        <option value="" selected disabled>Select Type</option>
                        <option value="Loan">Loan</option>
                        <option value="Advance">Salary Advance</option>
                        <option value="Arrear">Arrear</option>
                    </select>
                    <div class="error" id="error_popup-type-selection"></div>
                </div>
                <div class="input-box">
                    <label for="popup-amount-date">Date</label>
                    <input type="date" id="popup-amount-date" name="popup-amount-date">
                    <div class="error" id="error_popup-amount-date"></div>
                </div>
                <div class="input-box">
                    <label for="popup-amount-amount">Amount</label>
                    <input type="number" id="popup-amount-amount" name="popup-amount-amount">
                    <div class="error" id="error_popup-amount-amount"></div>
                </div>
                <div class="input-box paymenttype-select-box">
                    <label for="popup-payment-type">Type Of Pay</label>
                    <select name="popup-payment-type" id="popup-payment-type">
                        <option value="" selected disabled>Select Type</option>
                        <option value="In_hand">In Hand</option>
                        <!-- <option value="Online_Pay">Online Payment</option> -->
                    </select>
                    <div class="error" id="error_popup-payment-type"></div>
                </div>
                <div class="input-box">
                    <label for="popup-amount-notes">Description</label>
                    <textarea name="popup-amount-notes" id="popup-amount-notes">

                    </textarea>
                    <div class="error" id="error_popup-amount-notes"></div>
                </div>
                <div class="input-box">
                    <button class="btn">Submit</button>
                </div>
            </div>
        </form>
    </div>


    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- jQuery (needed for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        showaccountingdetails();

        function showaccountingdetails() {
            $('#addition-common-amont-table').hide()
            $('#deduct-common-amont-table').hide()

            $('.functional-btn').show();

            let emp_id = '<?= $emp_id ?>';
            let flag = false;

            if (!emp_id) {
                alert("Enter Employee name");
                flag = true;
            }
            if (flag) return;


            $.ajax({
                url: '<?= base_url() ?>/payrole/getemployeespayments/',
                method: 'get',
                data: {
                    emp_id: emp_id,
                },
                success: function(result) {

                    if ($.fn.DataTable.isDataTable('#addition-common-amont-table')) {
                        $('#addition-common-amont-table').DataTable().destroy();
                    }
                    if ($.fn.DataTable.isDataTable('#deduct-common-amont-table')) {
                        $('#deduct-common-amont-table').DataTable().destroy();
                    }

                    let creditBody = $('#addition-common-amont-table tbody');
                    let debitBody = $('#deduct-common-amont-table tbody');

                    creditBody.empty();
                    debitBody.empty();

                    let addedtabledata = '';
                    let deductedtabledata = '';

                    if (result.length !== 0) {
                        console.log('if statement');
                        result.forEach((row) => {
                            if (row.transaction_type === 'Credit') {
                                addedtabledata += `<tr>
                                                        <td>${row.pay_type}</td>
                                                        <td>${row.pay_date}</td>
                                                        <td>${row.pay_total_amount}</td>
                                                        <td>${row.pay_notes}</td>
                                                    </tr>`;
                            } else {
                                deductedtabledata += `<tr>
                                                        <td>${row.pay_type}</td>
                                                        <td>${row.pay_type != 'Arrear' ? row.deducted_date : row.pay_date }</td>
                                                        <td>${row.pay_type != 'Arrear' ?row.deduction_amount : row.pay_total_amount}</td>
                                                        <td>${row.pay_notes}</td>
                                                    </tr>`;
                            }
                        });
                    }

                    creditBody.append(addedtabledata);
                    debitBody.append(deductedtabledata);

                    $('#addition-common-amont-table').show();
                    $('#deduct-common-amont-table').show();

                    // Helper
                    const parseValue = val => parseFloat(val.toString().replace(/[^0-9.-]+/g, '')) || 0;

                    let payable = 0;
                    let recoverable = 0;
                    let remaining = 0;

                    // Init Credit Table with total displayed above
                    const creditTable = $('#addition-common-amont-table').DataTable({
                        paging: false,
                        info: false,
                        lengthChange: false,
                        searching: true,
                        ordering: true,
                        order: [
                            [1, 'DESC']
                        ],
                        drawCallback: function(settings) {
                            const api = this.api();
                            let totalAmount = api.column(2, {
                                search: 'applied'
                            }).data().reduce((a, b) => a + parseValue(b), 0);
                            let totalBalance = api.column(3, {
                                search: 'applied'
                            }).data().reduce((a, b) => a + parseValue(b), 0);
                            payable = parseFloat(totalAmount.toFixed(2));

                        }
                    });

                    // Init Debit Table with total displayed above
                    const debitTable = $('#deduct-common-amont-table').DataTable({
                        paging: false,
                        info: false,
                        lengthChange: false,
                        searching: true,
                        ordering: true,
                        order: [
                            [1, 'DESC']
                        ],
                        drawCallback: function(settings) {
                            const api = this.api();
                            let totalDeduction = api.column(2, {
                                search: 'applied'
                            }).data().reduce((a, b) => a + parseValue(b), 0);
                            recoverable = parseFloat(totalDeduction.toFixed(2));

                            remaining = payable - recoverable;
                            if (payable >= recoverable) {
                                style = 'color:green;';
                                console.log('green')
                            } else {
                                style = 'color:red;';
                                console.log(payable >= recoverable)
                            }
                            $('#credit-totals').html(
                                `<div>Total Paid: ₹${payable} </div><div>Total Recovery: ${recoverable}</div><div style="${style}">Total Balance: ₹${remaining}</div>`
                            );
                        }
                    });

                    // Link the search box from creditTable to both tables
                    $('#addition-common-amont-table_filter input').off().on('keyup change', function() {
                        let value = this.value;
                        creditTable.search(value).draw();
                        debitTable.search(value).draw();
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            })
        }


        $(document).on('click', '.functional-btn', function() {

            let paytype = $(this).data('paytype');
            $('#popup_add_amount_submit_form').trigger('reset');
            $('#popup-heading').text(paytype == 0 ? "Account Payable" : "Account Deductable");

            $('.loanpopup, .overflow').fadeIn();
            $('.paymenttype-select-box').fadeOut()
            $('#popup-amount-date').val('');
            $('#popup-amount-amount').val('');
            $('#popup-amount-notes').val('');

            $('#error_popup-amount-amount').text('');
            $('#error_popup-amount-notes').text('');
            $('#error_popup-type-selection').text('');
            $('#error_popup-amount-date').text('');

            $('#popup_transaction_type').val(paytype);
        })

        $(document).on('click', '.overflow', function() {
            $('.loanpopup, .overflow').fadeOut();

        })

        $(document).on('change', '#popup-type-selection', function() {
            let value = $(this).val();
            let trans = $('#popup_transaction_type').val();
            console.log(value)
            if (value == 'Arrear' && trans == 1) {
                $('.paymenttype-select-box').fadeOut()
            } else {
                $('.paymenttype-select-box').fadeIn()
            }
        })



        // submiting additional pay
        $(document).on('submit', '#popup_add_amount_submit_form', function(e) {
            e.preventDefault();

            let emp_id = '<?= $emp_id ?>';
            let type = $('#popup-type-selection').val();
            let date = $('#popup-amount-date').val();
            let amount = parseFloat($('#popup-amount-amount').val()) || 0; // ensure it's a number
            let note = $('#popup-amount-notes').val();
            let Paytype = $('#popup-payment-type').val() || 'NA';
            let trans = $('#popup_transaction_type').val() == 1 && type == 'Arrear' ? 'Hold' : ($('#popup_transaction_type').val() == 0 ? 'Credit' : 'Debit');

            // console.log(`${emp_id} - ${type} - ${date} - ${amount} - ${note} - ${Paytype} - ${trans}`);
            // return;
            $('#error_popup-amount-amount').text('');
            $('#error_popup-amount-notes').text('');
            $('#error_popup-type-selection').text('');
            $('#error_popup-amount-date').text('');


            let isempty = false;

            if (!amount) {
                $('#error_popup-amount-amount').text('Enter Amount.');
                isempty = true;
            }
            if (!note) {
                $('#error_popup-amount-notes').text('Enter Description.');
                isempty = true;
            }
            if (!type) {
                $('#error_popup-type-selection').text('Select Type.');
                isempty = true;
            }
            if (!date) {
                $('#error_popup-amount-date').text('Enter Date');
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
                    date: date,
                    note: note,
                    trans: trans,
                    payment: Paytype
                },
                // contentType: 'application/x-www-form-urlencoded; charset=UTF-8', // 👈 Add this
                // processData: true, // 👈 Ensures jQuery formats it correctly
                success: function(response) {
                    if (response.status === 'success') {
                        showaccountingdetails();
                        showPopup('Payment saved successfully', 'success');
                        $('#popup_add_amount_submit_form').trigger('reset');

                    } else if (response.status === 'no_open_balance') {
                        showPopup('No open balance found for deduction.', 'error');
                    } else {
                        showPopup('Error saving payment. Please try again.', 'error');
                    }
                },
                error: function(xhr, status, err) {
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
    </script>
</body>

</html>