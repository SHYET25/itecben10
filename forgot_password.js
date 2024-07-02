$(document).ready(function() {
    $('#submitBtn').click(function(e) {
        e.preventDefault(); // Prevent the default form submission
        
        // Fetch the email value from the input field
        var email = $('#forgotEmail').val();

        // Validate email format (basic validation)
        if (!isValidEmail(email)) {
            alert("Invalid email format");
            return;
        }

        // Disable submit button and show loading spinner
        $('#submitBtn').prop('disabled', true);
        $('#submitBtn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');

        // AJAX request to submit the form data
        $.ajax({
            type: 'POST',
            url: 'forgot_password.php',
            data: { email: email },
            dataType: 'json', // Expect JSON response from PHP
            success: function(response) {
                if (response.error) {
                    alert(response.error); // Show error message if exists
                    resetButton(); // Reset button to initial state
                } else {
                    // Show success checkmark and redirect after delay
                    $('#submitBtn').html('<i class="bi bi-check-circle-fill"></i> Success');
                    setTimeout(function() {
                        $('#forgotPasswordModal').modal('hide'); // Hide modal after success
                        window.location.replace('login.html'); // Redirect to login page
                    }, 2000); // 2 seconds delay
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + ' - ' + error);
                alert("Password reset request failed. Please try again later.");
                resetButton(); // Reset button to initial state
            }
        });
    });

    // Function to reset button to initial state
    function resetButton() {
        $('#submitBtn').prop('disabled', false);
        $('#submitBtn').html('Submit');
    }

    // Function to validate email format using a basic regex
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});
