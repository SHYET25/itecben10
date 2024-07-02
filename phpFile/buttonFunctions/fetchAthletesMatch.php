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

        // Ensure team is set and valid
        $team = isset($_GET['team']) ? $_GET['team'] : '';

        if (empty($team)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid team']);
            exit();
        }

        // Adjusted SQL query to join basketball_teams with athlete_info and select ath_team
        $stmt = $conn->prepare(
            "SELECT a.AthleteID, a.ath_name, a.ath_position, a.ath_img, b.ath_team
            FROM athlete_info a
            JOIN basketball_teams b ON a.AthleteID = b.ath_id AND b.ath_team = ?
            WHERE a.ath_sport = ?"
        );
        $stmt->bind_param("ss", $team, $coachSport);

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
