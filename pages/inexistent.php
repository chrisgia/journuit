<!DOCTYPE html>
<html>
	<head>
		<?php 
			require $_SERVER['DOCUMENT_ROOT']."/include/header.php"; 
		?>
		<title>journuit - Zugriff verweigert</title>
	</head>

	<body class="uk-height-viewport">
		<div class="uk-container uk-container-large">
			<div class="uk-flex uk-flex-column uk-flex-center uk-flex-middle">

				<div class="uk-margin-xlarge-top">
					<span id="journuit_big">
						<span class="white">jour</span><span class="black">nuit</span> <img data-src="/pictures/journuit-logo_big.png" alt="journuit Logo" uk-img>
					</span>
				</div>

				<div class="uk-margin-medium-top uk-text-center">
					<div class="uk-alert-danger" uk-alert>
						<p>Sie müssen <a href="login.php">sich anmelden</a>, um diese Seite aufrufen zu können.</p>
					</div>	
					<a href="/" class="uk-link">Zurück zur Startseite</a>
				</div>
			</div>
		</div>
	</body>
</html>