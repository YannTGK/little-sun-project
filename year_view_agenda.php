<?php
session_start();

// Controleer de rol van de ingelogde gebruiker
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$isManager = isset($_SESSION['role']) && $_SESSION['role'] === 'Manager';

// Configuration
$db_host = 'localhost';
$db_username = 'root';
$db_password = 'root';
$db_name = 'littlesun';

// Create a PDO instance
$pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_username, $db_password);

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

// Haal de gebruikersnaam van de ingelogde gebruiker uit de sessie
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

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

$pdo = null;
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
    <link rel="stylesheet" href="styles/agenda.css">
</head>
<body>
    <h1>Yearly View</h1>
<a href="monthly_view_agenda.php">monthly view</a>
<a href="./daily_vieuw_agenda.php">daily view</a>
<a href="weekly_view_agenda.php">weekly view</a>

    <div class="screen">
    <h1>Yearly Agenda</h1>

    <?php
        $currentYear = date("Y");
        $numDaysInYear = date("z", mktime(0, 0, 0, 12, 31, $currentYear)) + 1;

        echo "<div class='agenda'>";
        for ($dayOfYear = 1; $dayOfYear <= $numDaysInYear; $dayOfYear++) {
            $currentDate = date("Y-m-d", mktime(0, 0, 0, 1, $dayOfYear, $currentYear));
            echo "<div class='day'>";
            echo "<h2>" . date('l', strtotime($currentDate)) . "</h2>";
            echo "<p>" . date('F j, Y', strtotime($currentDate)) . "</p>";
            for ($hour = 7; $hour <= 19; $hour++) {
                echo "<div class='hour-block'>";
                echo "<p>$hour:00 - " . ($hour + 1) . ":00</p>";
                if (isset($agenda_items_by_day_and_hour[$currentDate]) && isset($agenda_items_by_day_and_hour[$currentDate][$hour])) {
                    $agenda_items_for_hour = $agenda_items_by_day_and_hour[$currentDate][$hour];
                    foreach ($agenda_items_for_hour as $agenda_item) {
                        if (isset($agenda_item["username"])) {
                            $starting_hour = intval(substr($agenda_item['startinghour'], 0, 2));
                            $end_hour = intval(substr($agenda_item['endhour'], 0, 2));
                            if ($hour >= $starting_hour && $hour < $end_hour) {
                                $bg_color = "red";
                            } else {
                                $bg_color = "";
                            }
                            if ($agenda_item["accept"] === null) {
                                $bg_color = "grey";
                            } elseif ($agenda_item["accept"] == 1) {
                                $bg_color = "green";
                            } elseif ($agenda_item["accept"] == 0) {
                                $bg_color = "red";
                            }
                            echo "<p style='background-color: $bg_color;'>";
                            echo $agenda_item["task"] . " - " . $agenda_item["username"] . "</p>";
                            if ($agenda_item["accept"] === null) {
                                echo "<form method='post' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
                                echo "<input type='hidden' name='task_id' value='" . $agenda_item["id"] . "'>";
                                echo "<input type='submit' name='accept_task' value='Accept'>";
                                echo "<input type='submit' name='decline_task' value='Decline'>";
                                echo "</form>";
                            }
                            // Print start hour and end hour
                            echo "<p style='background-color: $bg_color;'>Start hour: " . $agenda_item['startinghour'] . ", End hour: " . $agenda_item['endhour'] . "</p>";
                        } else {
                            echo "<p>" . $agenda_item["task"] . "</p>";
                        }
                    }
                }
                echo "</div>";
            }
            echo "</div>";
        }
        echo "</div>";
    ?>

    </div>
    <hr />
    <div class="row">
        <div class="col-xs-6">

        </div>
        <?php if($isAdmin || $isManager): ?>
        <div class="assingned_tasks">
            <h2>Assigned Tasks</h2>
            <?php
                foreach($assigned_tasks as $task) {
                    echo "<div>";
                    echo "<p>User: " . $task["username"] . ", Email: " . $task["email"] . "</p>";
                    echo "<p>TaskType: " . $task["TaskType"] . "</p>";
                    echo "</div>";
                }
            ?>
        </div>
    </div>
    <hr />

    <div class="agenda-form">
        <h2>Fill in agenda</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <select class="form-control" id="username" name="username">
                    <?php foreach($assigned_tasks as $task): ?>
                        <option value="<?php echo $task['username']; ?>" data-user-id="<?php echo $task['id']; ?>"><?php echo $task['username']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" name="user_id" id="user_id" value="">
            <div class="form-group">
                <label for="task">Task:</label>
                <select class="form-control" id="task" name="task">
                    <?php foreach($assigned_tasks as $task): ?>
                        <option value="<?php echo $task['TaskType']; ?>"><?php echo $task['TaskType']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="startinghour">Start hour:</label>
                <input type="time" class="form-control" id="startinghour" name="startinghour">
            </div>
            <div class="form-group">
                <label for="endhour">End hour:</label>
                <input type="time" class="form-control" id="endhour" name="endhour">
            </div>
            <div class="form-group">
                <label for="day">Date:</label>
                <input type="date" class="form-control" id="day" name="day">
            </div>

            <button type="submit" class="btn btn-primary">Save</button>

        </form>
    </div>
    <?php endif; ?>
    
    <script>
        document.getElementById('username').addEventListener('change', function() {
            var userId = this.options[this.selectedIndex].getAttribute('data-user-id');
            document.getElementById('user_id').value = userId;
        });
    </script>
</body>
</html>
