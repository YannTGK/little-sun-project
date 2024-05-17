<?php
session_start();

// Controleer de rol van de ingelogde gebruiker
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$isManager = isset($_SESSION['role']) && $_SESSION['role'] === 'Manager';

if (!$isAdmin && !$isManager) {
    header("Location: index.php");
    exit;
}

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
// Haal de gebruikersnaam van de ingelogde gebruiker uit de sessie
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

$assigned_tasks = fetchAssignedTasks($pdo);

// Haal alle vakantie-items op
$query_vacation = "SELECT * FROM vacation WHERE accepted = 1";
$stmt_vacation = $pdo->prepare($query_vacation);
$stmt_vacation->execute();
$vacation_items = $stmt_vacation->fetchAll(PDO::FETCH_ASSOC);

// Haal de gebruikersnaam van de ingelogde gebruiker uit de sessie
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

$assigned_tasks = fetchAssignedTasks($pdo);

// Groepeer taken per gebruiker
$user_tasks = [];
foreach ($assigned_tasks as $task) {
    $user_tasks[$task['user_id']][] = $task['TaskType'];
}

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
        header("Location: filinagenda.php");
        exit;
    }
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
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Fill in Agenda</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Oxygen:400,700" rel="stylesheet">
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/agenda.css">
</head>
<body>
    <?php include_once(__DIR__ . "/classes/nav.php"); ?>

    <div class="screen">
        <div class="title">
            <h1>Fill in Agenda</h1>
            <a class="kruis" href="./calendar.php"></a>
        </div>

        <!-- Foutmelding weergeven -->
        <?php if(isset($errorMessage) && !empty($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <div class="agenda-form">
            <form class="form-a" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <select class="form-control" id="username" name="username">
                        <?php 
                        $printed_usernames = array();
                        foreach($assigned_tasks as $task): 
                            if (!in_array($task['username'], $printed_usernames)):
                                $printed_usernames[] = $task['username'];
                        ?>
                            <option value="<?php echo $task['username']; ?>" data-user-id="<?php echo $task['id']; ?>"><?php echo $task['username']; ?></option>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </select>
                </div>
                <input type="hidden" name="user_id" id="user_id" value="">
                <div class="form-group">
                    <label for="task">Task:</label>
                    <select class="form-control" id="task" name="task">
                        <!-- Tasks will be populated dynamically using JavaScript -->
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
                
                <div class="editLink">
                    <button type="submit" class="formButton">Save</button>
                </div>
            </form>
        </div>
    </div>

    </div>

    <script>
        const userTasks = <?php echo json_encode($user_tasks); ?>;

        document.getElementById('username').addEventListener('change', function() {
            const userId = this.options[this.selectedIndex].getAttribute('data-user-id');
            document.getElementById('user_id').value = userId;

            const taskDropdown = document.getElementById('task');
            taskDropdown.innerHTML = ''; // Leeg de bestaande opties

            if (userTasks[userId]) {
                userTasks[userId].forEach(task => {
                    const option = document.createElement('option');
                    option.value = task;
                    option.textContent = task;
                    taskDropdown.appendChild(option);
                });
            }
        });

        // Trigger de change event bij het laden van de pagina om de taken voor de standaard geselecteerde gebruiker weer te geven
        document.getElementById('username').dispatchEvent(new Event('change'));
    </script>
</body>
</html>

