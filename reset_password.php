<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #040E20;
            font-family: "Play";
        }
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .card-body {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Reset Password</h2>
                        <form id="resetPasswordForm">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" id="passwordInput" placeholder="Enter new password" required>
                                <div class="invalid-feedback">
                                    Please enter a valid password.
                                    <ul>
                                        <li>Password must contain at least 8 characters.</li>
                                        <li>Password must contain at least one uppercase letter.</li>
                                        <li>Password must contain at least one lowercase letter.</li>
                                        <li>Password must contain at least one digit (0-9).</li>
                                        <li>Password must contain at least one special character (!@#$%^&*).</li>
                                    </ul>
                                </div>

                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" id="confirmPasswordInput" placeholder="Confirm new password" required>
                                <div class="invalid-feedback">
                                    Passwords do not match.
                                </div>
                            </div>
                            <button type="button" id="resetPasswordBtn" class="btn btn-primary w-100">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="reset_password.js"></script>
</body>
</html>
