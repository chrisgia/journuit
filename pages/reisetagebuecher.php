<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	if(isset($_GET["view"])) {
		$view = htmlspecialchars($_GET["view"]);
	} elseif(isset($_POST["view"])) {
		$view = htmlspecialchars($_POST["view"]);
	} else {
		$view = "meine-reisetagebuecher";
	}
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
			<div class="uk-flex uk-flex-column uk-flex-center uk-flex-middle">
				<?php 
				switch ($view) {
					case 'meine-reisetagebuecher':	
					// Fügt die Reisetagebücher des Benutzers in ein Array
					$id = $auth->getUserId();
	                $selectReisetagebuecher = $db->prepare("SELECT titel, beschreibung, url, public, erstellt_am, bild_id, bilder.file_ext FROM reisetagebuecher LEFT JOIN bilder ON (reisetagebuecher.bild_id = bilder.id) WHERE users_id = ?");
	                $selectReisetagebuecher->execute(array($id));
	                $reisetagebuecher = $selectReisetagebuecher->fetchAll(\PDO::FETCH_ASSOC);
				?>
				<div uk-grid>
					<div class="uk-child-width-1-4 uk-margin-top uk-margin-bottom uk-text-center uk-flex-middle" uk-grid>
						<?php
						foreach($reisetagebuecher as $reisetagebuch){
						?>
					    <div class="uk-width-auto">
					        <div class="uk-card uk-card-default uk-card-hover">
					            <div class="uk-card-media-top">
					            	<?php 
					            		if(!empty($reisetagebuch['bild_id'])){
					            			echo '<img src="/users/'.$username.'/'.$reisetagebuch['bild_id'].'.'.$reisetagebuch['file_ext'].'">';
					            		} else {
					            			echo '<img src="/pictures/default_picture.jpg">';
					            		} 
					            	?>
					            </div>
					            <div class="uk-card-body">
					            	<!-- Badge "öffentlich" wenn public gesetzt ist -->
					                <h3 class="uk-card-title"><?=$reisetagebuch['titel'];?></h3>
					                <p><?=$reisetagebuch['beschreibung'];?></p>
					            </div>
					        </div>
					    </div>
					    <?php
						}
					    ?>
					</div>
				</div>
				<?php 
				break;

				case 'neues-reisetagebuch':
				?>
				<div class="uk-margin-top uk-margin-bottom">
					<h1 class="uk-text-center">Neues Reisetagebuch</h1>
					<hr class="uk-width-1-1">

					<div id="titelbild" class="uk-margin uk-text-center">
			        	<!-- Hier erscheint das Titelbild sobald eins hochgeladen wird -->
			        </div>

					<form id="neues-reisetagebuch" method="POST">
					    <fieldset class="uk-fieldset">

					        <div class="uk-margin">
						        <input name="titel" class="uk-input" type="text" placeholder="Titel..." required>
					        </div>

					        <div class="uk-margin">
						        <input name="beschreibung" class="uk-input" type="text" placeholder="Beschreibung..." required>
					        </div>

					        <div class="uk-margin">
						    	<label>Öffentlich <input name="public" class="uk-checkbox" type="checkbox" value="1"></label>
					        </div>
					        
					        <div class="uk-margin">
						    	<div class="js-upload uk-placeholder uk-text-center">
						    		<span uk-icon="icon: cloud-upload"></span>
								    <span class="uk-text-middle">Titelbild hochladen (per Drag & Drop oder </span>
								    <div uk-form-custom>
								        <input type="file" name="files">
								        <span class="uk-link">direkter Auswahl</span>)
								    </div>
								</div>
								<progress id="js-progressbar" class="uk-progress" value="0" max="100" hidden></progress>
					        </div>

					        <input id="pictureId" name="pictureId" type="hidden" value="">
					        <input id="file_ext" name="file_ext" type="hidden" value="">

					    </fieldset>
					    <div class="uk-flex uk-flex-center uk-flex-middle">
					    	<button class="uk-button uk-button-default" name="create">Erstellen</button>
					    </div>
					</form>
					<hr class="uk-width-1-1">
				</div>
				<?php 
				// Formularverarbeitung 
				if(isset($_POST['create'])){
					$errors = array();
					if (ctype_space(htmlspecialchars($_POST['titel'])) || empty($_POST['titel'])) {
						array_push($errors, 'Der Titel darf nicht leer sein.');
					}

					if (ctype_space(htmlspecialchars($_POST['beschreibung'])) || empty($_POST['beschreibung'])) {
						array_push($errors, 'Die Beschreibung darf nicht leer sein.');
					}

					if (isset($_POST['public']) && ($_POST['public'] == "1")) {
						$public = 1;
					} else {
						$public = 0;
					}

					if($_POST['pictureId'] != "" && empty($errors)){
						if(!insertBild($db, $username, $_POST['pictureId'], $_POST['file_ext'])) {
							array_push($errors, 'Das Bild konnte nicht eingefügt werden.');
						}
					}

					if(empty($errors)){
						$userId = $auth->getUserId();
						$url = uniqueDbId($db, 'reisetagebuecher', 'url');
						$insertReisetagebuch = $db->prepare("INSERT INTO reisetagebuecher(users_id, titel, beschreibung, url, public, bild_id) VALUES(?, ?, ?, ?, ?, ?)");
						$insertReisetagebuch->execute(array(htmlspecialchars($userId), htmlspecialchars($_POST['titel']), htmlspecialchars($_POST['beschreibung']), $url, $public, htmlspecialchars($_POST['pictureId'])));
					} else {
						echo "<ul>";
						foreach($errors as $error){
							echo "<li>".$error."</li>";
						}
						echo "</ul>";
					}
				}
				break;
				}
			?>
			</div>
		</div>
		<?php 
			if(isset($_GET['login'])){echo "<script>UIkit.notification({message: 'Sie sind angemeldet.', status: 'success'});</script>";}
		?>
		<script>
			var createdFiles = [];
			var bar = document.getElementById('js-progressbar');
			var username = "<?php echo $userData[0]['username']; ?>";

			// Per Ajax wird beim Versenden des Formulars ein Skript aufgerufen der die Bilder die hochgeladen, aber im Endeffekt nicht benutzt wurden löscht.
			$(document).on('submit','form#neues-reisetagebuch', function(){
		    	if(createdFiles.length > 1){
		    		createdFiles = JSON.stringify(createdFiles);
				    $.ajax({
					  method: "POST",
					  url: '/include/cleanFolder.php',
					  data: { createdFiles : createdFiles, username : username }
					});
				}
			});

			// Skript zum uploaden von Bildern
		    UIkit.upload('.js-upload', {

		        url: '/include/upload.php',
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
		        },
		        complete: function () {
		        },

		        loadStart: function (e) {
		            bar.removeAttribute('hidden');
		            bar.max = e.total;
		            bar.value = e.loaded;
		        },

		        progress: function (e) {
		            bar.max = e.total;
		            bar.value = e.loaded;
		        },

		        loadEnd: function (e) {
		            bar.max = e.total;
		            bar.value = e.loaded;
		        },

		        completeAll: function (data) {
		            setTimeout(function () {
		                bar.setAttribute('hidden', 'hidden');
		            }, 1000);

		            console.log(data.response);
		            var infos = JSON.parse(data.response);
		            var fullPath = '../users/'+username+'/'+infos.pictureId+'.'+infos.file_ext;

		            createdFiles.push(infos.pictureId+'.'+infos.file_ext);

		            $('#pictureId').val(infos.pictureId);
		            $('#file_ext').val(infos.file_ext);
		            $('#titelbild').empty().append('<div uk-scrollspy="cls:uk-animation-fade"><img data-src="'+fullPath+'" uk-img></div>');
		            UIkit.notification({message: 'Ihr Titelbild wurde erfolgreich hochgeladen.', status: 'success'});
		        }
		    });
		</script>
	</body>
</html>