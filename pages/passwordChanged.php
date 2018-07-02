<?php require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; ?>
<!DOCTYPE html>
<html>
	<head>
		<?php 
			require $_SERVER['DOCUMENT_ROOT']."/include/header.php"; 
		?>
		<title>journuit - Passwort geändert</title>
	</head>

	<body class="uk-height-viewport">
		<div class="uk-container uk-container-large">
			<div class="uk-flex uk-flex-column uk-flex-center uk-flex-middle">

				<div class="uk-margin-xlarge-top">
					<span id="journuit_big">
						<span class="white">jour</span><span class="black">nuit</span> <img data-src="/pictures/journuit-logo_big.png" alt="journuit Logo" uk-img>
					</span>
				</div>

				<div class="uk-margin-medium-top">
					<div class="uk-alert-primary" uk-alert>
						<p>Sie werden in kürze umgeleitet...</p>
					</div>	

				</div>
			</div>
		</div>

		<script>
			UIkit.notification({message: 'Ihr Passwort wurde erfolgreich geändert.', status: 'success'});
			setInterval(function(){ window.location.replace('login.php'); }, 3000);
		</script>

	</body>
</html>