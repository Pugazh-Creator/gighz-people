<!DOCTYPE html>
<html>
<head>
    <title>Biometric Device Check</title>
    <style>
        #result {
            margin-top: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h2>Check Biometric Device Connection</h2>
    <button id="check-devive-btn">Check Device</button>

    <div id="result"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
       $(document).ready(function(){
        $('#check-devive-btn').click(function(){
            $('#result').text('⌛ Checking Device');

            $.ajax({
                url : '<?=base_url("biometric/check")?>',
                method : 'GET',
                success: function(response) {
                    let result=  response == 'success' ?  '✅ Device is Connected' : '❌ Device is Not Connected' ;
                    $('#result').text(result);
                    },
                    error: function(xhr, status, error) {
                       alert('❗failed to Connect Device');
                    }
            })
        })
       })
    </script>

</body>
</html>
