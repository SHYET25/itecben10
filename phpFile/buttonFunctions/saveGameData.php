<?php
// Ensure that the script only runs if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    include '../connection/connection.php';
    
    // Retrieve and sanitize input data
    $ath_bball_player_id = $_POST['ath_bball_player_id'];
    $game_team = $_POST['game_team'];

    // Debug output to verify received values
    error_log(print_r($_POST, true));
    
    // Check connection
    if ($conn->connect_error) {
        error_log('Connection failed: ' . $conn->connect_error);
        die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
    }
    
    // Check if player is already in the game for the specified team
    $checkQuery = "SELECT COUNT(*) as count FROM basketball_teams WHERE ath_id = ? AND ath_team = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("is", $ath_bball_player_id, $game_team);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    
    // Fetch the count from the result set
    $count = $row['count'];
    
    if ($count > 0) {
        error_log('Player is already in the game for the specified team');
        echo json_encode(['status' => 'error', 'message' => 'Player is already in the game for the specified team']);
        $checkStmt->close();
        $conn->close();
        exit;
    }

    // Prepare the insert query
    $insertQuery = "INSERT INTO basketball_teams (ath_id, ath_team) VALUES (?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    $insertStmt->bind_param("is", $ath_bball_player_id, $game_team);
    
    // Execute the statement
    if ($insertStmt->execute()) {
        error_log("Data inserted successfully for team: $game_team");
        echo json_encode(['status' => 'success', 'message' => 'Data inserted successfully for team']);
    } else {
        error_log('Error inserting data: ' . $insertStmt->error);
        echo json_encode(['status' => 'error', 'message' => 'Error inserting data for team ' . $game_team . ': ' . $insertStmt->error]);
        $insertStmt->close();
        $conn->close();
        exit;
    }
    
    // Close the statement and connection
    $insertStmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
