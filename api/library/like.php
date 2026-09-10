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
// Only POST request
// --------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only POST method is allowed."
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
// Get JSON Data
// --------------------------------------------------

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$songId = (int) ($data["song_id"] ?? 0);

if ($songId <= 0) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Valid song_id is required."
    ]);

    exit;
}


// --------------------------------------------------
// Check Song Exists
// --------------------------------------------------

$checkSong = mysqli_prepare(
    $conn,
    "SELECT id FROM songs WHERE id = ?"
);

mysqli_stmt_bind_param(
    $checkSong,
    "i",
    $songId
);

mysqli_stmt_execute($checkSong);

$songResult = mysqli_stmt_get_result($checkSong);

if (mysqli_num_rows($songResult) === 0) {

    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "Song not found."
    ]);

    exit;
}

mysqli_stmt_close($checkSong);


// --------------------------------------------------
// Check Already Liked
// --------------------------------------------------

$check = mysqli_prepare(
    $conn,
    "SELECT id FROM liked_songs WHERE user_id = ? AND song_id = ?"
);

mysqli_stmt_bind_param(
    $check,
    "ii",
    $userId,
    $songId
);

mysqli_stmt_execute($check);

$result = mysqli_stmt_get_result($check);

if (mysqli_num_rows($result) > 0) {

    echo json_encode([
        "success" => true,
        "liked" => true,
        "message" => "Song already liked."
    ]);

    exit;
}

mysqli_stmt_close($check);


// --------------------------------------------------
// Like Song
// --------------------------------------------------

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO liked_songs (user_id, song_id) VALUES (?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $userId,
    $songId
);

if (mysqli_stmt_execute($stmt)) {

    echo json_encode([
        "success" => true,
        "liked" => true,
        "message" => "Song liked successfully."
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to like song."
    ]);

}

mysqli_stmt_close($stmt);

?>