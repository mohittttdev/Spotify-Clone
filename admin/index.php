<?php

session_start();

/*
|--------------------------------------------------------------------------
| Admin Entry Point
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

header("Location: login.php");
exit;