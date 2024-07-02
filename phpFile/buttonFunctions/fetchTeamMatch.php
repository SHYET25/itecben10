<?php
// Database connection
include '../connection/connection.php';  // Ensure this includes your database connection script

// Check if the required parameters are set
if (isset($_GET['game_number']) && isset($_GET['team'])) {
    $gameNumber = $_GET['game_number'];
    $team = $_GET['team'];

    // Fetch bball_match_id based on match_name (gameNumber)
    $sql_match_id = "SELECT bball_match_id FROM basketball_matches WHERE match_name = ?";
    
    if ($stmt_match_id = $conn->prepare($sql_match_id)) {
        $stmt_match_id->bind_param('s', $gameNumber); // 's' for string
        $stmt_match_id->execute();
        $stmt_match_id->bind_result($bball_match_id);
        $stmt_match_id->fetch();
        $stmt_match_id->close();
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Query preparation for match_id failed'));
        exit(); // Exit if query preparation fails
    }

    // Array to hold all quarters' data
    $allQuartersData = array();

    // Loop through quarters 1 to 4
    for ($quarter = 1; $quarter <= 4; $quarter++) {
        // Prepare the SQL query
        $sql = "SELECT match_id, ath_bball_player_id, game_pts, game_2fgm, game_2pts, game_3fgm, game_3pts, game_ftm, game_ftpts, game_2fga, game_3fga, game_fta, game_ass, game_block, game_steal, game_ofreb, game_defreb, game_turn, game_foul, game_quarter, game_number, game_team
                FROM basketball_game_tracking 
                WHERE game_team = ? AND game_quarter = ? AND match_id = ?";
        
        // Execute the query
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ssi', $team, $quarter, $bball_match_id); // 'i' for integer, 's' for string
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $quarterData = array();
                while ($row = $result->fetch_assoc()) {
                    $quarterData[] = $row;
                }
                $allQuartersData[] = $quarterData;
            } else {
                $allQuartersData[] = array(); // No data found for this quarter
            }

            $stmt->close();
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Query preparation failed'));
            exit(); // Exit if query preparation fails
        }
    }

    // Close database connection
    $conn->close();

    // Return all quarters' data as JSON
    echo json_encode(array('status' => 'success', 'data' => $allQuartersData));
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid parameters'));
}
?>
