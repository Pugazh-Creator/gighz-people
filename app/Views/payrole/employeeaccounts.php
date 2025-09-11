<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('asset/css/payroll.css') ?>">
    <title>Accounting Management System</title>
</head>

<body class="payrole_body">
    <?= view('navbar/sidebar') ?>
    <div class="container employeeacoounting-container">
        <h3>Accounting</h3>
        <br>
        <div class="employee-accounting-header">

        </div>
        <div class="employee-accounting-border">
            <table class="payroll-table" id="employees-accounting-data-table">
                <thead>
                    <tr>
                        <th>S. No</th>
                        <th>Name</th>
                        <th>Loan</th>
                        <th>Salary Advance</th>
                        <th>Arrear</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">Total:</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- jQuery (needed for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        loadeemployeesaccountingtable()

        function loadeemployeesaccountingtable() {
            $.ajax({
                url: '<?= base_url() ?>/payrole/getallpayments',
                type: 'get',
                success: function(responce) {
                    console.log(responce);

                    if ($.fn.DataTable.isDataTable('#employees-accounting-data-table')) {
                        $('#employees-accounting-data-table').DataTable().destroy();
                    }

                    let table = $('#employees-accounting-data-table tbody');
                    table.empty();

                    let rowhtml = '';
                    Object.values(responce.data).forEach((r, index) => {
                        let loan = r.loan_addition - r.loan_deduction;
                        let advance = r.advance_addition - r.advance_deduction;
                        let arrear = r.arrear_addition - r.arrear_deduction;
                        let total = loan + advance + arrear;

                        rowhtml += `<tr> 
            <td>${index + 1}</td>
            <td><a href='<?= base_url() ?>/payrole/accounting/${r.emp_id}'>${r.name}</a></td>
            <td>${loan}</td>
            <td>${advance}</td>
            <td>${arrear}</td>
            <td>${total}</td>
        </tr>`;
                    });

                    table.append(rowhtml);

                    $('#employees-accounting-data-table').DataTable({
                        footerCallback: function(row, data, start, end, display) {
                            let api = this.api();

                            // Helper function to parse float
                            let parseFloatSafe = function(i) {
                                return typeof i === 'string' ?
                                    parseFloat(i.replace(/[^0-9.-]+/g, '')) || 0 : typeof i === 'number' ? i : 0;
                            };

                            // Loop over each column index to sum (Loan = 2, Advance = 3, Arrear = 4, Total = 5)
                            [2, 3, 4, 5].forEach(function(colIdx) {
                                let total = api
                                    .column(colIdx, {
                                        search: 'applied'
                                    })
                                    .data()
                                    .reduce((a, b) => parseFloatSafe(a) + parseFloatSafe(b), 0);

                                // Update footer
                                $(api.column(colIdx).footer()).html(total.toFixed(2));
                            });
                        },
                        paging: false,
                        info: false,
                        lengthChange: false,
                        searching: true,
                        ordering: true

                    });
                    
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            })
        }
    </script>
</body>

</html>