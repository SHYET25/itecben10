$(document).ready(function() {
    var selectedAthletes = [];

    // Function to handle AJAX errors
    function handleAjaxError(xhr, status, error) {
        console.error('AJAX Error:', error);
    }

    // Fetch logged-in user data and update the UI
    function fetchLoggedInUserData() {
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
            error: handleAjaxError
        });
    }

    // Populate the team dropdown
    function populateDropdown() {
        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetchTeams.php",
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const dropdown = $('#gameDropdown');
                    dropdown.empty();
                    dropdown.append('<option value="all">All</option>');
                    response.teams.forEach(function(team) {
                        dropdown.append(`<option value="${team}">${team}</option>`);
                    });
                } else {
                    console.error('Error fetching teams:', response.message);
                }
            },
            error: handleAjaxError
        });
    }

    // Fetch athlete data based on position and name filters
    function fetchAthletesData(position = 'All', name = '') {
        var gameNumber = $('#gameDropdown').val();
        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetchAthletes.php",
            data: { position: position, name: name, game_number: gameNumber },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    populateAthleteTable(response.data);
                } else {
                    console.error('Error:', response.message);
                }
            },
            error: handleAjaxError
        });
    }

    // Populate athlete table with data
    function populateAthleteTable(data) {
        var athleteTableBody = $('#athleteTableBody');
        athleteTableBody.empty();
        var athletesPerColumn = Math.ceil(data.length / 2);
        var column1 = $('<div class="col-md-6"></div>');
        var column2 = $('<div class="col-md-6"></div>');

        data.forEach(function(athlete, index) {
            var card = `<div class="card mb-3" style="max-width: 100%;">
                            <div class="row no-gutters">
                                <div class="col-md-4">
                                    <img src="${athlete.image_url}" class="card-img" alt="Athlete Image">
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h5 class="card-title">${athlete.ath_name}</h5>
                                        <p class="card-text"><strong>Position:</strong> ${athlete.ath_position}</p>
                                        <p class="card-text"><strong>ID:</strong> ${athlete.AthleteID}</p>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input athlete-checkbox" data-id="${athlete.AthleteID}" ${athlete.disabled ? 'disabled' : ''}>
                                            <label class="form-check-label">Select Athlete</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
            var $card = $(card);
            if (index < athletesPerColumn) {
                column1.append($card);
            } else {
                column2.append($card);
            }
            if (athlete.disabled) {
                $card.addClass('disabled-card');
            }
        });
        athleteTableBody.append($('<div class="row"></div>').append(column1, column2));

        // Handle checkbox changes
        $('.athlete-checkbox').change(function() {
            var athleteId = $(this).data('id');
            var athleteName = $(this).closest('.card-body').find('.card-title').text();
            var athletePosition = $(this).closest('.card-body').find('.card-text strong:contains("Position")').next().text();

            if ($(this).prop('checked')) {
                selectedAthletes.push({ AthleteID: athleteId, ath_name: athleteName, ath_position: athletePosition });
            } else {
                selectedAthletes = selectedAthletes.filter(function(athlete) {
                    return athlete.AthleteID !== athleteId;
                });
            }
            updateSelectedAthletesDisplay();
        });
    }

    // Update the display of selected athletes
    function updateSelectedAthletesDisplay() {
        var selectedAthletesList = $('#selectedAthletesList');
        selectedAthletesList.empty();

        selectedAthletes.forEach(function(athlete) {
            var athleteItem = `<div class="selected-athlete-item mr-2">
                                ${athlete.ath_name} (${athlete.ath_position})
                                <button type="button" class="btn btn-link btn-remove-athlete" data-id="${athlete.AthleteID}">&times;</button>
                               </div>`;
            selectedAthletesList.append(athleteItem);
        });

        $('.btn-remove-athlete').click(function() {
            var athleteId = $(this).data('id');
            selectedAthletes = selectedAthletes.filter(function(athlete) {
                return athlete.AthleteID !== athleteId;
            });
            updateSelectedAthletesDisplay();
            $('#athleteTableBody').find('input[data-id="' + athleteId + '"]').prop('checked', false);
        });
    }

    // Validate the selected team name
    function validateTeamName() {
        const teamName = $("#teamDropdown");
        const isValid = teamName.val().trim() !== '' && teamName.val().toLowerCase() !== 'all';
        teamName.toggleClass('is-invalid', !isValid);
        return isValid;
    }

    // Save game data for selected athletes
    function saveGameData() {
        var team = $('#teamDropdown').val();
        console.log('Selected team:', team);

        selectedAthletes.forEach(function(athlete) {
            var data = { ath_bball_player_id: athlete.AthleteID, game_team: team };

            $.ajax({
                type: "POST",
                url: "phpFile/buttonFunctions/saveGameData.php",
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Game data saved for athlete: ' + athlete.ath_name);
                        selectedAthletes = [];
                        updateSelectedAthletesDisplay();
                        fetchAthletesData($('#positionFilter').val(), $('#searchBar').val());
                        populateDropdown();
                    } else {
                        console.error('Error:', response.message);
                        alert(response.message);
                    }
                },
                error: handleAjaxError
            });
        });
    }

    // Event bindings
    $('#addGameButton').click(function() {
        fetchAthletesData();
        $('#gameModal').modal('show');
    });

    $('#closeModal').click(function() {
        $('#gameModal').modal('hide');
    });

    $('#positionFilter').change(function() {
        fetchAthletesData($(this).val(), $('#searchBar').val());
    });

    $('#searchBar').on('input', function() {
        fetchAthletesData($('#positionFilter').val(), $(this).val());
    });

    $('#gameDropdown').change(function() {
        document.getElementById('teamDropdown').value = this.value;
        fetchAthletesData($('#positionFilter').val(), $('#searchBar').val());
    });

    $('#saveGameButton').click(function() {
        if (validateTeamName()) {
            saveGameData();
        }
    });

    // Initial calls
    fetchLoggedInUserData();
    populateDropdown();
    fetchAthletesData();

    // ADD SCRIM TEAM------------------------------------------------------------------------------------------------------------
    
    $('#viewGameDataButton').click(function() {
        var gameNumber = $('#viewGameDropdown').val();
        var quarter = $('#quarterDropdown').val(); // Get selected quarter

        // Fetch teams dynamically based on gameNumber
        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetchTeam12.php",
            data: { game_number: gameNumber },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var firstTeam = response.data.team_1;
                    var secondTeam = response.data.team_2;

                    // Show modal
                    $('#viewGameDataModal').modal('show');

                    // Populate match name dropdown
                    populateMatchNameDropdown();

                    // Fetch team data
                    fetchTeamData(gameNumber, firstTeam, secondTeam, quarter);
                    fetchTeamDatax(gameNumber, firstTeam, secondTeam);
                    
                    
                    fetchTeamQuarterTotal(gameNumber, firstTeam, secondTeam, quarter);
                    

                } else {
                    console.error('Error fetching teams:', response.message);
                    // Handle error case if needed
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error fetching teams:', error);
                // Handle AJAX error
            }
        });
    });

    // Change handler for dropdowns
    $('#viewGameDropdown, #firstTeamDropdown, #secondTeamDropdown, #quarterDropdown').change(function() {
        var gameNumber = $('#viewGameDropdown').val();

        // Fetch teams dynamically based on gameNumber
        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetchTeam12.php",
            data: { game_number: gameNumber },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var firstTeam = response.data.team_1;
                    var secondTeam = response.data.team_2;
                    var quarter = $('#quarterDropdown').val(); // Get selected quarter

                    fetchTeamDatax(gameNumber, firstTeam, secondTeam);
                    fetchTeamData(gameNumber, firstTeam, secondTeam, quarter);
                    
                    fetchAndUpdateTeamTotal(gameNumber, firstTeam, secondTeam);
                    
                    fetchTeamQuarterTotal(gameNumber, firstTeam, secondTeam, quarter);

                } else {
                    console.error('Error fetching teams:', response.message);
                    // Handle error case if needed
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error fetching teams:', error);
                // Handle AJAX error
            }
        });
    });

    // Function to populate match name dropdown
    function populateMatchNameDropdown() {
        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetch_teams.php",
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var dropdown = $('#viewGameDropdown');
                    dropdown.empty(); // Clear existing options

                    // Iterate over match names and create options
                    response.matchNames.forEach(function(matchName) {
                        dropdown.append($('<option></option>').attr('value', matchName).text(matchName));
                    });
                } else {
                    console.error('Error fetching match names:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }

    // Call populateDropdown function when the page loads
    populateMatchNameDropdown();

    // Function to fetch team data for both teams
    function fetchTeamDatax(gameNumber, firstTeam, secondTeam) {
        function fetchData(team) {
            $.ajax({
                type: "GET",
                url: "phpFile/buttonFunctions/fetchTeamMatch.php",
                data: { game_number: gameNumber, team: team },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Process data here
                        console.log(`Data received for:`);
                        console.log(response.data); // Log the data received

                        // Example: Process each quarter's data if needed
                        response.data.forEach(function(quarterData, index) {
                            console.log(`Quarter ${index + 1} data:`);
                            console.log(quarterData);
                            // Process each quarter's data as needed
                        });

                    } else {
                        console.error(`Error fetching data for`, response.message);
                        // Handle error case if needed
                    }
                },
                error: function(xhr, status, error) {
                    console.error(`AJAX Error fetching data for:`, error);
                    // Handle AJAX error
                }
            });
        }

        // Call fetchData function for both teams
        fetchData(firstTeam);
        fetchData(secondTeam);
    }
// asd

    function fetchTeamQuarterSum(gameNumber, firstTeam, secondTeam, quarter, match_id, gameTeam, gameQuarter) {
        console.log("Game Number:", gameNumber);
        console.log("First Team:", firstTeam);
        console.log("Second Team:", secondTeam);
        console.log("Quarter:", quarter);
        console.log("Game Team:", gameTeam);
        console.log("Game Quarter:", gameQuarter);
        console.log("Match ID:", match_id);

        function updateTeamQuarterSums(sum) {
            console.log("Match asdad:", sum);
            $.ajax({
                type: "POST",
                url: "phpFile/buttonFunctions/updateQuarterResult.php",
                data: { sum: sum, team: gameTeam, match_id: match_id, quarter: gameQuarter },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        console.log(`Successfully updated Quarter for team: ${gameTeam}`);
                    } else {
                        console.error(`Error updating Quarter for team: ${gameTeam}`, response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }

        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/sumQuarter.php",
            data: { game_number: match_id, team: gameTeam, quarter: quarter },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    console.log("First Team Sums:", response.sum);
                    updateTeamQuarterSums(response.sum);
                } else {
                    console.error("Error fetching sums for first team:", response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });

    }



    function fetchTeamQuarterTotal(gameNumber, firstTeam, secondTeam, quarter) {
        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetchQuarterTotal.php",
            data: { game_number: gameNumber, team: firstTeam, quarter: quarter },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    populateQuarterTotalTable('#firstTeamQuarterTable tbody', response.data);
                    $('#firstTeamQuarterMessage').hide();
                } else {
                    $('#firstTeamQuarterTable tbody').empty(); // Clear table body
                    $('#firstTeamQuarterMessage').text('No data available for this team.').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                $('#firstTeamQuarterTable tbody').empty(); // Clear table body on error
                $('#firstTeamQuarterMessage').text('Error fetching data.').show();
            }
        });

        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetchQuarterTotal.php",
            data: { game_number: gameNumber, team: secondTeam, quarter: quarter },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    populateQuarterTotalTable('#secondTeamQuarterTable tbody', response.data);
                    $('#secondTeamQuarterMessage').hide();
                } else {
                    $('#secondTeamQuarterTable tbody').empty(); // Clear table body
                    $('#secondTeamQuarterMessage').text('No data available for this team.').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                $('#secondTeamQuarterTable tbody').empty(); // Clear table body on error
                $('#secondTeamQuarterMessage').text('Error fetching data.').show();
            }
        });
    }

    // Function to populate quarter total tables
    function populateQuarterTotalTable(tableBodySelector, data) {
        var tableBody = $(tableBodySelector);
        tableBody.empty(); // Clear existing table content

        if (data.length === 0) {
            tableBody.append('<tr><td colspan="18">No data available</td></tr>');
            return;
        }

        data.forEach(function(rowData) {
            var row = $('<tr>');

            // Adjust this based on your actual data structure returned from PHP

            // Assuming 'row' is your table row (<tr>) element

            row.append($('<td>').text(rowData['game_points']));
            row.append($('<td>').text(rowData['game_2fgm']));
            row.append($('<td>').text(rowData['game_3fgm']));
            row.append($('<td>').text(rowData['game_ftm']));
            row.append($('<td>').text(rowData['game_2pts']));
            row.append($('<td>').text(rowData['game_3pts']));
            row.append($('<td>').text(rowData['game_ftpts']));
            row.append($('<td>').text(rowData['game_2fga']));
            row.append($('<td>').text(rowData['game_3fga']));
            row.append($('<td>').text(rowData['game_fta']));
            row.append($('<td>').text(rowData['game_ass']));
            row.append($('<td>').text(rowData['game_block']));
            row.append($('<td>').text(rowData['game_steal']));
            row.append($('<td>').text(rowData['game_ofreb']));
            row.append($('<td>').text(rowData['game_defreb']));
            row.append($('<td>').text(rowData['game_turn']));
            row.append($('<td>').text(rowData['game_foul']));

           

            tableBody.append(row);
        });
    }

    function fetchAndUpdateTeamTotal (gameNumber, firstTeam, secondTeam, quarter, match_id, gameTeam, gameQuarter) {
        
        function updateTeamSums(sums) {
            console.log('Error updating sums for shit:' + sums);
            $.ajax({
                type: "POST",
                url: "phpFile/buttonFunctions/updateMatchResult.php",
                data: { sums: sums, team: gameTeam, match_id: match_id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        console.log(`Successfully updated sums for team: `);
                    } else {
                        console.error('Error updating sums for team:' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }
    
    
        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/sumMatch.php",
            data: { game_number: match_id, team: gameTeam },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    console.log("Second Team Sums:", response.sums);
                    updateTeamSums(response.sums);
                } else {
                    console.error("Error fetching sums for second team:", response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
    
    
    // Function to fetch and populate team data for a specific game and quarter
    function fetchTeamData(gameNumber, firstTeam, secondTeam, quarter) {
        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetchTeamData.php",
            data: { game_number: gameNumber, team: firstTeam, quarter: quarter },
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

    // Function to populate tables with player data
    var playerData = []; // Global variable to store player data

    function populateTable(tableBodySelector, data) {
        playerData = data; // Store data in global variable
    
        var tableBody = $(tableBodySelector);
        tableBody.empty(); // Clear existing table content
    
        if (data.length === 0) {
            tableBody.append('<tr><td colspan="18">No data available</td></tr>');
            return;
        }
    
        data.forEach(function(player) {
            var row = $('<tr>');
    
            // Loop through each property to create cells
            for (var key in player) {
                if (player.hasOwnProperty(key)) {
                    var cell = $('<td>').text(player[key]);
    
                    // Make cells editable on double-click except for specified columns
                    if (!['ath_bball_player_id', 'game_pts', 'game_2pts', 'game_3pts', 'game_ftpts', 'game_2fgm', 'game_3fgm', 'game_ftm', 'game_2fga', 'game_3fga', 'game_fta', 'game_ass', 'game_block', 'game_steal', 'game_ofreb', 'game_defreb', 'game_turn', 'game_foul'].includes(key)) {
                        cell.on('dblclick touchend', function(e) {
                            e.preventDefault();
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
                                updateDatabase(player, columnName, newContent);
                            });
                        });
                    }
    
                    // Add minus button before the cell content for specific columns
                    if (['game_2fgm', 'game_3fgm', 'game_ftm', 'game_2fga', 'game_3fga', 'game_fta', 'game_ass', 'game_block', 'game_steal', 'game_ofreb', 'game_defreb', 'game_turn', 'game_foul'].includes(key)) {
                        (function(key, player) { // Capture player data in closure
                            var minusButton = $('<button>')
                                .text('-')
                                .addClass('btn btn-danger mr-2')
                                .css({
                                    fontSize: '1rem',
                                    fontWeight: 'bold',
                                    padding: '0.3rem 0.5rem'
                                });
    
                            // Minus button click handler
                            minusButton.on('click', function() {
                                var currentCell = $(this).parent();
                                var currentValue = parseInt(currentCell.contents().filter(function() { return this.nodeType == 3; }).first().text()) || 0;
                                var newValue = currentValue - 1;
                                currentCell.contents().filter(function() { return this.nodeType == 3; }).first().replaceWith(newValue);
    
                                // Update database immediately
                                updateDatabase(player, key, newValue);
    
                                // Handle dependent updates
                                handleDependentUpdates(player, key, newValue);
                                
                                // Retrieve specific player data
                                retrieveSpecificPlayerData(player);
    
                            });
    
                            // Append minus button before cell content
                            cell.prepend(minusButton);
                        })(key, player);
                    }
    
                    // Add plus button after the cell content for specific columns
                    if (['game_2fgm', 'game_3fgm', 'game_ftm', 'game_2fga', 'game_3fga', 'game_fta', 'game_ass', 'game_block', 'game_steal', 'game_ofreb', 'game_defreb', 'game_turn', 'game_foul'].includes(key)) {
                        (function(key, player) { // Capture player data in closure
                            var plusButton = $('<button>')
                                .text('+')
                                .addClass('btn btn-success')
                                .css({
                                    fontSize: '1rem',
                                    fontWeight: 'bold',
                                    padding: '0.2rem 0.5rem'
                                });
    
                            // Plus button click handler
                            plusButton.on('click', function() {
                                var currentCell = $(this).parent();
                                var currentValue = parseInt(currentCell.contents().filter(function() { return this.nodeType == 3; }).first().text()) || 0;
                                var newValue = currentValue + 1;
                                currentCell.contents().filter(function() { return this.nodeType == 3; }).first().replaceWith(newValue);
    
                                // Update database immediately
                                updateDatabase(player, key, newValue);
                                
                                // Handle dependent updates
                                handleDependentUpdates(player, key, newValue);
    
                                // Retrieve specific player data
                                retrieveSpecificPlayerData(player);
    
                            });
    
                            // Append plus button after cell content
                            cell.append(plusButton);
                        })(key, player);
                    }
    
                    row.append(cell);
                }
            }
    
            tableBody.append(row);
        });
    }
    
    // Function to update database via AJAX
    function updateDatabase(player, columnName, newValue) {
        var gameNumber = $('#viewGameDropdown').val();
    
        // Fetch teams dynamically based on gameNumber
        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetchTeam12.php",
            data: { game_number: gameNumber },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var firstTeam = response.data.team_1;
                    var secondTeam = response.data.team_2;
                    var quarter = $('#quarterDropdown').val(); // Get selected quarter
    
                    // Update database with fetched teams
                    $.ajax({
                        type: "POST",
                        url: "phpFile/buttonFunctions/updatePlayerData.php",
                        data: {
                            player_id: player.ath_bball_player_id,
                            column_name: columnName,
                            new_value: newValue,
                            player_team: player.game_team,
                            quarter: player.game_quarter,
                            match_id: player.match_id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                console.log('Database updated successfully');
                                // Reload updated data
                                fetchTeamData(gameNumber, firstTeam, secondTeam, quarter);
                                fetchTeamQuarterTotal(gameNumber, firstTeam, secondTeam, quarter);
                                fetchTeamQuarterSum(gameNumber, firstTeam, secondTeam, quarter, player.match_id, player.game_team, player.game_quarter);
                                fetchAndUpdateTeamTotal(gameNumber, firstTeam, secondTeam, quarter, player.match_id, player.game_team, player.game_quarter);
                                
                            } else {
                                console.error('Failed to update database:', response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', error);
                        }
                    });
    
                } else {
                    console.error('Error fetching teams:', response.message);
                    // Handle error case if needed
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error fetching teams:', error);
                // Handle AJAX error
            }
        });
    }
    
    // Function to handle dependent updates
    function handleDependentUpdates(player, columnName, newValue) {
        let dependentUpdates = [];
    
        switch (columnName) {
            case 'game_2fgm':
                dependentUpdates.push({ column: 'game_2pts', value: newValue * 2 });
                break;
            case 'game_3fgm':
                dependentUpdates.push({ column: 'game_3pts', value: newValue * 3 });
                break;
            case 'game_ftm':
                dependentUpdates.push({ column: 'game_ftpts', value: newValue * 1 });
                break;
            default:
                return; // No dependent column to update
        }
    
        // Calculate total points
        const totalPoints = (player.game_2pts + player.game_3pts + player.game_ftpts) +
                            (dependentUpdates.find(update => update.column === 'game_2pts') ? (newValue * 2) - player.game_2pts : 0) +
                            (dependentUpdates.find(update => update.column === 'game_3pts') ? (newValue * 3) - player.game_3pts : 0) +
                            (dependentUpdates.find(update => update.column === 'game_ftpts') ? (newValue * 1) - player.game_ftpts : 0);
    
        dependentUpdates.push({ column: 'game_pts', value: totalPoints });
    
        // Perform the dependent updates
        dependentUpdates.forEach(update => {
            updateDatabase(player, update.column, update.value);
        });
    }
    
    // Function to retrieve specific player data
    function retrieveSpecificPlayerData(player) {
        console.log("Player Data Retrieved:");
        console.log("Player ID:", player.ath_bball_player_id);
        console.log("Player Name:", player.player_name); // Assuming player_name is a property in player data
        // Perform other actions with player data if needed
    }

    // VIEW DATAS ==================================================================================================================================================================


    function populateDropdowns() {
        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetchTeams.php",
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const dropdown1 = $('#matchTeam1');
                    const dropdown2 = $('#matchTeam2');
                    dropdown1.empty();
                    dropdown2.empty();

                    // Populate dropdown options
                    response.teams.forEach(function(team) {
                        dropdown1.append(`<option value="${team}">${team}</option>`);
                        dropdown2.append(`<option value="${team}">${team}</option>`);
                    });

                    // Set default values and update options
                    if (response.teams.length > 1) {
                        dropdown1.val(response.teams[0]);
                        dropdown2.val(response.teams[1]);
                    }

                    updateDropdownOptions(dropdown2, response.teams[0]);
                    updateDropdownOptions(dropdown1, response.teams[1]);

                    // Dropdown change event handlers
                    dropdown1.off('change').on('change', function() {
                        updateDropdownOptions(dropdown2, $(this).val());
                        fetchTeamOneData();
                    });

                    dropdown2.off('change').on('change', function() {
                        updateDropdownOptions(dropdown1, $(this).val());
                        fetchTeamTwoData();
                    });

                    // Fetch initial data
                    fetchTeamOneData();
                    fetchTeamTwoData();
                } else {
                    console.error('Error fetching teams:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }

    // Function to update dropdown options based on the selected team
    function updateDropdownOptions(dropdown, selectedTeam) {
        dropdown.find('option').each(function() {
            $(this).prop('disabled', $(this).val() === selectedTeam);
        });
    }

    // Function to populate the athlete table
    function populateTableMatch(athleteTableBody, data) {
        athleteTableBody.empty();
        var numColumns = 4;
        var columnSizeClass = 'col-md-' + Math.floor(12 / numColumns);

        data.forEach(function(athlete) {
            var card = `
                <div class="${columnSizeClass} mb-4">
                    <div class="card" style="max-width: 100%;">
                        <img src="${athlete.ath_img}" class="card-img-top" alt="Athlete Image">
                        <div class="card-body">
                            <h5 class="card-title">${athlete.ath_name}</h5>
                            <p class="card-text"><strong>Position:</strong> ${athlete.ath_position}</p>
                            <p class="card-text"><strong>ID:</strong> ${athlete.AthleteID}</p>
                            <p class="card-text editable" data-ath-id="${athlete.AthleteID}" data-ath-team="${athlete.ath_team}">
                                <strong>Team:</strong> ${athlete.ath_team.trim()}
                            </p>
                        </div>
                    </div>
                </div>`;
            var $card = $(card);
            athleteTableBody.append($card);
            if (athlete.disabled) {
                $card.addClass('disabled-card');
            }
        });

        // Attach double-click event to make the team name editable
        $('.editable').off('dblclick').on('dblclick', function() {
            var $this = $(this);
            var teamName = $this.text().replace('Team:', '').trim();
            var input = $(`<input type="text" class="edit-input" value="${teamName}" />`);
            $this.html(`<strong>Team:</strong> `).append(input);
            input.focus();

            // Attach focusout event to update the team name
            input.off('focusout').on('focusout', function() {
                var newTeamName = $(this).val().trim();
                var athId = $this.data('ath-id');
                var oldTeamName = $this.data('ath-team');

                $.ajax({
                    type: "POST",
                    url: "phpFile/buttonFunctions/updateTeam.php",
                    data: { ath_id: athId, ath_team: newTeamName, old_team: oldTeamName },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $this.html(`<strong>Team:</strong> ${newTeamName}`);
                            $this.data('ath-team', newTeamName);
                            reloadAllFunctions();
                        } else {
                            alert(response.message || 'Unknown error');
                            $this.html(`<strong>Team:</strong> ${oldTeamName}`);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        $this.html(`<strong>Team:</strong> ${oldTeamName}`);
                    }
                });
            });
        });
    }

    // Function to fetch team data and populate the respective table
    function fetchTeamDataCreate(dropdownId, tableId) {
        var team = $(dropdownId).val();
        $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetchAthletesMatch.php",
            data: { team: team },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    populateTableMatch($(tableId), response.data);
                } else {
                    console.error('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }

    // Function to fetch data for the first team
    function fetchTeamOneData() {
        fetchTeamDataCreate('#matchTeam1', '#teamOneTable');
    }

    // Function to fetch data for the second team
    function fetchTeamTwoData() {
        fetchTeamDataCreate('#matchTeam2', '#teamTwoTable');
    }

    // Function to reload all data and dropdowns
    function reloadAllFunctions() {
        populateDropdowns();
        fetchTeamOneData();
        fetchTeamTwoData();
    }

    // Function to fetch athletes for a specific team
    function fetchAthletes(team) {
        return $.ajax({
            type: "GET",
            url: "phpFile/buttonFunctions/fetchAthletesMatch.php",
            data: { team: team },
            dataType: 'json'
        });
    }

    // Function to create a new basketball match
    function createBasketballMatch() {
        var matchName = $('#matchName').val().trim();
        var team1 = $('#matchTeam1').val();
        var team2 = $('#matchTeam2').val();
        
        if (matchName && team1 && team2) {
            // Fetch athletes for both teams
            $.when(fetchAthletes(team1), fetchAthletes(team2)).done(function(response1, response2) {
                var team1Athletes = response1[0].data;
                var team2Athletes = response2[0].data;
                var allAthletes = team1Athletes.concat(team2Athletes);

                // Check for duplicate athletes in both teams
                var duplicateAthletes = [];
                var athleteIds = new Set();

                allAthletes.forEach(function(athlete) {
                    if (athleteIds.has(athlete.AthleteID)) {
                        duplicateAthletes.push(athlete);
                    } else {
                        athleteIds.add(athlete.AthleteID);
                    }
                });

                var proceed = true;
                if (duplicateAthletes.length > 0) {
                    var confirmMessage = duplicateAthletes.map(a => a.ath_name).join(', ') + " is on both teams. Do you still want to insert?";
                    proceed = confirm(confirmMessage);
                }

                if (proceed) {
                    $.ajax({
                        type: "POST",
                        url: "phpFile/buttonFunctions/createBasketballMatch.php",
                        data: {
                            match_name: matchName,
                            team1: team1,
                            team2: team2,
                            team1_score: 0,
                            team2_score: 0,
                            match_win: '--',
                            match_lose: '--',
                            athletes: allAthletes
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                alert('Match created successfully');
                                // Optionally, reload data or update the UI here
                            } else {
                                alert('Error creating match: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', error);
                        }
                    });
                }
            }).fail(function(xhr, status, error) {
                console.error('AJAX Error:', error);
            });
        } else {
            alert('Please enter the match name and select both teams');
        }
    }

    // Event listener for the create match modal button
    $('#createMatchModalButton').click(function() {
        populateDropdowns();
        $('#createMatchModal').modal('show');
    });

    // Event listener for creating matches
    $('#createMatches').click(function() {
        createBasketballMatch();
    });

    // Initial population of the dropdowns
    populateDropdowns();
    
    });

