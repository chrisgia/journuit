<?php 
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php';
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	if(isset($_GET["view"])) {
		$view = htmlspecialchars($_GET["view"]);
	}	
?>
<!DOCTYPE html>
<html>
	<head>
		<?php 
			require $_SERVER['DOCUMENT_ROOT']."/include/header.php"; 
		?>
		<title>journuit - Passwort vergessen</title>
	</head>

	<body class="uk-height-viewport">
		<?php require $_SERVER['DOCUMENT_ROOT']."/include/navbar.php";?>
		<div class="uk-container uk-container-large">
			<div class="uk-flex uk-flex-column uk-flex-center uk-flex-middle">
				<div class="uk-margin-top">
					<span id="journuit_big">
						<span class="white">jour</span><span class="black">nuit</span> <img data-src="http://landausflugsplaner.de/pictures/journuit-logo_big.png" alt="journuit Logo" uk-img>
					</span>
				</div>

			<?php
			switch ($view) {
				case 'enterEmail':
				?>	
				<p>Bitte geben Sie Ihre Email-Adresse ein. Es wird Ihnen dann ein Link gesendet, um Ihr Passwort zurückzusetzen.</p>
				<div class="uk-margin-top uk-margin-bottom">
					<form method="POST">
						<fieldset class="uk-fieldset">

							<div class="uk-margin">
								<div class="uk-inline">
									<span class="uk-form-icon" uk-icon="icon: mail"></span>
									<input name="email" class="uk-input" type="text" placeholder="Email-Adresse...">
								</div>
							</div>

						</fieldset>
						<div class="uk-flex uk-flex-center uk-flex-middle">
							<button class="uk-button uk-button-default" name="senden">Senden</button>
						</div>
					</form>
				</div>
				<?php
					if(isset($_POST['senden'])){
						$email = htmlspecialchars($_POST['email']);
				        $selectUserData = $db->prepare("SELECT vorname, nachname, users.username FROM users_data JOIN users ON (users_data.id = users.id) WHERE users.email = ?");
						$selectUserData->execute(array($email));
						$userData = $selectUserData->fetchAll(\PDO::FETCH_ASSOC);
						if(!empty($userData)){
							$username = $userData[0]['username'];
							$fullname = $userData[0]['vorname']." ".$userData[0]['nachname'];
						}
						$subject = 'journuit - Passwort zurückzusetzen';

						try {
						    $auth->forgotPassword($_POST['email'], function ($selector, $token) use ($username, $email, $fullname, $subject) {

						        $url = 'http://www.landausflugsplaner.de/pages/resetPassword.php?view=changePassword&selector='.\urlencode($selector).'&token='.\urlencode($token);
						        $message = 'Hallo '.$username.', <br/>bitte klicken Sie auf den folgenden Link um das Passwort für Ihr Konto bei journuit zurückzusetzen: <a href="'.$url.'">Passwort zurücksetzen</a>. <br/>Vielen Dank.';
						        sendMail($email, $fullname, $subject, $message, $copy = false, $attachments = NULL);
						    });
						}
						catch (\Delight\Auth\InvalidEmailException $e) {
						    $error = "Die Email-Adresse ist ungültig.";
						}
						catch (\Delight\Auth\TooManyRequestsException $e) {
						   $error = "Die maximale Anzahl an Anfragen wurde überschritten.";
						}

						if(!empty($error)){
							echo "<div class=\"uk-alert-danger\" uk-alert>";
							echo 	"<a class=\"uk-alert-close\" uk-close></a>";
							echo 	"<p>".$error."</p>";
							echo "</div>";
						} else {
							echo "<div class=\"uk-alert-success\" uk-alert>";
							echo 	"<a class=\"uk-alert-close\" uk-close></a>";
							echo 	"<p>Die Email wurde versendet.</p>";
							echo "</div>";
						}
					}

				break;

				case 'changePassword':
					if(isset($_GET['selector'], $_GET['token'])){
						$selector = htmlspecialchars($_GET['selector']);
						$token = htmlspecialchars($_GET['token']);

						if ($auth->canResetPassword($selector, $token)) {
						    ?>
						    <div class="uk-margin-top uk-margin-bottom">
							    <form id="passwordChange" method="POST">
									<fieldset class="uk-fieldset">

										<div class="uk-margin">
											<div class="uk-inline">
												<span class="uk-form-icon" uk-icon="icon: lock"></span>
												<input name="passwort" class="uk-input" type="password" placeholder="Neues Passwort...">
											</div>
										</div>

										<div class="uk-margin">
											<div class="uk-inline">
												<span class="uk-form-icon" uk-icon="icon: lock"></span>
												<input name="passwort_confirm" class="uk-input" type="password" placeholder="Passwort wiederholen...">
											</div>
										</div>

										<input type="hidden" name="selector" value="<?=$selector;?>">
										<input type="hidden" name="token" value="<?=$token;?>">
									</fieldset>
									<div class="uk-flex uk-flex-center uk-flex-middle">
										<button class="uk-button uk-button-default" name="changePassword">Passwort ändern</button>
									</div>
								</form>
							</div>
							<div class="uk-margin" id="error">
							</div>
						    <?php
						} else {
							echo "<div class=\"uk-alert-danger\" uk-alert>";
							echo 	"<a class=\"uk-alert-close\" uk-close></a>";
							echo 	"<p>Der Authorisierungstoken ist abgelaufen.</p>";
							echo "</div>";
						}
					} 

				break;

				default :
					require 'unavailable.php';
				break;
				}
				?>
			</div>
		</div>
		<script>
			<?php
			if(isset($_POST['changePassword'], $_POST['passwort'], $_POST['passwort_confirm'], $_POST['selector'], $_POST['token'])){
				?>
				$.ajax({
					url : 'resetPassword_ajax.php',
					type : 'POST',
					data : {
						passwort : '<?=$_POST['passwort'];?>',
						passwort_confirm : '<?=$_POST['passwort_confirm'];?>',
						selector : '<?=$selector;?>',
						token : '<?=$token;?>'
					},
					success : function(response) {
						if(response == 'OK'){
							console.log(response);
							window.location.href = 'passwordChanged.php';
						} else {
							console.log('fehler');
							$('#error').empty().append('<div class=\"uk-alert-danger\" uk-alert><a class=\"uk-alert-close\" uk-close></a><p>'+response+'</p>');
						}
					}
				});
			<?php
			}
			?>
		</script>
	</body>
</html>