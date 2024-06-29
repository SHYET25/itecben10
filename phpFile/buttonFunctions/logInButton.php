<?php
include '../connection/connection.php'; // Adjust path as necessary

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate inputs (basic validation)
    if (empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
        exit();
    }

    // Function to check credentials and redirect based on user type
    function authenticateUser($conn, $email, $password) {
        $stmt = $conn->prepare("SELECT * FROM athlete_info WHERE ath_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if ($password === $user['ath_pass']) { // Plain text comparison
                session_start();
                $_SESSION['ath_email'] = $user['ath_email'];
                echo json_encode(['status' => 'success', 'redirectUrl' => 'athdashb.html']);
                exit();
            } else {
                return ['status' => 'error', 'message' => 'Incorrect password.'];
            }
        }

        $stmt = $conn->prepare("SELECT * FROM coach_info WHERE coach_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if ($password === $admin['coach_pass']) { // Plain text comparison
                session_start();
                $_SESSION['coach_email'] = $admin['coach_email'];
                echo json_encode(['status' => 'success', 'redirectUrl' => 'coachdashb.html']);
                exit();
            } else {
                return ['status' => 'error', 'message' => 'Incorrect password.'];
            }
        }

        return ['status' => 'error', 'message' => 'Email not found.'];
    }

    // Attempt to authenticate the user
    $authResult = authenticateUser($conn, $email, $password);
    echo json_encode($authResult);
    exit();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
    exit();
}
?>
