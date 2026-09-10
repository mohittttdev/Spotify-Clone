<?php

require_once "../../config/database.php";

header("Content-Type: application/json; charset=UTF-8");

$sql = "SELECT 
            id,
            name,
            bio,
            image,
            country,
            verified
        FROM artists
        WHERE status = 'active'
        ORDER BY name ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch artists"
    ]);
    exit;
}

$artists = [];

while ($row = mysqli_fetch_assoc($result)) {
    $row['verified'] = (bool) $row['verified'];
    $artists[] = $row;
}

echo json_encode([
    "success" => true,
    "count" => count($artists),
    "data" => $artists
]);