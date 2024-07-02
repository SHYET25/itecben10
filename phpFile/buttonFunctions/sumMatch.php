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

    // Prepare the SQL query
    $sql = "SELECT 
                match_id, 
                game_points, 
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
                game_foul, 
                game_quarter, 
                game_team
            FROM basketball_matches_quarters 
            WHERE game_team = ? AND match_id = ?";
        
    // Execute the query
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('si', $team, $gameNumber); // 'i' for integer, 's' for string
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Initialize sums
            $sums = array(
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
                $sums['game_points'] += $row['game_points'];
                $sums['game_2fgm'] += $row['game_2fgm'];
                $sums['game_2pts'] += $row['game_2pts'];
                $sums['game_3fgm'] += $row['game_3fgm'];
                $sums['game_3pts'] += $row['game_3pts'];
                $sums['game_ftm'] += $row['game_ftm'];
                $sums['game_ftpts'] += $row['game_ftpts'];
                $sums['game_2fga'] += $row['game_2fga'];
                $sums['game_3fga'] += $row['game_3fga'];
                $sums['game_fta'] += $row['game_fta'];
                $sums['game_ass'] += $row['game_ass'];
                $sums['game_block'] += $row['game_block'];
                $sums['game_steal'] += $row['game_steal'];
                $sums['game_ofreb'] += $row['game_ofreb'];
                $sums['game_defreb'] += $row['game_defreb'];
                $sums['game_turn'] += $row['game_turn'];
                $sums['game_foul'] += $row['game_foul'];
            }

            // Close the result set
            $result->close();

            // Output sums as JSON response
            echo json_encode(array('status' => 'success', 'sums' => $sums));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'No rows found'));
        }

        $stmt->close();
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Query preparation failed'));
    }

    // Close database connection
    $conn->close();
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid parameters'));
}
?>
