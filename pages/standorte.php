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

    if(isset($_GET['id'])){
        $standortId = htmlspecialchars($_GET['id']);
        if(!isset($_GET['view'])){
            $view = "standort";
        }
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
        <title>journuit - Orte</title>
    </head>

    <body class="uk-height-viewport">
        <?php require $_SERVER['DOCUMENT_ROOT']."/include/navbar.php";?>
        <div class="uk-container uk-container-large">
            <?php
            if(isset($standortId) && !empty($standortId)){
                $selectStandort = $db->prepare("SELECT name, beschreibung, standorte.lat, standorte.lon, bild_id, bilder.file_ext FROM standorte LEFT JOIN bilder ON (standorte.bild_id = bilder.id) WHERE standorte.id = ? AND standorte.users_id = ?");
                $selectStandort->execute(array($standortId, $userId));
                $standort = $selectStandort->fetchAll(\PDO::FETCH_ASSOC);

                // Ist der Standort nicht vorhanden oder von einem anderen Benutzer, wird man zum default case weitergeleitet (nicht vorhandene Seite)
                if(empty($standort)){
                    $view = 'not_available';
                }
            }
            switch ($view) {
                case 'meine':
                ?>
                <div class="uk-flex uk-flex-column uk-flex-center uk-margin-top">
                <?php
                // Fügt die Standorte des Benutzers in ein Array
                $selectStandorte = $db->prepare("SELECT id, users_id, name, beschreibung FROM standorte WHERE users_id = ? ORDER BY name");
                $selectStandorte->execute(array($userId));
                $standorte = $selectStandorte->fetchAll(\PDO::FETCH_ASSOC);
                if(!empty($standorte)){
                    ?>
                    <div class="uk-text-center uk-margin-medium-bottom">
                        <span class="uk-h1">Meine Orte (<?=sizeOf($standorte);?>)</span>
                    </div>

                    <div class="uk-text-center uk-margin-medium-bottom">
                        <a href="standorte.php?view=neu" class="uk-button-text"><i uk-icon="plus"></i></button>
                    </div>
                    <?php
                    foreach($standorte as $standort){
                        $standortBeschreibung = 'Keine Beschreibung';
                        if($standort['beschreibung'] != ''){
                            $standortBeschreibung = $standort['beschreibung'];
                        }
                        ?>
                        <div class="uk-text-center uk-margin-small-bottom">
                            <span><a class="uk-link-text" href="standorte.php?id=<?=$standort['id'];?>"><?=$standort['name'];?> (<i><?=$standortBeschreibung?></i>)</a> <a class="uk-icon-link" uk-icon="icon: file-edit" href="standorte.php?view=bearbeiten&id=<?=$standort['id'];?>"></a></span>
                        </div>
                    <?php
                    }
                } else {
                    ?>
                    <div class="uk-margin-top uk-text-center">
                        <span>Sie haben noch keinen Standort erstellt.</span><br/>
                        <button class="uk-button uk-button-text uk-text-uppercase"><a class="uk-heading uk-link-reset" href="standorte.php?view=neu">Neuer Standort erstellen</a></button>
                    </div>
                <?php
                }
                ?>
                </div>
                <?php
                break;

                case 'neu':
                    ?>
                    <div class="uk-flex uk-flex-column uk-flex-center uk-flex-middle uk-margin-top"> 
                    <?php
                    require '../include/neuerStandort.php';
                    ?>
                    </div>
                    <?php
                break;

                case 'bearbeiten':
                    if(!empty($standort)){
                    ?>
                    <div class="uk-flex uk-flex-column uk-flex-center uk-flex-middle">
                        <h2 class="uk-text-center uk-margin-top"><?=$standort[0]['name'];?></h2>
                        <hr class="uk-width-1-1">

                        <form id="standort-bearbeiten" method="POST" action="#">
                            <fieldset class="uk-fieldset">

                                <div class="uk-margin uk-text-center">
                                    <label class="uk-form-label">Standorteingabe</label>
                                    <div class="uk-form-controls">
                                        <div class="uk-inline">
                                            <span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: location"></span>
                                            <input type="text" class="uk-input uk-form-width-large" id="locationInput" placeholder="Standort"/>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <span class="uk-text-small uk-text-lead">Latitude</span>
                                    <span class="uk-text-small uk-text-lead uk-float-right">Longitude</span>
                                </div>

                                <div class="uk-margin-bottom">
                                    <input type="text" class="uk-input uk-form-width-small" id="lat" name="lat" placeholder="Latitude" value="<?=$standort[0]['lat'];?>"/>
                                    <input type="text" class="uk-input uk-form-width-small uk-float-right" id="lon" name="lon" placeholder="Longitude" value="<?=$standort[0]['lon'];?>"/>
                                </div>

                                <!-- Standort von einem Bild erkennen -->
                                <div class="uk-margin">
                                    <div id="standortVonBild" class="uk-placeholder uk-text-center">
                                        <span uk-icon="icon: location"></span>
                                        <span class="uk-text-middle">Standort per Bild (via Drag & Drop oder </span>
                                        <div uk-form-custom>
                                            <input type="file" name="files">
                                            <!-- Dateigröße auf 5MB limitieren -->
                                            <input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
                                            <span class="uk-link">direkter Auswahl</span>)
                                        </div>
                                    </div>
                                    <progress id="js-progressbar" class="uk-progress" value="0" max="100" hidden></progress>
                                </div>

                                <div id="pickerMap"></div>

                                <div class="uk-margin uk-text-center">
                                    <div class="uk-inline">
                                        <span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: quote-right"></span>
                                        <input type="text" class="uk-input uk-form-width-large" id="standortname" name="name" placeholder="Standortname..." value="<?=$standort[0]['name'];?>" required/>
                                    </div>
                                </div>

                                <div class="uk-margin">
                                    <textarea name="beschreibung" id="standortBeschreibung" class="uk-textarea" rows="5" type="text" placeholder="Beschreibung..."><?=$standort[0]['beschreibung'];?></textarea>
                                </div>

                                <!-- Bild für den Standort anlegen -->
                                <div class="uk-margin">
                                    <div id="standortBildUpload" class="uk-placeholder uk-text-center">
                                        <span uk-icon="icon: cloud-upload"></span>
                                        <span class="uk-text-middle">Standortbild ersetzen (per Drag & Drop oder </span>
                                        <div uk-form-custom>
                                            <input type="file" name="files">
                                            <!-- Dateigröße auf 5MB limitieren -->
                                            <input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
                                            <span class="uk-link">direkter Auswahl</span>)
                                        </div>
                                    </div>
                                    <progress id="js-progressbar2" class="uk-progress" value="0" max="100" hidden></progress>
                                </div>

                                <div id="loading1" class="uk-text-center" hidden>
                                    <div uk-spinner></div>
                                    <span>Das Bild wird verarbeitet...</span>
                                </div>

                                <input id="pictureId" name="pictureId" type="hidden" value="">
                                <input id="file_ext" name="file_ext" type="hidden" value="">

                                <div id="standortBild" class="uk-margin uk-text-center">
                                    <?php
                                    $pictureId = '';
                                    $file_ext = '';
                                    if(!empty($standort[0]['bild_id'])){
                                        $pictureId = $standort[0]['bild_id'];
                                        $file_ext = $standort[0]['file_ext'];
                                        echo '<img class="standortbild" src="/users/'.$username.'/'.$pictureId.'.'.$file_ext.'">';
                                    } else {
                                        echo '<img class="standortbild" src="/pictures/no-picture_small.jpg">';
                                    } 
                                    ?>
                                </div>

                                <div id="standortErrors">
                                    <!-- Hier erscheinen die Fehler beim Erstellen eines Standortes -->
                                </div>

                                <div class="uk-margin">
                                    <button type="button" id="deleteStandort<?=$standortId;?>" class="uk-icon-link delete" uk-icon="icon: trash;"><span class="red uk-text-uppercase">Standort löschen</span></button>
                                </div>

                            </fieldset>
                            <div class="uk-flex uk-flex-center uk-flex-middle">
                                <button type="submit" class="uk-button uk-button-default" id="save" name="save">Speichern</button>
                            </div>
                        </form>
                        <hr class="uk-width-1-1">
                        <?php
                        } else {
                            ?>
                            <div class="uk-margin-top uk-alert-danger" uk-alert>
                                <p>Dieser Standort ist nicht vorhanden.</p>
                            </div>
                            <?php
                        }
                    ?>
                    </div>
                <?php
                // Formularverarbeitung 
                if(isset($_POST['save'], $_POST['name'], $_POST['lat'], $_POST['lon'])){
                    $updateQuery = '';
                    $errors = array();

                    if (ctype_space(htmlspecialchars($_POST['name'])) || empty($_POST['name'])) {
                        array_push($errors, 'Der Titel darf nicht leer sein.');
                    }

                    if (ctype_space(htmlspecialchars($_POST['lat'])) || empty($_POST['lat'])) {
                        array_push($errors, 'Es wurde kein Standort für diese Werte gefunden.');
                    }


                    if (ctype_space(htmlspecialchars($_POST['lon'])) || empty($_POST['lon'])) {
                        array_push($errors, 'Es wurde kein Standort für diese Werte gefunden.');
                    }


                    $updateArray = array(
                        htmlspecialchars($_POST['name']), 
                        htmlspecialchars($_POST['beschreibung']),
                        htmlspecialchars($_POST['lat']),
                        htmlspecialchars($_POST['lon'])
                    );

                    if(isset($_POST['pictureId']) && $_POST['file_ext'] && !empty($_POST['pictureId']) && !empty($_POST['file_ext']) && empty($errors)){
                        if(!empty($pictureId) && !empty($file_ext)){
                            // Ersetzen des vorherigen Bildes
                            $pictureError = updateBild($db, $username, $pictureId, htmlspecialchars($_POST['pictureId']), $file_ext, htmlspecialchars($_POST['file_ext']));
                        } else {
                            // War noch kein Bild vorhanden, wird es eingefügt
                            $pictureError = insertBild($db, $username, htmlspecialchars($_POST['pictureId']), htmlspecialchars($_POST['file_ext']));
                        }

                        if(!$pictureError){
                            array_push($errors, 'Das Bild konnte nicht ersetzt werden.');
                        } else {
                            $updateQuery .= ', bild_id = ?';
                            array_push($updateArray, htmlspecialchars($_POST['pictureId']));
                        }
                    }

                    array_push($updateArray, $standortId);

                    if(empty($errors)){
                        $updateStandort = $db->prepare("UPDATE standorte SET name = ?, beschreibung = ?, lat = ?, lon = ?".$updateQuery." WHERE id = ?");
                        $updateStandort->execute($updateArray);
                        echo "<script>window.location.href = 'standorte.php?id=".$standortId."&standortErfolgreich=true';</script>";
                    } else {
                        echo "<div class=\"uk-text-center\">";
                            echo "<ul>";
                            foreach($errors as $error){
                                echo "<li>".$error."</li>";
                            }
                            echo "</ul>";
                        echo "</div>";
                    }
                }
                break;

                case 'standort': 
                ?>
                <div class="uk-flex uk-flex-center uk-flex-column uk-flex-middle">
                    <div class="uk-margin-top uk-margin-bottom">
                        <div class="uk-text-center uk-text-lead uk-h2"><?=$standort[0]['name'];?></div>
                        <div class="uk-text-center uk-text-lead"><?=$standort[0]['beschreibung'];?></div>

                        <div id="standortbild" class="uk-margin uk-text-center">
                            <?php 
                            if(!empty($standort[0]['bild_id'])){
                                echo '<img data-src="../users/'.$username.'/'.$standort[0]['bild_id'].'.'.$standort[0]['file_ext'].'" uk-img class="uk-border-rounded">'; 
                            } else {
                                echo '<img class="uk-border-rounded" data-src="/pictures/no-picture_small.jpg" uk-img>';
                            } 
                            ?>
                        </div>

                        <input type="hidden" id="lat" value="<?=$standort[0]['lat'];?>">
                        <input type="hidden" id="lon" value="<?=$standort[0]['lon'];?>">
                        <input type="hidden" id="locationInput" value="">  

                        <div id="pickerMap"></div>         
                    </div>
                </div>
                <?php
                break;

                default:
                    require '../include/unavailable.php';
                break;
            }
            ?>
            </div>  
        </div>
        <script>
            var username = "<?php echo $username; ?>";
            var latitude = 49.609283;
            var longitude = 6.551267;

            // Wenn ein vorhandener Standort angezeigt wird, nimmt die Map die lat und lon Werte an. Default werden diese auf EURESA in Saarburg gestellt
            if($('#lat').val() != ''){
                latitude = $('#lat').val();
            }

            if($('#lon').val() != ''){
                longitude = $('#lon').val();
            }

            $('#pickerMap').locationpicker({
                location: {
                    latitude: latitude,
                    longitude: longitude
                },
                inputBinding: {
                    latitudeInput: $('#lat'),
                    longitudeInput: $('#lon'),
                    locationNameInput: $('#locationInput')
                },
                addressFormat: 'street_address',
                enableAutocomplete: true,
                radius: null,
                oninitialized: function (component) {
                    var fullAddress = $('#pickerMap').locationpicker('map').location.formattedAddress;
                    $('#locationInput').val(fullAddress);
                }
            });

            $('#standortErstellen').on('click', function(){
                $.ajax({
                    url : '/ajax/standorte_ajax.php',
                    type : 'POST',
                    data : {
                        standortname : $('#standortname').val(),
                        lat : $('#lat').val(),
                        lon : $('#lon').val(),
                        beschreibung : $('#standortBeschreibung').val(),
                        pictureId : $('#pictureId').val(),
                        file_ext : $('#file_ext').val()
                    },
                    success : function(response) {
                        var response = JSON.parse(response);
                        if(response.status == 'OK'){
                            $('#standortname').val('');
                            $('#standortBeschreibung').val('');
                            $('#pictureId').val('');
                            $('#file_ext').val('');
                            $('#standortBild').empty();
                            $('#standorte').empty().append(response.data);
                            window.location.href = 'standorte.php?view=meine&standortErfolgreich=true';
                        } else if(response.status == 'ERROR') {
                            $('#standortErrors').empty().append(response.data);
                        }
                    }
                });
            });

            $(document.body).on('click', '.delete', function(){
                var standortId = this.id.replace("deleteStandort", "");
                UIkit.modal.confirm('Wollen Sie diesen Standort wirklich löschen ?').then(function() {
                    $.ajax({
                        url : '/ajax/deleteStandort.php',
                        type : 'POST',
                        data : {
                            standortId: standortId
                        },
                        success : function(response) {
                            var response = JSON.parse(response);
                            if(response.status == 'OK'){
                                window.location.href="standorte.php?view=meine";
                            } else if(response.status == 'ERROR'){
                                UIkit.notification({message: 'Dieser Standort konnte nicht entfernt werden.', status: 'danger'});
                            }
                        }
                    });
                }, function() {
                    // Wenn der Benutzer auf "Cancel" drückt...
                });
            });
        </script>
        <script src="/js/standortScript.js"></script>
    </body>
</html>