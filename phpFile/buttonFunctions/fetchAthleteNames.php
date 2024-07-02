<?php
// fetchFinalizedData.php

include '../connection/connection.php';  

// Set the content type to application/json
header('Content-Type: application/json');

if (isset($_GET['game_number'])) {
    $gameNumber = $_GET['game_number'];

    // Convert the gameNumber into bball_match_id
    $sql_match_id = "SELECT bball_match_id FROM basketball_matches WHERE match_name = ?";
    if ($stmt_match_id = $conn->prepare($sql_match_id)) {
        $stmt_match_id->bind_param('s', $gameNumber); // 's' for string
        $stmt_match_id->execute();
        $stmt_match_id->bind_result($bball_match_id);
        $stmt_match_id->fetch();
        $stmt_match_id->close();

        if (!$bball_match_id) {
            echo json_encode(array('status' => 'error', 'message' => 'No match ID found for the given game number'));
            exit(); // Exit if no match ID is found
        }
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Query preparation for match_id failed'));
        exit(); // Exit if query preparation fails
    }

    // Get all athlete IDs
    $sql_get_athletes = "SELECT ath_bball_id FROM basketball_athlete_info";
    if ($stmt_athletes = $conn->prepare($sql_get_athletes)) {
        $stmt_athletes->execute();
        $stmt_athletes->bind_result($athleteID);
        
        $athleteIDs = [];
        while ($stmt_athletes->fetch()) {
            $athleteIDs[] = $athleteID;
        }
        $stmt_athletes->close();
        
        if (empty($athleteIDs)) {
            echo json_encode(array('status' => 'error', 'message' => 'No athletes found'));
            exit(); // Exit if no athletes are found
        }
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Query preparation for athletes failed'));
        exit(); // Exit if query preparation fails
    }

    // Fetch game tracking data for each athlete
    $query = "SELECT * FROM basketball_game_tracking WHERE match_id = ? AND ath_bball_player_id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("is", $bball_match_id, $athleteID); // Bind parameters dynamically later
        
        $finalData = [];
        foreach ($athleteIDs as $athleteID) {
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $finalData[] = $row;
                }
            }
        }
        $stmt->close();
        
        if (!empty($finalData)) {
            echo json_encode([
                'status' => 'success',
                'data' => $finalData
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No data found for the given parameters'
            ]);
        }
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Query preparation for game tracking data failed'));
        exit(); // Exit if query preparation fails
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid parameters'
    ]);
}

$conn->close();
?>
