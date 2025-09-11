<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('asset/css/companyHoliday.css') ?>">
    <title>Company Holidays</title>
</head>

<body>
    <?= view('navbar/sidebar') ?>
    <section class="container company-holiday-cont">
        <h2 class="company-holiday-heading">Company Holidays</h2>
        <?php if ($session == 1 || $session == 10 || $session == 11) : ?>
            <div class="form-box">
                <form action="<?= base_url('insert_holidays') ?>" method="post">
                    <div class="input-box">
                        <label for="holiday_name">Holiday Name : </label>
                        <input type="text" id="holiday_name" name="holiday_name" placeholder="Enter holiday name" required>
                    </div>
                    <div class="input-box">
                        <label for="holiday_date">Holiday Date : </label>
                        <input type="date" id="holiday_date" name="holiday_date" placeholder="Enter holiday date" required>
                    </div>
                    <div class="input-box">
                        <label for="holiday_type">Holiday Type : </label>
                        <select name="holiday_type" id="holiday_type">
                            <option value="festival">Festival</option>
                            <option value="first_saturday">1st Saturday</option>
                        </select>
                    </div>
                    <button type="submit">Add</button>
                </form>
            </div>
        <?php endif; ?>
        <div class="show-holiday">
            <?php if (!empty($holidays)) : ?>
                <table>
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Holidays</th>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Month</th>
                            <?php if ($session == 1 || $session == 10 || $session == 11) : ?>
                                <th>Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $serial = 1 ?>
                        <?php foreach ($holidays as $holiday): ?>
                            <tr>
                                <td><?= $serial++ ?></td>
                                <td>
                                    <span class="result-box-<?= $holiday['id'] ?>" id="name-<?= $holiday['id'] ?>"> <?= esc($holiday['holiday_name']) ?></span>
                                    <input class="edit-input" type="text" id="edit-input-name-<?= $holiday['id'] ?>" style="display: none;">
                                </td>
                                <td>
                                    <span class="result-box-<?= $holiday['id'] ?>" id="date-<?= $holiday['id'] ?>"><?= esc($holiday['holiday_date']) ?></span>
                                    <input class="edit-input" type="date" id="edit-input-date-<?= $holiday['id'] ?>" style="display: none;">
                                </td>
                                <td><?= esc($holiday['day']) ?></td>
                                <td><?= esc($holiday['month']) ?></td>
                                <?php if ($session == 1 || $session == 10 || $session == 11) : ?>
                                    <td>
                                        <button class="edit-btn" data-id="<?= $holiday['id'] ?>"><i class='bx bx-pencil'></i></button>
                                        <button class="save-btn" data-id="<?= $holiday['id'] ?>" style="display: none;"><i class='bx bx-check'></i></button> |
                                        <a href="<?= base_url('deleteHoliday')?>/<?=$holiday['id']?>" class="dustbin"><i class='bx bx-trash'></i></a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (!empty($saturday)) : ?>
                    <table class="saturday_holiday">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Holidays</th>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Month</th>
                                <?php if ($session == 1 || $session == 10 || $session == 11) : ?>
                                    <th>Action</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $serial = 1 ?>
                            <?php foreach ($saturday as $holiday): ?>
                                <tr>
                                    <td><?= $serial++ ?></td>
                                    <td>
                                        <span class="result-box-<?= $holiday['id'] ?>" id="name-<?= $holiday['id'] ?>"> <?= esc($holiday['holiday_name']) ?></span>
                                        <input class="edit-input" type="text" id="edit-input-name-<?= $holiday['id'] ?>" style="display: none;">
                                    </td>
                                    <td>
                                        <span class="result-box-<?= $holiday['id'] ?>" id="date-<?= $holiday['id'] ?>"><?= esc($holiday['holiday_date']) ?></span>
                                        <input class="edit-input" type="date" id="edit-input-date-<?= $holiday['id'] ?>" style="display: none;">
                                    </td>
                                    <td><?= esc($holiday['day']) ?></td>
                                    <td><?= esc($holiday['month']) ?></td>
                                    <?php if ($session == 1 || $session == 10 || $session == 11) : ?>
                                        <td>
                                            <button class="edit-btn" data-id="<?= $holiday['id'] ?>"><i class='bx bx-pencil'></i></button>
                                            <button class="save-btn" data-id="<?= $holiday['id'] ?>" style="display: none;"><i class='bx bx-check'></i></button> |
                                            <a href="<?= base_url('deleteHoliday')?>/<?=$holiday['id']?>" class="dustbin"><i class='bx bx-trash'></i></a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php endif ?>
            <?php else: ?>
                <p>Holidays not found</p>
            <?php endif; ?>
        </div>
    </section>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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


        $(document).ready(function() {
            // Click event for Edit button
            $(document).on('click', '.edit-btn', function(event) {
                event.stopPropagation(); // Prevent click event from bubbling up

                var id = $(this).data('id');

                // Hide text spans and show input fields
                $('#name-' + id).hide();
                $('#date-' + id).hide();

                $('#edit-input-name-' + id).val($('#name-' + id).text().trim()).show().focus();
                $('#edit-input-date-' + id).val($('#date-' + id).text().trim()).show();

                // Hide edit button and show save button
                $(this).hide();
                $('.save-btn[data-id="' + id + '"]').show();
            });

            // Click event for Save button
            $(document).on('click', '.save-btn', function(event) {
                event.stopPropagation();

                var id = $(this).data('id');
                var updatedName = $('#edit-input-name-' + id).val();
                var updatedDate = $('#edit-input-date-' + id).val();

                $.ajax({
                    url: '<?= base_url('holidays/updateHoliday') ?>', // Update this with your actual backend URL
                    type: 'POST',
                    data: {
                        id: id,
                        holiday_name: updatedName,
                        holiday_date: updatedDate
                    },
                    success: function(response) {
                        // Update UI with new values
                        $('#name-' + id).text(updatedName).show();
                        $('#date-' + id).text(updatedDate).show();

                        $('#edit-input-name-' + id).hide();
                        $('#edit-input-date-' + id).hide();

                        $('.save-btn[data-id="' + id + '"]').hide();
                        $('.edit-btn[data-id="' + id + '"]').show();
                    },
                    error: function(xhr, status, error) {
                        alert('Failed to update holiday. Please try again.');
                    }
                });
            });

            // Click anywhere outside to cancel editing
            $(document).on('click', function(event) {
                if (!$(event.target).closest('.edit-input, .save-btn, .edit-btn').length) {
                    $('.edit-input').each(function() {
                        var id = $(this).attr('id').split('-').pop();

                        $('#name-' + id).show();
                        $('#date-' + id).show();

                        $('#edit-input-name-' + id).hide();
                        $('#edit-input-date-' + id).hide();

                        $('.save-btn[data-id="' + id + '"]').hide();
                        $('.edit-btn[data-id="' + id + '"]').show();
                    });
                }
            });

            // Prevent hiding input fields when clicking inside them
            $(document).on('click', '.edit-input', function(event) {
                event.stopPropagation();
            });
        });
    </script>
</body>

</html>