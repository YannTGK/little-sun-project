<?php
session_start();

// Controleer de rol van de ingelogde gebruiker
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$isManager = isset($_SESSION['role']) && $_SESSION['role'] === 'Manager';

// Create a PDO instance
$pdo = new PDO('mysql:host=ID436917_littlesun.db.webhosting.be;dbname=ID436917_littlesun', 'ID436917_littlesun', 'LittleSun5');

// Define a function to fetch assigned tasks
function fetchAssignedTasks($pdo) {
    $query = "SELECT a.id, a.username, a.email, t.TaskType, at.tasktype_id, at.user_id 
               FROM account a 
               JOIN assignedtasks at ON a.id = at.user_id 
               JOIN tasks t ON at.tasktype_id = t.id";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchUniqueTaskTypes($pdo) {
    $query = "SELECT DISTINCT id, TaskType FROM tasks"; // Include the task ID to use later
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchAssignedUsernames($pdo) {
    $query = "SELECT DISTINCT username, id FROM account"; // Select distinct usernames
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Define a function to fetch agenda items for the logged-in user or all users if Manager
function fetchAgendaItems($pdo, $username, $isManager) {
    if ($isManager) {
        $query = "SELECT * FROM agenda";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $agenda_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $query = "SELECT * FROM agenda WHERE username = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $agenda_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $agenda_items_by_day_and_hour = [];

    foreach ($agenda_items as $item) {
        $day = $item['day'];
        $hour = intval(substr($item['startinghour'], 0, 2)); // Extract hour from starting hour

        if (!isset($agenda_items_by_day_and_hour[$day])) {
            $agenda_items_by_day_and_hour[$day] = [];
        }

        if (!isset($agenda_items_by_day_and_hour[$day][$hour])) {
            $agenda_items_by_day_and_hour[$day][$hour] = [];
        }

        $agenda_items_by_day_and_hour[$day][$hour][] = $item;
    }

    return $agenda_items_by_day_and_hour;
}

function insertAgendaItem($pdo, $user_id, $username, $task, $startinghour, $endhour, $day) {
    $query = "INSERT INTO agenda (user_id, username, task, startinghour, endhour, day) VALUES (:user_id, :username, :task, :startinghour, :endhour, :day)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':task', $task);
    $stmt->bindParam(':startinghour', $startinghour);
    $stmt->bindParam(':endhour', $endhour);
    $stmt->bindParam(':day', $day);
    $stmt->execute();
}

function acceptOrDeclineTask($pdo, $task_id, $accept) {
    $query = "UPDATE agenda SET accept = :accept WHERE id = :task_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':accept', $accept, PDO::PARAM_INT);
    $stmt->bindParam(':task_id', $task_id, PDO::PARAM_INT);
    $stmt->execute();
}

$assigned_tasks = fetchAssignedTasks($pdo);
$unique_task_types = fetchUniqueTaskTypes($pdo);

// Haal de gebruikersnaam van de ingelogde gebruiker uit de sessie
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Bepaal de startdatum en einddatum van de huidige maand
if(isset($_GET['month'])) {
    $selectedMonth = $_GET['month'];
    $startOfMonth = date('Y-m-d', strtotime('first day of ' . $selectedMonth));
    $endOfMonth = date('Y-m-d', strtotime('last day of ' . $selectedMonth));
} else {
    $selectedMonth = date('Y-m', strtotime('first day of this month'));
    $startOfMonth = date('Y-m-d', strtotime('first day of this month'));
    $endOfMonth = date('Y-m-d', strtotime('last day of this month'));
}

// Haal de agenda-items op voor de ingelogde gebruiker of alle gebruikers als Manager
$agenda_items_by_day_and_hour = fetchAgendaItems($pdo, $username, $isManager);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["accept_task"])) {
        acceptOrDeclineTask($pdo, $_POST["task_id"], 1);
    } elseif (isset($_POST["decline_task"])) {
        acceptOrDeclineTask($pdo, $_POST["task_id"], 0);
    } else {
        $username = $_POST["username"];
        $task = $_POST["task"];
        $startinghour = $_POST["startinghour"];
        $endhour = $_POST["endhour"];
        $day = $_POST["day"];
        // Get user_id based on the selected username
        $user_id = $_POST["user_id"];
        insertAgendaItem($pdo, $user_id, $username, $task, $startinghour, $endhour, $day);
    }
    header("Location: ". htmlspecialchars($_SERVER["PHP_SELF"]));
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Agenda</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Oxygen:400,700" rel="stylesheet">
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/monthview.css">
    <link rel="stylesheet" href="styles/agenda.css">
        
</head>
<body>
    <?php include_once(__DIR__ . "/classes/nav.php"); ?>

    <div class="screen">
        <div class="title">
            <div class="titleLeft">
                <div class="dateNav">
                    <a href="?month=<?php echo date('Y-m', strtotime('-1 month', strtotime($startOfMonth))); ?>">&#10094;</a>
                    <h3>
                        <?php echo date('F Y', strtotime($selectedMonth)); ?>
                    </h3>
                    <a href="?month=<?php echo date('Y-m', strtotime('+1 month', strtotime($startOfMonth))); ?>">&#10095;</a>
                </div>
                
            </div>
            <div class="titleRight">
                <div class="dropdown">
                    <button class="dropdown-button">View Options</button>
                    <div class="dropdown-content">
                        <a class="formButton" href="./daily_vieuw_agenda.php">Daily view</a>
                        <a class="formButton" href="visibleagenda.php">Weekly view</a>
                        <a class="formButton" href="./monthly_view_agenda.php">Monthly view</a>
                        <a class="formButton" href="year_view_agenda.php">Yearly view</a>
                    </div>
                </div>
                <a class="kruis" href="./calendar.php"></a>
            </div>
        </div>

        <?php if($isAdmin || $isManager): ?>
            
            <div class="agenda-form">
                <a href="filinagenda.php" class="formButton">+ Add task</a>
            </div>
        <?php endif; ?>

        <div class="holder">
            <table class="agenda">
                <thead>
                    <tr>
                        <th>Monday</th>
                        <th>Tuesday</th>
                        <th>Wednesday</th>
                        <th>Thursday</th>
                        <th>Friday</th>
                        <th>Saturday</th>
                        <th>Sunday</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Determine the first day and the last day of the current month
                    $currentYear = date("Y");
                    $currentMonth = date("m");
                    $startOfMonth = date("Y-m-01");
                    $endOfMonth = date("Y-m-t", strtotime($startOfMonth));
                    $currentDate = $startOfMonth;

                    // Calculate the number of empty cells before the first day of the month
                    $firstDayOfMonth = date("N", strtotime($startOfMonth));
                    $emptyCells = $firstDayOfMonth - 1;

                    // Print empty cells before the first day of the month
                    echo "<tr>";
                    for ($i = 0; $i < $emptyCells; $i++) {
                        echo "<td></td>";
                    }

                    // Loop through all the days of the month
                    while ($currentDate <= $endOfMonth) {
                        // If it's the beginning of a week and not the first week, start a new row
                        if (date("N", strtotime($currentDate)) == 1 && $currentDate != $startOfMonth) {
                            echo "</tr><tr>";
                        }

                        echo "<td>";
                        echo "<div class='date'><a href='daily_vieuw_agenda.php?date=" . date('Y-m-d', strtotime($currentDate)) . "'>" . date('j', strtotime($currentDate)) . "</a></div>";

                        if (isset($agenda_items_by_day_and_hour[$currentDate])) {
                            foreach ($agenda_items_by_day_and_hour[$currentDate] as $hour => $agenda_items_for_hour) {
                                foreach ($agenda_items_for_hour as $agenda_item) {
                                    echo "<div class='event'>" . $agenda_item["task"] . " - " . $agenda_item["username"] . "</div>";
                                }
                            }
                        }

                        echo "</td>";

                        $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                    }

                    echo "</tr>";
                    ?>
                </tbody>
            </table>
        </div>

    </div>
        
    <script>
        document.getElementById('username').addEventListener('change', function() {
            var userId = this.options[this.selectedIndex].getAttribute('data-user-id');
            document.getElementById('user_id').value = userId;
        });
    </script>
</body>
</html>

