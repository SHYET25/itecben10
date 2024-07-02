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
        $position = isset($_GET['position']) ? $_GET['position'] : 'All';
        $name = isset($_GET['name']) ? $_GET['name'] : '';

        if ($position === 'All' && $name === '') {
            $stmt = $conn->prepare("SELECT *,
                CASE WHEN EXISTS (SELECT 1 FROM basketball_teams 
                                  WHERE ath_id = athlete_info.AthleteID 
                                  ) THEN 1 ELSE 0 END as disabled 
                FROM athlete_info 
                WHERE ath_sport = ?");
            $stmt->bind_param("s", $coachSport);
        } elseif ($position === 'All') {
            $stmt = $conn->prepare("SELECT *,
                CASE WHEN EXISTS (SELECT 1 FROM basketball_teams 
                                  WHERE ath_id = athlete_info.AthleteID 
                                  ) THEN 1 ELSE 0 END as disabled 
                FROM athlete_info 
                WHERE ath_sport = ? AND ath_name LIKE ?");
            $name = '%' . $name . '%';
            $stmt->bind_param("ss",$coachSport, $name);
        } elseif ($name === '') {
            $stmt = $conn->prepare("SELECT *,
                CASE WHEN EXISTS (SELECT 1 FROM basketball_teams 
                                  WHERE ath_id = athlete_info.AthleteID 
                                  ) THEN 1 ELSE 0 END as disabled 
                FROM athlete_info 
                WHERE ath_sport = ? AND ath_position = ?");
            $stmt->bind_param("ss",  $coachSport, $position);
        } else {
            $stmt = $conn->prepare("SELECT *,
                CASE WHEN EXISTS (SELECT 1 FROM basketball_teams 
                                  WHERE ath_id = athlete_info.AthleteID 
                                 ) THEN 1 ELSE 0 END as disabled 
                FROM athlete_info 
                WHERE ath_sport = ? AND ath_position = ? AND ath_name LIKE ?");
            $name = '%' . $name . '%';
            $stmt->bind_param("sss", $coachSport, $position, $name);
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
