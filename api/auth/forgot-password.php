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


// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON data."
    ]);

    exit;
}


// Get email
$email = trim($data["email"] ?? "");


// Validate email
if ($email === "") {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Email is required."
    ]);

    exit;
}


// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid email address."
    ]);

    exit;
}


// Find user
$query = "
    SELECT id, name, email, status
    FROM users
    WHERE email = ?
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


mysqli_stmt_bind_param($stmt, "s", $email);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// User not found
if (!$user) {

    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "No account found with this email."
    ]);

    exit;
}


// Check account status
if ($user["status"] !== "active") {

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Your account is not active."
    ]);

    exit;
}


// Generate secure reset token
$resetToken = bin2hex(random_bytes(32));


// Token expiry - 15 minutes
$resetTokenExpiry = date(
    "Y-m-d H:i:s",
    time() + (15 * 60)
);


// Save token in database
$query = "
    UPDATE users
    SET
        reset_token = ?,
        reset_token_expiry = ?,
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
    "ssi",
    $resetToken,
    $resetTokenExpiry,
    $userId
);


if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to create reset token."
    ]);

    exit;
}


mysqli_stmt_close($stmt);


// Reset password URL
$resetLink =
    "http://localhost/spotify/reset-password.php?token="
    . urlencode($resetToken);


// Success response
http_response_code(200);

echo json_encode([
    "success" => true,
    "message" => "Password reset link generated successfully.",
    "data" => [
        "email" => $user["email"],
        "expires_at" => $resetTokenExpiry,
        "reset_token" => $resetToken,
        "reset_link" => $resetLink
    ]
]);

?>