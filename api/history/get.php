<?php

header("Content-Type: application/json");

require_once "../../config/database.php";
require_once "../../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "YOUR_SECRET_KEY";

/* Only GET method */
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only GET method is allowed"
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

/* Get history */
$query = "
    SELECT
        listening_history.id,
        listening_history.user_id,
        listening_history.song_id,
        listening_history.played_at,
        listening_history.duration_played,
        listening_history.completed,

        songs.title,
        songs.audio,
        songs.cover,

        artists.id AS artist_id,
        artists.name AS artist_name,

        albums.id AS album_id,
        albums.name AS album_name

    FROM listening_history

    INNER JOIN songs
        ON listening_history.song_id = songs.id

    LEFT JOIN artists
        ON songs.artist_id = artists.id

    LEFT JOIN albums
        ON songs.album_id = albums.id

    WHERE listening_history.user_id = ?

    ORDER BY listening_history.played_at DESC
";

$stmt = $conn->prepare($query);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$history = [];

while ($row = $result->fetch_assoc()) {

    $history[] = $row;

}

/* Response */
echo json_encode([
    "success" => true,
    "message" => "Listening history fetched successfully",
    "count" => count($history),
    "data" => $history
]);

$stmt->close();
$conn->close();

?>