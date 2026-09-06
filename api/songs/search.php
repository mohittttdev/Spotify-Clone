<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/database.php";


// Only GET request allowed
if ($_SERVER["REQUEST_METHOD"] !== "GET") {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only GET method is allowed."
    ]);

    exit;
}


// Get search query
$search = isset($_GET["q"]) ? trim($_GET["q"]) : "";


// Validate search query
if ($search === "") {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Search query is required."
    ]);

    exit;
}


// Pagination
$page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;
$limit = isset($_GET["limit"]) ? (int) $_GET["limit"] : 20;


// Validate page
if ($page < 1) {
    $page = 1;
}


// Validate limit
if ($limit < 1) {
    $limit = 20;
}

if ($limit > 100) {
    $limit = 100;
}


$offset = ($page - 1) * $limit;


// Search pattern
$searchPattern = "%" . $search . "%";


// Count matching songs
$countQuery = "
    SELECT COUNT(*) AS total

    FROM songs

    LEFT JOIN artists
        ON songs.artist_id = artists.id

    LEFT JOIN albums
        ON songs.album_id = albums.id

    WHERE songs.status = 'active'

    AND (
        songs.title LIKE ?
        OR artists.name LIKE ?
        OR albums.name LIKE ?
        OR songs.genre LIKE ?
    )
";


$countStmt = mysqli_prepare($conn, $countQuery);


if (!$countStmt) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database error."
    ]);

    exit;
}


mysqli_stmt_bind_param(
    $countStmt,
    "ssss",
    $searchPattern,
    $searchPattern,
    $searchPattern,
    $searchPattern
);


if (!mysqli_stmt_execute($countStmt)) {

    mysqli_stmt_close($countStmt);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to search songs."
    ]);

    exit;
}


$countResult = mysqli_stmt_get_result($countStmt);

$countData = mysqli_fetch_assoc($countResult);

$totalSongs = (int) $countData["total"];

mysqli_stmt_close($countStmt);


// Get matching songs
$query = "
    SELECT
        songs.id,
        songs.artist_id,
        songs.album_id,
        songs.title,
        songs.audio,
        songs.cover,
        songs.duration,
        songs.audio_source,
        songs.cover_source,
        songs.genre,
        songs.release_date,
        songs.play_count,
        songs.status,
        songs.created_at,
        songs.updated_at,

        artists.name AS artist_name,
        artists.image AS artist_image,

        albums.name AS album_name,
        albums.cover AS album_cover

    FROM songs

    LEFT JOIN artists
        ON songs.artist_id = artists.id

    LEFT JOIN albums
        ON songs.album_id = albums.id

    WHERE songs.status = 'active'

    AND (
        songs.title LIKE ?
        OR artists.name LIKE ?
        OR albums.name LIKE ?
        OR songs.genre LIKE ?
    )

    ORDER BY songs.play_count DESC, songs.id DESC

    LIMIT ? OFFSET ?
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
    "ssssii",
    $searchPattern,
    $searchPattern,
    $searchPattern,
    $searchPattern,
    $limit,
    $offset
);


// Execute
if (!mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch search results."
    ]);

    exit;
}


$result = mysqli_stmt_get_result($stmt);


$songs = [];


// Build songs response
while ($row = mysqli_fetch_assoc($result)) {

    $songs[] = [

        "id" => (int) $row["id"],

        "title" => $row["title"],

        "artist" => [

            "id" => $row["artist_id"] !== null
                ? (int) $row["artist_id"]
                : null,

            "name" => $row["artist_name"],

            "image" => $row["artist_image"]
        ],

        "album" => [

            "id" => $row["album_id"] !== null
                ? (int) $row["album_id"]
                : null,

            "name" => $row["album_name"],

            "cover" => $row["album_cover"]
        ],

        "audio" => $row["audio"],

        "cover" => $row["cover"],

        "duration" => (int) $row["duration"],

        "audio_source" => $row["audio_source"],

        "cover_source" => $row["cover_source"],

        "genre" => $row["genre"],

        "release_date" => $row["release_date"],

        "play_count" => (int) $row["play_count"],

        "status" => $row["status"],

        "created_at" => $row["created_at"],

        "updated_at" => $row["updated_at"]
    ];
}


mysqli_stmt_close($stmt);


// Pagination
$totalPages = $totalSongs > 0
    ? (int) ceil($totalSongs / $limit)
    : 0;


// Success response
http_response_code(200);

echo json_encode([

    "success" => true,

    "message" => "Search results fetched successfully.",

    "data" => [

        "query" => $search,

        "songs" => $songs,

        "pagination" => [

            "current_page" => $page,

            "per_page" => $limit,

            "total_songs" => $totalSongs,

            "total_pages" => $totalPages
        ]
    ]
]);

?>