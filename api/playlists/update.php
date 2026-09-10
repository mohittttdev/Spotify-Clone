<?php

header("Content-Type: application/json");

require_once "../../config/database.php";
require_once "../../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/*
|--------------------------------------------------------------------------
| Only PUT method allowed
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only PUT method is allowed."
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
$name = trim($data["name"] ?? "");
$description = trim($data["description"] ?? "");
$cover = trim($data["cover"] ?? "default-cover.jpg");

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if ($playlistId <= 0) {
    http_response_code(422);

    echo json_encode([
        "success" => false,
        "message" => "Valid playlist_id is required."
    ]);

    exit;
}

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
| Check playlist ownership
|--------------------------------------------------------------------------
*/

$checkQuery = "
    SELECT id
    FROM playlists
    WHERE id = ? AND user_id = ?
";

$checkStmt = mysqli_prepare($conn, $checkQuery);

if (!$checkStmt) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare query."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $checkStmt,
    "ii",
    $playlistId,
    $userId
);

mysqli_stmt_execute($checkStmt);

$result = mysqli_stmt_get_result($checkStmt);

if (mysqli_num_rows($result) === 0) {
    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "Playlist not found or you do not own this playlist."
    ]);

    mysqli_stmt_close($checkStmt);
    mysqli_close($conn);

    exit;
}

mysqli_stmt_close($checkStmt);

/*
|--------------------------------------------------------------------------
| Update playlist
|--------------------------------------------------------------------------
*/

$updateQuery = "
    UPDATE playlists
    SET name = ?, description = ?, cover = ?
    WHERE id = ? AND user_id = ?
";

$updateStmt = mysqli_prepare($conn, $updateQuery);

if (!$updateStmt) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare update query."
    ]);

    exit;
}

mysqli_stmt_bind_param(
    $updateStmt,
    "sssii",
    $name,
    $description,
    $cover,
    $playlistId,
    $userId
);

if (mysqli_stmt_execute($updateStmt)) {

    echo json_encode([
        "success" => true,
        "message" => "Playlist updated successfully.",
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
        "message" => "Failed to update playlist."
    ]);
}

mysqli_stmt_close($updateStmt);
mysqli_close($conn);