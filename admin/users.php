
<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

/* Fetch all users */
$query = "
    SELECT
        id,
        name,
        email,
        profile_image,
        role,
        status,
        created_at
    FROM users
    ORDER BY id DESC
";

$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Users - Spotify Admin</title>

    <link rel="stylesheet" href="assets/css/admin.css">

</head>

<body>

<div class="admin-wrapper">

    <?php require_once "includes/sidebar.php"; ?>

    <main class="admin-content">

        <?php require_once "includes/header.php"; ?>

        <div class="dashboard">

            <!-- Page Header -->
            <div class="dashboard-header">

                <div>
                    <h1>Users</h1>

                    <p>
                        Manage registered users from the admin panel.
                    </p>
                </div>

            </div>


            <!-- Users Table -->
            <div class="table-container">

                <div class="table-header">

                    <h2>All Users</h2>

                    <span>
                        Total:
                        <?= $result ? $result->num_rows : 0 ?>
                    </span>

                </div>


                <div class="table-responsive">

                    <table>

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>User</th>

                                <th>Email</th>

                                <th>Role</th>

                                <th>Status</th>

                                <th>Created</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($result && $result->num_rows > 0): ?>

                            <?php while ($user = $result->fetch_assoc()): ?>

                                <tr>

                                    <!-- ID -->
                                    <td>
                                        <?= (int) $user["id"] ?>
                                    </td>


                                    <!-- User -->
                                    <td>

                                        <div class="user-info">

                                            <?php if (!empty($user["profile_image"])): ?>

                                                <img
                                                    src="../<?= htmlspecialchars($user["profile_image"]) ?>"
                                                    alt="Profile"
                                                    class="user-avatar"
                                                >

                                            <?php else: ?>

                                                <div class="user-avatar default-avatar">
                                                    <?= strtoupper(substr($user["name"], 0, 1)) ?>
                                                </div>

                                            <?php endif; ?>


                                            <div>

                                                <strong>
                                                    <?= htmlspecialchars($user["name"]) ?>
                                                </strong>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- Email -->
                                    <td>
                                        <?= htmlspecialchars($user["email"]) ?>
                                    </td>


                                    <!-- Role -->
                                    <td>

                                        <?php if ($user["role"] === "admin"): ?>

                                            <span class="badge badge-admin">
                                                Admin
                                            </span>

                                        <?php else: ?>

                                            <span class="badge badge-user">
                                                User
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Status -->
                                    <td>

                                        <?php if ($user["status"] === "active"): ?>

                                            <span class="badge badge-active">
                                                Active
                                            </span>

                                        <?php else: ?>

                                            <span class="badge badge-inactive">
                                                Inactive
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Created -->
                                    <td>

                                        <?= !empty($user["created_at"])
                                            ? date("d M Y", strtotime($user["created_at"]))
                                            : "N/A"
                                        ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="6" class="no-data">
                                    No users found.
                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <?php require_once "includes/footer.php"; ?>

    </main>

</div>

</body>

</html>