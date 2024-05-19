<?php
include_once(__DIR__ . "/classes/personal.php");
include_once(__DIR__ . "/classes/add.php");
include_once(__DIR__ . "/classes/createTaskType.php");
include_once(__DIR__ . "/classes/assignedtask.php");

$error = '';


if (!empty($_POST['delete_personal'])) {
    $personalIdToDelete = $_POST['delete_personal'];
    try {
        personal::deleteById($personalIdToDelete);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if (!empty($_POST['username']) && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['role']) && !empty($_POST['hubname'])) {
    try {
        $personal = new personal();

        $personal->setName($_POST['username']);
        $personal->setEmail($_POST['email']);
        $personal->setRole($_POST['role']);

        $options = [
            'cost' => 12
        ];
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT, $options);
        $personal->setPassword($hashedPassword);

        if (!empty($_FILES['profilePictureFile']['name'])) {
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($_FILES['profilePictureFile']['name']);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            $check = getimagesize($_FILES['profilePictureFile']['tmp_name']);
            if ($check !== false) {
                $uploadOk = 1;
            } else {
                throw new Exception("File is not an image.");
            }

            if ($_FILES['profilePictureFile']['size'] > 500000) {
                throw new Exception("Sorry, your file is too large.");
            }

            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                throw new Exception("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
            }

            if ($uploadOk == 0) {
                throw new Exception("Sorry, your file was not uploaded.");
            } else {
                if (move_uploaded_file($_FILES['profilePictureFile']['tmp_name'], $targetFile)) {
                    $profilePicturePath = $targetFile;
                } else {
                    throw new Exception("Sorry, there was an error uploading your file.");
                }
            }

            $personal->setProfilePicture($profilePicturePath);
        }

        $personal->setHubname($_POST['hubname']);


        $userId = $personal->save();


        if (!empty($_POST['tasks'])) {
            $tasks = $_POST['tasks'];
            foreach ($tasks as $taskId) {
                $assignedTask = new AssignedTask();
                $assignedTask->setUsername($_POST['username']); 
                $assignedTask->setTaskType($taskId);
                $assignedTask->save();
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$allpersonals = personal::getAll();
$allHubs = Hub::getAll();
$allTasks = Task::getAll();
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>addpersonal</title>
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/addPersonel.css">
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this person?");
        }
    </script>
</head>

<body>
    <?php include_once(__DIR__ . "/classes/nav.php"); ?>

    <div class="screen">
        <div class="title">
            <h1>Add personal</h1>
            <a class="kruis" href="./index.php"></a>
        </div>

        <div class="holder">
            <div class="left">
                <?php if (!empty($error)) : ?>
                    <div><?php echo $error ?></div>
                <?php endif; ?>
                <form action="" method="post" enctype="multipart/form-data" class="formHolder">
                    <div class="form">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" />
                    </div>

                    <div class="form">
                        <label for="email">Email</label>
                        <input type="text" name="email" id="email" />
                    </div>

                    <div class="form">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" />
                    </div>

                    <div class="form">
                        <label for="profilePictureFile">Profile Picture</label>
                        <input type="file" name="profilePictureFile" id="profilePictureFile" />
                    </div>

                    <div class="selectGroup">
                        <div class="form">
                            <label for="role">Role</label>
                            <select name="role" id="role">
                                <option value="Manager">Manager</option>
                                <option value="personal">personal</option>
                            </select>
                        </div>

                        <div class="form">
                            <label for="hubname">Hubname</label>
                            <select name="hubname" id="hubname">
                                <?php foreach ($allHubs as $hub) : ?>
                                    <option value="<?php echo $hub['hubname']; ?>"><?php echo $hub['hubname']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="selectGroup">
                        <div class="tasktype">
                            <label>personal task(s)</label>
                            <div class="taskItems">
                                <?php foreach ($allTasks as $task) : ?>
                                    <div class="taskItem">
                                        <input type="checkbox" name="tasks[]" value="<?php echo $task['id']; ?>"> 
                                        <label><?php echo $task['TaskType']; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <span class="editLink">
                        <input type="submit" value="Add personal" class="formButton" />
                    </span>
                    <span class="editLink">
                    <input type="button" value="All personal" class="formButton" onclick="window.location.href = 'workershub.php';" />
                    </span>

                </form>
            </div>


            </div>
        </div>
    </div>
</body>

</html>
