<?php
    require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
    require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

    if(isset($_GET["view"])) {
        $view = htmlspecialchars($_GET["view"]);
    } elseif(isset($_POST["view"])) {
        $view = htmlspecialchars($_POST["view"]);
    }

    if(isset($_GET['datum'])){
        $view = "eintrag";
        $eintragsdatum = htmlspecialchars($_GET['datum']);
    }

    if(isset($_POST['rtbId'])){
        $rtbId = htmlspecialchars($_POST['rtbId']);
    } elseif(isset($_GET['rtbId'])){
        $rtbId = htmlspecialchars($_POST['rtbId']);
    }

    if(isset($_POST['rtb'])){
        $rtbUrl = htmlspecialchars($_POST['rtb']);
    } elseif(isset($_GET['rtb'])){
        $rtbUrl = htmlspecialchars($_GET['rtb']);
    }

    $onlyLogged = array('neuer-eintrag', 'bearbeiten');
    checkAuthorization($userId, $view, $onlyLogged);
?>
<!DOCTYPE html>
<html>
    <head>
        <?php 
            require $_SERVER['DOCUMENT_ROOT']."/include/header.php"; 
        ?>
        <title>journuit - Einträge</title>
    </head>

    <body class="uk-height-viewport">
        <?php require $_SERVER['DOCUMENT_ROOT']."/include/navbar.php";?>
        <div class="uk-container uk-container-large">
            <?php 
            
            if(isset($rtbUrl) && !empty($rtbUrl)){
                $rtbId = getRtbIdFromUrl($db, $rtbUrl);
                $selectRtbData = $db->prepare("SELECT titel, users.username FROM reisetagebuecher JOIN users ON (users_id = users.id) WHERE reisetagebuecher.id = ?");
                $selectRtbData->execute(array($rtbId));
                $rtbData = $selectRtbData->fetchAll(\PDO::FETCH_ASSOC);
                $rtbTitel = $rtbData[0]['titel'];
                $rtbCreator = $rtbData[0]['username'];
            }

            switch ($view) {
                case 'neuer-eintrag':
                    // Formularverarbeitung 
                    if(isset($_POST['create'], $_POST['standort'], $_POST['dateTime'], $_POST['titel'], $_POST['eintrag'])){
                        $errors = array();
                        if (ctype_space(htmlspecialchars($_POST['titel'])) || empty($_POST['titel'])) {
                            array_push($errors, 'Der Titel darf nicht leer sein.');
                        }

                        if (ctype_space(htmlspecialchars($_POST['eintrag'])) || empty($_POST['eintrag'])) {
                            array_push($errors, 'Der Eintrag darf nicht leer sein.');
                        }

                        if (isset($_POST['zusammenfassung']) && ($_POST['zusammenfassung'] == "1")) {
                            $zusammenfassung = 1;
                        } else {
                            $zusammenfassung = 0;
                        }

                        if (isset($_POST['public']) && ($_POST['public'] == "1")) {
                            $public = 1;
                        } else {
                            $public = 0;
                        }

                        if (isset($_POST['entwurf']) && ($_POST['entwurf'] == "1")) {
                            $entwurf = 1;
                        } else {
                            $entwurf = 0;
                        }

                        if($_POST['picture1Id'] != "" && empty($errors)){
                            if(!insertBild($db, $username, $_POST['picture1Id'], $_POST['file1_ext'])) {
                                array_push($errors, 'Das Bild konnte nicht eingefügt werden.');
                            }
                        }

                        if($_POST['picture2Id'] != "" && empty($errors)){
                            if(!insertBild($db, $username, $_POST['picture2Id'], $_POST['file2_ext'])) {
                                array_push($errors, 'Das Bild konnte nicht eingefügt werden.');
                            }
                        }

                        if($_POST['picture3Id'] != "" && empty($errors)){
                            if(!insertBild($db, $username, $_POST['picture3Id'], $_POST['file3_ext'])) {
                                array_push($errors, 'Das Bild konnte nicht eingefügt werden.');
                            }
                        }

                        $datum = substr(htmlspecialchars($_POST['dateTime']), 0, 10);
                        $uhrzeit = str_replace(':', '', substr(htmlspecialchars($_POST['dateTime']), 11, 5));

                        if(empty($errors)){
                            $insertEintrag = $db->prepare("INSERT INTO eintraege(reisetagebuch_id, titel, text, datum, uhrzeit, standort_id, entwurf, zusammenfassung, public) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $insertEintrag->execute(array($rtbId, htmlspecialchars($_POST['titel']), htmlspecialchars($_POST['eintrag']), $datum, $uhrzeit, htmlspecialchars($_POST['standort']), $entwurf, $zusammenfassung, $public));
                            echo "<script>window.location.href = 'reisetagebuecher.php?rtb=".$rtbUrl."&eintragErfolgreich=true';</script>";
                        } else {
                            echo "<ul>";
                            foreach($errors as $error){
                                echo "<li>".$error."</li>";
                            }
                            echo "</ul>";
                        }
                    } else {
                        // Gespeicherte Standorte des Benutzers 
                        $selectStandorte = $db->prepare("SELECT id, name FROM standorte WHERE users_id = ? ORDER BY name");
                        $selectStandorte->execute(array($userId));
                        $standorte = $selectStandorte->fetchAll(\PDO::FETCH_ASSOC);
                        if(isOwner($db, $userId, $rtbId)){
                            ?>
                            <div class="uk-margin-top uk-margin-bottom">
                                <h1 class="uk-text-center">Neuer Eintrag</h1>
                                <h2 class="uk-text-center uk-margin-remove-top"><?=$rtbTitel;?></h2>
                                <hr class="uk-width-1-1">

                                <!-- Modal um neue Standorte zu erstellen, geht auf wenn man "Neuer Standort" in der Selectbox auswählt -->
                                <div id="standorteModal" class="uk-flex-top" uk-modal>
                                    <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
                                        <button class="uk-modal-close-default" type="button" uk-close></button>
                                        <h2 class="uk-text-center">Neuer Standort</h2>
                                        <hr class="uk-width-1-1">

                                        <form id="neuer-standort" method="POST">
                                            <fieldset class="uk-fieldset">

                                                <div class="uk-margin uk-text-center">
                                                    <!-- Standorteingabe -->
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
                                                    <input type="text" class="uk-input uk-form-width-small" id="lat" name="lat" placeholder="Latitude"/>
                                                    <input type="text" class="uk-input uk-form-width-small uk-float-right" id="lon" name="lon" placeholder="Longitude"/>
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
                                                        <input type="text" class="uk-input uk-form-width-large" id="standortname" name="standortname" placeholder="Standortname..." required/>
                                                    </div>
                                                </div>

                                                <div class="uk-margin">
                                                    <textarea name="beschreibung" id="standortBeschreibung" class="uk-textarea" rows="5" type="text" placeholder="Beschreibung..."></textarea>
                                                </div>

                                                <!-- Bild für den Standort anlegen -->
                                                <div class="uk-margin">
                                                    <div id="standortBildUpload" class="uk-placeholder uk-text-center">
                                                        <span uk-icon="icon: cloud-upload"></span>
                                                        <span class="uk-text-middle">Standortbild (via Drag & Drop oder </span>
                                                        <div uk-form-custom>
                                                            <input type="file" name="files">
                                                            <!-- Dateigröße auf 5MB limitieren -->
                                                            <input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
                                                            <span class="uk-link">direkter Auswahl</span>)
                                                        </div>
                                                    </div>
                                                    <progress id="js-progressbar2" class="uk-progress" value="0" max="100" hidden></progress>
                                                </div>

                                                <input id="pictureId" name="pictureId" type="hidden" value="">
                                                <input id="file_ext" name="file_ext" type="hidden" value="">

                                                <div id="standortBild" class="uk-margin uk-text-center">
                                                    <!-- Hier erscheint das Standortbild sobald eins hochgeladen wird -->
                                                </div>

                                                <div id="errors">
                                                    <!-- Hier erscheinen die Fehler beim Erstellen eines Standortes -->
                                                </div>

                                            </fieldset>
                                            <div class="uk-flex uk-flex-center uk-flex-middle">
                                                <button type="button" class="uk-button uk-button-default" id="standortErstellen">Erstellen</button>
                                            </div>
                                        </form>
                                        <hr class="uk-width-1-1">
                                    </div>
                                </div>

                                <form id="neuer-eintrag" method="POST">
                                    <fieldset class="uk-fieldset">

                                        <div class="uk-margin">
                                            <label>Zusammenfassung <input id="zusammenfassung" name="zusammenfassung" class="uk-checkbox" type="checkbox" value="1"></label>
                                        </div>

                                        <div class="uk-margin">
                                            <!-- Selectbox mit den gespeicherten Standorten des Benutzers -->
                                            <select id="standorte" class="uk-select uk-form-width-medium" name="standort">
                                                <option value="default" selected>Standort auswählen</option>
                                                <option value="neuer-standort" class="uk-text-bold">Neuer Standort</option>
                                                <?php 
                                                foreach($standorte as $standort){
                                                    echo "<option value=\"".$standort['id']."\">".$standort['name']."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="uk-margin">
                                            <div class="uk-inline" id="dateInput">
                                                <span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: calendar"></span>
                                                <input type="text" name="dateTime" class="uk-input uk-form-width-medium flatpickr" placeholder="Datum & Uhrzeit" required>
                                            </div>
                                        </div>

                                        <div class="uk-margin">
                                            <input name="titel" class="uk-input" type="text" placeholder="Titel..." required>
                                        </div>

                                        <div class="uk-margin">
                                            <textarea name="eintrag" class="uk-textarea" rows="5" placeholder="Eintrag..." required></textarea>
                                        </div>

                                        <div class="uk-margin">
                                            <div id="eintragsBildUpload" class="js-upload uk-placeholder uk-text-center">
                                                <span uk-icon="icon: cloud-upload"></span>
                                                <span class="uk-text-middle">Bilder hochladen (max. 3, per Drag & Drop oder </span>
                                                <div uk-form-custom>
                                                    <input type="file" name="files">
                                                    <!-- Dateigröße auf 5MB limitieren -->
                                                    <input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
                                                    <span class="uk-link">direkter Auswahl</span>)
                                                </div>
                                            </div>
                                            <progress id="js-progressbar3" class="uk-progress" value="0" max="100" hidden></progress>
                                        </div>

                                        <div class="uk-margin">
                                            <label>Öffentlich <input name="public" class="uk-checkbox" type="checkbox" value="1"></label>
                                        </div>
                                        
                                        <div id="bilder" class="uk-margin uk-text-center">
                                        <!-- Hier erscheinen die hochgeladene Bilder-->
                                        </div>

                                        <input id="picture1Id" name="picture1Id" type="hidden" value="">
                                        <input id="file1_ext" name="file1_ext" type="hidden" value="">
                                        <input id="picture2Id" name="picture2Id" type="hidden" value="">
                                        <input id="file2_ext" name="file2_ext" type="hidden" value="">
                                        <input id="picture3Id" name="picture3Id" type="hidden" value="">
                                        <input id="file3_ext" name="file3_ext" type="hidden" value="">

                                        <input id="rtbUrl" name="rtb" type="hidden" value="<?=$rtbUrl;?>">

                                    </fieldset>
                                    <div class="uk-flex uk-flex-center uk-flex-middle">
                                        <button class="uk-button uk-button-default uk-margin-right" name="entwurf">Als Entwurf speichern</button>
                                        <button class="uk-button uk-button-default" name="create">Erstellen</button>
                                    </div>
                                </form>
                                <hr class="uk-width-1-1">
                            </div>
                        <?php
                        } else {
                            ?>
                            <div class="uk-margin-top uk-alert-danger" uk-alert>
                                <p>Dieses Reisetagebuch ist nicht vorhanden.</p>
                            </div>
                        <?php
                        }
                    }
                    break;

                case 'eintrag':
                    // Die Daten des Eintrags mit dem gegebenen Datum und rtbId ausgeben
                    $selectEintraege = $db->prepare("SELECT id, titel, text, uhrzeit, standort_id, zusammenfassung, public FROM eintraege WHERE reisetagebuch_id = ? AND datum = ? AND entwurf = 0 ORDER BY uhrzeit ASC");
                    $selectEintraege->execute(array($rtbId, $eintragsdatum));
                    $eintraege = $selectEintraege->fetchAll(\PDO::FETCH_ASSOC);
                    $formatiertesDatum = strftime("%e. %B %Y", strtotime($eintragsdatum));
                    ?>
                    <div class="uk-margin-top uk-width-auto">
                        <?php
                        if(isOwner($db, $userId, $rtbId)){
                            ?>
                            <div>
                                <div class="uk-text-center uk-text-lead" id="rtbTitel"><?=$rtbTitel;?> <span class="uk-text-small">von <?=$username;?></span></div>
                            </div>

                            <div class="uk-margin uk-text-center">
                                <span class="uk-h2"><?=$formatiertesDatum;?></span>
                            </div>
                            <hr class="uk-width-1-1">  
                            <?php 
                            foreach($eintraege as $eintrag) {
                                // Gespeicherte Standorte des Benutzers 
                                $selectStandort = $db->prepare("SELECT name FROM standorte WHERE id = ?");
                                $standortId = htmlspecialchars($eintrag['standort_id']);
                                $selectStandort->execute(array($standortId));
                                $standort = $selectStandort->fetchAll(\PDO::FETCH_ASSOC);
                                $standortName = $standort[0]['name'];

                                $uhrzeit = substr_replace($eintrag['uhrzeit'], ':', 2, 0);
                                ?>
                                <div class="uk-margin-top eintragHeader">
                                    <span class="uk-float-left">
                                        <?=$uhrzeit;?>, <span class="uk-text-lead"><?=$eintrag['titel'];?></span> 
                                        <?php if($eintrag['public'] == 1){
                                            echo "<i class=\"far fa-eye black\"></i>";
                                        } else {
                                            echo "<i class=\"far fa-eye-slash black\"></i>";
                                        }
                                        ?>
                                    </span>
                                    <span class="uk-float-right">
                                        <i uk-icon="icon: location; ratio: 1.5"></i> <i><?=$standortName;?></i>
                                        <a href="eintraege.php?id=<?=$eintrag['id'];?>" class="uk-icon-link uk-margin-left" uk-icon="icon: file-edit; ratio: 1.2"></a>
                                    </span>
                                </div>
                                <br/>
                                <div class="uk-margin-top eintragText">
                                    <p><?=$eintrag['text'];?></p>
                                </div>
                                <hr class="uk-width-1-1">
                                <?php
                            }

                        } else { ?>
                            
                            <div>
                                <div class="uk-text-center uk-text-lead" id="rtbTitel"><?=$rtbTitel;?> <span class="uk-text-small">von <?=$rtbCreator;?></span></div>
                            </div>

                            <div class="uk-margin uk-text-center">
                                <span class="uk-h2"><?=$formatiertesDatum;?></span>
                            </div>
                            <hr class="uk-width-1-1">  

                            <?php 
                            foreach($eintraege as $eintrag) {
                                if($eintrag['public'] == 1){
                                    // Gespeicherte Standorte des Benutzers 
                                    $selectStandort = $db->prepare("SELECT name FROM standorte WHERE id = ?");
                                    $standortId = htmlspecialchars($eintrag['standort_id']);
                                    $selectStandort->execute(array($standortId));
                                    $standort = $selectStandort->fetchAll(\PDO::FETCH_ASSOC);
                                    $standortName = $standort[0]['name'];

                                    $uhrzeit = substr_replace($eintrag['uhrzeit'], ':', 2, 0);
                                    ?>
                                    <div class="uk-margin-top eintragHeader">
                                        <span class="uk-float-left">
                                            <?=$uhrzeit;?>, <span class="uk-text-lead"><?=$eintrag['titel'];?></span> 
                                        </span>
                                        <span class="uk-float-right">
                                            <i uk-icon="icon: location; ratio: 1.5"></i> <i><?=$standortName;?></i>
                                        </span>
                                    </div>
                                    <br/>
                                    <div class="uk-margin-top eintragText">
                                        <p><?=$eintrag['text'];?></p>
                                    </div>
                                    <hr class="uk-width-1-1">
                                <?php
                                }
                            }
                        } 
                        ?>
                        </div>
                    
                <?php
            break;
        }
        ?>
        </div>
        <script>
            var username = "<?php echo $username; ?>";
            // Wenn der Eintrag eine Zusammenfassung ist, kann man weder Standort noch Datum eingeben
            $('#zusammenfassung').change(function(){
                if($(this).is(':checked')){
                    $('#standorte').hide();
                    $('#dateInput').hide();
                }
                else {
                    $('#standorte').show();
                    $('#dateInput').show();
                }    
            });

            // Einstellungen des Datepickers
            $(".flatpickr").flatpickr({
                enableTime: true,
                altInput: true,
                altFormat: "j. F Y H:i",
                dateFormat: "Y-m-d H:i",
                /*minTime: "16:00",
                maxTime: "22:00",*/
                time_24hr: true
            });

            $('#standorte').change(function () {
                var selectedOption = $(this).find("option:selected");
                var selectedValue = selectedOption.val();
                if(selectedValue == 'neuer-standort'){
                    $("select option").prop("selected", false);
                    UIkit.modal('#standorteModal').show();
                    // Einstellungen des Locationpickers
                    $('#pickerMap').locationpicker({
                        location: {
                            latitude: 49.21202227196742,
                            longitude: 6.856657780487012
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
                            updateInput(fullAddress);
                        }
                    });
                }
            });

            function updateInput(address){
                $('#locationInput').val(address);
            }

            var bar1 = document.getElementById('js-progressbar');

            // Skript zum ermitteln des Standorts von einem Bild
            UIkit.upload('#standortVonBild', {

                url: '/include/standortVonUpload.php',
                multiple: false,
                mime: 'image/*',
                method: 'POST',

                beforeSend: function () {
                },
                beforeAll: function () {
                },
                load: function () {
                },
                error: function () {
                    console.log('test');
                },
                complete: function () {
                },

                loadStart: function (e) {
                    bar1.removeAttribute('hidden');
                    bar1.max = e.total;
                    bar1.value = e.loaded;
                },

                progress: function (e) {
                    bar1.max = e.total;
                    bar1.value = e.loaded;
                },

                loadEnd: function (e) {
                    bar1.max = e.total;
                    bar1.value = e.loaded;
                },

                completeAll: function (data) {
                    setTimeout(function () {
                        bar1.setAttribute('hidden', 'hidden');
                    }, 1000);

                    var exifData = JSON.parse(data.response);
                    if('error' in exifData){
                        UIkit.notification({message: exifData.error, status: 'danger'});
                    } else {
                        $('#lat').val(exifData.lat);
                        $('#lon').val(exifData.lon);

                        $('#pickerMap').locationpicker({
                            inputBinding: {
                                latitudeInput: $('#lat'),
                                longitudeInput: $('#lon')
                            },
                            radius: null
                        });
                    }                   
                }
            });

            var bar2 = document.getElementById('js-progressbar2');

            // Skript zum uploaden vom Standortbild
            UIkit.upload('#standortBildUpload', {

                url: '/include/upload.php',
                multiple: false,
                mime: 'image/*',
                method: 'POST',
                params: {
                    width: 300,
                    height: 300
                },

                beforeSend: function () {
                },
                beforeAll: function () {
                },
                load: function () {
                },
                error: function () {
                    console.log('test');
                },
                complete: function () {
                },

                loadStart: function (e) {
                    bar2.removeAttribute('hidden');
                    bar2.max = e.total;
                    bar2.value = e.loaded;
                },

                progress: function (e) {
                    bar2.max = e.total;
                    bar2.value = e.loaded;
                },

                loadEnd: function (e) {
                    bar2.max = e.total;
                    bar2.value = e.loaded;
                },

                completeAll: function (data) {
                    setTimeout(function () {
                        bar2.setAttribute('hidden', 'hidden');
                    }, 1000);

                    var infos = JSON.parse(data.response);
                    var fullPath = '../users/'+username+'/tmp_'+infos.pictureId+'.'+infos.file_ext;

                    $('#pictureId').val(infos.pictureId);
                    $('#file_ext').val(infos.file_ext);
                    $('#standortBild').empty().append('<div class="uk-animation-fade"><img data-src="'+fullPath+'" uk-img></div>');
                    UIkit.notification({message: 'Ihr Standortbild wurde erfolgreich hochgeladen.', status: 'success'});
                }
            });

            $('#standortErstellen').on('click', function(){
                $('#errors').empty();
                $.ajax({
                    url : 'standorte_ajax.php',
                    type : 'POST',
                    data : {
                        standortname : $('#standortname').val(),
                        lat : $('#lat').val(),
                        lon : $('#lon').val(),
                        beschreibung : $('#standortBeschreibung').val(),
                        pictureId : ''
                    },
                    success : function(response) {
                        var response = JSON.parse(response);
                        if(response.status == 'OK'){
                            $('#standortname').val('');
                            $('#standortBeschreibung').val('');
                            $('#standortBild').empty();
                            $('#standorte').empty().append(response.data);
                            UIkit.notification({message: 'Ihr Standort wurde erfolgreich erstellt.', status: 'success'});
                            UIkit.modal('#standorteModal').hide();
                        } else if(response.status == 'ERROR') {
                            $('#errors').empty().append(response.data);
                        }
                    }
                });
            });

            var bar3 = document.getElementById('js-progressbar3');

            // Skript zum uploaden vom Standortbild
            UIkit.upload('#eintragsBildUpload', {

                url: '/include/upload.php',
                multiple: false,
                mime: 'image/*',
                method: 'POST',
                params: {
                    width: 300,
                    height: 300
                },

                beforeSend: function () {
                },
                beforeAll: function () {
                },
                load: function () {
                },
                error: function () {
                    console.log('test');
                },
                complete: function () {
                },

                loadStart: function (e) {
                    bar3.removeAttribute('hidden');
                    bar3.max = e.total;
                    bar3.value = e.loaded;
                },

                progress: function (e) {
                    bar3.max = e.total;
                    bar3.value = e.loaded;
                },

                loadEnd: function (e) {
                    bar3.max = e.total;
                    bar3.value = e.loaded;
                },

                completeAll: function (data) {
                    setTimeout(function () {
                        bar3.setAttribute('hidden', 'hidden');
                    }, 1000);

                    var infos = JSON.parse(data.response);
                    var fullPath = '../users/'+username+'/tmp_'+infos.pictureId+'.'+infos.file_ext;

                    $('#picture1Id').val(infos.pictureId);
                    $('#file1_ext').val(infos.file_ext);
                    $('#standortBild').empty().append('<div class="uk-animation-fade"><img data-src="'+fullPath+'" uk-img></div>');
                    UIkit.notification({message: 'Ihr Standortbild wurde erfolgreich hochgeladen.', status: 'success'});
                }
            });
        </script>
    </body>
</html> 