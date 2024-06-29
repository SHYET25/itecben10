function validateAthleteForm() {
    let isValid = true;

    const name = $("#nameInput");
    const username = $("#usernameInput");
    const email = $("#emailInput");
    const password = $("#passwordInput");
    const confirmPassword = $("#confirmPasswordInput");

    // Check if fields are empty
    if (name.val().trim() === '') {
        name.addClass('is-invalid');
        isValid = false;
    } else {
        name.removeClass('is-invalid');
    }

    if (username.val().trim() === '') {
        username.addClass('is-invalid');
        isValid = false;
    } else {
        username.removeClass('is-invalid');
    }

    if (email.val().trim() === '') {
        email.addClass('is-invalid');
        isValid = false;
    } else if (!validateEmail(email.val())) {
        email.addClass('is-invalid');
        isValid = false;
    } else {
        email.removeClass('is-invalid');
    }

    if (password.val().trim() === '') {
        password.addClass('is-invalid');
        isValid = false;
    } else if (!validatePassword(password.val())) {
        password.addClass('is-invalid');
        isValid = false;
    } else {
        password.removeClass('is-invalid');
    }

    if (confirmPassword.val().trim() === '') {
        confirmPassword.addClass('is-invalid');
        isValid = false;
    } else if (confirmPassword.val().trim() !== password.val().trim()) {
        confirmPassword.addClass('is-invalid');
        isValid = false;
    } else {
        confirmPassword.removeClass('is-invalid');
    }

    return isValid;
}

function validateCoachForm() {
    let isValid = true;

    const coach_name = $("#coach_name");
    const coach_user = $("#coach_user");
    const coach_email = $("#coach_email");
    const coach_password = $("#coach_password");
    const coach_confirmPassword = $("#coach_confirmPassword");

    // Check if fields are empty
    if (coach_name.val().trim() === '') {
        coach_name.addClass('is-invalid');
        isValid = false;
    } else {
        coach_name.removeClass('is-invalid');
    }

    if (coach_user.val().trim() === '') {
        coach_user.addClass('is-invalid');
        isValid = false;
    } else {
        coach_user.removeClass('is-invalid');
    }

    if (coach_email.val().trim() === '') {
        coach_email.addClass('is-invalid');
        isValid = false;
    } else if (!validateEmail(coach_email.val())) {
        coach_email.addClass('is-invalid');
        isValid = false;
    } else {
        coach_email.removeClass('is-invalid');
    }

    if (coach_password.val().trim() === '') {
        coach_password.addClass('is-invalid');
        isValid = false;
    } else if (coach_password.val().trim().length < 8 || coach_password.val().trim().length > 20) {
        coach_password.addClass('is-invalid');
        isValid = false;
    } else {
        coach_password.removeClass('is-invalid');
    }

    if (coach_confirmPassword.val().trim() === '') {
        coach_confirmPassword.addClass('is-invalid');
        isValid = false;
    } else if (coach_confirmPassword.val().trim() !== coach_password.val().trim()) {
        coach_confirmPassword.addClass('is-invalid');
        isValid = false;
    } else {
        coach_confirmPassword.removeClass('is-invalid');
    }

    return isValid;
}

function validateEmail(email, coach_email) {
    const re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
    return re.test(String(email, coach_email).toLowerCase());
    
}

function validatePassword(password, coach_password) {
    const re = /^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,}$/;
    return re.test(password);
}

$(document).ready(function() {
    $('#nextButtonAthlete').click(function(event) {
        event.preventDefault(); // Prevent form submission
        console.log('Next button clicked'); // Log message to the console


            $('#athleteModal2').modal('hide');
            $('#athleteModal').modal('show');
        
    });

    $('#signUpButton').click(function(event) {
        event.preventDefault(); // Prevent default form submission
        console.log('Submit button clicked'); // Log message to the console

        
            $.ajax({
                type: "POST",
                url: "phpFile/buttonFunctions/signUpButton.php",
                data: $('#signUpAthleteForm').serialize(), // Serialize form data
                dataType: 'json', // Parse response as JSON
                success: function(response) {
                    if (validateAthleteForm()) {
                        if (response.status === 'success') {
                            
                                $('#insertionConfirmed .modal-body').text('Data inserted successfully');
                                $('#insertionConfirmed').modal('show');
                                setTimeout(function() {
                                    $('#insertionConfirmed').modal('hide');
                                    window.location.href = "login.html";
                                }, 2000);
                            
                        } else {
                            $('#errorConfirmed .modal-body').text('Email Already Exists!');
                            $('#errorConfirmed').modal('show');
                            setTimeout(function() {
                                $('#errorConfirmed').modal('hide');
                                window.location.href = "sign-up.html";
                            }, 2000);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        
    });

    $('#nextButtonCoach').click(function(event) {
        event.preventDefault(); // Prevent form submission
        console.log('Next asd clicked'); // Log message to the console

        if (validateCoachForm()) {
            $('#coachModal').modal('hide');
            $('#coachModal2').modal('show');
        }
    });

    $('#goBack').click(function(event) {
        event.preventDefault(); // Prevent form submission
        console.log('Next asd clicked'); // Log message to the console

        
            $('#coachModal2').modal('hide');
            $('#coachModal').modal('show');
        
    });

    $('#goBackBtn').click(function(event) {
        event.preventDefault(); // Prevent form submission
        console.log('Next asd clicked'); // Log message to the console

        
            $('#athleteModal').modal('hide');
            $('#athleteModal2').modal('show');
        
    });


    $('#signUpCoachUpButton').click(function(event) {
        event.preventDefault(); // Prevent default form submission
        console.log('Submit button clicked'); // Log message to the console

        if (validateCoachForm()) {
            $.ajax({
                type: "POST",
                url: "phpFile/buttonFunctions/signUpCoachButton.php",
                data: $('#signUpCoachForm').serialize(), // Serialize form data
                dataType: 'json', // Parse response as JSON
                success: function(response) {
                    if (response.status === 'success') {
                        $('#insertionConfirmed .modal-body').text('Data inserted successfully');
                        $('#insertionConfirmed').modal('show');
                        setTimeout(function() {
                            $('#insertionConfirmed').modal('hide');
                            window.location.href = "login.html";
                        }, 2000);
                    } else {
                        $('#errorConfirmed .modal-body').text('Email Already Exists!');
                        $('#errorConfirmed').modal('show');
                        setTimeout(function() {
                            $('#errorConfirmed').modal('hide');
                            window.location.href = "sign-up.html";
                        }, 2000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }
    });
});
