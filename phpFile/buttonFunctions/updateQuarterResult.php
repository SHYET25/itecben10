<?php
// Database connection
include '../connection/connection.php';  // Ensure this includes your database connection script

if (isset($_POST['sum']) && isset($_POST['team']) && isset($_POST['match_id']) && isset($_POST['quarter'])) {
    $sum = $_POST['sum'];
    $team = $_POST['team'];
    $match_id = $_POST['match_id'];
    $quarter = $_POST['quarter'];

    // Construct the update query
    $sql = "UPDATE basketball_matches_quarters 
            SET 
                game_points = {$sum['game_points']},
                game_2fgm = {$sum['game_2fgm']},
                game_2pts = {$sum['game_2pts']},
                game_3fgm = {$sum['game_3fgm']},
                game_3pts = {$sum['game_3pts']},
                game_ftm = {$sum['game_ftm']},
                game_ftpts = {$sum['game_ftpts']},
                game_2fga = {$sum['game_2fga']},
                game_3fga = {$sum['game_3fga']},
                game_fta = {$sum['game_fta']},
                game_ass = {$sum['game_ass']},
                game_block = {$sum['game_block']},
                game_steal = {$sum['game_steal']},
                game_ofreb = {$sum['game_ofreb']},
                game_defreb = {$sum['game_defreb']},
                game_turn = {$sum['game_turn']},
                game_foul = {$sum['game_foul']}
            WHERE match_id = '{$match_id}' AND game_team = '{$team}' AND game_quarter = '{$quarter}'";

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
