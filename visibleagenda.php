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

// Haal alle vakantie-items op
$query_vacation = "SELECT * FROM vacation WHERE accepted = 1";
$stmt_vacation = $pdo->prepare($query_vacation);
$stmt_vacation->execute();
$vacation_items = $stmt_vacation->fetchAll(PDO::FETCH_ASSOC);

$errorMessage = ''; // Initialiseer de foutmelding

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the user is on vacation for the specified date
    $username = $_POST["username"];
    $day = $_POST["day"];
    $user_id = $_POST["user_id"];

    $onVacation = false;
    foreach ($vacation_items as $item) {
        if ($item['username'] === $username && $item['date'] <= $day && $item['enddate'] >= $day) {
            $onVacation = true;
            break;
        }
    }

    if ($onVacation) {
        // User is on vacation, set error message
        $errorMessage = "Selected user is on vacation on the specified date. Please choose a different date or user.";
    } else {
        // Proceed with inserting the agenda item
        $task = $_POST["task"];
        $startinghour = $_POST["startinghour"];
        $endhour = $_POST["endhour"];
        
        insertAgendaItem($pdo, $user_id, $username, $task, $startinghour, $endhour, $day);
        header("Location: ". htmlspecialchars($_SERVER["PHP_SELF"]));
        exit;
    }
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
    <!-- Foutmelding weergeven -->
    <?php if(isset($errorMessage) && !empty($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <h1>Weekly View</h1>
    <a href="monthly_view_agenda.php">Monthly View</a>
    <a href="dailyvieuw_agenda.php">Daily View</a>
    <a href="year_view_agenda.php">Year View</a>

    <div class="screen">
        <h1>Hourly Agenda</h1>
        <div class="navigation">
            <form action="" method="post">
                <input type="submit" name="prev_week" value="Previous Week">
                <input type="submit" name="next_week" value="Next Week">
            </form>
        </div>
        <div class="agenda">
            <div class="hour">
                <div class="hour-block">
                    <p>6:00 - 7:00</p>
                </div>
                <?php
                    for ($hour = 7; $hour <= 19; $hour++) {
                        echo "<div class='hour-block'>";
                        echo "<p>$hour:00 - " . ($hour + 1) . ":00</p>";
                        echo "</div>";
                    }
                ?>
            </div>
            <?php
                $startOfWeek = date('Y-m-d', strtotime('monday this week'));

                if (isset($_POST['prev_week'])) {
                    $startOfWeek = date('Y-m-d', strtotime($startOfWeek . ' -1 week'));
                } elseif (isset($_POST['next_week'])) {
                    $startOfWeek = date('Y-m-d', strtotime($startOfWeek . ' +1 week'));
                }

                $endOfWeek = date('Y-m-d', strtotime($startOfWeek . ' +6 days'));

                $currentDate = $startOfWeek;
                while ($currentDate <= $endOfWeek) {
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
                    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
                }
            ?>
        </div>
    </div>

  
    <hr />
    <div class="row">
        <div class="col-xs-6">

        </div>
        <?php if($isAdmin || $isManager): ?>

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
    </div>
</body>
</html>
