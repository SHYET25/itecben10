<?php
include '../connection/connection.php';

// Set the content type to application/json
header('Content-Type: application/json');

// Fetch match results from database
$query = "SELECT bball_match_id, match_name, team_1, team_2, team_1_score, team_2_score, match_win, match_lose FROM basketball_matches";

$result = $conn->query($query);

if ($result) {
    $matches = array();

    while ($row = $result->fetch_assoc()) {
        $matches[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $matches
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch match results: ' . $conn->error
    ]);
}

$conn->close();
?>
