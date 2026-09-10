<?php

header("Content-Type: application/json");

require_once "../../config/database.php";
require_once "../../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/* JWT secret — login.php mein jo secret use kiya hai, wahi rakho */
$secret_key = "YOUR_SECRET_KEY";

/* Only POST method */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only POST method is allowed"
    ]);

    exit;
}

/* Get Authorization header */
$headers = getallheaders();

$authHeader = $headers["Authorization"]
    ?? $headers["authorization"]
    ?? "";

if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Authentication token is required"
    ]);

    exit;
}

$jwt = $matches[1];

/* Verify JWT */
try {

    $decoded = JWT::decode(
        $jwt,
        new Key($secret_key, "HS256")
    );

    $user_id = $decoded->data->id;

} catch (Exception $e) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Invalid or expired token"
    ]);

    exit;
}

/* Get JSON body */
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data["song_id"])) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "song_id is required"
    ]);

    exit;
}

/* Get values */
$song_id = (int) $data["song_id"];

$duration_played = isset($data["duration_played"])
    ? (int) $data["duration_played"]
    : 0;

$completed = isset($data["completed"])
    ? (int) $data["completed"]
    : 0;

/* Validate values */
if ($song_id <= 0) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid song_id"
    ]);

    exit;
}

if ($duration_played < 0) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid duration_played"
    ]);

    exit;
}

if ($completed !== 0 && $completed !== 1) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "completed must be 0 or 1"
    ]);

    exit;
}

/* Check song exists */
$checkSong = $conn->prepare(
    "SELECT id FROM songs WHERE id = ?"
);

$checkSong->bind_param("i", $song_id);
$checkSong->execute();

$result = $checkSong->get_result();

if ($result->num_rows === 0) {

    http_response_code(404);

    echo json_encode([
        "success" => false,
        "message" => "Song not found"
    ]);

    $checkSong->close();
    $conn->close();

    exit;
}

$checkSong->close();

/* Insert history */
$query = "
    INSERT INTO listening_history
    (user_id, song_id, duration_played, completed)
    VALUES (?, ?, ?, ?)
";

$stmt = $conn->prepare($query);

$stmt->bind_param(
    "iiii",
    $user_id,
    $song_id,
    $duration_played,
    $completed
);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Song added to history",
        "history_id" => $stmt->insert_id,
        "song_id" => $song_id,
        "duration_played" => $duration_played,
        "completed" => $completed
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to add history"
    ]);

}

$stmt->close();
$conn->close();

?>