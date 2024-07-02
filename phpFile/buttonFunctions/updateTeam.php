<?php
include '../connection/connection.php';

if (isset($_POST['ath_id']) && isset($_POST['ath_team']) && isset($_POST['old_team'])) {
    $athId = $_POST['ath_id'];
    $newTeamName = $_POST['ath_team'];
    $oldTeamName = $_POST['old_team'];

    // Check if the new team exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM `basketball_teams` WHERE `ath_team` = ?");
    $stmt->bind_param("s", $newTeamName);
    $stmt->execute();
    $stmt->bind_result($teamCount);
    $stmt->fetch();
    $stmt->close();

    if ($teamCount === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Team does not exist (Check Drop Dwon)']);
        exit();
    }

    // Check if the player is already on the new team
    $stmt = $conn->prepare("SELECT COUNT(*) FROM `basketball_teams` WHERE `ath_id` = ? AND `ath_team` = ?");
    $stmt->bind_param("is", $athId, $newTeamName);
    $stmt->execute();
    $stmt->bind_result($playerCount);
    $stmt->fetch();
    $stmt->close();

    if ($playerCount > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Player is already on the same team']);
        exit();
    }

    // Update the team
    $stmt = $conn->prepare("UPDATE `basketball_teams` SET `ath_team` = ? WHERE `ath_id` = ? AND `ath_team` = ?");
    $stmt->bind_param("sis", $newTeamName, $athId, $oldTeamName);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update team']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
}
$conn->close();
?>
