<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

try {
    $conn = new PDO('mysql:host=localhost;dbname=littlesun', "root", "root");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Check if vacation_id is set
if (isset($_POST['user_id'])) {
    // Update accepted status
    $user_id = $_POST['user_id'];
    $query = "UPDATE vacation SET accepted = 1 WHERE user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
}

// Query to select specific columns instead of all
$query = "SELECT user_id, username, reason, date, accepted FROM vacation";
$stmt = $conn->query($query);
$vacations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Vacations</title>
    <style>
        .in-treatment { color: blue; }
        .accepted { color: green; }
        .rejected { color: red; }
    </style>
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/vacations.css">
</head>
<body>

    <?php include_once(__DIR__ . "/classes/nav.php"); ?>
    <div class="screen">
        <div class="title">
            <h1>Vacation requests</h1>
            <a class="kruis" href="./calendar.php"></a>
        </div>

        <div class="holder">
            <div class="left">
                <ul class="overflow">
                    <?php foreach ($vacations as $vacation): ?>
                        <?php
                        $accepted = $vacation['accepted'];
                        $status = ($accepted === null) ? "in treatment" : ($accepted ? "accepted" : "rejected");
                        $class = ($accepted === null) ? "in-treatment" : ($accepted ? "accepted" : "rejected");
                        ?>
                        <li class="rList <?php echo $class; ?>">
                            User ID: <?php echo htmlspecialchars($vacation['user_id']); ?>, 
                            Username: <?php echo htmlspecialchars($vacation['username']); ?> - 
                            <?php echo htmlspecialchars($vacation['reason']); ?> on <?php echo htmlspecialchars($vacation['date']); ?> - 
                            <?php echo htmlspecialchars($status); ?> 
                            <?php if ($status === 'rejected'): ?>
                                <form method="post" action="">
                                    <input type="hidden" name="user_id" value="<?php echo $vacation['user_id']; ?>">
                                    <button type="submit">Accept</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

</body>
</html>
