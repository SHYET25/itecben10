<?php
include '../connection/connection.php';

$criteria = isset($_GET['criteria']) ? $_GET['criteria'] : 'total_percentage';
$validCriteria = ['shooting', 'passing', 'rebounding', 'defending', 'total_percentage'];

if (!in_array($criteria, $validCriteria)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid criteria']);
    exit();
}

$sql = "SELECT ai.ath_name, ai.ath_user, ai.ath_img, bap.shooting, bap.passing, bap.rebounding, bap.defending, bap.total_percentage
        FROM `basketball_athlete_percentage` bap
        JOIN `athlete_info` ai ON bap.ath_id = ai.AthleteID
        ORDER BY bap.$criteria DESC";

$result = $conn->query($sql);

$athletes = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $athletes[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $athletes]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No athletes found']);
}

$conn->close();
?>
