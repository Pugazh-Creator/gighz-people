</div>
<style>
    /* Default popup styles */
    .popup {
        width: 300px;
        height: 200px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transform: scale(0.7);
        opacity: 0;
        position: relative;
        color: #fff;
        transition: background 0.3s;
    }

    .popup.success {
        background: linear-gradient(135deg, #4caf50, #43a047);
    }

    .popup.error {
        background: linear-gradient(135deg, #f44336, #d32f2f);
    }

    .popup .icon {
        font-size: 40px;
        margin-bottom: 10px;
    }

    .popup .message {
        font-size: 18px;
        font-weight: 500;
        text-align: center;
        padding: 0 10px;
    }

    /* overflow */
    .overflow {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.4s ease;
        z-index: 1000;
    }

    .overflow.show {
        visibility: visible;
        opacity: 1;
    }

    /* Animations */
    .zoomIn {
        animation: zoomIn 0.5s forwards;
    }

    .zoomOut {
        animation: zoomOut 0.4s forwards;
    }

    @keyframes zoomIn {
        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    @keyframes zoomOut {
        to {
            transform: scale(0.7);
            opacity: 0;
        }
    }

    .popup .close-btn {
        position: absolute;
        top: 10px;
        right: 12px;
        background: transparent;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
    }
</style>

<!-- Popup HTML (footer) -->
<div class="overflow" id="overflow">
    <div class="popup" id="popupBox">
        <button class="close-btn" id="closePopupBtn">&times;</button>
        <div class="icon" id="popupIcon">✅</div>
        <div class="message" id="popupMessage"></div>
    </div>
</div>
<!-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> -->

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        const modeKey = "theme";
        const $toggleSwitch = $("#toggleSwitch");
        const $knobIcon = $("#toggleSwitch .switch-knob i");

        // Load saved theme
        if (localStorage.getItem(modeKey) === "dark") {
            $("body").addClass("dark-mode");
            $toggleSwitch.addClass("active");
            $knobIcon.removeClass("bx-sun").addClass("bx-moon");
        }

        // Toggle theme
        $toggleSwitch.on("click", function() {
            $(this).toggleClass("active");
            $("body").toggleClass("dark-mode");

            if ($(this).hasClass("active")) {
                localStorage.setItem(modeKey, "dark");
                $knobIcon.removeClass("bx-sun").addClass("bx-moon");
            } else {
                localStorage.setItem(modeKey, "light");
                $knobIcon.removeClass("bx-moon").addClass("bx-sun");
            }
        });
    });



    //------------------------- POPUP STATUS -------------------------
    $(document).ready(function() {

        /**
         * showPopup - displays a popup with success or error status
         * @param {string} message - Message text
         * @param {string} type - "success" or "error" (default: success)
         * @param {number} duration - Auto close time in ms (default: 3000)
         */
        window.showPopup = function(message = "Action completed!", type = "success", duration = 3000) {
            const $overlay = $('#overflow');
            const $popup = $('#popupBox');
            const $icon = $('#popupIcon');
            const $msg = $('#popupMessage');

            // Set message
            $msg.text(message);

            // Set type: success or error
            $popup.removeClass('success error').addClass(type);
            $icon.text(type === 'success' ? '✅' : '❌');

            // Show popup
            $overlay.addClass('show');
            $popup.removeClass('zoomOut').addClass('zoomIn');

            // Auto-close after duration
            setTimeout(closePopup, duration);
        }

        function closePopup() {
            const $overlay = $('#overflow');
            const $popup = $('#popupBox');

            $popup.removeClass('zoomIn').addClass('zoomOut');

            setTimeout(function() {
                $overlay.removeClass('show');
                $popup.removeClass('zoomOut success error');
            }, 400);
        }

        // Close button click
        $('#closePopupBtn').on('click', closePopup);

    });
</script>

</body>

</html>