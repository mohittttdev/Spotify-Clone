<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";


// --------------------------------------------------
// Only POST request allowed
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
// Get JWT token
// --------------------------------------------------

$token = getBearerToken();


// Token required

if (!$token) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Authentication token is required."
    ]);

    exit;
}


// --------------------------------------------------
// Verify token
// --------------------------------------------------

$user = authenticateUser();


// --------------------------------------------------
// Delete token from database
// --------------------------------------------------

$query = "
    DELETE FROM jwt_tokens
    WHERE token = ?
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
    "s",
    $token
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Logout failed."
    ]);

    exit;
}


$deletedRows = mysqli_stmt_affected_rows($stmt);

mysqli_stmt_close($stmt);


// --------------------------------------------------
// Check token
// --------------------------------------------------

if ($deletedRows === 0) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Token is already logged out or invalid."
    ]);

    exit;
}


// --------------------------------------------------
// Success
// --------------------------------------------------

http_response_code(200);

echo json_encode([
    "success" => true,
    "message" => "Logout successful."
]);