<?php
include '../connection/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $coach_name = $_POST['coach_name'];
    $coach_user = $_POST['coach_user'];
    $coach_email = $_POST['coach_email'];
    $coach_password = $_POST['coach_password'];
    $coach_confirmPassword = $_POST['coach_confirmPassword'];
    $sports = $_POST['cbtnradio'];

    // Define the table name based on the selected sport
    $table = '';
    switch ($sports) {
        case 'basketball':
            $table = 'basketball_coach_info';
            break;
        case 'volleyball':
            $table = 'volleyball_coach_info';
            break;
        case 'badminton':
            $table = 'badminton_coach_info';
            break;
        case 'soccer':
            $table = 'soccer_coach_info';
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid sport selected']);
            exit(); // Stop further execution
    }

    // Prepare and execute the SELECT query for the corresponding table
    $stmt = $conn->prepare("SELECT * FROM coach_info WHERE coach_email = ? OR coach_user = ?");
    $stmt->bind_param("s", $coach_email, $coach_user);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the email already exists in the selected sport's table, return an error message
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error']);
        exit();
    } else {

    // Insert into the sport-specific coach table
        $sql = "INSERT INTO $table (coach_name, coach_user, coach_email, coach_pass, coach_sport) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $coach_name, $coach_user, $coach_email, $coach_password, $sports);

        if ($stmt->execute()) {
            // Insert into the general coach_info table
            $general_sql = "INSERT INTO coach_info (coach_name, coach_user, coach_email, coach_pass, coach_sport) VALUES (?, ?, ?, ?, ?)";
            $general_stmt = $conn->prepare($general_sql);
            $general_stmt->bind_param("sssss", $coach_name, $coach_user, $coach_email, $coach_password, $sports);

            if ($general_stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Unable to record data in coach_info table']);
                error_log('Insert operation failed in coach_info: ' . $general_stmt->error);
            }
            
            $general_stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unable to record data']);
            error_log('Insert operation failed: ' . $stmt->error);
        }
    }
    $stmt->close();
    $conn->close();
}
?>
