<style>
    /* Sidebar Container */
    .sidebar {
        height: 95vh;
        background: #363636;
        width: 230px;
        position: fixed;
        top: 10px;
        left: 5px;
        padding: 10px 0;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: width 0.3s ease;
        z-index: 1;
    }

    /* Collapsed state */
    .sidebar.collapsed {
        width: 70px;
    }

    .sidebar.collapsed .sidebar-top-child.child1 img,
    .sidebar.collapsed .sidebar-top-child.child2 ul li a .nav-text,
    .sidebar.collapsed .sidebar-top-child.child2 ul li button .nav-text,
    .sidebar.collapsed .sidebar-bottom p {
        display: none;
    }

    .sidebar.collapsed .sidebar-bottom {
        text-align: center;
    }

    .sidebar.collapsed .sidebar-bottom p {
        display: block;
        font-size: 12px;
    }

    /* Logo */
    .sidebar .image-container {
        text-align: center;
    }

    .sidebar .image-container img {
        height: 70px;
        background: #fff;
        padding: 5px 15px;
        border-radius: 10px;
    }

    /* Top Section */
    .sidebar .sidebar-top {
        flex: 9;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    /* Navigation */
    .sidebar-top-child.child2 ul {
        list-style: none;
        margin: 0;
        padding: 0 10px;
    }

    .sidebar-top-child.child2 ul li {
        margin-bottom: 5px;
        position: relative;
    }

    .sidebar-top-child.child2 ul li a,
    .sidebar-top-child.child2 ul li button {
        width: 80%;
        background: transparent;
        color: #fff;
        display: flex;
        align-items: center;
        border-radius: 8px;
        padding: 8px 12px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        justify-content: space-between;
        transition: background 0.2s ease;
        transition: all .5s ease;
    }

    .sidebar.collapsed .sidebar-top-child.child2 ul li a {
        justify-content: center;
        width: fit-content;
    }

    .sidebar-top-child.child2 ul li a:hover,
    .sidebar-top-child.child2 ul li button:hover {
        background: linear-gradient(#eb5975, #eb59be);
    }

    body.dark-mode .sidebar-top-child.child2 ul li a:hover,
    body.dark-mode .sidebar-top-child.child2 ul li button:hover {
        background: #fff;
        color: #363636;
    }

    /* Submenu */
    .sidebar-top-child.child2 ul li ul {
        list-style: none;
        padding-left: 20px;
        display: none;
    }

    .sidebar-top-child.child2 ul li.active ul {
        display: block;
    }

    .sidebar-top-child.child2 ul li ul li a {
        /* background: #555; */
        font-size: 14px;
        padding: 6px 10px;
        margin: 3px 0;
    }

    /* Bottom Section */
    .sidebar-bottom {
        flex: 1;
        padding: 10px;
        border-top: 1px solid #555;
        color: #fff;
    }

    .sidebar-bottom a {
        display: block;
        color: #e74c3c;
        text-decoration: none;
        text-align: center;
        margin: 0 auto 10px;
    }

    body.dark-mode .sidebar-bottom a {
        color: #fff;
    }

    body.dark-mode .sidebar-bottom {
        border-top-color: #fff;
    }

    .sidebar-bottom p {
        font-size: 13px;
        opacity: 0.7;
        text-align: center;
        margin: 0;
    }

    /* Toggle Button */
    .sidebar .toggle-btn {
        background: #444;
        border: none;
        color: #fff;
        padding: 6px 10px;
        margin: 0 auto;
        border-radius: 5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sidebar #toggleBtn {
        position: absolute;
        top: 5px;
        height: 40px;
        width: 40px;
        right: -15px;
        border-radius: 50%;
        z-index: 2;
    }

    /* ----------------------------- */
    .sidebar-top-child.child2 ul li ul li a {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #fff;
    }

    .sidebar-top-child.child2 ul li ul li a i {
        display: none;
        /* hide icons by default */
    }

    /* Collapsed mode → show only icons */
    .sidebar.collapsed .sidebar-top-child.child2 ul li ul li a .nav-text {
        display: none;
        /* hide text */
    }

    .sidebar.collapsed .sidebar-top-child.child2 ul li ul li a i {
        display: inline-block;
        /* show icon */
    }


    /* When sidebar collapsed */
    body.sidebar-collapsed .container,
    body.sidebar-collapsed .header {
        width: calc(100% - 85px);
        margin-left: 85px;
    }

    body.dark-mode .sidebar {
        background: linear-gradient(#363636, #dc1b40);
    }
</style>
<?php
$array_main_menu = [];
$array_main_menu_data = [];
foreach ($basedata["emp_info"] as $emp_data) {

    $catogory_name = $emp_data['user_category'];
    // print_r($catogory_name);
    $array_main_menu = json_decode($emp_data['permission']);
    //    print_r($array_main_menu);
    $array_main_menu_data = ($array_main_menu[0]->permission[0]->main_menu[0]);
}
?>

<div class="sidebar" id="sidebar">
    <button class="toggle-btn" id="toggleBtn"><i class='bx bx-chevron-left'></i></button>
    <div class="sidebar-top">
        <!-- Logo -->
        <div class="sidebar-top-child child1">
            <div class="image-container">
                <img src="<?= base_url('asset/images/favicon.png') ?>" alt="Company Logo">
            </div>
        </div>

        <!-- Navigation -->
        <div class="sidebar-top-child child2">
            <ul>
                <li><a href="<?=base_url()?>dashboard"><span><i class='bx bxs-dashboard'></i> <span class="nav-text">Dashboard</span><span></a></li>

                <li>
                    <a href="#" role="button"><span><i class='bx bxs-group'></i><span class="nav-text"> HR</span><span> <i class='bx bx-chevron-down'></i></a>

                    <ul>
                        <?php if ($array_main_menu_data->hrmanagement[0]->sub_menu[0]->hrdashboard[0]->view == '1') : ?>
                            <li><a href="#"><i class='bx bxs-user'></i> <span class="nav-text">Dashboard</span></a></li>
                        <?php endif; ?>

                        <?php if ($array_main_menu_data->hrmanagement[0]->sub_menu[0]->employeemasterdata[0]->view == '1') : ?>
                            <li><a href="#"><i class='bx bxs-user'></i> <span class="nav-text">Employees</span></a></li>
                        <?php endif; ?>

                        <?php if ($array_main_menu_data->hrmanagement[0]->sub_menu[0]->recruitment[0]->view == '1') : ?>
                            <li><a href="#"><i class='bx bx-briefcase'></i> <span class="nav-text">Recruitment</span></a></li>
                        <?php endif; ?>

                        <?php if ($array_main_menu_data->hrmanagement[0]->sub_menu[0]->payroll[0]->view == '1') : ?>
                            <li><a href="#"><i class='bx bx-money'></i> <span class="nav-text">Payroll</span></a></li>
                        <?php endif; ?>

                        <?php if ($array_main_menu_data->hrmanagement[0]->sub_menu[0]->accounting[0]->view == '1') : ?>
                            <li><a href="#"><i class='bx bx-calculator'></i> <span class="nav-text">Accounting</span></a></li>
                        <?php endif; ?>

                        <?php if ($array_main_menu_data->hrmanagement[0]->sub_menu[0]->appraisal[0]->view == '1') : ?>
                            <li><a href="#"><i class='bx bx-line-chart'></i> <span class="nav-text">Appraisal</span></a></li>
                        <?php endif; ?>

                        <?php if ($array_main_menu_data->hrmanagement[0]->sub_menu[0]->career[0]->view == '1') : ?>
                            <li><a href="#"><i class='bx bx-rocket'></i> <span class="nav-text">Career</span></a></li>
                        <?php endif; ?>

                        <?php if ($array_main_menu_data->hrmanagement[0]->sub_menu[0]->policy[0]->view == '1') : ?>
                            <li><a href="#"><i class='bx bx-book'></i> <span class="nav-text">Policy</span></a></li>
                        <?php endif; ?>

                        <?php if ($array_main_menu_data->hrmanagement[0]->sub_menu[0]->disciplinary[0]->view == '1') : ?>
                            <li><a href="#"><i class='bx bx-error'></i> <span class="nav-text">Disciplinary</span></a></li>
                        <?php endif; ?>

                    </ul>

                </li>

                <li>
                    <a href="#" role="button"><span><i class='bx bxs-cog'></i><span class="nav-text"> IT</span> <span><i class='bx bx-chevron-down'></i></a>
                    <ul>
                        <li><a href="#"><i class='bx bx-error'></i> <span class="nav-text">Assets</span></a></li>
                        <li><a href="#"><i class='bx bx-error'></i> <span class="nav-text">ISMS</span></a></li>
                    </ul>
                </li>

                <li><a href="#"><span><i class='bx bx-time'></i><span class="nav-text"> Timesheet</span><span></a></li>
                <li><span><a href="#"><span><i class='bx bxs-calendar'></i><span class="nav-text"> Holidays</span><span></a></li>
            </ul>
        </div>
    </div>

    <!-- Bottom Section -->
    <div class="sidebar-bottom">
        <a href="#"><i class='bx bx-log-out'></i> <span>Logout</span></a>
        <p>Version <?= $basedata['version']?></p>

    </div>
</div>


<script>
    $(document).ready(function() {
        // Submenu toggle
        $(".sidebar-top-child.child2 ul li a").on("click", function(e) {
            // ✅ If sidebar is collapsed, block submenu toggle
            // if ($("#sidebar").hasClass("collapsed")) {
            //     e.preventDefault();
            //     return false;
            // }

            let $parentLi = $(this).parent("li");

            // Toggle active state
            if ($parentLi.hasClass("active")) {
                $parentLi.removeClass("active");
            } else {
                $(".sidebar-top-child.child2 ul li").removeClass("active");
                $parentLi.addClass("active");
            }
        });

        // Sidebar collapse toggle
        $("#toggleBtn").on("click", function() {
            $("#sidebar").toggleClass("collapsed");
            $("body").toggleClass("sidebar-collapsed"); // 👈 Add class to body

            // Close submenus when collapsed
            if ($("#sidebar").hasClass("collapsed")) {
                $(".sidebar-top-child.child2 ul li").removeClass("active");
            }

            // Change arrow direction
            let icon = $(this).find("i");
            if ($("#sidebar").hasClass("collapsed")) {
                icon.removeClass("bx-chevron-left").addClass("bx-chevron-right");
            } else {
                icon.removeClass("bx-chevron-right").addClass("bx-chevron-left");
            }
        });
    });
</script>