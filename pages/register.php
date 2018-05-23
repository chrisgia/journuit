<!DOCTYPE html>
<html>
	<head>
		<?php require $_SERVER['DOCUMENT_ROOT']."/include/header.php"; ?>
		<title>Journuit - Registrierung</title>
	</head>

	<body>
		<?php require $_SERVER['DOCUMENT_ROOT']."/include/navbar.php";?>
		<div class="uk-container uk-container-large">
			<div class="uk-flex uk-flex-column uk-flex-center uk-flex-middle">
				<div>
					<span id="journuit_big">
						<span id="white">jour</span><span id="black">nuit</span> <img data-src="http://landausflugsplaner.de/pictures/journuit-logo_big.png" alt="journuit Logo" uk-img>
					</span>
				</div>
				<div class="uk-margin-top uk-margin-bottom">
					<form>
					    <fieldset class="uk-fieldset">

					        <div class="uk-margin">
					            <div class="uk-inline">
						            <span class="uk-form-icon" uk-icon="icon: user"></span>
						            <input class="uk-input" type="text" placeholder="Vor- und Nachname...">
						        </div>
					        </div>

					        <div class="uk-margin">
					            <div class="uk-inline">
						            <span class="uk-form-icon" uk-icon="icon: mail"></span>
						            <input class="uk-input" type="text" placeholder="Email-Adresse...">
						        </div>
					        </div>

					        <div class="uk-margin">
					            <div class="uk-inline">
						            <span class="uk-form-icon" uk-icon="icon: lock"></span>
						            <input class="uk-input" type="password" placeholder="Passwort...">
						        </div>
					        </div>

					        <div class="uk-margin">
					        	<div class="uk-inline">
						            <span class="uk-form-icon" uk-icon="icon: lock"></span>
						            <input class="uk-input" type="password" placeholder="Passwortbestätigung...">
						        </div>
					        </div>
					    </fieldset>
					    <div class="uk-flex uk-flex-center uk-flex-middle">
					    	<button class="uk-button uk-button-default">Registrieren</button>
					    </div>
					</form>
				</div>
			</div>
		</div>
	</body>
</html>