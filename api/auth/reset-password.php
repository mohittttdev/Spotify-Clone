<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/database.php";

// Only POST request allowed
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only POST method is allowed."
    ]);

    exit;
}


// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON data."
    ]);

    exit;
}


// Get input
$token = trim($data["token"] ?? "");
$password = $data["password"] ?? "";
$confirmPassword = $data["confirm_password"] ?? "";


// Validate token
if ($token === "") {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Reset token is required."
    ]);

    exit;
}


// Validate password
if ($password === "") {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Password is required."
    ]);

    exit;
}


// Password length
if (strlen($password) < 8) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 8 characters."
    ]);

    exit;
}


// Confirm password
if ($password !== $confirmPassword) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Passwords do not match."
    ]);

    exit;
}


// Find user by reset token
$query = "
    SELECT id, status, reset_token_expiry
    FROM users
    WHERE reset_token = ?
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

mysqli_stmt_bind_param($stmt, "s", $token);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// Invalid token
if (!$user) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid reset token."
    ]);

    exit;
}


// Check token expiry
if (
    empty($user["reset_token_expiry"]) ||
    strtotime($user["reset_token_expiry"]) < time()
) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Reset token has expired."
    ]);

    exit;
}


// Check user status
if ($user["status"] !== "active") {

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Your account is not active."
    ]);

    exit;
}


// Hash new password
$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);


// Update password
$query = "
    UPDATE users
    SET
        password = ?,
        reset_token = NULL,
        reset_token_expiry = NULL,
        updated_at = NOW()
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


$userId = (int) $user["id"];

mysqli_stmt_bind_param(
    $stmt,
    "si",
    $hashedPassword,
    $userId
);

if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to reset password."
    ]);

    exit;
}

mysqli_stmt_close($stmt);


// Success
http_response_code(200);

echo json_encode([
    "success" => true,
    "message" => "Password reset successfully."
]);