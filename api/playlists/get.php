<?php

header("Content-Type: application/json");

require_once "../../config/database.php";
require_once "../../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/*
|--------------------------------------------------------------------------
| Only GET method allowed
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only GET method is allowed."
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| JWT Authentication
|--------------------------------------------------------------------------
*/

$headers = getallheaders();

$authHeader = $headers["Authorization"]
    ?? $headers["authorization"]
    ?? "";

if (!preg_match("/Bearer\s(\S+)/", $authHeader, $matches)) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Authentication token is required."
    ]);

    exit;
}

$token = $matches[1];

try {
    $jwtConfig = require __DIR__ . "/../../config/jwt.php";

    $decoded = JWT::decode(
        $token,
        new Key($jwtConfig["secret"], "HS256")
    );

    $userId = (int) $decoded->data->id;

} catch (Exception $e) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Invalid or expired token."
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Optional playlist ID
|--------------------------------------------------------------------------
*/

$playlistId = (int) ($_GET["playlist_id"] ?? 0);

/*
|--------------------------------------------------------------------------
| Get specific playlist
|--------------------------------------------------------------------------
*/

if ($playlistId > 0) {

    $query = "
        SELECT
            p.id,
            p.user_id,
            p.name,
            p.description,
            p.cover,
            p.created_at,
            COUNT(ps.id) AS total_songs
        FROM playlists p
        LEFT JOIN playlist_songs ps
            ON p.id = ps.playlist_id
        WHERE p.id = ? AND p.user_id = ?
        GROUP BY
            p.id,
            p.user_id,
            p.name,
            p.description,
            p.cover,
            p.created_at
    ";

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to prepare query."
        ]);

        exit;
    }

    mysqli_stmt_bind_param($stmt, "ii", $playlistId, $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        http_response_code(404);

        echo json_encode([
            "success" => false,
            "message" => "Playlist not found."
        ]);

        exit;
    }

    $playlist = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    /*
    |--------------------------------------------------------------------------
    | Get songs of specific playlist
    |--------------------------------------------------------------------------
    */

    $songsQuery = "
        SELECT
            ps.id AS playlist_song_id,
            s.id,
            s.title,
            s.audio,
            s.cover,
            s.duration,
            a.id AS artist_id,
            a.name AS artist_name,
            al.id AS album_id,
            al.name AS album_name
        FROM playlist_songs ps
        INNER JOIN songs s
            ON ps.song_id = s.id
        LEFT JOIN artists a
            ON s.artist_id = a.id
        LEFT JOIN albums al
            ON s.album_id = al.id
        WHERE ps.playlist_id = ?
        ORDER BY ps.id DESC
    ";

    $songsStmt = mysqli_prepare($conn, $songsQuery);

    mysqli_stmt_bind_param($songsStmt, "i", $playlistId);
    mysqli_stmt_execute($songsStmt);

    $songsResult = mysqli_stmt_get_result($songsStmt);

    $songs = [];

    while ($song = mysqli_fetch_assoc($songsResult)) {
        $songs[] = $song;
    }

    mysqli_stmt_close($songsStmt);

    $playlist["total_songs"] = (int) $playlist["total_songs"];
    $playlist["songs"] = $songs;

    echo json_encode([
        "success" => true,
        "playlist" => $playlist
    ]);

    mysqli_close($conn);

    exit;
}

/*
|--------------------------------------------------------------------------
| Get all playlists of logged-in user
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        p.id,
        p.user_id,
        p.name,
        p.description,
        p.cover,
        p.created_at,
        COUNT(ps.id) AS total_songs
    FROM playlists p
    LEFT JOIN playlist_songs ps
        ON p.id = ps.playlist_id
    WHERE p.user_id = ?
    GROUP BY
        p.id,
        p.user_id,
        p.name,
        p.description,
        p.cover,
        p.created_at
    ORDER BY p.id DESC
";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare query."
    ]);

    exit;
}

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$playlists = [];

while ($playlist = mysqli_fetch_assoc($result)) {
    $playlist["total_songs"] = (int) $playlist["total_songs"];

    $playlists[] = $playlist;
}

echo json_encode([
    "success" => true,
    "user_id" => $userId,
    "total" => count($playlists),
    "playlists" => $playlists
]);

mysqli_stmt_close($stmt);
mysqli_close($conn);