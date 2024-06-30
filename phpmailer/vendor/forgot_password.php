<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'autoload.php'; // Path to PHPMailer autoload.php
require '../../phpFile/connection/connection.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Validate email address
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format";
        exit();
    }

    // Generate a unique token
    $token = bin2hex(random_bytes(50));
    $expires = time() + 1800; // Token expires in 30 minutes

    // Save the token and expiry time in the database
    $stmt = $conn->prepare("INSERT INTO password_reset (email, token, expires) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token=?, expires=?");
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        exit();
    }
    $stmt->bind_param('sssss', $email, $token, $expires, $token, $expires);
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Send the email
    $mail = new PHPMailer(true);

    try {
        //Server settings
   $mail = new PHPMailer;
        $mail->IsSMTP();
        $mail->SMTPAuth = true;                    
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587; 
        $mail->Username = ''; // SMTP username
        $mail->Password = '';
        $mail->FromName = "City of Imus Scholarship Program";
        $mail->Subject = "Reset Password";


        // Content
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = "You requested a password reset. Click <a href='reset_password.php?token=$token'>here</a> to reset your password.";

        $mail->send();
        echo 'Password reset link has been sent to your email';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
