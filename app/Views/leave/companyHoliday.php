<?php
$holliday_permission = '';
foreach ($basedata["emp_info"] as $emp_data) {

    $catogory_name = $emp_data['user_category'];
    // print_r($catogory_name);
    $array_main_menu = json_decode($emp_data['permission']);
    //    print_r($array_main_menu);
    $holliday_permission = ($array_main_menu[0]->permission[0]->main_menu[0]->hrmanagement[0]->sub_menu[0]->holiday);
}
?>
<div class="holidays_page">
    <div class="holidayheader">
        <div class="">
            <h1>2025</h1>
            <select name="holiday_year" id="holiday_year">
                <?php
                // $currentYear = date('Y'); // current year
                foreach ($year_selection as $year) {
                    $selected = $year == $currentYear ? 'selected' : '';
                    echo "<option value=\"$year\" $selected>$year</option>";
                }
                ?>
            </select>
        </div>
        <div class="">
            <?php 
                if($holliday_permission[0]->add == '1'){
                    echo '<button id="add_holiday_btn">Add Holiday</button>';
                }
            ?>
        </div>
    </div>
    <div class="holidaybody">
        <div class="calendar" id="calendar"></div>
    </div>
</div>

<div class="model" id="addholidaycontainer">
    <form action="#" id="holiday_add_form">
        <h2>Add Holiday</h2>
        <div class="cls-btn">✕</div>
        <div class="input-box">
            <label for="holiday_date">Date</label>
            <input type="date" name="holiday_date" id="holiday_date">
            <div class="error-txt" id="error-holiday_date"></div>
        </div>
        <div class="input-box">
            <label for="holiday_name">Holidays Name</label>
            <input type="text" name="holiday_name" id="holiday_name">
            <div class="error-txt" id="error-holiday_name"></div>
        </div>
        <div class="input-box">
            <label for="holiday_type">Holidays Name</label>
            <select name="holiday_type" id="holiday_type">
                <option value="festival">Festival</option>
                <option value="first_saturday">1st Saturday</option>
                <div class="error-txt" id="error-holiday_type"></div>
            </select>
        </div>
        <div>
            <button type="submit" class="smt-btn">submit</button>
        </div>
    </form>
</div>
<div class="model" id="updateholidaycontainer">
    <form action="#" id="holiday_update_form">
        <h2>update Holiday</h2>
        <div class="cls-btn">✕</div>
        <div class="input-box">
            <label for="update_holiday_date">Date</label>
            <input type="date" name="update_holiday_date" id="update_holiday_date">
            <div class="error-txt" id="error-update_holiday_date"></div>
        </div>
        <div class="input-box">
            <label for="update_holiday_name">Holidays Name</label>
            <input type="text" name="update_holiday_name" id="update_holiday_name">
            <div class="error-txt" id="error-update_holiday_name"></div>
        </div>
        <div>
            <button type="submit" class="smt-btn">submit</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {

        const monthNames = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        const weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, "0");
        const day = String(today.getDate()).padStart(2, "0");
        const todayStr = `${year}-${month}-${day}`;
        getHolidays(year);

        function getHolidays(year) {
            $.ajax({
                url: baseurl + 'hrcontroller/getHolidays/' + year,
                method: "GET",
                success: function(res) {
                    // Map DB data to calendar-friendly format
                    const holidays = res.map(h => ({
                        id: h.id,
                        holiday_date: h.holiday_date,
                        holiday_name: h.holiday_name,
                        holiday_type: h.holiday_type.replace('_', '-') // first_saturday -> first-saturday
                    }));

                    buildCalendar(year, holidays);
                }
            })
        }

        const isHR = '<?= $holliday_permission[0]->edit == '1' ?>' // PHP variable injected into JS

        function buildCalendar(year, holidays) {
            $("#calendar").empty();
            for (let month = 0; month < 12; month++) {
                let $monthDiv = $("<div>").addClass("month");
                $monthDiv.append(`<h2>${monthNames[month]}</h2>`);

                let $daysGrid = $("<div>").addClass("days");

                weekdays.forEach(day => {
                    $daysGrid.append($("<div>").addClass("weekday").text(day));
                });

                const firstDay = new Date(year, month, 1).getDay();
                const totalDays = new Date(year, month + 1, 0).getDate();

                for (let i = 0; i < firstDay; i++) {
                    $daysGrid.append($("<div>"));
                }

                for (let day = 1; day <= totalDays; day++) {
                    const date = new Date(year, month, day);
                    const yearStr = date.getFullYear();
                    const monthStr = String(date.getMonth() + 1).padStart(2, "0");
                    const dayStr = String(date.getDate()).padStart(2, "0");
                    const dateStr = `${yearStr}-${monthStr}-${dayStr}`;

                    let $dayDiv = $("<div>").addClass("day").text(day);

                    if (dateStr === todayStr) $dayDiv.addClass("today");

                    const holiday = holidays.find(h => h.holiday_date === dateStr);
                    if (holiday) {
                        $dayDiv.addClass(holiday.holiday_type)
                            .attr("data-tooltip", holiday.holiday_name)
                            .attr("data-id", holiday.id); // Add holiday ID

                        if (isHR) {
                            // Add edit/delete icons
                            const icons = `
                        <span class="holiday-icons">
                            <i class="edit-icon" title="Edit">✎</i>
                            <i class="delete-icon" title="Delete">🗑</i>
                        </span>
                    `;
                            $dayDiv.append(icons);
                        }
                    }

                    $daysGrid.append($dayDiv);
                }

                $monthDiv.append($daysGrid);
                $("#calendar").append($monthDiv);
            }
        }

        $('#holiday_year').on('change', function() {
            let selectedYear = $(this).val();
            console.log(selectedYear);
            getHolidays(selectedYear);
        })


        $(document).on('click', '#add_holiday_btn', function() {
            $('.error-txt').text('');
            $('#addholidaycontainer, .overlay').fadeIn();
        })

        $('.overlay, .cls-btn').on('click', function() {
            $('.model, .overlay').fadeOut();
        })

        $('#holiday_add_form').on('submit', function(e) {
            e.preventDefault();

            $('.error-txt').text('');

            let holiday_date = $('#holiday_date').val();
            let holiday_name = $('#holiday_name').val();
            let holiday_type = $('#holiday_type').val();
            let flag = false;
            if (!holiday_date) {
                $('#error-holiday_date').text('Enter Holiday Date.');
                flag = true;
            }

            if (!holiday_name) {
                $('#error-holiday_name').text('Enter Holiday Name.');
                flag = true;
            }

            if (!holiday_type) {
                $('#error-holiday_type').text('select Holiday Type.');
                flag = true;
            }
            if (flag) return;

            $('.smt-btn').text('');
            $('.smt-btn').text('Processing...').prop('disabled', true);

            $.ajax({
                url: baseurl + 'hrcontroller/addAndFetchHoliday',
                method: "POST",
                data: {
                    holiday_date: holiday_date,
                    holiday_name: holiday_name,
                    holiday_type: holiday_type
                },
                success: function(res) {
                    if (res.status == 'success') {
                        showPopup(res.message, res.status)
                        $('#holiday_add_form').trigger('reset');
                        getHolidays(year)
                    } else {
                        showPopup(res.message, res.status)

                    }
                    $('.smt-btn').text('Submit').prop('disabled', false);
                },
                error: function(err) {
                    showPopup("Failed To add Holiday", 'error')
                    console.log(err)
                    $('.smt-btn').text('Submit').prop('disabled', false);
                }
            })
        })


        $(document).on("click", ".edit-icon", function(e) {
            console.log('edit button clicked');
            e.stopPropagation();
            const holidayId = $(this).closest(".day").data("id");

            // Fetch holiday data via AJAX
            $.ajax({
                url: baseurl + "hrcontroller/getHolidayById/" + holidayId,
                method: "GET",
                success: function(res) {
                    // Fill the update form
                    console.log(res);
                    $("#update_holiday_date").val(res.holiday_date);
                    $("#update_holiday_name").val(res.holiday_name);
                    $("#holiday_update_form").data("id", holidayId); // store ID for update later

                    // Show the modal
                    $("#updateholidaycontainer, .overlay").fadeIn();
                }
            });
        });

        $("#holiday_update_form").on("submit", function(e) {
            e.preventDefault();

            let holiday_date = $("#update_holiday_date").val();
            let holiday_name = $("#update_holiday_name").val();
            let flag = false;
            if (!holiday_date) {
                $('#error-update_holiday_date').text('Enter Holiday date');
                flag = true;
            }
            if (!holiday_name) {
                $('#error-update_holiday_name').text('Enter Holiday Name');
                flag = true;
            }
            if (flag) return;

            const holidayId = $(this).data("id");

            const formData = {
                holiday_date: $("#update_holiday_date").val(),
                holiday_name: $("#update_holiday_name").val()
            };

            $(".smt-btn").text("Processing...").prop("disabled", true);

            $.ajax({
                url: baseurl + "hrcontroller/updateHoliday/" + holidayId,
                method: "POST",
                data: formData,
                success: function(res) {
                    console.log(res);
                    showPopup(res.message, res.status)
                    $(".cls-btn").click();
                    getHolidays($("#holiday_year").val()); // refresh calendar
                    $('.smt-btn').text('Submit').prop('disabled', false);
                    getHolidays(year)
                },
                error: function() {
                    showPopup('!somthing went wrong', 'error')
                    $('.smt-btn').text('Submit').prop('disabled', false);
                },
                complete: function() {
                    $(".smt-btn").text("Submit").prop("disabled", false);
                }
            });
        });

        $(document).on("click", ".delete-icon", function(e) {
            e.stopPropagation();
            const holidayId = $(this).closest(".day").data("id");
            if (confirm("Are you sure you want to delete this holiday?" + holidayId)) {
                $.ajax({
                    url: baseurl + "hrcontroller/deleteHoliday/" + holidayId,
                    method: "POST",
                    success: function(res) {
                        console.log(res);
                        showPopup(res.message, res.status)
                        getHolidays(year)
                        getHolidays($('#holiday_year').val()); // refresh calendar
                    }
                });
            }
        });
    })
</script>