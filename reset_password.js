$(document).ready(function() {
    $('#resetPasswordBtn').click(function(event) {
        event.preventDefault();

        // Reset validation classes
        $('#passwordInput').removeClass('is-invalid');
        $('#confirmPasswordInput').removeClass('is-invalid');

        // Fetch values
        var token = $('input[name="token"]').val();
        var password = $('#passwordInput').val().trim();
        var confirmPassword = $('#confirmPasswordInput').val().trim();

        // Validate password
        if (password === '') {
            $('#passwordInput').addClass('is-invalid');
            return;
        } else if (!validatePassword(password)) {
            $('#passwordInput').addClass('is-invalid');
            return;
        }

        // Validate confirm password
        if (confirmPassword === '') {
            $('#confirmPasswordInput').addClass('is-invalid');
            return;
        } else if (password !== confirmPassword) {
            $('#confirmPasswordInput').addClass('is-invalid');
            return;
        }

        // Perform AJAX request
        $.ajax({
            url: 'reset_password_handler.php',
            type: 'POST',
            data: {
                token: token,
                password: password,
                confirm_password: confirmPassword
            },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    alert(response.error); // Show error message
                } else {
                    alert(response.message); // Show success message
                    window.location.href = 'login.html'; // Redirect to login page
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Function to validate password format
    function validatePassword(password) {
        const re = /^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,}$/;
        return re.test(password);
    }
});
