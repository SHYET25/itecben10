<?php
include '../connection/connection.php';

if (isset($_POST['match_name']) && isset($_POST['team1']) && isset($_POST['team2']) && isset($_POST['team1_score']) && isset($_POST['team2_score']) && isset($_POST['match_win']) && isset($_POST['match_lose']) && isset($_POST['athletes'])) {
    $matchName = $_POST['match_name'];
    $team1 = $_POST['team1'];
    $team2 = $_POST['team2'];
    $team1_score = $_POST['team1_score'];
    $team2_score = $_POST['team2_score'];
    $match_win = $_POST['match_win'];
    $match_lose = $_POST['match_lose'];
    $athletes = $_POST['athletes'];


    $stmt_check = $conn->prepare("SELECT match_id FROM basketball_match_name WHERE match_name = ?");
    $stmt_check->bind_param("s", $matchName);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Match name already exists (Finalize the match First..)']);
        exit();
    }
    $stmt_check->close();

    // Begin transaction
    $conn->begin_transaction();
    // Begin transaction

    try {
        // Insert into basketball_matches
        $stmt = $conn->prepare("INSERT INTO basketball_matches(match_name, team_1, team_2, team_1_score, team_2_score, match_win, match_lose) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiss", $matchName, $team1, $team2, $team1_score, $team2_score, $match_win, $match_lose);

        if (!$stmt->execute()) {
            throw new Exception('Failed to create match');
        }

        // Get the match ID
        $matchId = $stmt->insert_id;
        $stmt->close();

        // Insert into basketball_match_name
        $stmt_match_name = $conn->prepare("INSERT INTO basketball_match_name(match_id, match_name) VALUES (?, ?)");
        $stmt_match_name->bind_param("is", $matchId, $matchName);
        if (!$stmt_match_name->execute()) {
            throw new Exception('Failed to insert match name');
        }
        $stmt_match_name->close();

        // Prepare insert statement for basketball_game_tracking
        $stmt = $conn->prepare("INSERT INTO basketball_game_tracking(match_id, ath_bball_player_id, game_pts, game_2fgm, game_2pts, game_3fgm, game_3pts, game_ftm, game_ftpts, game_2fga, game_3fga, game_fta, game_ass, game_block, game_steal, game_ofreb, game_defreb, game_turn, game_foul, game_quarter, game_number, game_team) VALUES (?, ?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, ?, 1, ?)");
        
        // Prepare insert statement for basketball_matches_quarters
        $stmt_quarters = $conn->prepare("INSERT INTO basketball_matches_quarters(match_id, game_quarter, game_team, game_points, game_2fgm, game_3fgm, game_ftm, game_2pts, game_3pts, game_ftpts, game_2fga, game_3fga, game_fta, game_ass, game_block, game_steal, game_ofreb, game_defreb, game_turn, game_foul) VALUES (?, ?, ?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0)");

        // Insert each athlete for each quarter
        foreach ($athletes as $athlete) {
            $athleteId = $athlete['AthleteID'];
            $athleteTeam = $athlete['ath_team'];
            for ($quarter = 1; $quarter <= 4; $quarter++) {
                // Bind parameters for basketball_game_tracking
                $stmt->bind_param("iiss", $matchId, $athleteId, $quarter, $athleteTeam);
                if (!$stmt->execute()) {
                    throw new Exception('Failed to insert game tracking data');
                }
            }
        }

        for ($quarter = 1; $quarter <= 4; $quarter++) {
            // Bind parameters for basketball_matches_quarters
            $stmt_quarters->bind_param("iis", $matchId, $quarter, $team1);
            if (!$stmt_quarters->execute()) {
                throw new Exception('Failed to insert match quarters data');
            }
            $stmt_quarters->bind_param("iis", $matchId, $quarter, $team2);
            if (!$stmt_quarters->execute()) {
                throw new Exception('Failed to insert match quarters data');
            }
        }

        $stmt_result = $conn->prepare("INSERT INTO basketball_match_result(match_id, game_team, game_pts, game_2fgm, game_2pts, game_3fgm, game_3pts, game_ftm, game_ftpts, game_2fga, game_3fga, game_fta, game_ass, game_block, game_steal, game_ofreb, game_defreb, game_turn, game_foul) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $zero = 0; // temporary variable for zero value
        $stmt_result->bind_param("isiiiiiiiiiiiiiiiii", $matchId, $team1, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero);

        if (!$stmt_result->execute()) {
            throw new Exception('Failed to insert match result');
        }

        $stmt_result->bind_param("isiiiiiiiiiiiiiiiii", $matchId, $team2, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero, $zero);

        if (!$stmt_result->execute()) {
            throw new Exception('Failed to insert match result');
        }

        $stmt_result->close();
        $stmt->close();
        $stmt_quarters->close();
        $conn->commit();
        echo json_encode(['status' => 'success']);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
}
$conn->close();
?>
