<?php

include_once(__DIR__ . "/classes/personal.php");

if (!empty($_POST['delete_manager'])) {
    $DeleteManager = $_POST['delete_manager'];
    try {
        Hub::deleteById($DeleteManager);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}


if (!empty($_POST)) {
  try {
    $manager = new Manager();

    $manager->setName($_POST['name']);
    $manager->setEmail($_POST['email']);
    $manager->setRole($_POST['role']);

    $options = [
        'cost' => 12
    ];
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT, $options);

    $manager->setPassword($hashedPassword);

    $manager->setProfilePicture($_POST['profilePicture']);
    $manager->save();

  } catch (Exception $e) {
    $error = $e->getMessage();
  }
}
$allManagers = Manager::getAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>addManager</title>
</head>
<a style="color: red" href="./index.php">GO HOME</a>

<body>
  <?php if (isset($error)) : ?>
    <div><?php echo $error ?></div>
  <?php endif; ?>
  <form action="" method="post">
      <label for="name">Name</label>
      <input type="text" name="name" id="name" />
      <label for="email">Email</label>
      <input type="text" name="email" id="email" />
      <label for="password">Password</label>
      <input type="password" name="password" id="password" />
      <label for="profilePicture">Profile Picture</label>
      <input type="text" name="profilePicture" id="profilePicture" />
      <label for="role">Role</label> 
      <input type="text" name="role" id="role" />
      <input type="submit" value="Add Manager" />
    </form>

    <?php foreach ($allManagers as $manager) : ?>
        <div>
            <?php echo htmlspecialchars($manager['username']) . " " . htmlspecialchars($manager['email']); ?>
            <form action="" method="post" style="display: inline;">
                <input type="hidden" name="delete_hub" value="<?php echo $manager['id']; ?>">
                <input type="submit" value="Delete">
            </form>
        </div>
    <?php endforeach ?>
</body>

</html>
