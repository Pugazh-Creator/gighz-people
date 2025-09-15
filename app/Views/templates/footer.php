</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
</script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">
</body>

</html>