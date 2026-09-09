<?php
/**
 * Admin Panel Header
 * Spotify Clone
 *
 * Usage:
 * require_once "includes/header.php";
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Spotify Clone Admin Panel">

    <title>
        <?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : ''; ?>
        Spotify Admin
    </title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        /* ================================
           GLOBAL
        ================================= */

        :root {
            --primary: #1db954;
            --primary-hover: #1ed760;

            --bg-dark: #121212;
            --bg-card: #181818;
            --bg-sidebar: #000000;
            --bg-hover: #282828;

            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;

            --border-color: #282828;

            --sidebar-width: 250px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
        }

        a {
            text-decoration: none;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
        }

        /* ================================
           SCROLLBAR
        ================================= */

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-dark);
        }

        ::-webkit-scrollbar-thumb {
            background: #444;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #666;
        }

        /* ================================
           MAIN LAYOUT
        ================================= */

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .admin-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-width: 0;
            background: var(--bg-dark);
        }

        .admin-content {
            padding: 30px;
        }

        /* ================================
           TOPBAR
        ================================= */

        .admin-topbar {
            height: 75px;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-color);

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 30px;

            position: sticky;
            top: 0;
            z-index: 100;
        }

        .admin-topbar h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .admin-topbar p {
            color: var(--text-secondary);
            font-size: 13px;
            margin: 5px 0 0;
        }

        /* ================================
           BUTTONS
        ================================= */

        .btn-spotify {
            background: var(--primary);
            color: #000;
            border: none;
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 25px;
            transition: 0.3s;
        }

        .btn-spotify:hover {
            background: var(--primary-hover);
            color: #000;
            transform: scale(1.03);
        }

        .btn-outline-spotify {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
            font-weight: 600;
            padding: 9px 18px;
            border-radius: 25px;
            transition: 0.3s;
        }

        .btn-outline-spotify:hover {
            background: var(--primary);
            color: #000;
        }

        /* ================================
           CARDS
        ================================= */

        .admin-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
        }

        .admin-card h5 {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .admin-card p {
            color: var(--text-secondary);
        }

        /* ================================
           FORMS
        ================================= */

        .form-label {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            background: #242424;
            color: #fff;
            border: 1px solid #383838;
            border-radius: 8px;
            padding: 11px 14px;
        }

        .form-control:focus,
        .form-select:focus {
            background: #242424;
            color: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(29, 185, 84, 0.15);
        }

        .form-control::placeholder {
            color: #777;
        }

        .form-select option {
            background: #242424;
            color: #fff;
        }

        /* ================================
           TABLE
        ================================= */

        .admin-table {
            width: 100%;
            color: #fff;
            margin-bottom: 0;
        }

        .admin-table thead {
            background: #242424;
        }

        .admin-table th {
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 600;
            padding: 15px;
            border-bottom: 1px solid #383838;
        }

        .admin-table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            color: #ddd;
        }

        .admin-table tbody tr:hover {
            background: var(--bg-hover);
        }

        /* ================================
           BADGES
        ================================= */

        .badge-active {
            background: rgba(29, 185, 84, 0.15);
            color: var(--primary);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-inactive {
            background: rgba(255, 80, 80, 0.15);
            color: #ff6b6b;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* ================================
           ALERTS
        ================================= */

        .alert {
            border-radius: 10px;
            border: none;
        }

        /* ================================
           RESPONSIVE
        ================================= */

        @media (max-width: 992px) {

            .admin-main {
                margin-left: 0;
            }

            .admin-content {
                padding: 20px;
            }

            .admin-topbar {
                padding: 0 20px;
            }

        }

        @media (max-width: 576px) {

            .admin-content {
                padding: 15px;
            }

            .admin-topbar {
                height: 65px;
                padding: 0 15px;
            }

            .admin-topbar h1 {
                font-size: 18px;
            }

            .admin-card {
                padding: 18px;
            }

        }

    </style>

</head>

<body>

    <!-- Admin Wrapper -->
    <div class="admin-wrapper">

        <?php
        /*
         * Sidebar yahan include hoga.
         *
         * Jab sidebar.php ready ho jaye:
         *
         * require_once __DIR__ . '/sidebar.php';
         */
        ?>

        <!-- Main Content -->
        <main class="admin-main">

            <!-- Topbar -->
            <header class="admin-topbar">

                <div>

                    <h1>
                        <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?>
                    </h1>

                    <p>
                        Manage your Spotify Clone
                    </p>

                </div>

                <div class="d-flex align-items-center gap-3">

                    <span class="text-secondary d-none d-md-block">
                        <i class="fa-regular fa-user me-1"></i>
                        Admin
                    </span>

                </div>

            </header>

            <!-- Page Content -->
            <section class="admin-content">