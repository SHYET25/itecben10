<?php
// Database connection
include '../connection/connection.php';  // Ensure this includes your database connection script

if (isset($_POST['sums']) && isset($_POST['team']) && isset($_POST['match_id'])) {
    $sums = $_POST['sums'];
    $team = $_POST['team'];
    $match_id = $_POST['match_id'];

    // Construct the update query
    $sql = "UPDATE basketball_match_result 
            SET 
                game_pts = {$sums['game_points']},
                game_2fgm = {$sums['game_2fgm']},
                game_2pts = {$sums['game_2pts']},
                game_3fgm = {$sums['game_3fgm']},
                game_3pts = {$sums['game_3pts']},
                game_ftm = {$sums['game_ftm']},
                game_ftpts = {$sums['game_ftpts']},
                game_2fga = {$sums['game_2fga']},
                game_3fga = {$sums['game_3fga']},
                game_fta = {$sums['game_fta']},
                game_ass = {$sums['game_ass']},
                game_block = {$sums['game_block']},
                game_steal = {$sums['game_steal']},
                game_ofreb = {$sums['game_ofreb']},
                game_defreb = {$sums['game_defreb']},
                game_turn = {$sums['game_turn']},
                game_foul = {$sums['game_foul']}
            WHERE match_id = '{$match_id}' AND game_team = '{$team}'";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        echo json_encode(array('status' => 'success'));
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Update failed: ' . $conn->error));
    }

    $conn->close();
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid parameters'));
}
?>
