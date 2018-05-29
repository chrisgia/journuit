<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';
	session_start(); 

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
				?>
				<div class="uk-child-width-expand@s uk-text-center" uk-grid>
				    <div>
				        <div class="uk-card uk-card-default uk-card-body">Item</div>
				    </div>
				    <div>
				        <div class="uk-card uk-card-default uk-card-body">Item</div>
				    </div>
				    <div>
				        <div class="uk-card uk-card-default uk-card-body">Item</div>
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

					<form method="POST">
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
					        <input id="filename" name="filename" type="hidden" value="">

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
					if (ctype_space($_POST['titel']) || empty($_POST['titel'])) {
						array_push($errors, 'Der Titel darf nicht leer sein.');
					}

					if (ctype_space($_POST['beschreibung']) || empty($_POST['beschreibung'])) {
						array_push($errors, 'Die Beschreibung darf nicht leer sein.');
					}

					if (isset($_POST['public']) && ($_POST['public'] == "1")) {
						$public = 1;
					} else {
						$public = 0;
					}

					if(!insertBild($db, $username, $_POST['pictureId'], $_POST['filename'])) {
						array_push($errors, 'Das Bild konnte nicht eingefügt werden.');
					}

					if(empty($error)){
						$userId = $auth->getUserId();
						$url = uniqueDbId($db, 'reisetagebuecher', 'url');
						$insertReisetagebuch = $db->prepare("INSERT INTO reisetagebuecher(benutzer_id, titel, beschreibung, url, public, bild_id) VALUES(?, ?, ?, ?, ?, ?)");
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
			// Skript zum uploaden vom Titelbild

			var bar = document.getElementById('js-progressbar');
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

		            var username = "<?php echo $userData[0]['username']; ?>";
		            var infos = JSON.parse(data.response);
		            var fullPath = '../users/'+username+'/'+infos.file_name;

		            $('#pictureId').val(infos.pictureId);
		            $('#filename').val(infos.file_name);
		            $('#titelbild').empty().append('<div uk-scrollspy="cls:uk-animation-fade"><img data-src="'+fullPath+'" uk-img></div>');
		            UIkit.notification({message: 'Ihr Titelbild wurde erfolgreich hochgeladen.', status: 'success'});
		        }
		    });
		</script>
	</body>
</html>