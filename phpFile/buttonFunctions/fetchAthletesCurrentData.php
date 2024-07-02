<?php
// fetchAllAthletesData.php

include '../connection/connection.php';  

// Set the content type to application/json
header('Content-Type: application/json');

// SQL query to select all available athletes' data
$sql = "SELECT `ath_bball_id`, `total_pts`, `total_2fgm`, `total_2pts`, `total_3fgm`, `total_3pts`, `total_ftm`, `total_ftpts`, `total_2fga`, `total_3fga`, `total_fta`, `total_ass`, `total_block`, `total_steal`, `total_ofreb`, `total_defreb`, `total_reb`, `total_turn`, `total_foul`, `total_game` FROM basketball_athlete_info";

if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        $athletesData = [];
        while ($row = $result->fetch_assoc()) {
            $athletesData[] = $row;
        }
        
        // Output the athletes' data as JSON
        echo json_encode([
            'status' => 'success',
            'data' => $athletesData
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No athletes found'
        ]);
    }
    $result->free();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Query failed: ' . $conn->error
    ]);
}

$conn->close();
?>
