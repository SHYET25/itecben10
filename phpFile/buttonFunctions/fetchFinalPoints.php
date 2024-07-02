<?php
// Database connection
include '../connection/connection.php'; // Adjust the path as necessary

// Check if match_id and team parameters are set
if (isset($_GET['match_id']) && isset($_GET['team'])) {
    $matchId = $_GET['match_id'];
    $team = $_GET['team'];

    // Query to fetch bball_match_id from basketball_matches
    $sql_match_id = "SELECT bball_match_id FROM basketball_matches WHERE match_name = ?";
    
    $stmt_match_id = $conn->prepare($sql_match_id);
    if ($stmt_match_id) {
        $stmt_match_id->bind_param('i', $matchId); // Assuming match_id is an integer
        if ($stmt_match_id->execute()) {
            $stmt_match_id->bind_result($bball_match_id);
            $stmt_match_id->fetch();
            $stmt_match_id->close();

            // Query to fetch final points for the specified match and team
            $sql = "SELECT game_pts FROM basketball_match_result WHERE match_id = ? AND game_team = ?";
    
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("si", $bball_match_id, $team);
                
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $response = array(
                            'status' => 'success',
                            'data' => $row['game_pts']
                        );
                    } else {
                        $response = array(
                            'status' => 'error',
                            'message' => 'No points found for the specified team and match'
                        );
                    }
                } else {
                    $response = array(
                        'status' => 'error',
                        'message' => 'Execution error: ' . $stmt->error
                    );
                }
                $stmt->close();
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => 'Prepare statement failed: ' . $conn->error
                );
            }
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Execute statement failed: ' . $stmt_match_id->error
            );
        }
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Query preparation failed'
        );
    }
} else {
    $response = array(
        'status' => 'error',
        'message' => 'Missing match_id or team parameter'
    );
}

// Close database connection
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
