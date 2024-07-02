<?php
// fetchFinalizedData.php

include '../connection/connection.php';  

// Set the content type to application/json
header('Content-Type: application/json');

    // Get all athlete IDs
    $sql_get_athletes = "SELECT ath_id FROM basketball_athlete_percentage";
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
    $query = "SELECT * FROM basketball_athlete_info WHERE ath_bball_id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $athleteID); // Bind parameters dynamically later
        
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
 

$conn->close();
?>
