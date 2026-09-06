<?php

header("Content-Type: application/json");

require_once "../../config/database.php";

/* Get all albums */
$query = "
    SELECT
        albums.id,
        albums.name,
        albums.cover,
        artists.id AS artist_id,
        artists.name AS artist_name
    FROM albums
    LEFT JOIN artists ON albums.artist_id = artists.id
    ORDER BY albums.id DESC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch albums"
    ]);
    exit;
}

$albums = [];

while ($album = mysqli_fetch_assoc($result)) {

    $albums[] = [
        "id" => $album["id"],
        "name" => $album["name"],
        "cover" => $album["cover"],
        "artist" => [
            "id" => $album["artist_id"],
            "name" => $album["artist_name"]
        ]
    ];

}

echo json_encode([
    "success" => true,
    "total" => count($albums),
    "albums" => $albums
]);

mysqli_close($conn);