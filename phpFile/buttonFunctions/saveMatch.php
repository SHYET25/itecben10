<?php
// Ensure that the script only runs if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    include '../connection/connection.php';
    
    // Retrieve and sanitize input data
    $ath_bball_player_id = $_POST['ath_bball_player_id'];
    $game_pts = intval($_POST['game_pts']);
    $game_2fgm = intval($_POST['game_2fgm']);
    $game_2pts = intval($_POST['game_2pts']);
    $game_3fgm = intval($_POST['game_3fgm']);
    $game_3pts = intval($_POST['game_3pts']);
    $game_ftm = intval($_POST['game_ftm']);
    $game_ftpts = intval($_POST['game_ftpts']);
    $game_2fga = intval($_POST['game_2fga']);
    $game_3fga = intval($_POST['game_3fga']);
    $game_fta = intval($_POST['game_fta']);
    $game_ass = intval($_POST['game_ass']);
    $game_block = intval($_POST['game_block']);
    $game_steal = intval($_POST['game_steal']);
    $game_ofreb = intval($_POST['game_ofreb']);
    $game_defreb = intval($_POST['game_defreb']);
    $game_turn = intval($_POST['game_turn']);
    $game_foul = intval($_POST['game_foul']);
    $game_team = $_POST['game_team'];
    
    // Debug output to verify received values
    error_log(print_r($_POST, true));
    
    // Check connection
    if ($conn->connect_error) {
        die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
    }
    
    // Check if player is already in the game for the specified team
    $checkQuery = "SELECT COUNT(*) as count FROM basketball_game_tracking WHERE ath_bball_player_id = ? AND game_team = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("is", $ath_bball_player_id, $game_team);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    $count = $row['count'];
    
    if ($count > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Player is already in the game for the specified team']);
        $checkStmt->close();
        $conn->close();
        exit;
    }
    
    // Prepare the insert query
    $insertQuery = "INSERT INTO basketball_game_tracking (ath_bball_player_id, game_pts, game_2fgm, game_2pts, game_3fgm, game_3pts, game_ftm, game_ftpts, game_2fga, game_3fga, game_fta, game_ass, game_block, game_steal, game_ofreb, game_defreb, game_turn, game_foul, game_quarter, game_team) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);
    
    // Insert data for each quarter
    for ($quarter = 1; $quarter <= 4; $quarter++) {
        $insertStmt->bind_param("iiiiiiiiiiiiiiiiiiis", $ath_bball_player_id, $game_pts, $game_2fgm, $game_2pts, $game_3fgm, $game_3pts, $game_ftm, $game_ftpts, $game_2fga, $game_3fga, $game_fta, $game_ass, $game_block, $game_steal, $game_ofreb, $game_defreb, $game_turn, $game_foul, $quarter, $game_team);
        
        // Execute the statement
        if ($insertStmt->execute()) {
            error_log("Data inserted successfully for quarter: $quarter");
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error inserting data for quarter ' . $quarter . ': ' . $insertStmt->error]);
            $insertStmt->close();
            $conn->close();
            exit;
        }
    }
    
    // Close the statement and connection
    $insertStmt->close();
    $conn->close();
    
    echo json_encode(['status' => 'success', 'message' => 'Data inserted successfully for all quarters']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
