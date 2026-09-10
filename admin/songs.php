
<?php

session_start();

/* Admin authentication */
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";


/* Fetch all songs */
$query = "
    SELECT
        songs.id,
        songs.title,
        songs.audio,
        songs.cover,
        songs.duration,
        songs.audio_source,
        songs.genre,
        songs.release_date,
        songs.play_count,
        songs.status,
        artists.name AS artist_name,
        albums.name AS album_name
    FROM songs
    LEFT JOIN artists
        ON songs.artist_id = artists.id
    LEFT JOIN albums
        ON songs.album_id = albums.id
    ORDER BY songs.id DESC
";

$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Songs - Spotify Admin</title>

    <link rel="stylesheet" href="assets/css/admin.css">

</head>


<body>

<div class="admin-wrapper">

    <!-- Sidebar -->
    <?php require_once "includes/sidebar.php"; ?>


    <main class="admin-content">

        <!-- Header -->
        <?php require_once "includes/header.php"; ?>


        <div class="dashboard">

            <!-- Page Header -->
            <div class="dashboard-header">

                <div>

                    <h1>Songs</h1>

                    <p>
                        Manage all songs from the admin panel.
                    </p>

                </div>

                <div>

                    <a href="add-song.php" class="btn btn-primary">
                        + Add Song
                    </a>

                </div>

            </div>


            <!-- Songs Table -->
            <div class="table-container">

                <div class="table-header">

                    <h2>All Songs</h2>

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

                                <th>Song</th>

                                <th>Artist</th>

                                <th>Album</th>

                                <th>Genre</th>

                                <th>Duration</th>

                                <th>Source</th>

                                <th>Plays</th>

                                <th>Status</th>

                                <th>Actions</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if ($result && $result->num_rows > 0): ?>

                            <?php while ($song = $result->fetch_assoc()): ?>

                                <tr>

                                    <!-- ID -->
                                    <td>
                                        <?= (int) $song["id"] ?>
                                    </td>


                                    <!-- Song -->
                                    <td>

                                        <div class="song-info">

                                            <?php if (!empty($song["cover"])): ?>

                                                <img
                                                    src="../<?= htmlspecialchars($song["cover"]) ?>"
                                                    alt="Song Cover"
                                                    class="song-cover"
                                                >

                                            <?php else: ?>

                                                <div class="song-cover default-cover">
                                                    ♪
                                                </div>

                                            <?php endif; ?>


                                            <div>

                                                <strong>
                                                    <?= htmlspecialchars($song["title"]) ?>
                                                </strong>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- Artist -->
                                    <td>

                                        <?= !empty($song["artist_name"])
                                            ? htmlspecialchars($song["artist_name"])
                                            : "Unknown"
                                        ?>

                                    </td>


                                    <!-- Album -->
                                    <td>

                                        <?= !empty($song["album_name"])
                                            ? htmlspecialchars($song["album_name"])
                                            : "Single"
                                        ?>

                                    </td>


                                    <!-- Genre -->
                                    <td>

                                        <?= !empty($song["genre"])
                                            ? htmlspecialchars($song["genre"])
                                            : "N/A"
                                        ?>

                                    </td>


                                    <!-- Duration -->
                                    <td>

                                        <?php

                                        $duration = (int) $song["duration"];

                                        $minutes = floor($duration / 60);
                                        $seconds = $duration % 60;

                                        echo sprintf(
                                            "%d:%02d",
                                            $minutes,
                                            $seconds
                                        );

                                        ?>

                                    </td>


                                    <!-- Source -->
                                    <td>

                                        <?php if ($song["audio_source"] === "local"): ?>

                                            <span class="badge badge-local">
                                                Local
                                            </span>

                                        <?php else: ?>

                                            <span class="badge badge-external">
                                                External
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Play Count -->
                                    <td>

                                        <?= number_format(
                                            (int) $song["play_count"]
                                        ) ?>

                                    </td>


                                    <!-- Status -->
                                    <td>

                                        <?php if ($song["status"] === "active"): ?>

                                            <span class="badge badge-active">
                                                Active
                                            </span>

                                        <?php else: ?>

                                            <span class="badge badge-inactive">
                                                Inactive
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Actions -->
                                    <td>

                                        <div class="action-buttons">

                                            <a
                                                href="edit-song.php?id=<?= (int) $song["id"] ?>"
                                                class="btn btn-edit"
                                            >
                                                Edit
                                            </a>


                                            <a
                                                href="delete-song.php?id=<?= (int) $song["id"] ?>"
                                                class="btn btn-delete"
                                                onclick="return confirm('Are you sure you want to delete this song?');"
                                            >
                                                Delete
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>


                        <?php else: ?>

                            <tr>

                                <td colspan="10" class="no-data">
                                    No songs found.
                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <!-- Footer -->
        <?php require_once "includes/footer.php"; ?>

    </main>

</div>

</body>

</html>
