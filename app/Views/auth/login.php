<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GigHz</title>
    <link rel="stylesheet" href="<?= base_url('asset/css/login.css') ?>">
    <link rel="icon" href="<?= base_url('asset/images/favicon.png') ?>">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

</head>

<body>
    <div class="container">
        <div class="left">
            <div>
                <h2>Welcome To GigHz</h2>
                <p>your hub for smarter, faster, and seamless digital experiences.</p>
            </div>
        </div>
        <div class="right">
            <div>
                <form action="#" id="login-form">
                    <h1 class="login-heading">Login</h1>
                    <div class="input-box login">
                        <label for="gighz-id">GigHz ID</label>
                        <input type="text" id="gighz-id" placeholder="Enter GigHz ID">
                        <p class="error" id="error-gighz-id"></p>
                    </div>
                    <div class="input-box login">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" placeholder="Enter Password">
                        <p class="error" id="error-login-password"></p>
                    </div>
                    <!-- Reset Password Start -->
                    <div class="input-box mail" style="display: none;">
                        <label for="official-mail">Official Mail</label>
                        <input type="text" id="official-mail" placeholder="e.g. abc@gighz.net">
                        <p class="error" id="error-official-mail"></p>
                    </div>
                    <button style="display: none;" class="mail" type="button" id="get-otp-btn">Get OTP</button>

                    <div class="input-box otp-box" style="display: none;">
                        <label for="otp">OTP</label>
                        <input type="password" id="otp" placeholder="Enter OTP">
                        <p class="error" id="error-otp"></p>
                    </div>
                    <button style="display: none;" class="otp-box" type="button" id="otp-submit-btn">Submit OTP</button>


                    <div class="input-box password" style="display: none;">
                        <label for="login-password">Password</label>
                        <input type="password" id="reset-password" placeholder="Enter Password">
                        <p class="error" id="error-reset-password"></p>
                    </div>
                    <div class="input-box password" style="display: none;">
                        <label for="login-password">Confirm Password</label>
                        <input type="password" id="confirm-password" placeholder="Enter Confirm Password">
                        <p class="error" id="error-confirm-password"></p>
                    </div>
                    <button style="display: none;" class="password" type="button" id="reset-password-btn">Reset</button>
                    <p style="display: none;" class="go-back">Go to login <span><a href="#" role="button" id="go-back-login">Login</a></span></p>

                    <!-- Password Reset End -->
                    <div class="login">
                        <p>I forgot my Password <span><a href="#" role="button" id="reset-password-link">Reset Password</a></span></p>
                    </div>
                    <button type="submit" class="login">Submit</button>
                </form>
            </div>
        </div>
    </div>
    <div class="social-meadia">
        <div><a href="https://gighz.net/"><img src="<?= base_url() ?>/asset/icons/world-wide-web.png" alt="website"></a></div>
        <div><a href="https://www.facebook.com/people/GigHz/61568394910577/"><img src="<?= base_url() ?>/asset/icons/facebook.png" alt="Facebook"></a></div>
        <div><a href="https://www.youtube.com/@GighzTechnologies"><img src="<?= base_url() ?>/asset/icons/youtube.png" alt="Youtube"></a></div>
        <div><a href="https://www.linkedin.com/in/chandra-thimma/"><img src="<?= base_url() ?>/asset/icons/linkedin.png" alt="linked in"></a></div>
        <div>3.0.1</div>
    </div>


    <p class="popup-msg" style="display: none;"></p>


    <script>
        $(document).ready(function() {


            $('#login-form').on('submit', function() {
                let id = $('#gighz-id').val();
                $('.error').text('');
                let password = $('#login-password').val();
                let iseempty = false;

                if (!id) {
                    $('#error-gighz-id').text('ID feild Should not empty.')
                    iseempty = true;
                }
                if (!password) {
                    $('#error-login-password').text('Password feild Should not empty.')
                    iseempty = true;
                }

                if (iseempty) return;



                $.ajax({
                    url: '<?= base_url() ?>loginUser',
                    method: 'POST',
                    data: {
                        emp_id: id,
                        password: password
                    },
                    success: function(res) {
                        console.log('stage 3');
                        if (res.status == 'success') {
                            $('.popup-msg').text(res.message);
                            $('.popup-msg').fadeIn().delay(2000).fadeOut()
                            window.location.href = "<?php

                                                    use CodeIgniter\Database\BaseUtils;

                                                    echo base_url(); ?>/dashboard";

                        } else {
                            console.log(res.message);
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                })
            })

            $('#reset-password-link').on('click', function(e) {
                e.preventDefault();
                $('.login-heading').text('Reset Password');
                $('.login').fadeOut();
                $('.mail').delay(1000).fadeIn();
                $('.go-back').fadeIn();
            })

            $('#get-otp-btn').on('click', function() {
                $('.error').text('');
                let mail = $('#official-mail').val()
                $(this).text('Sending...')
                let iseempty = false;

                if (!mail) {
                    $('#error-official-mail').text('Official mail feild Should not empty.');
                    iseempty = true;
                }
                if (iseempty) {
                    $(this).text('Send OTP')
                    return;
                }

                $.ajax({
                    url: '<?= base_url() ?>send_otp',
                    method: 'POST',
                    data: {
                        mail: mail
                    },
                    success: function(res) {
                        if (res.status == 'success') {
                            $(this).text('Send OTP')
                            $('.popup-msg').text(res.massage);
                            $('.popup-msg').css('background-color', 'Green');
                            $('.popup-msg').fadeIn().delay(2000).fadeOut();

                            $('.mail').fadeOut();
                            $('.otp-box').delay(1000).fadeIn();
                        } else {
                            $(this).text('Send OTP')
                            $('.popup-msg').text(res.massage);
                            $('.popup-msg').css('background-color', 'red');
                            $('.popup-msg').fadeIn().delay(2000).fadeOut()
                        }
                    },
                    error: function(error) {
                        $(this).text('Send OTP')
                        console.log(error);
                    }
                })
            })

            $('#otp-submit-btn').on('click', function() {
                $('.error').text('');
                let otp = $('#otp').val();
                let mail = $('#official-mail').val();
                console.log(mail);
                let iseempty = false;

                $(this).text('verifying...')

                if (!otp) {
                    $('#error-otp').text('OTP feild Should not empty.');
                    iseempty = true;
                }
                if (iseempty) {
                    $(this).text('Verify')
                    return;
                }

                $.ajax({
                    url: '<?= base_url() ?>verify-otp',
                    method: 'POST',
                    data: {
                        otp: otp,
                        mail: mail
                    },
                    success: function(res) {
                        if (res.status == 'success') {
                            $('.popup-msg').text(res.massage);
                            $('.popup-msg').css('background-color', 'Green');
                            $('.popup-msg').fadeIn().delay(2000).fadeOut();

                            $('.otp-box').fadeOut();
                            $('.password').delay(1000).fadeIn();
                            $(this).text('Verify')
                        } else {
                            $('.popup-msg').text(res.massage);
                            $('.popup-msg').css('background-color', 'red');
                            $('.popup-msg').fadeIn().delay(2000).fadeOut();
                            $(this).text('Verify')
                        }
                    },
                    error: function(err) {
                        console.log(err);
                    }
                })

            })

            $('#reset-password-btn').on('click', function() {
                let mail = $('#official-mail').val();
                $(this).text('Resetting...')
                let password = $('#reset-password').val();
                let corn_password = $('#confirm-password').val();

                let iseempty = false;

                if (!password) {
                    $('#error-reset-password').text('Password feild Should not empty.');
                    iseempty = true;
                }
                if (!corn_password) {
                    $('#error-confirm-password').text('Confirm Password feild Should not empty.');
                    iseempty = true;
                }

                if (password != corn_password) {
                    $('#error-confirm-password').text('Password & Confirm Password not match.');
                    iseempty = true;
                }

                if (iseempty) {
                    $(this).text('Reset')
                    return;
                }

                $.ajax({
                    url: '<?= base_url() ?>reset-password',
                    method: 'POST',
                    data: {
                        mail: mail,
                        password: password
                    },
                    success: function(res) {
                        if (res.status == 'success') {
                            $('.popup-msg').text(res.massage);
                            $('.popup-msg').css('background-color', 'Green');
                            $('.popup-msg').fadeIn().delay(2000).fadeOut();

                            $('.password').fadeOut();
                            $('.go-back').fadeOut();
                            $('#login-form').trigger('reset')
                            $('.login-heading').text('Login');
                            $('.login').delay(1000).fadeIn();
                        } else {
                            $('.popup-msg').text(res.massage);
                            $('.popup-msg').css('background-color', 'red');
                            $('.popup-msg').fadeIn().delay(2000).fadeOut();
                        }
                        $(this).text('Reset')
                    },
                    error: function(err) {
                        console.log(err);
                        $(this).text('Reset')

                    }
                })
            })

            $('#go-back-login').on('click', function() {
                $('.otp-box').fadeOut();
                $('#login-form').trigger('reset')
                $('.login-heading').text('Login');
                $('.mail').fadeOut();
                $('.password').fadeOut();
                $('.go-back').fadeOut();
                $('.login').delay(1000).fadeIn();
            })

        })
    </script>

</body>

</html>