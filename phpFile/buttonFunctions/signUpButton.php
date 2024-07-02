<?php
include '../connection/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ath_name = $_POST['name'];
    $ath_user = $_POST['username'];
    $ath_email = $_POST['email'];
    $ath_password = $_POST['password'];
    $confirm_password = $_POST['confirmPassword'];
    $position = $_POST['position'];
    $sports = $_POST['abtnradio'];

    // Prepare and execute the SELECT query for the corresponding table
    $stmt = $conn->prepare("SELECT * FROM athlete_info WHERE ath_email = ? OR ath_user = ?");
    $stmt->bind_param("ss", $ath_email, $ath_user);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the email already exists in the selected sport's table, return an error message
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email or Username already exists']);
        exit();
    } else {
        // If the email doesn't exist, proceed with the insertion
        $sql = "INSERT INTO athlete_info (ath_name, ath_user, ath_email, ath_pass, ath_sport, ath_position) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $ath_name, $ath_user, $ath_email, $ath_password, $sports, $position);

        if ($stmt->execute()) {
            // Get the last inserted ID from athlete_info
            $last_id = $conn->insert_id;

            $table = '';
            switch ($sports) {
                case 'basketball':
                    $table = 'basketball_athlete_info';
                    $general_sql = "INSERT INTO $table (ath_bball_id, total_pts, total_2fgm, total_2pts, total_3fgm, total_3pts, total_ftm, total_ftpts, total_2fga, total_3fga, total_fta, total_ass, total_block, total_steal, total_ofreb, total_defreb, total_reb, total_turn, total_foul, total_game) VALUES (?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)";
                    $general_stmt = $conn->prepare($general_sql);
                    $general_stmt->bind_param("i", $last_id);

                    if ($general_stmt->execute()) {
                        // Insert into basketball_athlete_percentage table
                        $percentage_sql = "INSERT INTO basketball_athlete_percentage (ath_id, shooting, shooting_2, shooting_3, shooting_1, passing, of_reb, def_reb, rebounding, defending, blocking, stealing, total_percentage) VALUES (?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0)";
                        $percentage_stmt = $conn->prepare($percentage_sql);
                        $percentage_stmt->bind_param("i", $last_id);

                        if ($percentage_stmt->execute()) {
                            echo json_encode(['status' => 'success']);
                        } else {
                            echo json_encode(['status' => 'error', 'message' => 'Unable to record data in basketball_athlete_percentage table']);
                            error_log('Insert operation failed in basketball_athlete_percentage: ' . $percentage_stmt->error);
                        }

                        $percentage_stmt->close();
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Unable to record data in ' . $table . ' table']);
                        error_log('Insert operation failed in ' . $table . ': ' . $general_stmt->error);
                    }

                    $general_stmt->close();
                    break;
                case 'volleyball':
                    $table = 'volleyball_athlete_info';
                    $general_sql = "INSERT INTO $table (ath_name, ath_user, ath_email, ath_pass, ath_sport) VALUES (?, ?, ?, ?, ?)";
                    $general_stmt = $conn->prepare($general_sql);
                    $general_stmt->bind_param("sssss", $ath_name, $ath_user, $ath_email, $ath_password, $sports);

                    if ($general_stmt->execute()) {
                        echo json_encode(['status' => 'success']);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Unable to record data in ' . $table . ' table']);
                        error_log('Insert operation failed in ' . $table . ': ' . $general_stmt->error);
                    }

                    $general_stmt->close();
                    break;
                case 'badminton':
                    $table = 'badminton_athlete_info';
                    break;
                case 'soccer':
                    $table = 'soccer_athlete_info';
                    break;
                default:
                    echo json_encode(['status' => 'error', 'message' => 'Invalid sport selected']);
                    exit(); // Stop further execution
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unable to record data']);
            error_log('Insert operation failed in athlete_info: ' . $stmt->error);
        }
    }

    $stmt->close();
    $conn->close();
}
?>
