<?php
    require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
    require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

    if(isset($_GET["view"])) {
        $view = htmlspecialchars($_GET["view"]);
    } elseif(isset($_POST["view"])) {
        $view = htmlspecialchars($_POST["view"]);
    } else {
        $view = "meine";
    }

    if(isset($_POST['rtbId'])){
        $rtbId = htmlspecialchars($_POST['rtbId']);
    } elseif(isset($_GET['rtbId'])){
        $rtbId = htmlspecialchars($_POST['rtbId']);
    }

    if(isset($_POST['rtb'])){
        $rtbUrl = htmlspecialchars($_POST['rtb']);
    } elseif(isset($_GET['rtb'])){
        $view = "reisetagebuch";
        $rtbUrl = htmlspecialchars($_GET['rtb']);
    }

    $onlyLogged = array('meine', 'neu', 'bearbeiten');
    checkAuthorization($userId, $view, $onlyLogged);

?>
<!DOCTYPE html>
<html>
    <head>
        <?php 
            require $_SERVER['DOCUMENT_ROOT']."/include/header.php"; 
        ?>
        <title>journuit - Reisetagebücher</title>
    </head>

    <body class="uk-height-viewport">
        <?php require $_SERVER['DOCUMENT_ROOT']."/include/navbar.php";?>
        <div class="uk-container uk-container-large">
            <?php
            switch ($view) {
                case 'meine':
                // Fügt die Reisetagebücher des Benutzers in ein Array
                $selectStandorte = $db->prepare("SELECT id, users_id, name, beschreibung, lat, lon, bild_id FROM standorte WHERE users_id = ?");
                $selectStandorte->execute(array($userId));
                $standorte = $selectStandorte->fetchAll(\PDO::FETCH_ASSOC);
                if(!empty($standorte)){
                    ?>
                    <div class="uk-child-width-1-3@l uk-child-width-1-1@s uk-margin-top uk-margin-bottom uk-text-center" uk-grid>
                    <?php
                    foreach($standorte as $standort){
                    ?>
                        <div>
                        <?php
                        echo "<div class=\"uk-card uk-card-default uk-card-hover uk-animation-toggle uk-height-large rtbCard\" onclick=\"document.location='standorte.php?view=bearbeiten&id=".$standort['id']."'\">"; ?>
                                <?php 
                                    if(!empty($reisetagebuch['bild_id'])){
                                        echo '<img class="standortbild" src="/users/'.$username.'/'.$reisetagebuch['bild_id'].'.'.$reisetagebuch['file_ext'].'">';
                                    } else {
                                        echo '<img class="standortbild" src="/pictures/no-picture.png">';
                                    } 
                                ?>
                                <div class="uk-overlay uk-overlay-default uk-position-bottom">
                                    <span class="uk-h2"><?=$standort['name'];?></span>
                                    <p><?=$standort['beschreibung'];?></p>
                                </div>   
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                    </div>
                <?php
                } else {
                    ?>
                    <div class="uk-margin-top uk-text-center">
                        <span>Sie haben noch kein Standort angelegt.</span><br/>
                        <button class="uk-button uk-button-text uk-text-uppercase"><a class="uk-heading uk-link-reset newRtbLink" href="reisetagebuecher.php?view=neu">Neuer Standort anlegen</a></button>
                    </div>
                <?php
                }
                break;
            }
            ?>  
        </div>
    </body>
</html>