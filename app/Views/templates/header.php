<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="<?= base_url('asset/images/favicon.png') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>GigHz People</title>
</head>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

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

    .container {
        width: calc(100% - 255px);
        /* background: yellow; */
        margin-left: 255px;
        margin-top: 60px;
    }

    button {
        cursor: pointer;
        border: none;
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

    .user_image {
        border-radius: 50%;
        width: 40px;
    }
</style>

<body>
    <div class="header">
        <div>
            Dashboard
        </div>
        <div>
            <p>Admin</p>
            <img class="user_image" src="https://i.pinimg.com/736x/38/44/fe/3844fe3d529e6f8d1659dfc2fb48dd0c.jpg" alt="">
        </div>
    </div>