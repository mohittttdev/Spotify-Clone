
<?php
?>

<style>


    .admin-sidebar {
        width: var(--sidebar-width);
        height: 100vh;

        background: var(--bg-sidebar);
        border-right: 1px solid var(--border-color);

        position: fixed;
        top: 0;
        left: 0;

        display: flex;
        flex-direction: column;

        padding: 25px 15px;

        z-index: 1000;

        transition: transform 0.3s ease;
    }

    /* ================================
       LOGO
    ================================= */

    .sidebar-logo {
        display: flex;
        align-items: center;
        gap: 12px;

        padding: 0 15px;
        margin-bottom: 35px;

        color: #fff;
        font-size: 22px;
        font-weight: 800;

        text-decoration: none;
    }

    .sidebar-logo i {
        color: var(--primary);
        font-size: 30px;
    }

    .sidebar-logo:hover {
        color: #fff;
    }

    /* ================================
       NAVIGATION
    ================================= */

    .sidebar-nav {
        flex: 1;
    }

    .sidebar-section-title {
        color: #777;
        font-size: 11px;
        font-weight: 700;

        text-transform: uppercase;
        letter-spacing: 1px;

        padding: 0 15px;
        margin: 20px 0 10px;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 14px;

        padding: 12px 15px;
        margin-bottom: 5px;

        border-radius: 8px;

        color: var(--text-secondary);
        font-size: 14px;
        font-weight: 500;

        transition: all 0.3s ease;
    }

    .sidebar-link i {
        width: 20px;
        text-align: center;
        font-size: 17px;
    }

    .sidebar-link:hover {
        background: var(--bg-hover);
        color: #fff;
    }

    .sidebar-link.active {
        background: var(--bg-hover);
        color: var(--primary);
        font-weight: 600;
    }

    .sidebar-link.active i {
        color: var(--primary);
    }

    /* ================================
       SIDEBAR FOOTER
    ================================= */

    .sidebar-footer {
        border-top: 1px solid var(--border-color);
        padding-top: 20px;
    }

    .sidebar-admin {
        display: flex;
        align-items: center;
        gap: 12px;

        padding: 12px 15px;
        margin-bottom: 10px;
    }

    .sidebar-admin-avatar {
        width: 38px;
        height: 38px;

        border-radius: 50%;

        background: var(--primary);
        color: #000;

        display: flex;
        align-items: center;
        justify-content: center;

        font-weight: 700;
        font-size: 14px;
    }

    .sidebar-admin-info {
        min-width: 0;
    }

    .sidebar-admin-info strong {
        display: block;
        color: #fff;
        font-size: 13px;
    }

    .sidebar-admin-info span {
        display: block;
        color: var(--text-secondary);
        font-size: 11px;
        margin-top: 3px;
    }

    /* ================================
       MOBILE TOGGLE
    ================================= */

    .sidebar-toggle {
        display: none;

        position: fixed;
        top: 15px;
        left: 15px;

        width: 40px;
        height: 40px;

        border: none;
        border-radius: 8px;

        background: var(--primary);
        color: #000;

        font-size: 18px;

        z-index: 1100;
    }

    .sidebar-overlay {
        display: none;

        position: fixed;
        inset: 0;

        background: rgba(0, 0, 0, 0.6);

        z-index: 999;
    }

    /* ================================
       RESPONSIVE
    ================================= */

    @media (max-width: 992px) {

        .admin-sidebar {
            transform: translateX(-100%);
        }

        .admin-sidebar.show {
            transform: translateX(0);
        }

        .sidebar-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-overlay.show {
            display: block;
        }

    }

</style>


<!-- Mobile Toggle Button -->
<button
    class="sidebar-toggle"
    id="sidebarToggle"
    type="button"
    aria-label="Toggle sidebar"
>
    <i class="fa-solid fa-bars"></i>
</button>


<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>


<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">

    <!-- Logo -->
    <a href="index.php" class="sidebar-logo">

        <i class="fa-brands fa-spotify"></i>

        <span>Spotify Admin</span>

    </a>


    <!-- Navigation -->
    <nav class="sidebar-nav">

        <div class="sidebar-section-title">
            Main Menu
        </div>


        <!-- Dashboard -->
        <a
            href="index.php"
            class="sidebar-link <?= ($pageTitle ?? '') === 'Dashboard' ? 'active' : ''; ?>"
        >
            <i class="fa-solid fa-gauge-high"></i>
            <span>Dashboard</span>
        </a>


        <!-- Music Management -->
        <div class="sidebar-section-title">
            Music Management
        </div>


        <!-- Songs -->
        <a
            href="songs/index.php"
            class="sidebar-link <?= ($pageTitle ?? '') === 'Songs' ? 'active' : ''; ?>"
        >
            <i class="fa-solid fa-music"></i>
            <span>Songs</span>
        </a>


        <!-- Artists -->
        <a
            href="artists/index.php"
            class="sidebar-link <?= ($pageTitle ?? '') === 'Artists' ? 'active' : ''; ?>"
        >
            <i class="fa-solid fa-microphone"></i>
            <span>Artists</span>
        </a>


        <!-- Albums -->
        <a
            href="albums/index.php"
            class="sidebar-link <?= ($pageTitle ?? '') === 'Albums' ? 'active' : ''; ?>"
        >
            <i class="fa-solid fa-compact-disc"></i>
            <span>Albums</span>
        </a>


        <!-- Playlists -->
        <a
            href="playlists/index.php"
            class="sidebar-link <?= ($pageTitle ?? '') === 'Playlists' ? 'active' : ''; ?>"
        >
            <i class="fa-solid fa-list"></i>
            <span>Playlists</span>
        </a>


        <!-- User Management -->
        <div class="sidebar-section-title">
            User Management
        </div>


        <!-- Users -->
        <a
            href="users/index.php"
            class="sidebar-link <?= ($pageTitle ?? '') === 'Users' ? 'active' : ''; ?>"
        >
            <i class="fa-solid fa-users"></i>
            <span>Users</span>
        </a>


        <!-- Settings -->
        <a
            href="settings.php"
            class="sidebar-link <?= ($pageTitle ?? '') === 'Settings' ? 'active' : ''; ?>"
        >
            <i class="fa-solid fa-gear"></i>
            <span>Settings</span>
        </a>

    </nav>


    <!-- Sidebar Footer -->
    <div class="sidebar-footer">

        <div class="sidebar-admin">

            <div class="sidebar-admin-avatar">
                A
            </div>

            <div class="sidebar-admin-info">

                <strong>Admin</strong>

                <span>Administrator</span>

            </div>

        </div>


        <!-- Logout -->
        <a
            href="logout.php"
            class="sidebar-link"
        >
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>

    </div>

</aside>


<script>

    document.addEventListener("DOMContentLoaded", function () {

        const sidebar = document.getElementById("adminSidebar");
        const toggle = document.getElementById("sidebarToggle");
        const overlay = document.getElementById("sidebarOverlay");

        if (!sidebar || !toggle || !overlay) return;

        toggle.addEventListener("click", function () {

            sidebar.classList.toggle("show");
            overlay.classList.toggle("show");

        });

        overlay.addEventListener("click", function () {

            sidebar.classList.remove("show");
            overlay.classList.remove("show");

        });

    });

</script>
