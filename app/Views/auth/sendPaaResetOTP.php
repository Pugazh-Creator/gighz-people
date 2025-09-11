<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('asset/css/resetPass.css') ?>">
    <title>Password Reset</title>
</head>

<body>
    <section class="password-reset-container">

        <div class="form-box">
            <form action="<?= base_url('/send_otp') ?>" method="post">
                <?= csrf_field(); ?>
                <div class="input-box">
                    <label for="email2">Official Mail</label>
                    <input type="text" name="email2" value="<?= set_value('email2') ?>" id="email2" placeholder="Enter Email">
                    <span class="error-text">
                        <?= isset($validation) ? dispaly_form_error($validation, 'email') : '' ?>
                    </span>
                </div>
                <button class="otpBtn" id="send_otp">Send OTP</button>
            </form>
            <input type="text" id="email" name="email">
            <button class="otpBtn" id="send">for Test</button>
        </div>
    </section>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#send').click(function(){
                var email = $('#email').val();
                console.log(email);
                console.log('mail sended');
                $.ajax({
                    url : "<?=base_url('funtest')?>",
                    type : 'POST',
                    data : {email : email},
                    success: function(response) {
                       alert('mail send successfully');
                    },
                    error: function(xhr, status, error) {
                        alert('Failed to update leave.');
                    }
                });
            });
        });
    </script>
</body>

</html>