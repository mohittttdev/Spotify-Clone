<?php

header("Content-Type: application/json");

require_once "../../config/database.php";
require_once "../../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/*
|--------------------------------------------------------------------------
| Only POST method allowed
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only POST method is allowed."
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
| Read JSON data
|--------------------------------------------------------------------------
*/

$data = json_decode(file_get_contents("php://input"), true);

$playlistId = (int) ($data["playlist_id"] ?? 0);
$songId = (int) ($data["song_id"] ?? 0);

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if ($playlistId <= 0 || $songId <= 0) {
    http_response_code(422);

    echo json_encode([
        "success" => false,
        "message" => "Valid playlist_id and song_id are required."
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Check playlist ownership
|--------------------------------------------------------------------------
*/

$playlistQuery = "
    SELECT id
    FROM playlists
    WHERE id = ? AND user_id = ?
";

$playlistStmt = mysqli_prepare($conn, $playlistQuery);

if (!$playlistStmt) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare playlist query."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $playlistStmt,
    "ii",
    $playlistId,
    $userId
);

mysqli_stmt_execute($playlistStmt);

$playlistResult = mysqli_stmt_get_result($playlistStmt);

if (mysqli_num_rows($playlistResult) === 0) {
    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "Playlist not found or you do not own this playlist."
    ]);

    mysqli_stmt_close($playlistStmt);
    mysqli_close($conn);

    exit;
}

mysqli_stmt_close($playlistStmt);

/*
|--------------------------------------------------------------------------
| Check song exists
|--------------------------------------------------------------------------
*/

$songQuery = "
    SELECT id
    FROM songs
    WHERE id = ?
";

$songStmt = mysqli_prepare($conn, $songQuery);

if (!$songStmt) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare song query."
    ]);

    exit;
}

mysqli_stmt_bind_param($songStmt, "i", $songId);
mysqli_stmt_execute($songStmt);

$songResult = mysqli_stmt_get_result($songStmt);

if (mysqli_num_rows($songResult) === 0) {
    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "Song not found."
    ]);

    mysqli_stmt_close($songStmt);
    mysqli_close($conn);

    exit;
}

mysqli_stmt_close($songStmt);

/*
|--------------------------------------------------------------------------
| Check duplicate song
|--------------------------------------------------------------------------
*/

$duplicateQuery = "
    SELECT id
    FROM playlist_songs
    WHERE playlist_id = ? AND song_id = ?
";

$duplicateStmt = mysqli_prepare($conn, $duplicateQuery);

if (!$duplicateStmt) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare duplicate query."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $duplicateStmt,
    "ii",
    $playlistId,
    $songId
);

mysqli_stmt_execute($duplicateStmt);

$duplicateResult = mysqli_stmt_get_result($duplicateStmt);

if (mysqli_num_rows($duplicateResult) > 0) {
    http_response_code(409);

    echo json_encode([
        "success" => false,
        "message" => "Song is already in this playlist."
    ]);

    mysqli_stmt_close($duplicateStmt);
    mysqli_close($conn);

    exit;
}

mysqli_stmt_close($duplicateStmt);

/*
|--------------------------------------------------------------------------
| Add song to playlist
|--------------------------------------------------------------------------
*/

$insertQuery = "
    INSERT INTO playlist_songs
    (playlist_id, song_id)
    VALUES (?, ?)
";

$insertStmt = mysqli_prepare($conn, $insertQuery);

if (!$insertStmt) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare insert query."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $insertStmt,
    "ii",
    $playlistId,
    $songId
);

if (mysqli_stmt_execute($insertStmt)) {

    $playlistSongId = mysqli_insert_id($conn);

    http_response_code(201);

    echo json_encode([
        "success" => true,
        "message" => "Song added to playlist successfully.",
        "playlist_song_id" => $playlistSongId,
        "playlist_id" => $playlistId,
        "song_id" => $songId
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to add song to playlist."
    ]);
}

mysqli_stmt_close($insertStmt);
mysqli_close($conn);