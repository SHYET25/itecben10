<?php
include '../connection/connection.php';

// Set the content type to application/json
header('Content-Type: application/json');

if (isset($_GET['match_id'], $_GET['team'])) {
    $matchId = $_GET['match_id'];
    $team = $_GET['team'];

    // Prepare and execute query
    $sql = "SELECT game_quarter, game_points, game_2fgm, game_3fgm, game_ftm, game_2pts, game_3pts, game_ftpts, game_2fga, game_3fga, game_fta, game_ass, game_block, game_steal, game_ofreb, game_defreb, game_turn, game_foul 
            FROM basketball_matches_quarters 
            WHERE match_id = ? AND game_team = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ss', $matchId, $team); // 's' for string
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Query preparation failed: ' . $conn->error
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid parameters'
    ]);
}

$conn->close();
?>
