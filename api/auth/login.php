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
// Get JSON data
// --------------------------------------------------

$data = json_decode(
    file_get_contents("php://input"),
    true
);


if (!is_array($data)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON data."
    ]);

    exit;
}


// --------------------------------------------------
// Get email and password
// --------------------------------------------------

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";


// --------------------------------------------------
// Validation
// --------------------------------------------------

if ($email === "" || $password === "") {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Email and password are required."
    ]);

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid email address."
    ]);

    exit;
}


// --------------------------------------------------
// Find user
// --------------------------------------------------

$query = "
    SELECT
        id,
        name,
        email,
        password,
        role,
        status
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


mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


$user = mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


// --------------------------------------------------
// User not found
// --------------------------------------------------

if (!$user) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password."
    ]);

    exit;
}


// --------------------------------------------------
// Check account status
// --------------------------------------------------

if ($user["status"] !== "active") {

    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Your account is not active."
    ]);

    exit;
}


// --------------------------------------------------
// Verify password
// --------------------------------------------------

if (!password_verify($password, $user["password"])) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password."
    ]);

    exit;
}


// --------------------------------------------------
// JWT configuration
// --------------------------------------------------

$jwtConfig = require __DIR__ . "/../../config/jwt.php";


$issuedAt = time();

$expire = $issuedAt + $jwtConfig["expire"];


$payload = [

    "iss" => $jwtConfig["issuer"],

    "aud" => $jwtConfig["audience"],

    "iat" => $issuedAt,

    "exp" => $expire,

    "data" => [

        "id" => (int) $user["id"],

        "name" => $user["name"],

        "email" => $user["email"],

        "role" => $user["role"]

    ]

];


// --------------------------------------------------
// Generate JWT
// --------------------------------------------------

$token = JWT::encode(
    $payload,
    $jwtConfig["secret"],
    "HS256"
);


// --------------------------------------------------
// Save JWT token
// --------------------------------------------------

$tokenQuery = "
    INSERT INTO jwt_tokens
    (
        user_id,
        token,
        expires_at
    )
    VALUES (?, ?, FROM_UNIXTIME(?))
";

$tokenStmt = mysqli_prepare(
    $conn,
    $tokenQuery
);


if (!$tokenStmt) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to create authentication token."
    ]);

    exit;
}


mysqli_stmt_bind_param(
    $tokenStmt,
    "isi",
    $user["id"],
    $token,
    $expire
);


if (!mysqli_stmt_execute($tokenStmt)) {

    mysqli_stmt_close($tokenStmt);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to save authentication token."
    ]);

    exit;
}


mysqli_stmt_close($tokenStmt);


// --------------------------------------------------
// Success response
// --------------------------------------------------

http_response_code(200);

echo json_encode([

    "success" => true,

    "message" => "Login successful.",

    "token" => $token,

    "expires_in" => $jwtConfig["expire"],

    "user" => [

        "id" => (int) $user["id"],

        "name" => $user["name"],

        "email" => $user["email"],

        "role" => $user["role"]

    ]

]);