<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include_once(__DIR__ . "/classes/allworkers.php");

$allWorkers = new AllWorkers();
$workersData = $allWorkers->fetchWorkers();

$groupedData = []; // Hier zullen we gegevens groeperen op basis van de gebruikers-ID

// Groepeer de gegevens op basis van gebruikers-ID
foreach ($workersData as $worker) {
    $userId = $worker['user_id'];
    if (!isset($groupedData[$userId])) {
        $groupedData[$userId] = [
            'user_id' => $userId,
            'username' => $worker['username'],
            'email' => $worker['email'],
            'profilepicture' => $worker['profilepicture'],
            'role' => $worker['role'],
            'hubname' => $worker['hubname'],
            'TaskType' => $worker['TaskType'],
            'start' => [],
            'end' => [],
            'day' => [],
            'total_hours_worked' => '00:00' // Initialiseer totale uren gewerkt
        ];
    }

    // Voeg de extra gegevens toe aan de georganiseerde structuur
    $groupedData[$userId]['start'][] = $worker['start'];
    $groupedData[$userId]['end'][] = $worker['end'];
    $groupedData[$userId]['day'][] = $worker['day'];
}

// Bereken en voeg totale uren gewerkt toe voor elke gebruiker
foreach ($groupedData as &$userData) {
    $totalHours = 0;
    foreach ($userData['start'] as $key => $start) {
        $start = new DateTime($start);
        $end = new DateTime($userData['end'][$key]);
        $diff = $start->diff($end);
        $totalHours += $diff->format('%H') + ($diff->format('%I') / 60);
    }
    $totalHours = floor($totalHours) . ':' . str_pad(round(($totalHours - floor($totalHours)) * 60), 2, '0', STR_PAD_LEFT);
    $userData['total_hours_worked'] = $totalHours;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>workershub</title>
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/workershub.css">
</head>

<body>
    <?php include_once(__DIR__ . "/classes/nav.php"); ?>

    <div class="screen">
        <div class="title">
            <h1>
                <?php if (isset($_SESSION['role'])) {
                    $role = $_SESSION['role'];
                    echo $role;
                } else {
                    echo "Rol niet gevonden in sessie.";
                } ?> employee information
            </h1>
            <a class="kruis" href="./index.php"></a>

        </div>
        <input type="text" id="usernameInput" placeholder="Filter on username...">
        <input type="text" id="hubnameInput" placeholder="Filter on hub-name...">
        <input type="text" id="taskTypeInput" placeholder="Filter on task...">
        <select id="dayInput">
            <option value="">Select day</option>
            <?php
            for ($i = 1; $i <= 31; $i++) {
                $dayFormatted = str_pad($i, 2, '0', STR_PAD_LEFT); // Voeg een nul toe aan enkele getallen
                echo "<option value='$dayFormatted'>$dayFormatted</option>";
            }
            ?>
        </select>

        <select id="monthInput">
            <option value="">Select month</option>
            <?php
            $months = array(
                1 => "January",
                2 => "February",
                3 => "March",
                4 => "April",
                5 => "May",
                6 => "June",
                7 => "July",
                8 => "August",
                9 => "September",
                10 => "October",
                11 => "November",
                12 => "December"
            );

            foreach ($months as $monthNumber => $monthName) {
                $monthNumberFormatted = str_pad($monthNumber, 2, '0', STR_PAD_LEFT); // Voeg een nul toe aan enkele getallen
                echo "<option value='$monthNumberFormatted'>$monthName</option>";
            }
            ?>
        </select>

        <select id="yearInput">
            <option value="">Select year</option>
            <?php
            $currentYear = date("Y");
            for ($i = $currentYear; $i >= $currentYear - 10; $i--) {
                echo "<option value='$i'>$i</option>";
            }
            ?>
        </select>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>E-mail</th>
                        <th>Profile picture</th>
                        <th>Role</th>
                        <th>Hubname</th>
                        <th>Tasktype</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Day</th>
                        <th>Total hours worked</th>
                    </tr>
                </thead>
                <tbody id="workersTableBody">
                    <?php
                    foreach ($groupedData as $userData) {
                        echo "<tr>";
                        echo "<td>" . $userData['user_id'] . "</td>";
                        echo "<td>" . $userData['username'] . "</td>";
                        echo "<td>" . $userData['email'] . "</td>";
                        echo "<td><img class='employeeImg' src='" . $userData['profilepicture'] . "' alt='Profielfoto'></td>";
                        echo "<td>" . $userData['role'] . "</td>";
                        echo "<td>" . $userData['hubname'] . "</td>";
                        echo "<td>" . $userData['TaskType'] . "</td>";
                        // Toon alle starttijden
                        echo "<td>";
                        foreach ($userData['start'] as $start) {
                            echo $start . "<br>";
                        }
                        echo "</td>";
                        // Toon alle eindtijden
                        echo "<td>";
                        foreach ($userData['end'] as $end) {
                            echo $end . "<br>";
                        }
                        echo "</td>";
                        // Toon alle dagen
                        echo "<td>";
                        foreach ($userData['day'] as $day) {
                            echo $day . "<br>";
                        }
                        echo "</td>";
                        // Toon totale uren gewerkt
                        echo "<td>" . $userData['total_hours_worked'] . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
$totalAllWorkersHours = 0;
foreach ($groupedData as $userData) {
    $totalAllWorkersHours += strtotime($userData['total_hours_worked']);
}

$totalAllWorkersHours = gmdate("H:i", $totalAllWorkersHours);
?>

<table>
    <tbody>
        <tr>
            <td colspan="10"><strong>Total all workers</strong></td>
            <td><?php echo $totalAllWorkersHours; ?></td>
        </tr>
    </tbody>
</table>


    </div>
    <script src="js/workershub.js"></script>
</body>

</html>

