<div uk-sticky="sel-target: .uk-navbar-container; cls-active: uk-navbar-sticky">
	<nav class="uk-navbar-container uk-height-1-1" id="navbar" uk-navbar>
		<div class="uk-navbar-left">
			<?php 
				if(isset($_SESSION['auth_logged_in']) && $_SESSION['auth_logged_in'] == true) {
					$selectUserData = $db->prepare("SELECT vorname, nachname, users.username FROM users_data JOIN users ON (users_data.id = users.id) WHERE users_data.id = ?");
					$selectUserData->execute(array($userId));
					$userData = $selectUserData->fetchAll(\PDO::FETCH_ASSOC);
					$username = $userData[0]['username'];
					$fullname = $userData[0]['vorname']." ".$userData[0]['nachname'][0];
			?>
			<div class="uk-navbar-item">
				<ul class="uk-navbar-nav">
					<li class="nav_username">
						<a class="uk-link-heading" href="/">
							<span>
								<?php echo $username." (".$fullname.".)";?>
							</span>
						</a>
					</li>
					<li uk-tooltip="title: Abmelden; pos: bottom">
						<a href="/pages/logout.php" uk-icon="icon: sign-out"></a>
					</li>
				</ul>
			</div>
			<div class="uk-navbar-item">
				<img src="/pictures/dot.png" alt="dot" uk-img">
			</div>
			<div class="uk-navbar-item" uk-tooltip="title: Meine Reisetagebücher; pos: bottom">
				<ul class="uk-navbar-nav">
					<li><a class="nav_icon" href="/pages/reisetagebuecher.php?view=meine" uk-icon="icon: thumbnails; ratio: 1.5"></a></li>
				</ul>
			</div>
			<div class="uk-navbar-item" uk-tooltip="title: Meine Orte; pos: bottom">
				<ul class="uk-navbar-nav">
					<li><a class="nav_icon" href="/pages/standorte.php?view=meine" uk-icon="icon: location; ratio: 1.5"></a></li>
				</ul>
			</div>
			<div class="uk-navbar-item" uk-tooltip="title: Neues Reisetagebuch; pos: bottom">
				<ul class="uk-navbar-nav">
					<li><a class="nav_icon" href="/pages/reisetagebuecher.php?view=neu" uk-icon="icon: plus; ratio: 1.5"></a></li>
				</ul>
			</div>
			<?php } else {
			?>
			<div class="uk-navbar-item">
				<ul class="uk-navbar-nav">
					<li><a class="uk-link-heading" href="/pages/register.php"><span class="nav_link">REGISTRIEREN</span></a></li>
				</ul>
			</div>
			<div class="uk-navbar-item" id="dot">
				<img src="/pictures/dot.png" alt="dot" uk-img">
			</div>
			<div class="uk-navbar-item">
				<ul class="uk-navbar-nav">
				   <li><a class="uk-link-heading" href="/pages/login.php"><span class="nav_link">ANMELDEN</span></a></li>
				</ul>
			</div>
			<?php } ?>
		</div>
		<div class="uk-navbar-right">
			<a class="uk-navbar-item uk-logo" href="/"><span class="journuit"><span class="white">jour</span><span class="black">nuit</span></span> <img data-src="/pictures/journuit-logo_mini.png" alt="journuit Logo" uk-img></a>
		</div>
	</nav>
</div>