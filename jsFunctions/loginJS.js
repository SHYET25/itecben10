$('#loginButton').click(function(event) {
    event.preventDefault();  // Prevent the form from submitting the default way

    
    if (validateLoginForm()) {
        $.ajax({
            url: 'phpFile/buttonFunctions/logInButton.php',  // Update with the actual path to your PHP script
            type: 'POST',
            data: $('#loginForm').serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    console.log("Login successful");
                    $('#insertionConfirmed .modal-body').text('Login Successful!!');
                    $('#insertionConfirmed').modal('show');
                    setTimeout(function() {
                        $('#insertionConfirmed').modal('hide');
                        window.location.href = response.redirectUrl; // Redirect to appropriate page
                    }, 2000);
                } else {
                    $('#insertionConfirmed .modal-body').text(response.message);
                    $('#insertionConfirmed').modal('show');
                    setTimeout(function() {
                        $('#insertionConfirmed').modal('hide');
                        
                    }, 1000);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
                alert('An error occurred while processing your request.');
            }
        });
    }
});


function validateLoginForm() {
    let isValid = true;

    const email = $("#email");
    const password = $("#password");

    if (email.val().trim() === '') {
        email.addClass('is-invalid');
        isValid = false;
    } else {
        email.removeClass('is-invalid');
    }

    if (password.val().trim() === '') {
        password.addClass('is-invalid');
        isValid = false;
    } else {
        password.removeClass('is-invalid');
    }

    return isValid;
}