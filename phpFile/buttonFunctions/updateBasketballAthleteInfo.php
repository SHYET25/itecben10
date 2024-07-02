<?php
// Database connection
include '../connection/connection.php'; // Adjust path as necessary

// Check if required parameters are set
if (isset($_POST['player_id'], $_POST['column_name'], $_POST['new_value'], $_POST['quarter'], $_POST['match_id'], $_POST['player_team'])) {
    // Sanitize input data
    $player_id = mysqli_real_escape_string($conn, $_POST['player_id']);
    $column_name = mysqli_real_escape_string($conn, $_POST['column_name']);
    $new_value = mysqli_real_escape_string($conn, $_POST['new_value']);
    $quarter = mysqli_real_escape_string($conn, $_POST['quarter']);
    $match_id = mysqli_real_escape_string($conn, $_POST['match_id']);
    $player_team = mysqli_real_escape_string($conn, $_POST['player_team']);

    // Prepare update query for basketball_athlete_info
    $sql_update_info = "UPDATE basketball_athlete_info 
                        SET total_pts = (SELECT SUM(game_pts) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_2fgm = (SELECT SUM(game_2fgm) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_2pts = (SELECT SUM(game_2pts) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_3fgm = (SELECT SUM(game_3fgm) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_3pts = (SELECT SUM(game_3pts) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_ftm = (SELECT SUM(game_ftm) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_ftpts = (SELECT SUM(game_ftpts) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_2fga = (SELECT SUM(game_2fga) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_3fga = (SELECT SUM(game_3fga) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_fta = (SELECT SUM(game_fta) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_ass = (SELECT SUM(game_ass) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_block = (SELECT SUM(game_block) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_steal = (SELECT SUM(game_steal) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_ofreb = (SELECT SUM(game_ofreb) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_defreb = (SELECT SUM(game_defreb) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_turn = (SELECT SUM(game_turn) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_foul = (SELECT SUM(game_foul) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id'),
                            total_game = (SELECT COUNT(*) FROM basketball_game_tracking WHERE ath_bball_player_id = '$player_id')
                        WHERE ath_bball_id = '$player_id'";

    // Execute update query for basketball_athlete_info
    if ($conn->query($sql_update_info) === TRUE) {
        // Query executed successfully
        echo json_encode(array('status' => 'success', 'message' => 'Data updated successfully'));
    } else {
        // Query execution failed
        echo json_encode(array('status' => 'error', 'message' => 'Failed to update data: ' . $conn->error));
    }

    // Close database connection
    $conn->close();
} else {
    // Invalid parameters
    echo json_encode(array('status' => 'error', 'message' => 'Invalid parameters'));
}
?>
