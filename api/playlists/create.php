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

$name = trim($data["name"] ?? "");
$description = trim($data["description"] ?? "");
$cover = trim($data["cover"] ?? "default-cover.jpg");

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if ($name === "") {
    http_response_code(422);

    echo json_encode([
        "success" => false,
        "message" => "Playlist name is required."
    ]);

    exit;
}

if (strlen($name) > 100) {
    http_response_code(422);

    echo json_encode([
        "success" => false,
        "message" => "Playlist name cannot exceed 100 characters."
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Insert playlist
|--------------------------------------------------------------------------
*/

$query = "
    INSERT INTO playlists
    (user_id, name, description, cover)
    VALUES (?, ?, ?, ?)
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

mysqli_stmt_bind_param(
    $stmt,
    "isss",
    $userId,
    $name,
    $description,
    $cover
);

if (mysqli_stmt_execute($stmt)) {

    $playlistId = mysqli_insert_id($conn);

    http_response_code(201);

    echo json_encode([
        "success" => true,
        "message" => "Playlist created successfully.",
        "playlist" => [
            "id" => $playlistId,
            "user_id" => $userId,
            "name" => $name,
            "description" => $description,
            "cover" => $cover
        ]
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to create playlist."
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);