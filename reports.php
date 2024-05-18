<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include_once(__DIR__ . "/classes/allworkers.php");

$allWorkers = new AllWorkers();
$workersData = $allWorkers->fetchWorkers();

$loggedInHub = $_SESSION['hubname']; // Haal de hubnaam op uit de sessie

$groupedData = []; // Hier zullen we gegevens groeperen op basis van de gebruikers-ID

// Groepeer de gegevens op basis van gebruikers-ID en filter op hubnaam
foreach ($workersData as $worker) {
    if ($worker['hubname'] === $loggedInHub) {
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
                'work_hours' => [], // Hier worden de werkuren opgeslagen
                'total_hours_worked' => '00:00' // Initialiseer totale uren gewerkt
            ];
        }

        // Voeg de extra gegevens toe aan de georganiseerde structuur
        $groupedData[$userId]['work_hours'][] = [
            'start' => $worker['start'],
            'end' => $worker['end'],
            'day' => $worker['day']
        ];
    }
}

// Bereken en voeg totale uren gewerkt toe voor elke gebruiker
foreach ($groupedData as &$userData) {
    $totalHours = 0;
    foreach ($userData['work_hours'] as $work) {
        $start = new DateTime($work['start']);
        $end = new DateTime($work['end']);
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
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Profile Picture</th>
                        <th>Username</th>
                        <th>E-mail</th>
                        <th>Role</th>
                        <th>Hubname</th>
                        <th>Tasktype</th>
                        <th>Total hours worked</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody id="workersTableBody">
                    <?php
                    foreach ($groupedData as $userData) {
                        echo "<tr>";
                        echo "<td>" . $userData['user_id'] . "</td>";
                        echo "<td><img class='employeeImg' src='" . $userData['profilepicture'] . "' alt='Profile Picture'></td>";
                        echo "<td>" . $userData['username'] . "</td>";
                        echo "<td>" . $userData['email'] . "</td>";
                        echo "<td>" . $userData['role'] . "</td>";
                        echo "<td>" . $userData['hubname'] . "</td>";
                        echo "<td>" . $userData['TaskType'] . "</td>";
                        echo "<td>" . $userData['total_hours_worked'] . "</td>";
                        // Voeg een knop toe om details weer te geven
                        echo "<td><button class='detailsButton' data-userid='" . $userData['user_id'] . "'>Details</button></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div id="popup" class="popup">
            <div class="popup-content">
                <span class="close" onclick="closePopup()">&times;</span>
                <h2 id="popupName"></h2>
                <img id="popupImg" class="employeeImg">
                <ul id="detailsList"></ul>
                <div id="totalWorkedHours"></div> <!-- Toegevoegd: sectie voor totaal gewerkte tijd per dag -->
            </div>
        </div>
    </div>

    <script>
        // Voeg een eventlistener toe aan alle details-knoppen
        const detailsButtons = document.querySelectorAll('.detailsButton');
        detailsButtons.forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.dataset.userid;
                const user = getUserDetails(userId);
                if (user) {
                    displayUserDetails(user);
                }
            });
        });

        // Simuleer het ophalen van gebruikersdetails (vervang dit door je eigen logica om details op te halen)
        function getUserDetails(userId) {
            // Hier kun je logica toevoegen
            // om de details van de gebruiker op te halen uit je datastructuur
            // Voor nu simuleren we gewoon wat dummy-details
            return {
                id: userId,
                name: <?php echo json_encode($groupedData); ?>[userId]['username'],
                profilePicture: <?php echo json_encode($groupedData); ?>[userId]['profilepicture'],
                work_hours: <?php echo json_encode($groupedData); ?>[userId]['work_hours']
            };
        }

        // Toon de details in de pop-up
        function displayUserDetails(user) {
            const popup = document.getElementById('popup');
            const nameElement = document.getElementById('popupName');
            const imgElement = document.getElementById('popupImg');
            const detailsList = document.getElementById('detailsList');
            const totalWorkedHours = document.getElementById('totalWorkedHours'); // Nieuw: sectie voor totaal gewerkte tijd per dag

            nameElement.textContent = user.name;
            imgElement.src = user.profilePicture;

            // Wis eerdere details
            detailsList.innerHTML = '';
            totalWorkedHours.innerHTML = ''; // Wis eerdere totaal gewerkte tijd

            // Vul de details in de lijst
            user.work_hours.forEach(work => {
                const listItem = document.createElement('li');
                listItem.textContent = 'Start: ' + work.start + ', End: ' + work.end + ', Day: ' + work.day;
                detailsList.appendChild(listItem);
            });

            // Bereken en toon totaal gewerkte tijd per dag
            const totalPerDay = {}; // Object om totaal gewerkte tijd per dag op te slaan
            user.work_hours.forEach(work => {
                const day = work.day;
                const start = new Date(work.start);
                const end = new Date(work.end);
                const diffInMilliseconds = end - start;
                const diffInHours = diffInMilliseconds / (1000 * 60 * 60); // Omzetten naar uren
                totalPerDay[day] = (totalPerDay[day] || 0) + diffInHours;
            });

            // Voeg totaal gewerkte tijd per dag toe aan de pop-up
            for (const [day, totalHours] of Object.entries(totalPerDay)) {
                if (!isNaN(totalHours)) {
                    const dayTotalElement = document.createElement('div');
                    dayTotalElement.textContent = `Total worked hours on ${day}: ${totalHours.toFixed(2)} hours`;
                    totalWorkedHours.appendChild(dayTotalElement);
                } else {
                    const dayTotalElement = document.createElement('div');
                    dayTotalElement.textContent = `Total worked hours on ${day}: 0 hours`;
                    totalWorkedHours.appendChild(dayTotalElement);
                }
            }

            // Toon de pop-up
            popup.style.display = 'block';
        }

        // Sluit de pop-up wanneer er buiten wordt geklikt
        window.onclick = function (event) {
            const popup = document.getElementById('popup');
            if (event.target == popup) {
                closePopup();
            }
        }

        // Sluit de pop-up wanneer er op de 'X' wordt geklikt
        function closePopup() {
            const popup = document.getElementById('popup');
            popup.style.display = 'none';
        }

        // Voeg event listener toe voor het filteren op gebruikersnaam
        const usernameInput = document.getElementById('usernameInput');
        usernameInput.addEventListener('input', function () {
            const filterValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#workersTableBody tr');
            tableRows.forEach(row => {
                const usernameCell = row.querySelector('td:nth-child(3)');
                if (usernameCell) {
                    const username = usernameCell.textContent.toLowerCase();
                    if (username.indexOf(filterValue) > -1) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });
    </script>
</body>

</html>

