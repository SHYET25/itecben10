<?php
// fetchAllAthletesData.php

include '../connection/connection.php';  

// Set the content type to application/json
header('Content-Type: application/json');

// SQL query to select all available athletes' data
$sql = "SELECT `ath_id`, `shooting`, `shooting_2`, `shooting_3`, `shooting_1`, `passing`, `of_reb`, `def_reb`, `rebounding`, `defending`, `blocking`, `stealing`, `total_percentage`
        FROM basketball_athlete_percentage";

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
