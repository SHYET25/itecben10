<?php
// fetchFinalizedData.php

include '../connection/connection.php';  

// Set the content type to application/json
header('Content-Type: application/json');

if (isset($_GET['game_number']) && isset($_GET['team'])) {
    $gameNumber = $_GET['game_number'];
    $team = $_GET['team'];

    $sql_match_id = "SELECT bball_match_id FROM basketball_matches WHERE match_name = ?";
    
    if ($stmt_match_id = $conn->prepare($sql_match_id)) {
        $stmt_match_id->bind_param('s', $gameNumber); // 's' for string
        $stmt_match_id->execute();
        $stmt_match_id->bind_result($bball_match_id);
        $stmt_match_id->fetch();
        $stmt_match_id->close();
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Query preparation for match_id failed'));
        exit(); // Exit if query preparation fails
    }

    $query = "SELECT game_team, game_pts FROM basketball_match_result WHERE match_id = ? AND game_team = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $bball_match_id, $team);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No data found'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid parameters'
    ]);
}

$stmt->close();
$conn->close();
?>
