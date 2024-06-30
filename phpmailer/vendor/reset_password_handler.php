<?php
require 'db.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "Passwords do not match";
        exit();
    }

    // Validate token and check expiry
    $stmt = $mysqli->prepare("SELECT email, expires FROM password_reset WHERE token=?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->bind_result($email, $expires);
    $stmt->fetch();
    $stmt->close();

    if (!$email || $expires < time()) {
        echo "Invalid or expired token";
        exit();
    }

    // Update the user's password
    $new_password_hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $mysqli->prepare("UPDATE athlete_info SET ath_password=? WHERE ath_email=?");
    $stmt->bind_param('ss', $new_password_hash, $email);
    $stmt->execute();
    $stmt->close();

    // Delete the token
    $stmt = $mysqli->prepare("DELETE FROM password_reset WHERE email=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->close();

    echo "Password has been reset successfully";
}
?>
