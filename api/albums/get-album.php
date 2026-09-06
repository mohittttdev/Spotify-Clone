<?php

header("Content-Type: application/json");

require_once "../../config/database.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Album ID is required"
    ]);
    exit;
}

$album_id = intval($_GET['id']);

/* Get album details */
$album_query = "
    SELECT 
        albums.id,
        albums.name,
        albums.cover,
        artists.id AS artist_id,
        artists.name AS artist_name
    FROM albums
    LEFT JOIN artists ON albums.artist_id = artists.id
    WHERE albums.id = ?
";

$stmt = mysqli_prepare($conn, $album_query);
mysqli_stmt_bind_param($stmt, "i", $album_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo json_encode([
        "success" => false,
        "message" => "Album not found"
    ]);
    exit;
}

$album = mysqli_fetch_assoc($result);

/* Get album songs */
$songs_query = "
    SELECT 
        songs.id,
        songs.title,
        songs.audio,
        songs.cover,
        songs.duration,
        artists.id AS artist_id,
        artists.name AS artist_name
    FROM songs
    LEFT JOIN artists ON songs.artist_id = artists.id
    WHERE songs.album_id = ?
    ORDER BY songs.id ASC
";

$songs_stmt = mysqli_prepare($conn, $songs_query);
mysqli_stmt_bind_param($songs_stmt, "i", $album_id);
mysqli_stmt_execute($songs_stmt);

$songs_result = mysqli_stmt_get_result($songs_stmt);

$songs = [];

while ($song = mysqli_fetch_assoc($songs_result)) {
    $songs[] = $song;
}

/* Final response */

echo json_encode([
    "success" => true,
    "album" => [
        "id" => $album["id"],
        "name" => $album["name"],
        "cover" => $album["cover"],
        "artist" => [
            "id" => $album["artist_id"],
            "name" => $album["artist_name"]
        ],
        "songs" => $songs
    ]
]);

mysqli_stmt_close($stmt);
mysqli_stmt_close($songs_stmt);
mysqli_close($conn);