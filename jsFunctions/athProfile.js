$(document).ready(function() {
    // Make an AJAX request to fetch the logged-in user data
    $.ajax({
        type: "GET",
        url: "phpFile/buttonFunctions/fetchLoggedIn.php",
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                console.log('Logged-in user type:', response.userType);
                console.log('Logged-in user data:', response.loggedInUserData);

                $('#name').text(response.loggedInUserData.ath_name);
                $('#username').text('@' + response.loggedInUserData.ath_user);
                $('#name1').text(response.loggedInUserData.ath_name);
                $('#username1').text('@' + response.loggedInUserData.ath_user);
            } else {

                alert(response.message);
                window.location.href = 'login.html';
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });

});