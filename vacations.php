<?php
session_start();

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

try {
    if (isset($_POST['vacation_id']) && isset($_POST['reject_reason'])) {
        $vacation_id = $_POST['vacation_id'];
        $reject_reason = $_POST['reject_reason'];
        $query = "UPDATE vacation SET accepted = 0, rejectreason = :reject_reason WHERE vacation_id = :vacation_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':vacation_id', $vacation_id);
        $stmt->bindParam(':reject_reason', $reject_reason);
        if (!$stmt->execute()) {
            throw new Exception("Error updating vacation record: " . implode(" ", $stmt->errorInfo()));
        }
    } elseif (isset($_POST['vacation_id'])) {
        $vacation_id = $_POST['vacation_id'];
        $query = "UPDATE vacation SET accepted = 1 WHERE vacation_id = :vacation_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':vacation_id', $vacation_id);
        if (!$stmt->execute()) {
            throw new Exception("Error updating vacation record: " . implode(" ", $stmt->errorInfo()));
        }
    }

    $query = "SELECT vacation_id, user_id, username, reason, date, accepted FROM vacation";
    $stmt = $conn->query($query);
    $vacations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($vacations === false) {
        $vacations = [];
    }
} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage();
    $vacations = [];
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    $vacations = [];
}
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
                                <input type="hidden" name="vacation_id" value="<?php echo $vacation['vacation_id']; ?>">
                                <button class="accept-btn" type="submit">Accept</button>
                            </form>
                            <form method="post" action="">
                                <input type="hidden" name="vacation_id" value="<?php echo $vacation['vacation_id']; ?>">
                                <input type="text" name="reject_reason" placeholder="Enter reject reason" required>
                                <button class="rejected-btn" type="submit">Reject</button>
                            </form>
                        <?php elseif ($status === 'in treatment'): ?>
                            <form method="post" action="">
                                <input type="hidden" name="vacation_id" value="<?php echo $vacation['vacation_id']; ?>">
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
