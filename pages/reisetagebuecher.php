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

	if(isset($_POST['rtbId'])){
        $rtbId = htmlspecialchars($_POST['rtbId']);
    } elseif(isset($_GET['rtbId'])){
        $rtbId = htmlspecialchars($_POST['rtbId']);
    }

    if(isset($_POST['rtb'])){
    	$view = "reisetagebuch";
        $rtbUrl = htmlspecialchars($_POST['rtb']);
    } elseif(isset($_GET['rtb'])){
    	$view = "reisetagebuch";
        $rtbUrl = htmlspecialchars($_GET['rtb']);
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
			
			<?php 
			if(isset($rtbUrl) && !empty($rtbUrl)){
				$rtbId = getRtbIdFromUrl($db, $rtbUrl);
	            $selectReisetagebuchDaten = $db->prepare("SELECT reisetagebuecher.id, users.username, titel, beschreibung, public, erstellt_am, bild_id, bilder.file_ext FROM reisetagebuecher LEFT JOIN bilder ON (reisetagebuecher.bild_id = bilder.id) JOIN users ON (users_id = users.id) WHERE reisetagebuecher.id = ?");
	            $selectReisetagebuchDaten->execute(array($rtbId));
	            $reisetagebuchDaten = $selectReisetagebuchDaten->fetchAll(\PDO::FETCH_ASSOC);
			}

			switch ($view) {
				case 'meine-reisetagebuecher':	
				// Fügt die Reisetagebücher des Benutzers in ein Array
                $selectReisetagebuecher = $db->prepare("SELECT titel, beschreibung, url, public, erstellt_am, bild_id, bilder.file_ext FROM reisetagebuecher LEFT JOIN bilder ON (reisetagebuecher.bild_id = bilder.id) WHERE users_id = ?");
                $selectReisetagebuecher->execute(array($userId));
                $reisetagebuecher = $selectReisetagebuecher->fetchAll(\PDO::FETCH_ASSOC);
                if(!empty($reisetagebuecher)){
					?>
					<div class="uk-child-width-1-3@l uk-child-width-1-1@s uk-margin-top uk-margin-bottom uk-text-center" uk-grid>
					<?php
					foreach($reisetagebuecher as $reisetagebuch){
					?>
					    <div>
				        <?php
				        echo "<div class=\"uk-card uk-card-default uk-card-hover uk-animation-toggle uk-height-large rtbCard\" onclick=\"document.location='reisetagebuecher.php?rtb=".$reisetagebuch['url']."'\">"; ?>
					        	<div class="uk-card-badge uk-label">
					        		<?php if($reisetagebuch['public'] == 1){
					        			echo "<i class=\"far fa-eye\"></i>";
					        		} else {
					        			echo "<i class=\"far fa-eye-slash\"></i>";
					        		}
					        		?>
					        	</div>
					            <?php 
				            		if(!empty($reisetagebuch['bild_id'])){
				            			echo '<img class="titelbild" src="/users/'.$username.'/'.$reisetagebuch['bild_id'].'.'.$reisetagebuch['file_ext'].'">';
				            		} else {
				            			echo '<img class="titelbild" src="/pictures/no-picture.png">';
				            		} 
				            	?>
					            <div class="uk-overlay uk-overlay-default uk-position-bottom">
					                <span class="uk-h2"><?=$reisetagebuch['titel'];?></span>
					                <p><?=$reisetagebuch['beschreibung'];?></p>
					                <span class="uk-text-small uk-float-right"><i>erstellt am <?=getMySqlDate($reisetagebuch['erstellt_am']);?></i></span><br/>
					            </div>   
					        </div>
					    </div>
				    <?php
					}
				    ?>
					</div>
				<?php
				} else {
					?>
					<div class="uk-margin-top uk-text-center">
						<span>Sie haben noch kein Reisetagebuch angelegt.</span><br/>
						<button class="uk-button uk-button-text uk-text-uppercase"><a class="uk-heading uk-link-reset newRtbLink" href="reisetagebuecher.php?view=neues-reisetagebuch">Neues Reisetagebuch anlegen</a></button>
					</div>
				<?php
				}

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
				        	<i><span id="char_count">25</span> verbleibend</i>
					        <input name="titel" id="titel" class="uk-input" type="text" placeholder="Titel (maximal 25 Zeichen)" onFocus="countChars('titel','char_count',25)" onKeyDown="countChars('titel','char_count',25)" onKeyUp="countChars('titel','char_count',25)" maxlength="25" required>
				        </div>

				        <div class="uk-margin">
					        <textarea name="beschreibung" class="uk-textarea" rows="5" type="text" placeholder="Beschreibung..." required></textarea>
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
							        <!-- Dateigröße auf 5MB limitieren -->
							        <input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
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
					$uniqueUrl = uniqueDbId($db, 'reisetagebuecher', 'url');
					$insertReisetagebuch = $db->prepare("INSERT INTO reisetagebuecher(users_id, titel, beschreibung, url, public, bild_id) VALUES(?, ?, ?, ?, ?, ?)");
					$insertReisetagebuch->execute(array(htmlspecialchars($userId), htmlspecialchars($_POST['titel']), htmlspecialchars($_POST['beschreibung']), $uniqueUrl, $public, htmlspecialchars($_POST['pictureId'])));
					echo "<script>window.location.href = 'reisetagebuecher.php?view=meine-reisetagebuecher&success=true';</script>";
				} else {
					echo "<ul>";
					foreach($errors as $error){
						echo "<li>".$error."</li>";
					}
					echo "</ul>";
				}
			}
			break;

			case 'reisetagebuch':
			
			?>
			<div class="uk-flex uk-flex-center uk-flex-column uk-flex-middle">
				<div class="uk-margin-top">
				<?php 
				if(isOwner($db, $userId, $rtbId)){
				?>
					<div>
						<div><a href="" class="uk-icon-link uk-float-left" uk-icon="icon: file-edit; ratio: 1.5"></a></div>
						<div><a href="" class="uk-icon-link uk-float-right" uk-icon="icon: social; ratio: 1.5"></a></div>
						<div><a href="" class="uk-icon-link uk-float-right far fa-map fa-big uk-margin-small-right"></a></div>
						<div class="uk-text-center uk-text-lead" id="rtbTitel"><?=$reisetagebuchDaten[0]['titel'];?> <span class="uk-text-small">von <?=$username;?></span></div>
					</div>

					<div id="titelbild" class="uk-margin uk-text-center">
			        	<?php 
			        	if(!empty($reisetagebuchDaten[0]['bild_id'])){
	            			echo '<img data-src="../users/'.$username.'/'.$reisetagebuchDaten[0]['bild_id'].'.'.$reisetagebuchDaten[0]['file_ext'].'" uk-img>'; 
	            		} else {
	            			echo '<img data-src="/pictures/no-picture.png" uk-img>';
	            		} 
			        	?>
			        </div>				    
			        <?php 
		            $selectDates = $db->prepare("SELECT DISTINCT datum FROM eintraege WHERE reisetagebuch_id = ? ORDER BY datum DESC");
		            $selectDates->execute(array($rtbId));
		            $dates = $selectDates->fetchAll(\PDO::FETCH_ASSOC);
		            if(!empty($dates)){
		            ?>
					    <table class="uk-table uk-table-hover uk-table-justify uk-table-divider">
						    <thead>
						        <tr>
							        <th class="uk-text-center">Einträge</th>
						            <th class="uk-text-right">
						            	<form method="POST" action="eintraege.php?view=neuer-eintrag">
						            		<input type="text" name="rtb" value="<?=$rtbUrl;?>" hidden>
						            		<button class="uk-button uk-button-text" name="neuer-eintrag"><i uk-icon="plus"></i> Neuer Eintrag</button>
						            	</form>
						            </th>
						        </tr>
						    </thead>
						    <tbody>
						    	<?php
							    	foreach($dates as $datum){
							    		$selectEintraege = $db->prepare("SELECT titel FROM eintraege WHERE reisetagebuch_id = ? AND datum = ?");
							            $selectEintraege->execute(array($rtbId, $datum['datum']));
							            $eintraege = $selectEintraege->fetchAll(\PDO::FETCH_ASSOC);

							            $formatiertesDatum = strftime("%e. %B %Y", strtotime($datum['datum']));
								    	?>
								        <tr class="eintragBox" onclick="document.location='eintraege.php?rtb=<?=$rtbUrl;?>&datum=<?=$datum['datum'];?>'">
								            <td>
								            <span class="uk-text-bold"><?=$formatiertesDatum;?> </span>
								            <i>
								            <?php
								            foreach($eintraege as $eintrag){
								            	echo $eintrag['titel'].", ";
								            }
								            ?>
								            ...
								            </i>	
								            </td>
								            <td class="uk-text-right">
								            	<form method="POST" action="eintraege.php?view=eintrag-bearbeiten">
								            		<input type="text" name="rtbId" value="<?=$rtbId;?>" hidden>
								            		<input type="text" name="datum" value="<?=$datum['datum'];?>" hidden>
								            		<button class="uk-button uk-button-text" name="eintrag-bearbeiten"><i uk-icon="file-edit"></i></button>
								            	</form>
								            </td>
								        </tr>
							        <?php
							    	}
							    	?>
						    </tbody>
						</table>
					<?php
					} else {
				    	?>
				    	<div class="uk-margin-top uk-text-center">
							<span>Sie haben zu diesem Reisetagebuch noch keinen Eintrag geschrieben.</span><br/>
							<form method="POST" action="eintraege.php?view=neuer-eintrag">
			            		<input type="text" name="rtb" value="<?=$rtbUrl;?>" hidden>
			            		<button class="uk-button uk-button-text" name="neuer-eintrag">Neuer Eintrag</button>
			            	</form>
						</div>
				    	<?php
				    }
				} else {
			    	?>
			    	<div>
						<div><a href="" class="uk-icon-link uk-float-right" uk-icon="icon: social; ratio: 1.5"></a></div>
						<div><a href="" class="uk-icon-link uk-float-right far fa-map fa-big uk-margin-small-right"></a></div>
						<div class="uk-text-center uk-text-lead" id="rtbTitel"><?=$reisetagebuchDaten[0]['titel'];?> <span class="uk-text-small">von <?=$reisetagebuchDaten[0]['username'];?></span></div>
					</div>

					<div id="titelbild" class="uk-margin uk-text-center">
			        	<?php 
			        	if(!empty($reisetagebuchDaten[0]['bild_id'])){
	            			echo '<img data-src="../users/'.$reisetagebuchDaten[0]['username'].'/'.$reisetagebuchDaten[0]['bild_id'].'.'.$reisetagebuchDaten[0]['file_ext'].'" uk-img>'; 
	            		} else {
	            			echo '<img data-src="/pictures/no-picture.png" uk-img>';
	            		} 
			        	?>
			        </div>				    
			        <?php 
		            $selectDates = $db->prepare("SELECT DISTINCT datum FROM eintraege WHERE reisetagebuch_id = ? ORDER BY datum DESC");
		            $selectDates->execute(array($rtbId));
		            $dates = $selectDates->fetchAll(\PDO::FETCH_ASSOC);
		            if(!empty($dates)){
		            ?>
					    <table class="uk-table uk-table-hover uk-table-justify uk-table-divider">
						    <thead>
						        <tr>
							        <th class="uk-text-center">Einträge</th>
						        </tr>
						    </thead>
						    <tbody>
						    	<?php
							    	foreach($dates as $datum){
							    		$selectEintraege = $db->prepare("SELECT titel FROM eintraege WHERE reisetagebuch_id = ? AND datum = ?");
							            $selectEintraege->execute(array($rtbId, $datum['datum']));
							            $eintraege = $selectEintraege->fetchAll(\PDO::FETCH_ASSOC);

							            $formatiertesDatum = strftime("%e. %B %Y", strtotime($datum['datum']));
								    	?>
								        <tr class="eintragBox" onclick="document.location='eintraege.php?rtb=<?=$rtbUrl;?>&datum=<?=$datum['datum'];?>'">
								            <td>
									            <span class="uk-text-bold"><?=$formatiertesDatum;?> </span>
									            <i>
									            <?php
									            foreach($eintraege as $eintrag){
									            	echo $eintrag['titel'].", ";
									            }
									            ?>
									            ...
									            </i>	
								            </td>
								        </tr>
							        <?php
							    	}
							    	?>
						    </tbody>
						</table>
					<?php
					} else {
				    	?>
				    	<div class="uk-margin-top uk-text-center">
							<span>Es wurde zu diesem Reisetagebuch noch nichts eingetragen.</span><br/>
						</div>
				    	<?php
				    }
			    	?>
			    <?php
				}
				?>
				</div>
			</div>
			<?php 
			break;
			}
		?>
		</div>
		<?php 
			// Success Benachrichtigungen 
			if(isset($_GET['login'])){echo "<script>UIkit.notification({message: 'Sie sind angemeldet.', status: 'success'});</script>";}
			if(isset($_GET['success'])){echo "<script>UIkit.notification({message: 'Ihr Reisetagebuch wurde erfolgreich erstellt.', status: 'success'});</script>";}
			if(isset($_GET['eintragErfolgreich'])){echo "<script>UIkit.notification({message: 'Ihr Eintrag wurde erfolgreich erstellt.', status: 'success'});</script>";}
		?>
		<script>
			var bar = document.getElementById('js-progressbar');
			var username = "<?php echo $username; ?>";

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
		        	console.log('test');
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

		            var infos = JSON.parse(data.response);
		            var fullPath = '../users/'+username+'/tmp_'+infos.pictureId+'.'+infos.file_ext;

		            $('#pictureId').val(infos.pictureId);
		            $('#file_ext').val(infos.file_ext);
		            $('#titelbild').empty().append('<div class="uk-animation-fade"><img data-src="'+fullPath+'" uk-img></div>');
		            UIkit.notification({message: 'Ihr Titelbild wurde erfolgreich hochgeladen.', status: 'success'});
		        }
		    });
		</script>
	</body>
</html>