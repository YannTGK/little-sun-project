<?php

include_once(__DIR__ . "/classes/createTaskType.php");


if (!empty($_POST['delete_task'])) {
    $DeleteTask = $_POST['delete_task'];
    try {

        task::deleteById($DeleteTask);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if (!empty($_POST['task'])) {
    try {
        $task = new Task();

        $task->setTask($_POST['task']);
        $task->save();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}


$allTasks = Task::getAll();

?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>addTask</title>
    <link rel="stylesheet" href="styles/normalize.css">
  <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/tasks.css">
</head>


<body>
    <?php include_once(__DIR__ . "/classes/nav.php"); ?>

    <div class="screen">
        <div class="title">
            <h1>Add a task</h1>
            <a class="kruis" href="./calendar.php"></a>
        </div>
        <?php if (isset($error)) : ?>
            <div><?php echo $error ?></div>
        <?php endif; ?>

        <div class="holder">

            <div class="left">
                <form action="" method="post" class="formHolder">
                    <div class="form">
                        <label for="task">Task name</label>
                        <input type="text" name="task" id="task" />
                    </div>
                    <span class="editLink">
                        <input type="submit" value="Add TaskType" class="formButton"/>
                    </span>
                </form>

            </div>

            <div class="right">
                <div class="overflow">

                    <?php foreach ($allTasks as $task) : ?>
                        <div class="displayHubs">
                            <div class="info">
                                <h3>Task: <?php echo htmlspecialchars($task['TaskType']); ?></h3>
                            </div>
                           
                            <form action="" method="post">
                                <input type="hidden" name="delete_task" value="<?php echo $task['id']; ?>">
                                <span class="editLink">
                                    <input type="submit" value="Delete" class="formButton2">
                                </span>
                                
                            </form>
                        </div>
                    <?php endforeach ?>
                </div>

            </div>
        </div>
    </div>
</body>

</html>
