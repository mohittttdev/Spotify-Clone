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


// Check JSON data
if (!is_array($data)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON data."
    ]);

    exit;
}


// Get values
$name = trim($data["name"] ?? "");
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";


// Validation
if ($name === "" || $email === "" || $password === "") {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Name, email and password are required."
    ]);

    exit;
}


// Validate name
if (strlen($name) < 2 || strlen($name) > 100) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Name must be between 2 and 100 characters."
    ]);

    exit;
}


// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid email address."
    ]);

    exit;
}


// Validate password
if (strlen($password) < 6) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Password must contain at least 6 characters."
    ]);

    exit;
}


// Check duplicate email
$checkQuery = "SELECT id FROM users WHERE email = ? LIMIT 1";

$checkStmt = mysqli_prepare($conn, $checkQuery);

if (!$checkStmt) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database error."
    ]);

    exit;
}


mysqli_stmt_bind_param(
    $checkStmt,
    "s",
    $email
);

mysqli_stmt_execute($checkStmt);

mysqli_stmt_store_result($checkStmt);


if (mysqli_stmt_num_rows($checkStmt) > 0) {

    mysqli_stmt_close($checkStmt);

    http_response_code(409);

    echo json_encode([
        "success" => false,
        "message" => "Email is already registered."
    ]);

    exit;
}


mysqli_stmt_close($checkStmt);


// Hash password
$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);


// Insert user
$insertQuery = "
    INSERT INTO users
    (name, email, password, role, status)
    VALUES (?, ?, ?, 'user', 'active')
";

$insertStmt = mysqli_prepare($conn, $insertQuery);


if (!$insertStmt) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to create user."
    ]);

    exit;
}


mysqli_stmt_bind_param(
    $insertStmt,
    "sss",
    $name,
    $email,
    $hashedPassword
);


if (!mysqli_stmt_execute($insertStmt)) {

    mysqli_stmt_close($insertStmt);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Registration failed."
    ]);

    exit;
}


// Get newly created user ID
$userId = mysqli_insert_id($conn);

mysqli_stmt_close($insertStmt);


// Success response
http_response_code(201);

echo json_encode([
    "success" => true,
    "message" => "Registration successful.",
    "user" => [
        "id" => $userId,
        "name" => $name,
        "email" => $email,
        "role" => "user"
    ]
]);