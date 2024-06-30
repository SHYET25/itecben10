<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'autoload.php'; // Path to PHPMailer autoload.php
require '../../phpFile/connection/connection.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Validate email address
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(array("error" => "Invalid email format"));
        exit();
    }

    // Generate a unique token
    $token = bin2hex(random_bytes(50));
    $expires = time() + 1800; // Token expires in 30 minutes

    // Save the token and expiry time in the database
    $stmt = $conn->prepare("INSERT INTO password_reset (email, token, expires) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token=?, expires=?");
    if (!$stmt) {
        echo json_encode(array("error" => "Prepare failed: (" . $conn->errno . ") " . $conn->error));
        exit();
    }
    $stmt->bind_param('sssss', $email, $token, $expires, $token, $expires);
    if (!$stmt->execute()) {
        echo json_encode(array("error" => "Execute failed: (" . $stmt->errno . ") " . $stmt->error));
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Send the email
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;                    
        $mail->Username = 'kyuuichi12@gmail.com'; // SMTP username
        $mail->Password = 'cayj eaug pwcx xqvn  '; // SMTP password or App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your_email@gmail.com', 'SPORTS SYSTEM');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = <<<EOT
        <p>Hello,</p>
        <p>You recently requested to reset your password for the SPORTS MANAGEMENT SYSTEM.</p>
        <p>Click the link below to reset your password:</p>
        <p><a href='http://localhost/system/phpmailer/vendor/reset_password.php?token=$token' target='_blank'>Reset Password</a></p>
        <p>If you did not request this password reset, please ignore this email.</p>
        <p>This password reset link is valid for 30 minutes from the time of this email.</p>
        <p>Thank you,</p>
        <p>The SPORTS MANAGEMENT SYSTEM Team</p>
        <p>Developer</p>
        EOT;
        
        $mail->send();
        echo json_encode(array("message" => "Password reset link has been sent to your email"));
    } catch (Exception $e) {
        echo json_encode(array("error" => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"));
    }
}
?>
