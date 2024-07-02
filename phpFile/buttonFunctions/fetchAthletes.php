<?php
session_start();
include '../connection/connection.php';

if (isset($_SESSION['coach_email'])) {
    $loggedInUserEmail = $_SESSION['coach_email'];
    $stmt = $conn->prepare("SELECT coach_sport FROM coach_info WHERE coach_email = ?");
    $stmt->bind_param("s", $loggedInUserEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $coachData = $result->fetch_assoc();
        $coachSport = $coachData['coach_sport'];

        // Ensure game_number is set and valid
        $game_number = isset($_GET['game_number']) ? $_GET['game_number'] : '';

        if (empty($game_number)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid game number']);
            exit();
        }

        $position = isset($_GET['position']) ? $_GET['position'] : 'All';
        $name = isset($_GET['name']) ? $_GET['name'] : '';

        if ($position === 'All' && $name === '') {
            $stmt = $conn->prepare("SELECT AthleteID, ath_name, ath_position,
                CASE WHEN EXISTS (SELECT 1 FROM basketball_teams 
                                  WHERE ath_id = athlete_info.AthleteID 
                                  AND ath_team = ?) THEN 1 ELSE 0 END as disabled 
                FROM athlete_info 
                WHERE ath_sport = ?");
            $stmt->bind_param("ss", $game_number, $coachSport);
        } elseif ($position === 'All') {
            $stmt = $conn->prepare("SELECT AthleteID, ath_name, ath_position,
                CASE WHEN EXISTS (SELECT 1 FROM basketball_teams 
                                  WHERE ath_id = athlete_info.AthleteID 
                                  AND ath_team = ?) THEN 1 ELSE 0 END as disabled 
                FROM athlete_info 
                WHERE ath_sport = ? AND ath_name LIKE ?");
            $name = '%' . $name . '%';
            $stmt->bind_param("sss", $game_number, $coachSport, $name);
        } elseif ($name === '') {
            $stmt = $conn->prepare("SELECT AthleteID, ath_name, ath_position, 
                CASE WHEN EXISTS (SELECT 1 FROM basketball_teams 
                                  WHERE ath_id = athlete_info.AthleteID 
                                  AND ath_team = ?) THEN 1 ELSE 0 END as disabled 
                FROM athlete_info 
                WHERE ath_sport = ? AND ath_position = ?");
            $stmt->bind_param("sss", $game_number, $coachSport, $position);
        } else {
            $stmt = $conn->prepare("SELECT AthleteID, ath_name, ath_position,
                CASE WHEN EXISTS (SELECT 1 FROM basketball_teams 
                                  WHERE ath_id = athlete_info.AthleteID 
                                  AND ath_team = ?) THEN 1 ELSE 0 END as disabled 
                FROM athlete_info 
                WHERE ath_sport = ? AND ath_position = ? AND ath_name LIKE ?");
            $name = '%' . $name . '%';
            $stmt->bind_param("ssss", $game_number, $coachSport, $position, $name);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $athletes = array();
        while ($row = $result->fetch_assoc()) {
            $athletes[] = $row;
        }

        echo json_encode(['status' => 'success', 'data' => $athletes]);
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Coach not found.']);
        exit();
    }
}

echo json_encode(['status' => 'error', 'message' => 'No user logged in.']);
exit();
?>
