<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	if(isset($_GET['rtb'])){
		$rtbUrl = htmlspecialchars($_GET['rtb']);
		$rtbId = getRtbIdFromUrl($db, $rtbUrl);
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php 
			require $_SERVER['DOCUMENT_ROOT']."/include/header.php"; 
		?>
		<title>journuit - Landkarte</title>
	</head>

	<body class="uk-height-viewport">
		<?php require $_SERVER['DOCUMENT_ROOT']."/include/navbar.php";?>
		<div class="uk-container uk-container-large">
			<?php
				$selectStandorte = $db->prepare("SELECT standorte.name, standorte.lat, standorte.lon, standorte.bild_id, bilder.file_ext, eintraege.titel FROM eintraege LEFT JOIN standorte ON (eintraege.standort_id = standorte.id) LEFT JOIN bilder ON (standorte.bild_id = bilder.id) WHERE eintraege.reisetagebuch_id = ? AND standorte.users_id = ?");
				$selectStandorte->execute(array($rtbId, $userId));
				$standorte = $selectStandorte->fetchAll(\PDO::FETCH_ASSOC);
				if(!empty($standorte)){
					?>
					<div class="uk-margin-top uk-text-center">
						<a class="uk-icon-link uk-margin-left" uk-icon="icon: arrow-left; ratio: 1.2" href="reisetagebuecher.php?rtb=<?=$rtbUrl;?>">Zurück zum Reisetagebuch</a>
					</div>

					<div id="pickerMap">
						<!-- Hier wird die Google Map angezeigt -->
					</div>

					<?php
				} else {
					?>
					<div class="uk-margin-medium-top uk-text-center">
						<div class="uk-alert-warning" uk-alert>
							<p>Dieses Reisetagebuch enthält bisher noch kein Ort.</p>
						</div>

						<div class="uk-margin-bottom">
							<a href="standorte.php?view=neu" class="uk-heading uk-link-reset uk-text-uppercase">Neuer Ort erstellen</a>
						</div>

						<div>	
							<a href="reisetagebuecher.php?rtb=<?=$rtbUrl;?>" class="uk-link-reset">Zurück zum Reisetagebuch</a>
						</div>
					</div>
					<?php
				}
			?>
		</div>
		<script>
			var eintraegeMap;
	    	var currentInfoWindow = null; 
	    	<?php 
	    	echo "var orte = ".json_encode($standorte).";"; 
	    	?>

	    	//Diese Funktion wird automatisch im case : map durch das Laden der Google Maps API aufgerufen
		    function initMap() {
		    	//Erstellen der "bounds" Variabel die später dazu dient die Map zu zentrieren wo alle Marker liegen
		    	var bounds = new google.maps.LatLngBounds();
		    	//Erstellen der Mitarbeiter-Map in der passenden div
		        eintraegeMap = new google.maps.Map(document.getElementById('pickerMap'), {
		        	mapTypeId: 'roadmap'
		        });

				//Für jeden ort wird ein Marker mit den dort wohnenden Mitarbeiter erstellt. Die "bounds" Variabel wird um die Position des Markers erweitert
		        for(ort in orte){
	        		bounds.extend(createMarker(ort).position);
			    }

			    //Die Map wird dort zentriert wo die Marker liegen
			    eintraegeMap.fitBounds(bounds);
			}

			function createMarker(ort){
				var ortLat = ort['lat'];
	    		var ortLon = ort['lon'];
	    		var ortname = ort['name'];

	            var marker = new google.maps.Marker( {
	                map     : eintraegeMap,
	                title: ortname,
	                position: new google.maps.LatLng(ortLat, ortLon)		              
	            });

	            marker.addListener('click', function() {
	            	//Schliessen von offenen infoWindows damit immer nur eins aufbleibt
		        	eintraegeMap.setCenter(this.getPosition());
					eintraegeMap.setZoom(12);
		    	});
		    	
		    	return marker;
			}
		</script>
	</body>
</html>