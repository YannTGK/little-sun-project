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

    $personal->setProfilePicture($_POST['profilePicture']);

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
</head>

<body>
  <a style="color: red" href="./index.php">GO HOME</a>
  <?php if (isset($error)) : ?>
    <div><?php echo $error ?></div>
  <?php endif; ?>
  <form action="" method="post">
    <label for="username">Username</label>
    <input type="text" name="username" id="username" />
    <label for="email">Email</label>
    <input type="text" name="email" id="email" />
    <label for="password">Password</label>
    <input type="password" name="password" id="password" />
    <label for="profilePicture">Profile Picture</label>
    <input type="text" name="profilePicture" id="profilePicture" />
    <label for="role">Role</label>
    <select name="role" id="role">
      <option value="Manager">Manager</option>
      <option value="Personal">Personal</option>
    </select>
    <label for="hubname">Hubname</label>
    <select name="hubname" id="hubname">
      <?php foreach ($allHubs as $hub) : ?>
        <option value="<?php echo $hub['hubname']; ?>"><?php echo $hub['hubname']; ?></option>
      <?php endforeach; ?>
    </select>
    <input type="submit" value="Add Personal" />
  </form>

  <h2>Update Password</h2>
  <form action="" method="post">
    <label for="personal_id">Personal ID</label>
    <input type="text" name="personal_id" id="personal_id" />
    <label for="new_password">New Password</label>
    <input type="password" name="new_password" id="new_password" />
    <input type="submit" name="update_password" value="Update Password" />
  </form>

  <h2>All Personals</h2>
  <?php foreach ($allPersonals as $personal) : ?>
    <div>
      <?php echo "ID: " . $personal['id'] . " - " . htmlspecialchars($personal['username']) . " " . htmlspecialchars($personal['email']); ?>
      <form action="" method="post" style="display: inline;">
        <input type="hidden" name="delete_personal" value="<?php echo $personal['id']; ?>">
        <input type="submit" value="Delete">
      </form>
    </div>
  <?php endforeach ?>
</body>

</html>