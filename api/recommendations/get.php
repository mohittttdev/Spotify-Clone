<?php

header("Content-Type: application/json");

require_once "../../config/database.php";
require_once "../../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/*
|--------------------------------------------------------------------------
| Only GET method allowed
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only GET method is allowed."
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
| Limit
|--------------------------------------------------------------------------
*/

$limit = (int) ($_GET["limit"] ?? 10);

if ($limit <= 0 || $limit > 50) {
    $limit = 10;
}

/*
|--------------------------------------------------------------------------
| Check user's liked songs
|--------------------------------------------------------------------------
*/

$likedQuery = "
    SELECT DISTINCT s.artist_id
    FROM liked_songs ls
    INNER JOIN songs s
        ON ls.song_id = s.id
    WHERE ls.user_id = ?
      AND s.artist_id IS NOT NULL
";

$likedStmt = mysqli_prepare($conn, $likedQuery);

if (!$likedStmt) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to prepare liked songs query."
    ]);

    exit;
}

mysqli_stmt_bind_param($likedStmt, "i", $userId);
mysqli_stmt_execute($likedStmt);

$likedResult = mysqli_stmt_get_result($likedStmt);

$artistIds = [];

while ($row = mysqli_fetch_assoc($likedResult)) {
    $artistIds[] = (int) $row["artist_id"];
}

mysqli_stmt_close($likedStmt);

/*
|--------------------------------------------------------------------------
| Recommendations based on liked artists
|--------------------------------------------------------------------------
*/

if (count($artistIds) > 0) {

    $placeholders = implode(",", array_fill(0, count($artistIds), "?"));

    $types = str_repeat("i", count($artistIds)) . "ii";

    $query = "
        SELECT DISTINCT
            s.id,
            s.title,
            s.audio,
            s.cover,
            s.duration,
            s.artist_id,
            a.name AS artist_name,
            s.album_id,
            al.name AS album_name
        FROM songs s
        LEFT JOIN artists a
            ON s.artist_id = a.id
        LEFT JOIN albums al
            ON s.album_id = al.id
        WHERE s.artist_id IN ($placeholders)
          AND s.id NOT IN (
              SELECT song_id
              FROM liked_songs
              WHERE user_id = ?
          )
        ORDER BY s.id DESC
        LIMIT ?
    ";

    $params = array_merge($artistIds, [$userId, $limit]);

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to prepare recommendation query."
        ]);

        exit;
    }

    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $songs = [];

    while ($song = mysqli_fetch_assoc($result)) {
        $songs[] = $song;
    }

    mysqli_stmt_close($stmt);

    echo json_encode([
        "success" => true,
        "user_id" => $userId,
        "type" => "liked_artists",
        "total" => count($songs),
        "recommendations" => $songs
    ]);

} else {

    /*
    |--------------------------------------------------------------------------
    | Fallback: latest songs
    |--------------------------------------------------------------------------
    */

    $query = "
        SELECT
            s.id,
            s.title,
            s.audio,
            s.cover,
            s.duration,
            s.artist_id,
            a.name AS artist_name,
            s.album_id,
            al.name AS album_name
        FROM songs s
        LEFT JOIN artists a
            ON s.artist_id = a.id
        LEFT JOIN albums al
            ON s.album_id = al.id
        ORDER BY s.id DESC
        LIMIT ?
    ";

    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => "Failed to prepare fallback query."
        ]);

        exit;
    }

    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $songs = [];

    while ($song = mysqli_fetch_assoc($result)) {
        $songs[] = $song;
    }

    mysqli_stmt_close($stmt);

    echo json_encode([
        "success" => true,
        "user_id" => $userId,
        "type" => "latest_songs",
        "total" => count($songs),
        "recommendations" => $songs
    ]);
}

mysqli_close($conn);