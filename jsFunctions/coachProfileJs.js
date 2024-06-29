$(document).ready(function() {
    var selectedAthletes = [];

    $.ajax({
        type: "GET",
        url: "phpFile/buttonFunctions/fetchLoggedIn.php",
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                $('#name').text(response.loggedInUserData.coach_name);
                $('#username').text('@' + response.loggedInUserData.coach_user);
                $('#name1').text(response.loggedInUserData.coach_name);
                $('#username1').text('@' + response.loggedInUserData.coach_user);
            } else {
                alert(response.message);
                window.location.href = 'login.html';
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });

    function populateAthleteTable(data) {
        var athleteTableBody = $('#athleteTableBody');
        athleteTableBody.empty();
        
        data.forEach(function(athlete) {
            var row = '<tr data-id="' + athlete.AthleteID + '">' +
                '<td><input type="checkbox" class="athlete-checkbox" data-id="' + athlete.AthleteID + '" ' + (athlete.disabled ? 'disabled' : '') + '></td>' +
                '<td>' + athlete.AthleteID + '</td>' +
                '<td>' + athlete.ath_name + '</td>' +
                '<td>' + athlete.ath_position + '</td>' +
                '</tr>';

            var $row = $(row);
            if (athlete.disabled) {
                $row.addClass('disabled-row');
                
            }
            athleteTableBody.append($row);
        });
    
        $('.athlete-checkbox').change(function() {
            var athleteId = $(this).attr('data-id');
            var athleteName = $(this).closest('tr').find('td:nth-child(3)').text();
            var athletePosition = $(this).closest('tr').find('td:nth-child(4)').text();
    
            if ($(this).prop('checked')) {
                selectedAthletes.push({
                    AthleteID: athleteId,
                    ath_name: athleteName,
                    ath_position: athletePosition
                });
            } else {
                selectedAthletes = selectedAthletes.filter(function(athlete) {
                    return athlete.AthleteID !== athleteId;
                });
            }
    
            updateSelectedAthletesDisplay();
        });
    }
    
    

    $('#closeModal').click(function() {
        $('#gameModal').modal('hide');
    });

    function updateSelectedAthletesDisplay() {
        var selectedAthletesList = $('#selectedAthletesList');
        selectedAthletesList.empty();

        selectedAthletes.forEach(function(athlete) {
            var athleteItem = '<div class="selected-athlete-item mr-2">' +
                athlete.ath_name + ' (' + athlete.ath_position + ')' +
                '<button type="button" class="btn btn-link btn-remove-athlete" data-id="' + athlete.AthleteID + '">&times;</button>' +
                '</div>';
            selectedAthletesList.append(athleteItem);
        });

        $('.btn-remove-athlete').click(function() {
            var athleteId = $(this).attr('data-id');
            selectedAthletes = selectedAthletes.filter(function(athlete) {
                return athlete.AthleteID !== athleteId;
            });

            updateSelectedAthletesDisplay();
            $('#athleteTableBody').find('input[data-id="' + athleteId + '"]').prop('checked', false);
        });
    }

    $('#addGameButton').click(function() {
        fetchAthletesData();
        $('#gameModal').modal('show');
    });

    $('#manageMatch').click(function() {
        $('#gameStatsModal').modal('show');
    });

    $('#positionFilter').change(function() {
        var selectedPosition = $(this).val();
        var searchName = $('#searchBar').val();
        fetchAthletesData(selectedPosition, searchName);
    });

    $('#searchBar').on('input', function() {
        var searchName = $(this).val();
        var selectedPosition = $('#positionFilter').val();
        fetchAthletesData(selectedPosition, searchName);
    });

    function fetchAthletesData(position = 'All', name = '') {
        var gameNumber = $('#gameDropdown').val(); // Get selected game number
        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetchAthletes.php",
            data: { position: position, name: name, game_number: gameNumber }, // Include game number
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    populateAthleteTable(response.data);
                } else {
                    console.error('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }

    function saveGameData() {
        var gameNumber = $('#gameDropdown').val();
        var team = $('#teamDropdown').val();
    
        console.log('Selected game number:', gameNumber);
        console.log('Selected team:', team);
    
        selectedAthletes.forEach(function(athlete) {
            var data = {
                ath_bball_player_id: athlete.AthleteID,
                game_pts: 0,
                game_2fgm: 0,
                game_2pts: 0,
                game_3fgm: 0,
                game_3pts: 0,
                game_ftm: 0,
                game_ftpts: 0,
                game_2fga: 0,
                game_3fga: 0,
                game_fta: 0,
                game_ass: 0,
                game_block: 0,
                game_steal: 0,
                game_ofreb: 0,
                game_defreb: 0,
                game_turn: 0,
                game_foul: 0,
                game_quarter: 1,
                game_number: gameNumber,
                game_team: team
            };
    
            $.ajax({
                type: "POST",
                url: "phpFile/buttonFunctions/saveGameData.php",
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Game data saved for athlete: ' + athlete.ath_name);
    
                        // Clear selected athletes and update display
                        selectedAthletes = [];
                        updateSelectedAthletesDisplay();
    
                        // Reset athlete table by refetching data
                        var selectedPosition = $('#positionFilter').val();
                        var searchName = $('#searchBar').val();
                        fetchAthletesData(selectedPosition, searchName);
                    } else {
                        console.error('Error:', response.message);
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        });
    }
    
    // Bind the save game function to the button click event
    $('#saveGameButton').click(function() {
        saveGameData();
    });

    $('#gameDropdown').change(function() {
        var selectedPosition = $('#positionFilter').val();
        var searchName = $('#searchBar').val();
        fetchAthletesData(selectedPosition, searchName);
    });
    

    $('#viewGameDataButton').click(function() {
        var gameNumber = $('#viewGameDropdown').val();
        var firstTeam = $('#firstTeamDropdown').val();
        var secondTeam = $('#secondTeamDropdown').val();
        var quarter = $('#quarterDropdown').val(); // Get selected quarter
    
        
        $('#viewGameDataModal').modal('show');
        populateGameNumbers(); // Populate game numbers dynamically
        fetchTeamData(gameNumber, firstTeam, secondTeam, quarter);
        updateSecondDropdown();
        
    });
    
    $('#viewGameDropdown, #firstTeamDropdown, #secondTeamDropdown, #quarterDropdown').change(function() {
        var gameNumber = $('#viewGameDropdown').val();
        var firstTeam = $('#firstTeamDropdown').val();
        var secondTeam = $('#secondTeamDropdown').val();
        var quarter = $('#quarterDropdown').val(); // Get selected quarter
    
        fetchTeamData(gameNumber, firstTeam, secondTeam, quarter);
        updateSecondDropdown();
    });
    
    function populateGameNumbers() {
        // Fetch game numbers dynamically (example implementation)
        var gameNumbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        var viewGameDropdown = $('#viewGameDropdown');
        viewGameDropdown.empty();
        gameNumbers.forEach(function(number) {
            viewGameDropdown.append('<option value="' + number + '">' + number + '</option>');
        });
    }
    
    function fetchTeamData(gameNumber, firstTeam, secondTeam, quarter) {
        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetchTeamData.php",
            data: { game_number: gameNumber, team: firstTeam, quarter: quarter }, // Pass quarter here
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    populateTable('#firstTeamTable tbody', response.data);
                    $('#firstTeamTableMessage').hide();
                } else {
                    populateTable('#firstTeamTable tbody', []);
                    $('#firstTeamTableMessage').text('No players available for this team.').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    
        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetchTeamData.php",
            data: { game_number: gameNumber, team: secondTeam, quarter: quarter },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    populateTable('#secondTeamTable tbody', response.data);
                    $('#secondTeamTableMessage').hide();
                } else {
                    populateTable('#secondTeamTable tbody', []);
                    $('#secondTeamTableMessage').text('No players available for this team.').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
    
    function populateTable(tableBodySelector, data) {
        var tableBody = $(tableBodySelector);
        tableBody.empty(); // Clear existing table content
        
        if (data.length === 0) {
            // Optionally show a message or perform other actions when data is empty
            tableBody.append('<tr><td colspan="18">No data available</td></tr>');
            return;
        }
        
        data.forEach(function(player) {
            var row = $('<tr>');
            
            // Loop through each property to create editable cells
            for (var key in player) {
                if (player.hasOwnProperty(key)) {
                    var cell = $('<td>').text(player[key]);
                    
                    // Double-click or touch event to start editing
                    cell.on('dblclick touchend', function(e) {
                        e.preventDefault(); // Prevent default action for touchend
                        var originalContent = $(this).text();
                        $(this).html('<input type="text" value="' + originalContent + '">');
                        $(this).children().first().focus();
                        
                        // Blur event to finish editing and update database
                        $(this).children().first().on('blur', function() {
                            var newContent = $(this).val();
                            var parentCell = $(this).parent();
                            var columnIndex = parentCell.index(); // Column index
                            var columnName = Object.keys(player)[columnIndex]; // Column name
                            
                            // Update UI with new content
                            parentCell.text(newContent);
                            
                            // Update database via AJAX
                            updateDatabase(player.ath_bball_player_id, columnName, newContent);
                        });
                    });
                    
                    row.append(cell);
                }
            }
            
            tableBody.append(row);
        });
    }
    
    // Function to update database via AJAX
    function updateDatabase(playerId, columnName, newValue) {
        $.ajax({
            type: "POST",
            url: "phpFile/buttonFunctions/updatePlayerData.php",
            data: {
                player_id: playerId,
                column_name: columnName,
                new_value: newValue
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    console.log('Database updated successfully');
                } else {
                    console.error('Failed to update database:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
    
    
    
    
    
    

    function updateSecondDropdown() {
        const firstDropdown = document.getElementById('firstTeamDropdown');
        const secondDropdown = document.getElementById('secondTeamDropdown');
        const selectedValue = firstDropdown.value;
        const selectedValue1 = secondDropdown.value;

        // Enable all options in the second dropdown
        const options = secondDropdown.options;
        for (let i = 0; i < options.length; i++) {
            options[i].disabled = false;
        }

        const options1 = firstDropdown.options;
        for (let i = 0; i < options1.length; i++) {
            options1[i].disabled = false;
        }

        // Disable the selected option in the second dropdown
        if (selectedValue) {
            const selectedOption = secondDropdown.querySelector(`option[value="${selectedValue}"]`);
            if (selectedOption) {
                selectedOption.disabled = true;
            }
        }

        if (selectedValue1) {
            const selectedOption1 = firstDropdown.querySelector(`option[value="${selectedValue1}"]`);
            if (selectedOption1) {
                selectedOption1.disabled = true;
            }
        }
    }
    
    
});


