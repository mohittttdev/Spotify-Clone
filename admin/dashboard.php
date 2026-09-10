<?php

session_start();

require_once "../config/database.php";
require_once "includes/auth.php";

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$users_count = 0;
$songs_count = 0;
$artists_count = 0;
$albums_count = 0;

/* Users */
$query = "SELECT COUNT(*) AS total FROM users";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $users_count = $row['total'];
}

/* Songs */
$query = "SELECT COUNT(*) AS total FROM songs";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $songs_count = $row['total'];
}

/* Artists */
$query = "SELECT COUNT(*) AS total FROM artists";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $artists_count = $row['total'];
}

/* Albums */
$query = "SELECT COUNT(*) AS total FROM albums";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $albums_count = $row['total'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - Spotify Clone</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f7f8;
            color: #222;
        }

        .dashboard {
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .topbar h1 {
            font-size: 28px;
        }

        .welcome {
            color: #777;
            margin-top: 6px;
        }

        .logout {
            text-decoration: none;
            background: #111;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
        }

        .logout:hover {
            background: #333;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }

        .card h3 {
            font-size: 15px;
            color: #777;
            margin-bottom: 12px;
        }

        .card .number {
            font-size: 32px;
            font-weight: bold;
        }

        .section {
            margin-top: 35px;
        }

        .section h2 {
            margin-bottom: 18px;
        }

        .quick-links {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .quick-link {
            display: block;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            text-decoration: none;
            color: #222;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }

        .quick-link:hover {
            transform: translateY(-2px);
        }

        .quick-link strong {
            display: block;
            margin-bottom: 7px;
        }

        .quick-link span {
            color: #777;
            font-size: 14px;
        }

        @media (max-width: 900px) {

            .stats,
            .quick-links {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 600px) {

            .dashboard {
                padding: 20px;
            }

            .topbar {
                align-items: flex-start;
                gap: 15px;
                flex-direction: column;
            }

            .stats,
            .quick-links {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="dashboard">

    <!-- Top Bar -->

    <div class="topbar">

        <div>

            <h1>Admin Dashboard</h1>

            <p class="welcome">
                Welcome to Spotify Clone Admin Panel
            </p>

        </div>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>


    <!-- Statistics -->

    <div class="stats">

        <div class="card">

            <h3>Total Users</h3>

            <div class="number">
                <?= $users_count ?>
            </div>

        </div>


        <div class="card">

            <h3>Total Songs</h3>

            <div class="number">
                <?= $songs_count ?>
            </div>

        </div>


        <div class="card">

            <h3>Total Artists</h3>

            <div class="number">
                <?= $artists_count ?>
            </div>

        </div>


        <div class="card">

            <h3>Total Albums</h3>

            <div class="number">
                <?= $albums_count ?>
            </div>

        </div>

    </div>


    <!-- Quick Links -->

    <div class="section">

        <h2>Quick Management</h2>

        <div class="quick-links">

            <a href="users/" class="quick-link">

                <strong>Users</strong>

                <span>
                    Manage registered users
                </span>

            </a>


            <a href="songs/" class="quick-link">

                <strong>Songs</strong>

                <span>
                    Manage songs and music
                </span>

            </a>


            <a href="artists/" class="quick-link">

                <strong>Artists</strong>

                <span>
                    Manage artists
                </span>

            </a>


            <a href="albums/" class="quick-link">

                <strong>Albums</strong>

                <span>
                    Manage albums
                </span>

            </a>

        </div>

    </div>

</div>

</body>

</html>