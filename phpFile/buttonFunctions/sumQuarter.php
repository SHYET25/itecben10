<?php
// Database connection
include '../connection/connection.php';

// Ensure error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if required parameters are set
if (isset($_GET['game_number']) && isset($_GET['team']) && isset($_GET['quarter'])) {
    // Fetch parameters
    $gameNumber = $_GET['game_number'];
    $team = $_GET['team'];
    $quarter = $_GET['quarter'];

    // Debug: Log received parameters
    error_log("Received parameters: game_number=$gameNumber, team=$team, quarter=$quarter");

    // Fetch bball_match_id based on match_name (gameNumber)
    $sql_match_id = "SELECT bball_match_id FROM basketball_matches WHERE match_name = ?";
    
    if ($stmt_match_id = $conn->prepare($sql_match_id)) {
        $stmt_match_id->bind_param('s', $gameNumber); // 's' for string
        $stmt_match_id->execute();
        $stmt_match_id->bind_result($bball_match_id);
        $stmt_match_id->fetch();
        $stmt_match_id->close();

        // Debug: Log fetched match_id
        error_log("Fetched match_id: $bball_match_id");

        // Prepare the SQL query
        $sql = "SELECT 
                    game_pts, 
                    game_2fgm, 
                    game_2pts, 
                    game_3fgm, 
                    game_3pts, 
                    game_ftm, 
                    game_ftpts, 
                    game_2fga, 
                    game_3fga, 
                    game_fta, 
                    game_ass, 
                    game_block, 
                    game_steal, 
                    game_ofreb, 
                    game_defreb, 
                    game_turn, 
                    game_foul
                FROM basketball_game_tracking 
                WHERE game_team = ? AND match_id = ? AND game_quarter = ?";
            
        // Execute the query
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('sii', $team, $gameNumber, $quarter); // 'i' for integer, 's' for string
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Initialize sums
                $sum = array(
                    'game_points' => 0,
                    'game_2fgm' => 0,
                    'game_2pts' => 0,
                    'game_3fgm' => 0,
                    'game_3pts' => 0,
                    'game_ftm' => 0,
                    'game_ftpts' => 0,
                    'game_2fga' => 0,
                    'game_3fga' => 0,
                    'game_fta' => 0,
                    'game_ass' => 0,
                    'game_block' => 0,
                    'game_steal' => 0,
                    'game_ofreb' => 0,
                    'game_defreb' => 0,
                    'game_turn' => 0,
                    'game_foul' => 0
                );

                // Fetch and calculate sums
                while ($row = $result->fetch_assoc()) {
                    $sum['game_points'] += $row['game_pts'];
                    $sum['game_2fgm'] += $row['game_2fgm'];
                    $sum['game_2pts'] += $row['game_2pts'];
                    $sum['game_3fgm'] += $row['game_3fgm'];
                    $sum['game_3pts'] += $row['game_3pts'];
                    $sum['game_ftm'] += $row['game_ftm'];
                    $sum['game_ftpts'] += $row['game_ftpts'];
                    $sum['game_2fga'] += $row['game_2fga'];
                    $sum['game_3fga'] += $row['game_3fga'];
                    $sum['game_fta'] += $row['game_fta'];
                    $sum['game_ass'] += $row['game_ass'];
                    $sum['game_block'] += $row['game_block'];
                    $sum['game_steal'] += $row['game_steal'];
                    $sum['game_ofreb'] += $row['game_ofreb'];
                    $sum['game_defreb'] += $row['game_defreb'];
                    $sum['game_turn'] += $row['game_turn'];
                    $sum['game_foul'] += $row['game_foul'];
                }

                // Close the result set
                $result->close();

                // Output sums as JSON response
                echo json_encode(array('status' => 'success', 'sum' => $sum));
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'No rows found'));
            }

            $stmt->close();
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Query preparation failed'));
        }
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Query preparation for match_id failed'));
    }

    // Close database connection
    $conn->close();
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid parameters'));
}
?>
