<?php
include '../connection/connection.php';

header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updateData from POST
    $updateData = $_POST['updateData'];

    // Check if the required fields are set in updateData
    if (isset($updateData['ath_bball_id'])) {
        // Assign variables from updateData
        $ath_bball_player_id = $updateData['ath_bball_id'];
        $total_pts = $updateData['total_pts'];
        $total_2fgm = $updateData['total_2fgm'];
        $total_2pts = $updateData['total_2pts'];
        $total_3fgm = $updateData['total_3fgm'];
        $total_3pts = $updateData['total_3pts'];
        $total_ftm = $updateData['total_ftm'];
        $total_ftpts = $updateData['total_ftpts'];
        $total_2fga = $updateData['total_2fga'];
        $total_3fga = $updateData['total_3fga'];
        $total_fta = $updateData['total_fta'];
        $total_ass = $updateData['total_ass'];
        $total_block = $updateData['total_block'];
        $total_steal = $updateData['total_steal'];
        $total_ofreb = $updateData['total_ofreb'];
        $total_defreb = $updateData['total_defreb'];
        $total_reb = $updateData['total_reb'];
        $total_turn = $updateData['total_turn'];
        $total_foul = $updateData['total_foul'];
        $total_game = $updateData['total_game'];

        // SQL statement to update basketball_athlete_info table
        $sql = "UPDATE basketball_athlete_info SET
            total_pts=?, total_2fgm=?, total_2pts=?, total_3fgm=?, total_3pts=?,
            total_ftm=?, total_ftpts=?, total_2fga=?, total_3fga=?, total_fta=?,
            total_ass=?, total_block=?, total_steal=?, total_ofreb=?, total_defreb=?,
            total_reb=?, total_turn=?, total_foul=?, total_game=?
            WHERE ath_bball_id=?";

        // Prepare the SQL statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters to the SQL statement
            $stmt->bind_param('iiiiiiiiiiiiiiiiiiii', // Adjust the 'iiii...' string to match the number of parameters
                $total_pts, $total_2fgm, $total_2pts, $total_3fgm, $total_3pts,
                $total_ftm, $total_ftpts, $total_2fga, $total_3fga, $total_fta,
                $total_ass, $total_block, $total_steal, $total_ofreb, $total_defreb,
                $total_reb, $total_turn, $total_foul, $total_game, $ath_bball_player_id);

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
