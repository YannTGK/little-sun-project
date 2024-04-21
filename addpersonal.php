<?php

include_once(__DIR__ . "/classes/personal.php");
include_once(__DIR__ . "/classes/add.php");

if (!empty($_POST['delete_personal'])) {
  $DeletePersonal = $_POST['delete_personal'];
  try {
    Personal::deleteById($DeletePersonal);
  } catch (Exception $e) {
    $error = $e->getMessage();
  }
}

if (!empty($_POST['update_password'])) {
  try {
    $personalId = $_POST['personal_id'];
    $newPassword = $_POST['new_password'];
    $personal = Personal::getById($personalId);
    if ($personal) {
      $options = [
        'cost' => 12
      ];
      $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT, $options);
      $personal->setPassword($hashedPassword);
      $personal->save();
    } else {
      throw new Exception("Personal not found.");
    }
  } catch (Exception $e) {
    $error = $e->getMessage();
  }
}

if (!empty($_POST['username']) && !empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['role']) && !empty($_POST['hubname'])) {
  try {
    $personal = new Personal();

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

    $personal->save();
  } catch (Exception $e) {
    $error = $e->getMessage();
  }
}

$allPersonals = Personal::getAll();
$allHubs = Hub::getAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>addPersonal</title>
  <link rel="stylesheet" href="styles/normalize.css">
  <link rel="stylesheet" href="styles/style.css">
  <link rel="stylesheet" href="styles/addPersonel.css">
</head>

<body>
  <?php include_once(__DIR__ . "/classes/nav.php"); ?>

  <div class="screen">
    <div class="title">
      <h1>Add Personnel</h1>
      <a class="kruis" href="./index.php"></a>
    </div>

    <div class="holder">
      
      <div class="left">
        <?php if (isset($error)) : ?>
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
                <option value="Personal">Personal</option>
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
          
          <span class="editLink">
            <input type="submit" value="Add Personal" class="formButton" />
          </span>
          
        </form>
      </div>

      <div class="right">
        <div class="updatePassword grouping">
          <h2>Update Password</h2>
          <form action="" method="post" class="formHolder">
            <div class="form">
              <label for="personal_id">Personal ID</label>
              <input type="text" name="personal_id" id="personal_id" />
            </div>

            <div class="form">
              <label for="new_password">New Password</label>
              <input type="password" name="new_password" id="new_password" />
            </div>
            <div class="editLink">
              <input type="submit" name="update_password" value="Update Password" class="formButton" />
            </div>
          </form>
        </div>
        
        <div class="allPersonel grouping">
          <!-- not enough space to place
            <h2>All Personals</h2>
          -->
          <?php foreach ($allPersonals as $personal) : ?>
            <div class="onePersonel">
              <div class="onePersonalInfo">
                <?php
                if (!empty($personal['profilePicture'])) {
                  echo '<img src="' . $personal['profilePicture'] . '" alt="Profile Picture" class="personalImg">';
                }
                ?>
                <span>
                  <?php echo "ID: " . $personal['id'] . " - " . htmlspecialchars($personal['username']) ?> <br><?php echo htmlspecialchars($personal['email']); ?>
                </span>
              </div>
              
              
              <form action="" method="post">
                <input type="hidden" name="delete_personal" value="<?php echo $personal['id']; ?>">
                <div class="editLink">
                  <input type="submit" value="Delete" class="formButton2">
                </div>
                
              </form>
            </div>
          <?php endforeach ?>
        </div>
        
      </div>
    </div>
    
    

    



  </div>


</body>

</html>
