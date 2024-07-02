<?php
// Database connection
include '../connection/connection.php';  // Ensure this includes your database connection script

// Check if the required parameters are set
if (isset($_GET['game_number']) && isset($_GET['quarter']) && isset($_GET['first_team']) && isset($_GET['second_team'])) {
    $gameNumber = $_GET['game_number'];
    $quarter = $_GET['quarter']; // Get quarter parameter

    $sql_match_id = "SELECT bball_match_id FROM basketball_matches WHERE match_name = ?";
    
    if ($stmt_match_id = $conn->prepare($sql_match_id)) {
        $stmt_match_id->bind_param('s', $gameNumber); // 's' for string
        $stmt_match_id->execute();
        $stmt_match_id->bind_result($bball_match_id);
        $stmt_match_id->fetch();
        $stmt_match_id->close();
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Query preparation for match_id failed'));
        exit(); // Exit if query preparation fails
    }
    $firstTeam = $_GET['first_team'];
    $secondTeam = $_GET['second_team'];

    // Prepare the SQL queries to fetch data for both teams
    $sqlFirstTeam = "SELECT * FROM basketball_matches_quarters WHERE match_id = ? AND game_quarter = ? AND game_team = ?";
    $sqlSecondTeam = "SELECT * FROM basketball_matches_quarters WHERE match_id = ? AND game_quarter = ? AND game_team = ?";

    // Execute the queries
    $firstTeamData = fetchData($conn, $sqlFirstTeam, $bball_match_id, $quarter, $firstTeam);
    $secondTeamData = fetchData($conn, $sqlSecondTeam, $bball_match_id, $quarter, $secondTeam);

    // Prepare response
    $response = array(
        'status' => 'success',
        'data' => array(
            'first_team' => $firstTeamData,
            'second_team' => $secondTeamData
        )
    );

    echo json_encode($response);
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid parameters'));
}

// Function to fetch data from database
function fetchData($conn, $sql, $gameNumber, $quarter, $team) {
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('isi', $gameNumber, $quarter, $team); // 'i' for integer, 's' for string
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC); // Fetch all rows as associative array
        } else {
            return array(); // Return empty array if no rows found
        }

        $stmt->close();
    } else {
        return array(); // Return empty array if query preparation fails
    }
}

// Close database connection
$conn->close();
?>
