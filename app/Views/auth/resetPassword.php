<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('asset/css/resetPass.css') ?>">
    <title>Reset Password</title>
</head>

<body>
    <section class="password-reset-container">

        <div class="form-box">
            <h3 class="form-headedr">Reset Password</h3>
            <div class="input-box">
                <label for="email">Official Email</label>
                <input type="email" name="email" id="email" placeholder="Enter Email" value="<?= set_value('email') ?>">
                <p id="email-msg"></p>
                <button id="send-otp">Send OTP</button>
            </div>
            <div class="input-box" id="otp-box" style="display:none">
                <label for="otp">OTP</label>
                <input type="number" name="otp" id="otp" placeholder="Enter otp" value="<?= set_value('otp') ?>">
                <p id="otp-msg"></p>
                <button id="verify-otp">Verify OTP</button>
            </div>
            <div class="input-box password-field" style="display:none">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter password" value="<?= set_value('password') ?>">

                <label for="corn_password">Confirm Password</label>
                <input type="password" name="corn_password" id="corn_password" placeholder="Enter Confirm Password" value="<?= set_value('corn_password') ?>">
                <p id="pass-msg"></p>
                <button id="submit">Submit</button>
            </div>

            <p class="back-login">Back to login <a href="<?= base_url('/') ?>">Login</a></p>

        </div>

    </section>
    <footer class="footer">
        <p>v<?= session('version') ?></p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Send OTP
            $('#send-otp').click(function() {
                var email = $('#email').val();
                if (email === '') {
                    $('#email-msg').text('Enter email!');
                    return;
                }
                $('#send-otp').hide(); // Hide the button until OTP is sent
                $.ajax({
                    url: "<?= base_url('/send_otp') ?>",
                    type: 'POST',
                    data: {
                        email: email
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#otp-box').show(); // Show OTP input box
                            $('#send-otp').show();
                            $('#send-otp').text('Resend OTP'); // Change button text after OTP sent
                            Swal.fire({
                                title: "Success!",
                                text: "OTP sent to your email.",
                                icon: "success"
                            });
                        } else {
                            $('#send-otp').show();
                            Swal.fire({
                                title: "Fail!",
                                text: "This email is not registered.",
                                icon: "error"
                            });
                        }
                    },
                    error: function() {
                        $('#send-otp').show();
                        Swal.fire({
                            title: "Fail!",
                            text: "This email is not registered.",
                            icon: "error"
                        });
                    }
                });
            });

            // Verify OTP
            $('#verify-otp').click(function() {
                var email = $('#email').val();
                var otp = $('#otp').val();

                if (otp === '') {
                    $('#otp-msg').text('Enter OTP!');
                    return;
                }

                $.ajax({
                    url: "<?= base_url('verify-otp') ?>",
                    type: 'POST',
                    data: {
                        email: email,
                        otp: otp
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $(".password-field").show(); // Show password reset fields
                            $('#otp-box').hide(); // Hide OTP input box
                            $('#otp-msg').text(''); // Clear OTP message
                            $('#send-otp').hide();
                        } else {
                            $('#otp-msg').text('Invalid OTP');
                        }
                    },
                    error: function() {
                        $('#otp-msg').text('Invalid OTP');
                    }
                });
            });

            // Reset Password
            $('#submit').click(function() {
                var email = $('#email').val();
                var password = $('#password').val();
                var confirmPassword = $('#corn_password').val();

                if (!password || !confirmPassword) {
                    $('#pass-msg').text('Enter Your Password');
                    return;
                }

                if (password !== confirmPassword) {
                    $('#pass-msg').text('Password and confirm Password do not match');
                    return;
                }

                $.ajax({
                    url: "<?= base_url('/reset-password') ?>",
                    type: 'POST',
                    data: {
                        email: email,
                        password: password
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: "Success!",
                                text: "Password updated successfully",
                                icon: "success"
                            });
                            // Optionally, redirect after success
                            setTimeout(function() {
                                window.location.href = '<?= base_url("/") ?>'; // Redirect to login page
                            }, 2000);
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: response.responseJSON.message || "Failed to update password.",
                                icon: "error"
                            });
                        }
                    },
                    error: function(response) {
                        Swal.fire({
                            title: "Error!",
                            text: response.responseJSON.message || "Failed to update password.",
                            icon: "error"
                        });
                    }
                });
            });
        });
    </script>


</body>

</html>