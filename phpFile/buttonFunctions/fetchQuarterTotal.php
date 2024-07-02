<?php
// Database connection
include '../connection/connection.php';  // Make sure you have a db_connection.php file that connects to your database

// Check if the required parameters are set
if (isset($_GET['game_number']) && isset($_GET['team']) && isset($_GET['quarter']) ) {
    $gameNumber = $_GET['game_number'];
    $team = $_GET['team'];
    $quarter = $_GET['quarter']; // Get quarter parameter

    // Fetch match_id based on gameNumber
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

    // Prepare the SQL query to fetch quarter data
    $sql = "SELECT *
            FROM basketball_matches_quarters 
            WHERE match_id = ? AND game_team = ? AND game_quarter = ?";
    
    // Execute the query
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('iss', $bball_match_id, $team, $quarter); // 'i' for integer, 's' for string
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
