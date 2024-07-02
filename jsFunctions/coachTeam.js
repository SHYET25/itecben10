$(document).ready(function() {
    fetchRankedAthletes('total_percentage'); // Default load

    $('#rankingCriteria').change(function() {
        const selectedCriteria = $(this).val();
        fetchRankedAthletes(selectedCriteria);
    });
});

function fetchRankedAthletes(criteria) {
    $.ajax({
        type: "GET",
        url: "phpFile/buttonFunctions/fetch_ranked_athletes.php",
        data: { criteria: criteria },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                displayAthletes(response.data);
            } else {
                console.error('Error:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
        }
    });
}

function displayAthletes(data) {
    const container = $('#athleteTableBody');
    container.empty();
    let rank = 1;
    data.forEach(function(athlete) {
        const card = 
            `<div class="card mb-3 position-relative">
                <div class="row g-0 align-items-center justify-content-center">
                    <div class="col-md-1 d-flex justify-content-center">
                        <div class="ranking-number">
                            ${rank}
                        </div>
                    </div>
                    <div class="col-md-3 d-flex justify-content-center">
                        <img src="${athlete.ath_img}" class="img-fluid rounded-start" alt="Athlete Image">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title mb-1">${athlete.ath_name}</h5>
                            <p class="card-text mb-1">Height: ${athlete.ath_height} cm</p>
                            <p class="card-text mb-1">Weight: ${athlete.ath_weight} kg</p>
                            <p class="card-text mb-3">Age: ${athlete.ath_age}</p>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-success" role="progressbar" style="width: ${athlete.total_percentage}%" aria-valuenow="${athlete.total_percentage}" aria-valuemin="0" aria-valuemax="100">Overall: ${athlete.total_percentage}</div>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: ${athlete.shooting}%" aria-valuenow="${athlete.shooting}" aria-valuemin="0" aria-valuemax="100">Shooting: ${athlete.shooting}</div>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-info" role="progressbar" style="width: ${athlete.passing}%" aria-valuenow="${athlete.passing}" aria-valuemin="0" aria-valuemax="100">Passing: ${athlete.passing}</div>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: ${athlete.rebounding}%" aria-valuenow="${athlete.rebounding}" aria-valuemin="0" aria-valuemax="100">Rebounding: ${athlete.rebounding}</div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: ${athlete.defending}%" aria-valuenow="${athlete.defending}" aria-valuemin="0" aria-valuemax="100">Defending: ${athlete.defending}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        container.append(card);
        rank++;
    });
}







