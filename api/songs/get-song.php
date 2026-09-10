<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/database.php";


// Only GET request allowed
if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only GET method is allowed."
    ]);

    exit;
}


// Get song ID
$songId = isset($_GET["id"]) ? (int) $_GET["id"] : 0;


// Validate song ID
if ($songId <= 0) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Valid song ID is required."
    ]);

    exit;
}


// Get song
$query = "
    SELECT
        songs.id,
        songs.artist_id,
        songs.album_id,
        songs.title,
        songs.audio,
        songs.cover,
        songs.duration,
        songs.audio_source,
        songs.cover_source,
        songs.genre,
        songs.release_date,
        songs.play_count,
        songs.status,
        songs.created_at,
        songs.updated_at,

        artists.name AS artist_name,
        artists.image AS artist_image,
        artists.bio AS artist_bio,
        artists.country AS artist_country,
        artists.verified AS artist_verified,

        albums.name AS album_name,
        albums.cover AS album_cover,
        albums.description AS album_description,
        albums.release_date AS album_release_date,
        albums.album_type AS album_type

    FROM songs

    LEFT JOIN artists
        ON songs.artist_id = artists.id

    LEFT JOIN albums
        ON songs.album_id = albums.id

    WHERE songs.id = ?
    AND songs.status = 'active'

    LIMIT 1
";


$stmt = mysqli_prepare($conn, $query);


if (!$stmt) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database error."
    ]);

    exit;
}


// Bind song ID
mysqli_stmt_bind_param(
    $stmt,
    "i",
    $songId
);


// Execute query
if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch song."
    ]);

    exit;
}


$result = mysqli_stmt_get_result($stmt);

$song = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// Song not found
if (!$song) {

    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "Song not found."
    ]);

    exit;
}


// Prepare response
$songData = [

    "id" => (int) $song["id"],

    "title" => $song["title"],

    "artist" => [
        "id" => $song["artist_id"] !== null
            ? (int) $song["artist_id"]
            : null,

        "name" => $song["artist_name"],

        "image" => $song["artist_image"],

        "bio" => $song["artist_bio"],

        "country" => $song["artist_country"],

        "verified" => (bool) $song["artist_verified"]
    ],

    "album" => [
        "id" => $song["album_id"] !== null
            ? (int) $song["album_id"]
            : null,

        "name" => $song["album_name"],

        "cover" => $song["album_cover"],

        "description" => $song["album_description"],

        "release_date" => $song["album_release_date"],

        "type" => $song["album_type"]
    ],

    "audio" => $song["audio"],

    "cover" => $song["cover"],

    "duration" => (int) $song["duration"],

    "audio_source" => $song["audio_source"],

    "cover_source" => $song["cover_source"],

    "genre" => $song["genre"],

    "release_date" => $song["release_date"],

    "play_count" => (int) $song["play_count"],

    "status" => $song["status"],

    "created_at" => $song["created_at"],

    "updated_at" => $song["updated_at"]
];


// Success response
http_response_code(200);

echo json_encode([
    "success" => true,

    "message" => "Song fetched successfully.",

    "data" => [
        "song" => $songData
    ]
]);

?>