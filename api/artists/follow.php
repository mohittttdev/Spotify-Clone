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
// Get Authorization Header
// --------------------------------------------------

$headers = getallheaders();

$authorization = $headers["Authorization"]
    ?? $headers["authorization"]
    ?? "";


// --------------------------------------------------
// Check Bearer Token
// --------------------------------------------------

if ($authorization === "" || !preg_match('/Bearer\s+(.+)/i', $authorization, $matches)) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);

    exit;
}


$token = trim($matches[1]);


// --------------------------------------------------
// JWT Configuration
// --------------------------------------------------

$jwtConfig = require __DIR__ . "/../../config/jwt.php";


// --------------------------------------------------
// Verify JWT
// --------------------------------------------------

try {

    $decoded = JWT::decode(
        $token,
        new Key($jwtConfig["secret"], "HS256")
    );

} catch (Exception $e) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Invalid or expired token."
    ]);

    exit;
}


// --------------------------------------------------
// Get User ID from JWT
// --------------------------------------------------

$user_id = (int) ($decoded->data->id ?? 0);


if ($user_id <= 0) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Invalid user information."
    ]);

    exit;
}


// --------------------------------------------------
// Get Artist ID
// --------------------------------------------------

$artist_id = (int) ($_POST["artist_id"] ?? 0);


if ($artist_id <= 0) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Artist ID is required."
    ]);

    exit;
}


// --------------------------------------------------
// Check Artist
// --------------------------------------------------

$stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM artists
     WHERE id = ?
     AND status = 'active'
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $artist_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$artist = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$artist) {

    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "Artist not found."
    ]);

    exit;
}


// --------------------------------------------------
// Check Existing Follow
// --------------------------------------------------

$stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM artist_follows
     WHERE user_id = ?
     AND artist_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $user_id,
    $artist_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$existingFollow = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// --------------------------------------------------
// Unfollow
// --------------------------------------------------

if ($existingFollow) {

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM artist_follows
         WHERE user_id = ?
         AND artist_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $user_id,
        $artist_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    echo json_encode([
        "success" => true,
        "action" => "unfollow",
        "following" => false,
        "message" => "Artist unfollowed."
    ]);

    exit;
}


// --------------------------------------------------
// Follow
// --------------------------------------------------

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO artist_follows
     (user_id, artist_id)
     VALUES (?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $user_id,
    $artist_id
);


if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    echo json_encode([
        "success" => true,
        "action" => "follow",
        "following" => true,
        "message" => "Artist followed."
    ]);

} else {

    mysqli_stmt_close($stmt);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to follow artist."
    ]);
}