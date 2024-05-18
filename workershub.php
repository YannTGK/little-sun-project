<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

include_once(__DIR__ . "/classes/personal.php");
include_once(__DIR__ . "/classes/allworkers.php");
include_once(__DIR__ . "/classes/createTaskType.php");
include_once(__DIR__ . "/classes/Db.php");

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];
    
    if (!empty($username) && !empty($new_password)) {
        // Check if user exists
        $db = new mysqli('localhost', 'root', 'root', 'littlesun');
        if ($db->connect_error) {
            die("Connection failed: " . $db->connect_error);
        }

        $stmt = $db->prepare("SELECT * FROM account WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "User does not exist.";
        } else {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
            // Update the password in the database
            $stmt = $db->prepare("UPDATE account SET password = ? WHERE username = ?");
            $stmt->bind_param('ss', $hashed_password, $username);
        
            if ($stmt->execute()) {
                echo "Password updated successfully.";
            } else {
                echo "Error updating password: " . $stmt->error;
            }
        }

        $stmt->close();
        $db->close();
    } else {
        echo "Username and new password must not be empty.";
    }
}

$allTasks = Task::getAll();

if (isset($_POST['add_task'])) {
    $username = $_POST['username'];
    $task_type = $_POST['task_type'];

    // Check if user exists
    $db = new mysqli('localhost', 'root', 'root', 'littlesun');
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    $stmt = $db->prepare("SELECT * FROM account WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "User does not exist.";
    } else {
        // Get user ID
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Insert task assignment into database
        $stmt = $db->prepare("INSERT INTO task_assignments (user_id, task_type) VALUES (?, ?)");
        $stmt->bind_param('is', $user_id, $task_type);

        if ($stmt->execute()) {
            echo "Task added successfully.";
        } else {
            echo "Error adding task: " . $stmt->error;
        }
    }

    $stmt->close();
    $db->close();
}

if (isset($_POST['delete_task'])) {
    // Your existing delete task logic
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
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            text-align: left;
            padding: 8px;
            border: none;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <?php include_once(__DIR__ . "/classes/nav.php"); ?>

    <div class="screen">
        <div class="title">
            <h1><?php echo $_SESSION['role'] ?? "Rol niet gevonden in sessie."; ?> employee information</h1>
            <a class="kruis" href="./index.php"></a>
        </div>
        <input type="text" id="usernameInput" placeholder="Filter on username...">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Profile picture</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Task(s)</th>
                        <th>Change Password</th>
                    </tr>
                </thead>
                <tbody id="workersTableBody">
                    <?php
                    $allWorkers = new AllWorkers();
                    $workersData = $allWorkers->fetchWorkers(); // Fetch all workers

                    $prevUsername = null;
                    $tasks = [];

                    foreach ($workersData as $worker) {
                        $currentUsername = $worker['username'];
                        
                        if ($currentUsername !== $prevUsername && $prevUsername !== null) {
                            // Output previous user's data
                            echo "<tr>";
                            echo "<td><img class='employeeImg' src='" . htmlspecialchars($prevWorker['profilepicture']) . "' alt='Profielfoto'></td>";
                            echo "<td>" . htmlspecialchars($prevWorker['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($prevWorker['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($prevWorker['role']) . "</td>";
                            echo "<td>";
                            echo "<select>";
                            foreach ($tasks as $task) {
                                echo "<option>" . htmlspecialchars($task) . "</option>";
                            }
                            echo "</select>";
                            echo "</td>";
                            echo "<td>
                                    <form method='post' onsubmit=\"return confirm('Are you sure you want to change the password?')\">
                                        <input type='hidden' name='username' value='" . htmlspecialchars($prevWorker['username']) . "'>
                                        <input type='password' name='new_password' placeholder='Enter new password' required>
                                        <input type='submit' name='submit' value='Change'>
                                    </form>
                                  </td>";
                            echo "</tr>";

                           
                            // Reset tasks array
                            $tasks = [];
                        }

                        // Add task to array if it's not
                        // already in the array
                        if (!in_array($worker['TaskType'], $tasks)) {
                            $tasks[] = $worker['TaskType'];
                        }

                        // Update previous username and worker
                        $prevUsername = $currentUsername;
                        $prevWorker = $worker;
                    }

                    // Output last user's data
                    if ($prevUsername !== null) {
                        echo "<tr>";
                        echo "<td><img class='employeeImg' src='" . htmlspecialchars($prevWorker['profilepicture']) . "' alt='Profielfoto'></td>";
                        echo "<td>" . htmlspecialchars($prevWorker['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($prevWorker['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($prevWorker['role']) . "</td>";
                        echo "<td>";
                        echo "<select>";
                        foreach ($tasks as $task) {
                            echo "<option>" . htmlspecialchars($task) . "</option>";
                        }
                        echo "</select>";
                        echo "</td>";
                        echo "<td>
                                <form method='post' onsubmit=\"return confirm('Are you sure you want to change the password?')\">
                                    <input type='hidden' name='username' value='" . htmlspecialchars($prevWorker['username']) . "'>
                                    <input type='password' name='new_password' placeholder='Enter new password' required>
                                    <input type='submit' name='submit' value='Change'>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div>
            <h2>Assign a task to a user</h2>
            <form method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <label for="task_type">TaskType:</label>
                <select id="task_type" name="task_type" required>
                    <?php foreach ($allTasks as $task) : ?>
                        <option value="<?php echo htmlspecialchars($task['TaskType']); ?>"><?php echo htmlspecialchars($task['TaskType']); ?></option>
                    <?php endforeach ?>
                </select>
                <input type="submit" name="add_task" value="Assign">
            </form>
        </div>

        <div>
            <h2>Delete a user task</h2>
            <form method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <label for="task_type">TaskType:</label>
                <select id="task_type" name="task_type" required>
                    <?php foreach ($allTasks as $task) : ?>
                        <option value="<?php echo htmlspecialchars($task['TaskType']); ?>"><?php echo htmlspecialchars($task['TaskType']); ?></option>
                    <?php endforeach ?>
                </select>
                <input type="submit" name="delete_task" value="Delete">
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const usernameInput = document.getElementById('usernameInput');
            const tableBody = document.getElementById('workersTableBody');
            const tableRows = tableBody.getElementsByTagName('tr');

            usernameInput.addEventListener('input', function() {
                const filter = this.value.toLowerCase();

                for (let i = 0; i < tableRows.length; i++) {
                    const usernameCell = tableRows[i].getElementsByTagName('td')[1];
                    if (usernameCell) {
                        const username = usernameCell.textContent || usernameCell.innerText;
                        if (username.toLowerCase().indexOf(filter) > -1) {
                            tableRows[i].style.display = "";
                        } else {
                            tableRows[i].style.display = "none";
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
