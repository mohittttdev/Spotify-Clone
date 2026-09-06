<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../includes/auth.php";
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


// Authenticate user

$user = authenticateUser();


// Get user from database

$query = "
    SELECT
        id,
        name,
        email,
        profile_image,
        role,
        status,
        created_at
    FROM users
    WHERE id = ?
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


$userId = (int) $user->id;

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $userId
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$dbUser = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// User not found

if (!$dbUser) {

    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "User not found."
    ]);

    exit;
}


// Check account status

if ($dbUser["status"] !== "active") {

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Your account is not active."
    ]);

    exit;
}


// Success response

http_response_code(200);

echo json_encode([
    "success" => true,
    "message" => "Authenticated user.",
    "user" => [
        "id" => (int) $dbUser["id"],
        "name" => $dbUser["name"],
        "email" => $dbUser["email"],
        "profile_image" => $dbUser["profile_image"],
        "role" => $dbUser["role"],
        "status" => $dbUser["status"],
        "created_at" => $dbUser["created_at"]
    ]
]);