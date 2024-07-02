<?php
// updateMatchResult.php

include '../connection/connection.php';  

// Set the content type to application/json
header('Content-Type: application/json');

if (isset($_POST['game_number'], $_POST['first_team'], $_POST['first_team_score'], 
    $_POST['second_team'], $_POST['second_team_score'], $_POST['match_win'], $_POST['match_lose'])) {

    $gameNumber = $_POST['game_number'];
    $firstTeam = $_POST['first_team'];
    $firstTeamScore = $_POST['first_team_score'];
    $secondTeam = $_POST['second_team'];
    $secondTeamScore = $_POST['second_team_score'];
    $matchWin = $_POST['match_win'];
    $matchLose = $_POST['match_lose'];

    // Retrieve the match ID based on the game number
    $sql_match_id = "SELECT bball_match_id FROM basketball_matches WHERE match_name = ?";
    
    if ($stmt_match_id = $conn->prepare($sql_match_id)) {
        $stmt_match_id->bind_param('s', $gameNumber); // 's' for string
        $stmt_match_id->execute();
        $stmt_match_id->bind_result($bball_match_id);
        $stmt_match_id->fetch();
        $stmt_match_id->close();

        if (!$bball_match_id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'No match found with game_number ' . $gameNumber
            ]);
            exit();
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Query preparation for match_id failed: ' . $conn->error
        ]);
        exit(); // Exit if query preparation fails
    }

    // Update the match result
    $query = "UPDATE basketball_matches 
              SET team_1 = ?, team_2 = ?, team_1_score = ?, team_2_score = ?, match_win = ?, match_lose = ?
              WHERE bball_match_id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ssisssi", $firstTeam, $secondTeam, $firstTeamScore, $secondTeamScore, $matchWin, $matchLose, $bball_match_id);
        $stmt->execute();
        
        // Check if any rows were affected
        if ($stmt->affected_rows > 0) {
            // Delete from basketball_match_name
            $query_delete = "DELETE FROM basketball_match_name WHERE match_id = ?";
            if ($stmt_delete = $conn->prepare($query_delete)) {
                $stmt_delete->bind_param("i", $bball_match_id);
                $stmt_delete->execute();
                $stmt_delete->close();
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to prepare delete statement: ' . $conn->error
                ]);
                exit();
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Match result updated successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'No rows affected (Update was unnecessary)'
            ]);
        }
        
        $stmt->close();
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
