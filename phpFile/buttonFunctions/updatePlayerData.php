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

    // Debug output
    error_log('Received parameters:');
    error_log('Player ID: ' . $player_id);
    error_log('Column Name: ' . $column_name);
    error_log('New Value: ' . $new_value);
    error_log('Quarter: ' . $quarter);
    error_log('Match ID: ' . $match_id);
    error_log('Player Team: ' . $player_team);

    // Prepare update query
    $sql = "UPDATE basketball_game_tracking SET `$column_name` = '$new_value' WHERE ath_bball_player_id = '$player_id' AND game_quarter = '$quarter' AND match_id = '$match_id' AND game_team = '$player_team'";
    
    // Execute query
    if ($conn->query($sql) === TRUE) {
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
    error_log('Invalid parameters received:');
    error_log('Player ID: ' . $_POST['player_id']);
    error_log('Column Name: ' . $_POST['column_name']);
    error_log('New Value: ' . $_POST['new_value']);
    error_log('Quarter: ' . $_POST['quarter']);
    error_log('Match ID: ' . $_POST['match_id']);
    error_log('Player Team: ' . $_POST['player_team']);
    echo json_encode(array('status' => 'error', 'message' => 'Invalid parameters'));
}
?>
