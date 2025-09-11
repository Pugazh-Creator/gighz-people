<style>
    .sidebar {
        width: 245px;
        height: 95vh;
        background: red;
        border-radius: 10px;
        position: fixed;
        left: 5px;
        top: 7px;
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
    }

    .sidebar .sidebar-footer {
        height: 10%;
        background: yellow;
    }

    img {
        width: 100px;
    }

    .sidebar-top {
        display: grid;
        gap: 20px;

    }

    .sidebar .sidebar-top:nth-child(1) {
        width: 100%;
        height: 10%;
        /* background: white; */
    }

    .sidebar .image-container {
        background: #fff;
        border-radius: 10px;
        width: fit-content;
        margin: auto;
        padding: 3px 15px;
    }

    .nav {
        width: auto;
        /* background: pink; */
        display: flex;
        justify-content: center;
    }

    .nav ul {
        width: 80%;
        /* background: yellow; */
    }

    .nav ul li {
        gap: 5px;
        padding: 5px 10px;
    }

    .nav ul li a,
    .nav ul li button {
        display: flex;
        gap: 20px;
        align-items: center;
        width: 90%;
        padding: 5px 10px;
        border-radius: 10px;
        background: transparent;
    }

    .nav ul li a:hover,
    .nav ul li button:hover {
        background: white;
        color: #363636;
    }

    .nav ul li ul {
        display: none;
    }

    .nav ul li.active ul {
        display: block;
    }

    .nav ul li ul {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
        padding-left: 20px;
    }

    /* Submenu visible when active */
    .nav ul li.active ul {
        max-height: 500px;
        /* big enough to fit all submenu items */
    }
</style>

<div class="sidebar">
    <div class="sidebar-top">
        <div>
            <div class="image-container">
                <img src="<?= base_url('asset/images/favicon.png') ?>" alt="gighz the PCB design company">
            </div>
        </div>
        <div class="nav primary-menu">
            <ul>
                <li><a href=""><i class='bx bxs-dashboard'></i>Dashboard</a></li>
                <li>
                    <button> <span><i class='bx bxs-group'></i> HR</span> <i class='bx bx-chevron-down'></i></button>
                    <ul>
                        <li><a href="">Dashboard</a></li>
                        <li><a href="">Employees</a></li>
                        <li><a href="">Recruitment</a></li>
                        <li><a href="">Payroll</a></li>
                        <li><a href="">Accounting</a></li>
                        <li><a href="">Appraisal</a></li>
                        <li><a href="">Career</a></li>
                        <li><a href="">Policy</a></li>
                        <li><a href="">Disciplinary</a></li>
                    </ul>
                </li>
                <li><button><span><i class='bx bxs-group'></i> IT</span> <i class='bx bx-chevron-down'></i></button></li>
                <li><a href=""><span>Timesheet</span></a></li>
                <li><a href=""><span>Holidays</span></a></li>
            </ul>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $(".nav ul li button").on("click", function() {
            let $parentLi = $(this).parent("li");

            // If the clicked menu is already open, close it
            if ($parentLi.hasClass("active")) {
                $parentLi.removeClass("active");
            } else {
                // Close all other menus
                $(".nav ul li").removeClass("active");

                // Open the clicked one
                $parentLi.addClass("active");
            }
        });
    });
</script>