

/** 
 * \\\\\\\\\\\\\\\\\\\\\\\\     Refreach Biometric   //////////////////////////////////////
 */
document.addEventListener('DOMContentLoaded', function () {
    const popup = document.querySelector('.bio-refreach-container');
    const overlay = document.querySelector('.overlay');
    const openBtn = document.getElementById('openRefreashBtn');

    // Show popup
    openBtn.addEventListener('click', function () {
        overlay.style.display = 'block';
        popup.style.display = 'block';
    });

    // Hide popup on overlay click
    overlay.addEventListener('click', function () {
        overlay.style.display = 'none';
        popup.style.display = 'none';
    });

    // Form submission and validation
    document.getElementById('refreashform').addEventListener('submit', function (e) {
        e.preventDefault();

        let start = document.getElementById('refreach_startdate').value;
        let end = document.getElementById('refreach_enddate').value;

        let hasError = false;

        // Clear previous errors
        document.getElementById('error_refreach_startdate').textContent = '';
        document.getElementById('error_refreach_enddate').textContent = '';

        // Validation
        if (!start) {
            document.getElementById('error_refreach_startdate').textContent = 'Enter Start Date';
            hasError = true;
        }

        if (!end) {
            document.getElementById('error_refreach_enddate').textContent = 'Enter End Date';
            hasError = true;
        }

        if (hasError) return;

        // Proceed with AJAX (fetch)
        fetch( 'http://localhost:8080/biometriccontroller/refreachBimetric', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}`
        })
            .then(response => response.text())
            .then(data => {
                console.log(data);
                // Close popup after successful response
                location.reload();
                overlay.style.display = 'none';
                popup.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
});
