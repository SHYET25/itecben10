<?php
// Database connection
include '../connection/connection.php'; // Adjust path as necessary

// Check if required parameters are set
if (isset($_POST['player_id'], $_POST['column_name'], $_POST['new_value'])) {
    // Sanitize input data
    $player_id = mysqli_real_escape_string($conn, $_POST['player_id']);
    $column_name = mysqli_real_escape_string($conn, $_POST['column_name']);
    $new_value = mysqli_real_escape_string($conn, $_POST['new_value']);

    // Prepare update query
    $sql = "UPDATE basketball_game_tracking SET `$column_name` = '$new_value' WHERE ath_bball_player_id = '$player_id'";
    
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
    echo json_encode(array('status' => 'error', 'message' => 'Invalid parameters'));
}
?>
