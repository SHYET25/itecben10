<?php
session_start();
include '../connection/connection.php'; // Adjust this path based on your file structure

// Check if a user is logged in (assuming either athlete or coach can access this)
if (isset($_SESSION['ath_email']) || isset($_SESSION['coach_email'])) {
    // Fetch POST data from AJAX request
    $gameId = $_POST['gameId'];
    $team1 = $_POST['team1'];
    $team2 = $_POST['team2'];

    // Prepare and execute SELECT query to fetch game statistics for both teams
    $query1 = "SELECT id, ath_bball_player_id, game_pts, game_2fgm, game_2pts, game_3fgm, game_3pts, game_ftm, game_ftpts, game_2fga, game_3fga, game_fta, game_ass, game_block, game_steal, game_ofreb, game_defreb, game_turn, game_foul, game_quarter, game_number, game_team 
               FROM basketball_game_tracking 
               WHERE game = ? AND game_team = ?";

    $query2 = "SELECT id, ath_bball_player_id, game_pts, game_2fgm, game_2pts, game_3fgm, game_3pts, game_ftm, game_ftpts, game_2fga, game_3fga, game_fta, game_ass, game_block, game_steal, game_ofreb, game_defreb, game_turn, game_foul, game_quarter, game_number, game_team 
               FROM basketball_game_tracking 
               WHERE game = ? AND game_team = ?";

    // Use prepared statements to prevent SQL injection
    $stmt1 = $conn->prepare($query1);
    $stmt1->bind_param("is", $gameId, $team1);
    $stmt1->execute();
    $result1 = $stmt1->get_result();

    $stmt2 = $conn->prepare($query2);
    $stmt2->bind_param("is", $gameId, $team2);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    if (!$result1 || !$result2) {
        // Handle query errors if any
        $response = array(
            'status' => 'error',
            'message' => 'Failed to fetch game statistics.'
        );
        echo json_encode($response);
        exit;
    }

    $team1Stats = array();
    $team2Stats = array();

    // Fetch data for Team 1
    while ($row = $result1->fetch_assoc()) {
        $team1Stats[] = $row;
    }

    // Fetch data for Team 2
    while ($row = $result2->fetch_assoc()) {
        $team2Stats[] = $row;
    }

    // Prepare response data
    $response = array(
        'status' => 'success',
        'data' => array(
            'team1Stats' => $team1Stats,
            'team2Stats' => $team2Stats
        )
    );

    echo json_encode($response);

    // Close statements and database connection
    $stmt1->close();
    $stmt2->close();
    mysqli_close($conn);

} else {
    // If no user is logged in
    echo json_encode(['status' => 'error', 'message' => 'No user logged in.']);
    exit();
}
?>
