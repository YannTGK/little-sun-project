<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

$conn = new PDO('mysql:host=localhost;dbname=littlesun', "root", "root");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['reason']) && isset($_POST['date'])) {
        $reason = $_POST['reason'];
        $date = $_POST['date'];

        try {
            $user_id = $_SESSION['id'];
            $username = $_SESSION['username'];
            $role = $_SESSION['role']; 
            $accepted = ($role === 'Manager') ? 1 : 0; 

            $stmt = $conn->prepare("INSERT INTO vacation (user_id, username, reason, date, accepted) VALUES (:user_id, :username, :reason, :date, :accepted)");

            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':accepted', $accepted);

            $stmt->execute();

            echo "Data saved successfully!";
        } catch (PDOException $e) {
            echo "Error executing query: " . $e->getMessage();
        }
    } else {
        echo "Please fill in all required fields.";
    }
}

$query = "SELECT * FROM vacation WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['id']);
$stmt->execute();
$vacations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome <?php echo $_SESSION['username']; ?></title>
    <style>
        .accepted { color: green; }
        .rejected { color: red; }
    </style>
</head>
<body>
<h1>Welcome <?php echo $_SESSION['username']; ?></h1>
<a href="index.php">HOME</a>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <label for="reason">Reason:</label><br>
    <input type="text" id="reason" name="reason"><br>
    <label for="date">Date and Time:</label><br>
    <input type="datetime-local" id="date" name="date"><br><br>
    <input type="submit" value="Save">
</form>

<h2>Your vacations:</h2>
<ul>
    <?php foreach ($vacations as $vacation): ?>
        <?php
        $accepted = $vacation['accepted'];
        $status = ($accepted === null) ? "in treatment" : ($accepted ? "accepted" : "rejected");
        $class = ($accepted === null) ? "in-treatment" : ($accepted ? "accepted" : "rejected");
        ?>
        <li class="<?php echo $class; ?>"><?php echo $vacation['reason']; ?> on <?php echo $vacation['date']; ?> - <?php echo $status; ?></li>
    <?php endforeach; ?>
</ul>

</body>
</html>
