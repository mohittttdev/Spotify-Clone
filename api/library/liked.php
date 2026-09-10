<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/database.php";

$autoload = __DIR__ . "/../../vendor/autoload.php";

if (!file_exists($autoload)) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Composer dependencies are not installed."
    ]);

    exit;
}

require_once $autoload;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


// --------------------------------------------------
// Only GET request
// --------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only GET method is allowed."
    ]);

    exit;
}


// --------------------------------------------------
// JWT Configuration
// --------------------------------------------------

$jwtConfig = require __DIR__ . "/../../config/jwt.php";

$secretKey = $jwtConfig["secret"];


// --------------------------------------------------
// Get Authorization Header
// --------------------------------------------------

$headers = getallheaders();

$authHeader = $headers["Authorization"]
    ?? $headers["authorization"]
    ?? "";

if (
    !$authHeader ||
    !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)
) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Authentication token is required."
    ]);

    exit;
}

$token = $matches[1];


// --------------------------------------------------
// Verify JWT Token
// --------------------------------------------------

try {

    $decoded = JWT::decode(
        $token,
        new Key($secretKey, "HS256")
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


// --------------------------------------------------
// Get Liked Songs
// --------------------------------------------------

$query = "
    SELECT
        liked_songs.id AS like_id,

        songs.id,
        songs.title,
        songs.audio,
        songs.cover,
        songs.duration,

        artists.id AS artist_id,
        artists.name AS artist_name,

        albums.id AS album_id,
        albums.name AS album_name

    FROM liked_songs

    INNER JOIN songs
        ON liked_songs.song_id = songs.id

    LEFT JOIN artists
        ON songs.artist_id = artists.id

    LEFT JOIN albums
        ON songs.album_id = albums.id

    WHERE liked_songs.user_id = ?

    ORDER BY liked_songs.id DESC
";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database query failed."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $userId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$songs = [];

while ($row = mysqli_fetch_assoc($result)) {
    $songs[] = $row;
}

mysqli_stmt_close($stmt);


// --------------------------------------------------
// Success Response
// --------------------------------------------------

echo json_encode([

    "success" => true,

    "user_id" => $userId,

    "total" => count($songs),

    "songs" => $songs

]);

?>