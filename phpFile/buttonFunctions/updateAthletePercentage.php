<?php
include '../connection/connection.php';

header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updateData from POST
    $updateData = $_POST['updateData'];

    // Check if the required fields are set in updateData
    if (isset($updateData['ath_id'])) {
        // Assign variables from updateData
        $ath_bball_player_id = $updateData['ath_id'];
        $shooting = $updateData['shooting'];
        $shooting_2 = $updateData['shooting_2'];
        $shooting_3 = $updateData['shooting_3'];
        $shooting_1 = $updateData['shooting_1'];
        $passing = $updateData['passing'];
        $of_reb = $updateData['of_reb'];
        $def_reb = $updateData['def_reb'];
        $rebounding = $updateData['rebounding'];
        $blocking = $updateData['blocking'];
        $stealing = $updateData['stealing'];
        $defending = $updateData['defending'];
        $total_percentage = $updateData['total_percentage'];

        // SQL statement to update basketball_athlete_info table
        $sql = "UPDATE basketball_athlete_percentage SET
            shooting=?, shooting_2=?, shooting_3=?, shooting_1=?,
            passing=?, of_reb=?, def_reb=?, rebounding=?, defending=?,
            blocking=?, stealing=?, total_percentage=?
            WHERE ath_id=?";

        // Prepare the SQL statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters to the SQL statement
            $stmt->bind_param('iiiiiiiiiiiii', // Adjust the 'iiii...' string to match the number of parameters
                $shooting, $shooting_2,
                $shooting_3, $shooting_1, $passing, $of_reb, $def_reb,
                $rebounding, $blocking, $stealing, $defending, $total_percentage, $ath_bball_player_id);

            // Execute the SQL statement
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                // If execution fails, return error message
                echo json_encode(['status' => 'error', 'message' => $stmt->error]);
            }

            // Close the statement
            $stmt->close();
        } else {
            // If preparation fails, return error message
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
    } else {
        // If required fields are not set, return error message
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data structure']);
    }
} else {
    // If request method is not POST, return error message
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

// Close the database connection
$conn->close();
?>
