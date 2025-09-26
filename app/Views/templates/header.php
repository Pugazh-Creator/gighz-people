<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="<?= base_url('asset/images/favicon.png') ?>">


    <link rel="stylesheet" href="<?= base_url('asset/css/index.css') ?>">
    <link rel="stylesheet" href="<?= base_url('asset/css/applyleave.css') ?>">
    <link rel="stylesheet" href="<?= base_url('asset/css/hrdashboard.css') ?>">


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <title>GigHz People</title>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

</head>
<style>
    * {
        margin: 0;
        padding: 0;
        font-family: "poppins", sans-serif;
    }

    ul {
        list-style: none;
    }

    a {
        text-decoration: none;
    }

    :root {
        --theme-red--: linear-gradient(90deg, #363636, #db1c40);
        --theme-black--: linear-gradient(#eb5975, #eb59be);

    }

    tbody tr:hover {
        /* background: linear-gradient(#eb59766e, #eb59bf6a); */
        background: var(--theme-black--);
    }

    tbody tr:hover td, tbody tr:hover td a {
        color: #fff;
        /* text white */
        /* background: #007bff; */
        /* optional: blue background */
    }

    tbody tr:hover td:nth-child(1) {
        border-radius: 10px 0px 0 10px;
    }

    tbody tr:hover td:last-child {
        border-radius: 0 10px 10px 0;
    }

    body.dark-mode tbody tr:hover {
        background: var(--theme-red--);
        color: #fff;
    }

    .container {
        width: calc(100% - 255px);
        /* background: yellow; */
        margin-left: 255px;
        margin-top: 60px;
        transition: all .3s ease;
    }

    textarea {
        resize: vertical;
        /* only up/down resizing */
        overflow: auto;
    }

    button {
        cursor: pointer;
        border: none;
        background: var(--theme-black--);
        color: #fff;
        letter-spacing: 1.5px;
    }

    button:hover {
        box-shadow: 0 0 10px #eb5975, 0 0 20px #eb59be;
    }

    body.dark-mode button:hover {
        box-shadow: 0 0 10px #363636, 0 0 20px #db1c40;
    }

    tbody tr {
        background: none;
    }

    tbody tr td {
        background: none;
    }

    body.dark-mode button {
        background: var(--theme-red--);
    }

    .header {
        width: calc(100% - 255px);
        background: transparent;
        backdrop-filter: blur(10px);
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: fixed;
        top: 0;
        right: 0;
        padding: 5px 0;
        transition: all .3s ease;
        /* border-bottom: 1px solid gray; */
        /* box-shadow: 2px 2px 10px gray; */

    }

    .header div {
        display: flex;
        align-self: center;
        justify-content: right;
        padding: 0 20px;
        height: 100%;
        /* background: green; */
        text-align: center;
    }

    .header div:nth-last-child(1) {
        gap: 20px;
        align-items: center;
    }

    .header .user_image {
        border-radius: 50%;
        width: 40px;
    }

    /* toggle */
    /* Toggle container */
    .header .toggle-switch {
        width: 10px;
        height: 25px;
        border-radius: 20px;
        background: #ebebeb;
        position: relative;
        cursor: pointer;
    }

    /* The knob */
    .header .toggle-switch .switch-circle {
        padding: 0px 12px;
        background: linear-gradient(#eb5975, #eb59be);
        border-radius: 50%;
        position: absolute;
        top: auto;
        left: 0;
        transition: all 5s ease;
    }

    /* Active (Dark mode ON) */
    .header .toggle-switch.active {
        /* background: #333; */
        justify-content: end;
    }

    .header .toggle-switch.active .switch-circle {
        right: 0;
        left: auto;
        background: linear-gradient(#dc1b40, #363636);
        /* color: #f1c40f; */
        /* moon color */
    }

    .overlay {
        width: 100vw;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 10;
        background: #d6d6d63a;
        display: none;
    }

    /* -----------------------DATATABLE------------------ */
    /* All buttons */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        background-color: #f5f5f5;
        /* button background */
        border: none;
        /* button border */
        color: #363636;
        /* text color */
        padding: 5px 12px;
        /* spacing */
        margin: 0 2px;
        /* spacing between buttons */
        border-radius: 5px;
        /* rounded corners */
        cursor: pointer;
    }

    /* Hover effect */
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: var(--theme-black--);
        color: #fff;
        border: none;
    }

    body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: var(--theme-red--);
        color: #fff;
        border: none;
    }

    /* Active page */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--theme-black--);
        color: #fff !important;
        border: none;
    }

    body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--theme-red--);
        color: #fff !important;
        border: none;
    }

    /* Active page */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        border: none;
    }

    /* Disabled buttons */
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        background: #eee;
        color: #999;
        cursor: not-allowed;
    }

    body.dark-mode .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        background: #eee;
        color: #999;
        cursor: not-allowed;
    }


    .error-txt {
        color: red;
        font-size: 12px;
    }
</style>

<body>

    <div class="header">
        <div>
            <h3><?php echo $thisPage ?></h3>
        </div>
        <div>
            <!-- Toggle Switch -->
            <div class="toggle-switch" id="toggleSwitch">
                <div class="switch-circle">
                    <!-- <i class='bx bx-sun'></i> -->
                </div>
            </div>
            <p><?= $basedata['name'] ?></p>

            <img class="user_image" src="<?= base_url() ?>asset/users/<?php echo $basedata['image'] != null ? $basedata['image'] : 'no_user.jpg' ?>" alt="">
        </div>
    </div>
    <div class="container">
        <div class="overlay"></div>

        <script>
            const baseurl = "<?= base_url() ?>";
        </script>