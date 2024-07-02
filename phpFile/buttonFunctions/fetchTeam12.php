<?php
// Database connection
include '../connection/connection.php';  // Ensure this includes your database connection script

// Check if the required parameter is set
if (isset($_GET['game_number'])) {
    $gameNumber = $_GET['game_number'];

    // Prepare the SQL query to fetch teams based on gameNumber
    $sql = "SELECT team_1, team_2 FROM basketball_matches WHERE match_name = ?";
    
    // Execute the query
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('s', $gameNumber); // 's' for string
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $teams = array(
                'team_1' => $row['team_1'],
                'team_2' => $row['team_2']
            );
            echo json_encode(array('status' => 'success', 'data' => $teams));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'No teams found for game number'));
        }

        $stmt->close();
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Query preparation failed'));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid parameters'));
}

// Close database connection
$conn->close();
?>
