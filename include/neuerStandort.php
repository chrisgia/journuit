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
			<!-- Hier erscheint das Standortbild sobald eins hochgeladen wird -->
		</div>

		<div id="standortErrors">
			<!-- Hier erscheinen die Fehler beim Erstellen eines Standortes -->
		</div>

	</fieldset>
	<div class="uk-flex uk-flex-center uk-flex-middle">
		<button type="button" class="uk-button uk-button-default" id="standortErstellen">Erstellen</button>
	</div>
</form>
<hr class="uk-width-1-1">