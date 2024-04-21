<?php

include_once(__DIR__ . "/classes/add.php");


if (!empty($_POST['delete_hub'])) {
    $DeleteHub = $_POST['delete_hub'];
    try {

        Hub::deleteById($DeleteHub);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

if (!empty($_POST['hubname']) && !empty($_POST['hublocation'])) {
    try {
        $hub = new Hub();

        $hub->setHubname($_POST['hubname']);
        $hub->setHublocation($_POST['hublocation']);
        $hub->save();

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}


$allHubs = Hub::getAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>addHub</title>
    <link rel="stylesheet" href="styles/normalize.css">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/addlocation.css">
</head>


<body>
    <?php include_once(__DIR__ . "/classes/nav.php"); ?>

    <div class="screen">
        <div class="title">
            <h1>Add Hub Location</h1>
            <a class="kruis" href="./index.php"></a>
        </div>
        <?php if (isset($error)) : ?>
            <div><?php echo $error ?></div>
        <?php endif; ?>

        <div class="holder">

            <div class="left">
                <form action="" method="post" class="formHolder">
                    <div class="form">
                        <label for="hubname">Hubname</label>
                        <input type="text" name="hubname" id="hubname" />
                    </div>
                    <div class="form">
                        <label for="hublocation">Hublocation</label>
                        <input type="text" name="hublocation" id="hublocation" />
                    </div>
                    <span class="editLink">
                    <input type="submit" value="Add hub" class="formButton"/>
                    </span>
                </form>
                <div class="hubImg">
                    
                </div>

            </div>

            <form action="" method="post" class="formHolder">
                <div class="form">
                    <label for="hubname">Hubname</label>
                    <input type="text" name="hubname" id="hubname" />
                </div>
                <div class="form">
                    <label for="hublocation">Hublocation</label>
                    <input type="text" name="hublocation" id="hublocation" />
                </div>
                <span class="editLink">
                <input type="submit" value="Add hub" class="formButton"/>
                </span>
                
            </form>

            <?php foreach ($allHubs as $hub) : ?>
                <div>
                    <?php echo htmlspecialchars($hub['hubname']) . " " . htmlspecialchars($hub['hublocation']); ?>
                    <form action="" method="post" style="display: inline;">
                        <input type="hidden" name="delete_hub" value="<?php echo $hub['id']; ?>">
                        <input type="submit" value="Delete">
                    </form>
                </div>
            <?php endforeach ?>
        </div>
        
       

        
    </div>
   
</body>

</html>
