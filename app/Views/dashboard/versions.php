<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Version</title>
    <style>
        .form {
            width: 50%;
            height: 90vh;
        }

        .form form {
            width: 500px;
            margin: 0 auto;
            height: 100%;
            background: transparent;
            backdrop-filter: blur(5px);
            padding: 0 40px;
            border-radius: 10px;
        }

        .form form h2 {
            padding: 5px 0;
            text-align: center;
        }

        .input-box {
            height: calc(100% / 8);
            margin: 20px 0;
            display: grid;
            align-items: center;
        }

        .radio1 {
            height: calc(100% / 8);
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .input-box input,
        .input-box textarea,
        .input-box select {
            height: 100%;
            padding: 1px 5px;
            border-radius: 10px;
            border: none;
            outline: none;

        }

        .form button {
            width: 40%;
            cursor: pointer;
            height: 40px;
            margin: auto;
            border: 2px solid #da2442;
            border-radius: 10px;
            background: transparent;
            color: #da2442;
            font-size: 18px;
            font-weight: 500;
            transition: all .5s;

        }

        .form button:hover {
            background: #da2442;
            color: white;
        }

        #dis {
            width: 10px;
        }
    </style>
</head>

<body>
    <?= view('navbar/sidebar') ?>
    <div class="container versionUpdates">
        <div class="form">
            <form action="<?= base_url('/add-version-details') ?>" method="post">
                <h2>Add Version</h2>
                <div class="input-box radio1">
                    <div> <label for="version">Version:</label></div>
                    <div> Max <input type="radio" name="version" id="version" value="max"></div>
                    <div> Min<input type="radio" name="version" id="version" value="min"></div>
                    <div>In Min<input type="radio" name="version" id="version" value="inmin"></div>
                    <div>
                        <input type="text" name="dispaly" id="versiondispaly" value="" readonly>
                    </div>
                    <?= isset($validation) ? dispaly_form_error($validation, 'version') : ''; ?>
                </div>
                <div class="input-box">
                    <label for="detailes">Version Details</label>
                    <textarea name="detailes" id="detailes"></textarea>
                    <?= isset($validation) ? dispaly_form_error($validation, 'detailes') : ''; ?>
                </div>
                <div class="input-box">
                    <label for="lanch_date">Lanched Date</label>
                    <input type="date" id="lanch_date" name="lanch_date">
                    <?= isset($validation) ? dispaly_form_error($validation, 'lanch_date') : ''; ?>
                </div>
                <div class="input-box">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
                <div class="input-box">
                    <label for="visible">Visibility</label>
                    <select name="visible" id="visible">
                        <option value="0">No Limit</option>
                        <option value="1">Limited </option>
                    </select>
                    <?= isset($validation) ? dispaly_form_error($validation, 'visible') : ''; ?>
                </div>
                <div class="input-box">
                    <button type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Assuming you're fetching this from PHP or somewhere else
            $.ajax({
                url: '<?= base_url() ?>/Dashboard/getAppVersion',
                type: "post",
                success: function(data) {
                    $('#versiondispaly').val('v' + data);
                },
                error: function(xhr, status, error) {
                    console.error("Falied to Load Version:", error);
                }

            });


            // Set it in the text box
        });
        // $('input[name="version"]').change(function() {
        //         var selected = $(this).val(); // max / min / inmin

        //         $.ajax({
        //             url: '<?= base_url() ?> /Dashboard/DynamicVersion',
        //             type: "POST",
        //             data: {
        //                 selected: selected
        //             }, // if needed by your controller
        //             success: function(response) {
        //                 console.log(response);
        //                 return;
        //                 $('#versiondispaly').val(response.version);
        //             },
        //             error: function() {
        //                 console.error("Could not fetch version.");
        //             }
        //         });
        //     });

        $(document).on('change', 'input[name="version"]', function() {
            $.ajax({
                url: '<?= base_url() ?>/dashboard/dynamicversion/',
                method: 'GET',
                data: {
                    selected: $(this).val()
                },
                success: function(response) {
                    // console.log(response)
                    $('#versiondispaly').val(response.version);
                },
                error: function() {
                    console.error("Could not fetch version.");
                }
            })
        })

        document.addEventListener("DOMContentLoaded", function() {
            <?php if (session()->getFlashdata('success')): ?>
                Swal.fire({
                    title: "Success!",
                    text: "<?= session()->getFlashdata('success'); ?>",
                    icon: "success"
                });
            <?php endif; ?>

            <?php if (session()->getFlashdata('fail')): ?>
                Swal.fire({
                    title: "Error!",
                    text: "<?= session()->getFlashdata('fail'); ?>",
                    icon: "error"
                });
            <?php endif; ?>
        });
    </script>
</body>

</html>