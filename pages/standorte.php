<div class="uk-flex uk-flex-column uk-flex-center uk-flex-middle">
	<div class="uk-margin-top uk-margin-bottom">
		<h1 class="uk-text-center">Neuer Standort</h1>
		<hr class="uk-width-1-1">

		<form id="neuer-standort" method="POST">
		    <fieldset class="uk-fieldset">

		        <div class="uk-margin">
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

		        <div id="pickerMap" style="width: 500px; height: 400px;"></div>

		        <div class="uk-margin">
		        	<div class="uk-inline">
						<span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: quote-right"></span>
		        		<input type="text" class="uk-input uk-form-width-large" id="standortname" name="standortname" placeholder="Standortname..." required/>
		        	</div>
		        </div>

		        <div class="uk-margin">
			        <textarea name="beschreibung" class="uk-textarea" rows="5" type="text" placeholder="Beschreibung..."></textarea>
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
		if (ctype_space(htmlspecialchars($_POST['standortname'])) || empty($_POST['standortname'])) {
			array_push($errors, 'Der Name darf nicht leer sein.');
		}

		if($_POST['pictureId'] != "" && empty($errors)){
			if(!insertBild($db, $username, $_POST['pictureId'], $_POST['file_ext'])) {
				array_push($errors, 'Das Bild konnte nicht eingefügt werden.');
			}
		}

		if(empty($errors)){
			$userId = $auth->getUserId();
			$insertStandort = $db->prepare("INSERT INTO standorte(users_id, name, beschreibung, lat, lon, bild_id) VALUES(?, ?, ?, ?, ?, ?)");
			$insertStandort->execute(array(htmlspecialchars($userId), htmlspecialchars($_POST['standortname']), htmlspecialchars($_POST['beschreibung']), htmlspecialchars($_POST['lat']), htmlspecialchars($_POST['lon']), htmlspecialchars($_POST['pictureId'])));
			echo "<script>window.location.replace('$redirect');</script>";
		} else {
			echo "<ul>";
			foreach($errors as $error){
				echo "<li>".$error."</li>";
			}
			echo "</ul>";
		}
	}
?>
</div>
		
<script>
	function updateInput(address){
        $('#locationInput').val(address);
	}
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
    var username = "<?php echo $username; ?>";

    // Skript zum uploaden vom Standortbild
    UIkit.upload('#standortBildUpload', {

        url: '/include/upload.php',
        multiple: false,
        mime: 'image/*',
        method: 'POST',
        params: {
        	width: 400,
        	height: 400
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
            $('#standortBild').empty().append('<div uk-scrollspy="cls:uk-animation-fade"><img data-src="'+fullPath+'" uk-img></div>');
            UIkit.notification({message: 'Ihr Standortbild wurde erfolgreich hochgeladen.', status: 'success'});
        }
    });
</script>