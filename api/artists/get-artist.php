<?php

require_once "../../config/database.php";

header("Content-Type: application/json; charset=UTF-8");

// Check artist ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid artist ID"
    ]);
    exit;
}

$artist_id = (int) $_GET['id'];

$sql = "SELECT
            id,
            name,
            bio,
            image,
            country,
            verified
        FROM artists
        WHERE id = ?
        AND status = 'active'
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database error"
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $artist_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);

    echo json_encode([
        "success" => false,
        "message" => "Artist not found"
    ]);
    exit;
}

$artist = mysqli_fetch_assoc($result);

$artist['verified'] = (bool) $artist['verified'];

mysqli_stmt_close($stmt);

echo json_encode([
    "success" => true,
    "data" => $artist
]);