<?php
include '../connection/connection.php'; // Include your database connection file

// Perform query to fetch match_name values
$stmt = $conn->prepare("SELECT `match_name` FROM `basketball_match_name`");
$stmt->execute();
$result = $stmt->get_result();

$matchNames = array();
while ($row = $result->fetch_assoc()) {
    $matchNames[] = $row['match_name'];
}

$stmt->close();
$conn->close();

// Prepare JSON response
$response = array(
    'status' => 'success',
    'matchNames' => $matchNames
);

header('Content-Type: application/json');
echo json_encode($response);
?>
