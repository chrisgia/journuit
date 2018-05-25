<?php
    session_start();
    require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php';
    $id = $auth->getUserId(); 
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
?>
<nav class="uk-navbar-container uk-height-1-1" id="navbar" uk-navbar>
	<div class="uk-navbar-left">
        <?php 
            if(isset($_SESSION['auth_logged_in']) && $_SESSION['auth_logged_in'] == true) {
                $selectUserData = $db->prepare("SELECT * FROM users_data WHERE id = ?");
                $selectUserData->execute(array($id));
                $userData = $selectUserData->fetchAll(\PDO::FETCH_ASSOC);
        ?>
        <div class="uk-navbar-item">
            <?php echo $userData[0]['vorname']." ".$userData[0]['nachname']." (#".str_pad($id, 4, '0', STR_PAD_LEFT).")";?>
        </div>
        <div class="uk-navbar-item">
            <ul class="uk-navbar-nav">
                <li><a class="uk-link-heading" href="/pages/logout.php"><span class="nav_link">ABMELDEN</span></a></li>
            </ul>
        </div>
        <?php } else {
        ?>
		<div class="uk-navbar-item">
            <ul class="uk-navbar-nav">
                <li><a class="uk-link-heading" href="/pages/register.php"><span class="nav_link">REGISTRIEREN</span></a></li>
            </ul>
        </div>
        <div class="uk-navbar-item">
        	<img src="http://landausflugsplaner.de/pictures/dot.png" alt="dot" uk-img">
    	</div>
    	<div class="uk-navbar-item">
            <ul class="uk-navbar-nav">
        	   <li><a class="uk-link-heading" href="/pages/login.php"><span class="nav_link">ANMELDEN</span></a></li>
            </ul>
    	</div>
         <?php } ?>
    </div>
    <div class="uk-navbar-right">
        <a class="uk-navbar-item uk-logo" href="/"><span id="white">jour</span><span id="black">nuit</span> <img data-src="/pictures/journuit-logo_mini.png" alt="journuit Logo" uk-img></a>
    </div>
</nav>

<script>
    // Warum funktionniert das nicht ?
    $(document).ready(function() {
        console.log(location.pathname);
        console.log("test");
        $('a[href="http://landausflugsplaner.de' + location.pathname + '"]').addClass('active');
    });
</script>