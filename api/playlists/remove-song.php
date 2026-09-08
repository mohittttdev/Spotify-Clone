<?php

header("Content-Type: application/json");

require_once "../../config/database.php";
require_once "../../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/*
|--------------------------------------------------------------------------
| Only DELETE method allowed
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only DELETE method is allowed."
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
| Check song in playlist
|--------------------------------------------------------------------------
*/

$checkQuery = "
    SELECT id
    FROM playlist_songs
    WHERE playlist_id = ? AND song_id = ?
";

$checkStmt = mysqli_prepare($conn, $checkQuery);

if (!$checkStmt) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare song query."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $checkStmt,
    "ii",
    $playlistId,
    $songId
);

mysqli_stmt_execute($checkStmt);

$result = mysqli_stmt_get_result($checkStmt);

if (mysqli_num_rows($result) === 0) {
    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "Song is not in this playlist."
    ]);

    mysqli_stmt_close($checkStmt);
    mysqli_close($conn);

    exit;
}

mysqli_stmt_close($checkStmt);

/*
|--------------------------------------------------------------------------
| Remove song from playlist
|--------------------------------------------------------------------------
*/

$deleteQuery = "
    DELETE FROM playlist_songs
    WHERE playlist_id = ? AND song_id = ?
";

$deleteStmt = mysqli_prepare($conn, $deleteQuery);

if (!$deleteStmt) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare delete query."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $deleteStmt,
    "ii",
    $playlistId,
    $songId
);

if (mysqli_stmt_execute($deleteStmt)) {

    echo json_encode([
        "success" => true,
        "message" => "Song removed from playlist successfully.",
        "playlist_id" => $playlistId,
        "song_id" => $songId
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to remove song from playlist."
    ]);
}

mysqli_stmt_close($deleteStmt);
mysqli_close($conn);