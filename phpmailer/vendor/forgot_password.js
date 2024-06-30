$(document).ready(function() {
    $('#submitBtn').click(function(e) {
        e.preventDefault(); // Prevent the default form submission
        
        // Fetch the email value from the input field
        var email = $('#email').val();

        // Validate email format (basic validation)
        if (!isValidEmail(email)) {
            alert("Invalid email format");
            return;
        }

        // AJAX request to submit the form data
        $.ajax({
            type: 'POST',
            url: 'forgot_password.php',
            data: { email: email },
            dataType: 'json', // Expect JSON response from PHP
            success: function(response) {
                if (response.error) {
                    alert(response.error); // Show error message if exists
                } else {
                    alert(response.message); // Show success message
                    window.location.replace('../../login.html'); // Redirect to login page
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: " + status + ' - ' + error);
                alert("Password reset request failed. Please try again later.");
            }
        });
    });

    // Function to validate email format using a basic regex
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});
