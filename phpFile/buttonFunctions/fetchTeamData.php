<?php
// Database connection
include '../connection/connection.php';  // Make sure you have a db_connection.php file that connects to your database

// Check if the required parameters are set
if (isset($_GET['game_number']) && isset($_GET['team']) && isset($_GET['quarter'])) {
    $gameNumber = $_GET['game_number'];
    $team = $_GET['team'];
    $quarter = $_GET['quarter']; // Get quarter parameter

    // Prepare the SQL query
    $sql = "SELECT ath_bball_player_id, game_pts, game_2fgm, game_2pts, game_3fgm, game_3pts, game_ftm, game_ftpts, game_2fga, game_3fga, game_fta, game_ass, game_block, game_steal, game_ofreb, game_defreb, game_turn, game_foul, game_quarter, game_number, game_team 
            FROM basketball_game_tracking 
            WHERE game_number = ? AND game_team = ? AND game_quarter = ?";
    // Execute the query
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('iss', $gameNumber, $team, $quarter); // 'i' for integer, 's' for string
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $data = array();
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode(array('status' => 'success', 'data' => $data));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'No data found'));
        }

        $stmt->close();
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Query preparation failed'));
    }

    $conn->close();
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid parameters'));
}
?>
