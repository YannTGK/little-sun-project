<?php

include_once(__DIR__ . "/classes/addmanager.php");

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
      <input type="submit" value="Add Manager" />
    </form>

    <?php foreach ($allManagers as $manager) : ?>
  <div>
    <?php if (isset($manager['name']) && isset($manager['email'])) : ?>
      <?php echo htmlspecialchars($manager['name']) . " " . htmlspecialchars($manager['email']); ?>
    <?php endif; ?>
  </div>
<?php endforeach ?>
</body>

</html>
