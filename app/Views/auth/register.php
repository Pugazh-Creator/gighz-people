<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register GigHz Employees</title>
    <link rel="stylesheet" href="<?php echo base_url('asset/css/register.css'); ?>">
</head>

<body>
    <?= view('navbar/sidebar') ?>

    <section class="container register">
        <?= view('navbar/header') ?>
        <div class="header">
            <h2>Add User</h2>
        </div>
        <div class="form-box">
            <form action="<?= base_url('auth/registerUser') ?>" method="post">
                <?= csrf_field(); ?>
                <div class="input-box">
                    <lable for="emp_id">Employee ID</lable>
                    <input type="text" name="emp_id" value="<?= set_value('emp_id') ?>" id="emp_id" placeholder="Enter Employee ID">
                    <span class="error text" style="color:red">
                        <?= isset($validation) ? dispaly_form_error($validation, 'emp_id') : '' ?>
                    </span>
                </div>
                <div class="input-box">
                    <lable for="username">Name</lable>
                    <input type="text" name="username" id="username" placeholder="Enter username" value="<?= set_value('username') ?>">
                    <span class="error text" style="color:red">
                        <?= isset($validation) ? dispaly_form_error($validation, 'username') : '' ?>
                    </span>
                </div>
                <div class="input-box">
                    <lable for="role">Role</lable>
                    <select name="role" id="role">
                        <option value="3">Employee</option>
                        <option value="1">Admin</option>
                        <option value="9">ERS Admin</option>
                        <option value="10">Super Admin</option>
                        <option value="11">HR Admin</option>
                        <option value="12">MKT Admin</option>
                    </select>
                    <span class="error text" style="color:red">
                        <?= isset($validation) ? dispaly_form_error($validation, 'role') : '' ?>
                    </span>
                </div>
                <div class="input-box">
                    <lable for="password">Password</lable>
                    <input type="password" name="password" id="password" placeholder="Enter password" value="<?= set_value('password') ?>">
                    <span class="error text" style="color:red">
                        <?= isset($validation) ? dispaly_form_error($validation, 'password') : '' ?>
                    </span>
                </div>
                <div class="input-box">
                    <lable for="corn_password">Confirm Password</lable>
                    <input type="password" name="corn_password" id="corn_password" placeholder="Enter Confirm Password" value="<?= set_value('corn_password') ?>">
                    <span class="error text" style="color:red">
                        <?= isset($validation) ? dispaly_form_error($validation, 'corn_password') : '' ?>
                    </span>
                </div>
                <div><button type="submit">Register</button></div>
            </form>
        </div>

    </section>
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
    </script>
</body>

</html>