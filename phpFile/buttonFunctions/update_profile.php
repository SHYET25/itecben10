<?php
session_start();
include '../connection/connection.php';

header('Content-Type: application/json');

// Enable error reporting and display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['ath_email'])) {
    $userEmail = $_SESSION['ath_email'];

    if ($conn->connect_error) {
        echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
        exit();
    }

    // Check if any data is being updated
    $updateSuccessful = false;
    $updateErrors = [];

    // Update username
    if (isset($_POST['username'])) {
        $newUsername = $_POST['username'];
        $updateStmt = $conn->prepare("UPDATE athlete_info SET ath_user = ? WHERE ath_email = ?");
        $updateStmt->bind_param("ss", $newUsername, $userEmail);
        if ($updateStmt->execute()) {
            $updateSuccessful = true;
        } else {
            $updateErrors['username'] = $conn->error;
        }
    }

    // Update name
    if (isset($_POST['name'])) {
        $newName = $_POST['name'];
        $updateStmt = $conn->prepare("UPDATE athlete_info SET ath_name = ? WHERE ath_email = ?");
        $updateStmt->bind_param("ss", $newName, $userEmail);
        if ($updateStmt->execute()) {
            $updateSuccessful = true;
        } else {
            $updateErrors['name'] = $conn->error;
        }
    }

    // Update email (not recommended for security reasons, consider implications carefully)
    if (isset($_POST['email'])) {
        $newEmail = $_POST['email'];
        $updateStmt = $conn->prepare("UPDATE athlete_info SET ath_email = ? WHERE ath_email = ?");
        $updateStmt->bind_param("ss", $newEmail, $userEmail);
        if ($updateStmt->execute()) {
            $updateSuccessful = true;
            $_SESSION['ath_email'] = $newEmail; // Update session email if changed
        } else {
            $updateErrors['email'] = $conn->error;
        }
    }

    // Update profile image
    if (isset($_FILES['profileImage']) && !empty($_FILES['profileImage']['name'])) {
        $targetDir = "../../images/prof_pics/";
        $imageFileType = strtolower(pathinfo($_FILES["profileImage"]["name"], PATHINFO_EXTENSION));
        $uniqueImageName = uniqid() . '.' . $imageFileType;
        $targetFile = $targetDir . $uniqueImageName;

        // Check if the file is a valid image
        $check = getimagesize($_FILES["profileImage"]["tmp_name"]);
        if ($check !== false) {
            // Check file size (500KB limit)
            if ($_FILES["profileImage"]["size"] > 500000) {
                echo json_encode(['error' => 'Sorry, your file is too large.']);
                exit();
            }

            // Move uploaded file to target directory
            if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $targetFile)) {
                // Update database with new profile image path
                $updateStmt = $conn->prepare("UPDATE athlete_info SET ath_img = ? WHERE ath_email = ?");
                $updateStmt->bind_param("ss", $uniqueImageName, $userEmail);
                if ($updateStmt->execute()) {
                    $updateSuccessful = true;
                    echo json_encode(['success' => true, 'image' => $uniqueImageName]);
                    exit();
                } else {
                    $updateErrors['profileImage'] = $conn->error;
                }
            } else {
                echo json_encode(['error' => 'Failed to move uploaded file']);
                exit();
            }
        } else {
            echo json_encode(['error' => 'File is not an image']);
            exit();
        }
    }

    // Check if any update was successful
    if ($updateSuccessful) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'No valid update data provided', 'details' => $updateErrors]);
    }

    $conn->close();
} else {
    echo json_encode(['error' => 'User not logged in']);
}
?>
