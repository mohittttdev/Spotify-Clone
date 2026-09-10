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
// Only DELETE request
// --------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only DELETE method is allowed."
    ]);

    exit;
}


// --------------------------------------------------
// JWT configuration
// --------------------------------------------------

$jwtConfig = require __DIR__ . "/../../config/jwt.php";


// --------------------------------------------------
// Get Authorization header
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

$jwt = $matches[1];


// --------------------------------------------------
// Verify JWT
// --------------------------------------------------

try {

    $decoded = JWT::decode(
        $jwt,
        new Key($jwtConfig["secret"], "HS256")
    );

    $user_id = (int) $decoded->data->id;

} catch (Exception $e) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Invalid or expired token."
    ]);

    exit;
}


// --------------------------------------------------
// Clear user's listening history
// --------------------------------------------------

$query = "
    DELETE FROM listening_history
    WHERE user_id = ?
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

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);


// --------------------------------------------------
// Execute delete
// --------------------------------------------------

if (mysqli_stmt_execute($stmt)) {

    $deletedCount = mysqli_stmt_affected_rows($stmt);

    echo json_encode([
        "success" => true,
        "message" => "Listening history cleared successfully.",
        "deleted_count" => $deletedCount
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to clear listening history."
    ]);

}


mysqli_stmt_close($stmt);
mysqli_close($conn);

?>