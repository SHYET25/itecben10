<?php
require 'phpFile/connection/connection.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo json_encode(array("error" => "Passwords do not match"));
        exit();
    }

    // Validate token and check expiry
    $stmt = $conn->prepare("SELECT email, expires FROM password_reset WHERE token=?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->bind_result($email, $expires);
    $stmt->fetch();
    $stmt->close();

    if (!$email || $expires < time()) {
        echo json_encode(array("error" => "Invalid or expired token"));
        exit();
    }

    // Update the user's password (storing plain text)
    $stmt = $conn->prepare("UPDATE athlete_info SET ath_pass=? WHERE ath_email=?");
    $stmt->bind_param('ss', $password, $email); // Store plain text password
    $stmt->execute();
    $stmt->close();

    // Delete the token
    $stmt = $conn->prepare("DELETE FROM password_reset WHERE email=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->close();

    echo json_encode(array("message" => "Password has been reset successfully"));
}
?>
