<?php 

$json = file_get_contents("../bronnen.json");

$bronnen = json_decode($json,true);



?>










<?php

foreach ($bronnen as $bron) {

	if($bron['id']!="woningkaarten"){
		continue;
	}



	?>
		<h1><?= $bron['name'] ?> (<?= $bron['organisation'] ?>)</h1>

		<h4>Klik op een adres om gegevens te zien</h4>

		<div class="row">
			<div class="col-md-3">
				<div class="personblock">
					<h3>Beschrijving bron</h3>
					
					<?= $bron['description'] ?> <br /><br />

					<?php if(strlen($bron['more-info'])){ ?>
						<a href="<?= $bron['more-info'] ?>">Meer informatie</a><br />
					<?php } ?>
					
					
				</div>
			</div>

			<div class="col-md-3">
				<div class="personblock">
					<h3>Gegevens bekijken</h3>
					
					<?= $bron['data-search'] ?> <br /><br />
					<?php if(strlen($bron['data-search-link'])){ ?>
						<a href="<?= $bron['data-search-link'] ?>">Bekijk de data hier</a><br />
					<?php } ?>
					
					
				</div>
			</div>

			<div class="col-md-3">
				<div class="personblock">
					<h3>Data downloaden</h3>
					
					<?= $bron['data-download'] ?> <br /><br />
					<?php if(strlen($bron['data-download-link'])){ ?>
						<a href="<?= $bron['data-download-link'] ?>">Download de data hier</a><br />
					<?php } ?>
					
					
				</div>
			</div>

			<div class="col-md-3">
				<div class="personblock">
					<h3>Data api?</h3>
					
					<?= $bron['data-api'] ?> <br /><br />
					<?php if(strlen($bron['data-api-link'])){ ?>
						<a href="<?= $bron['data-api-link'] ?>">Bevraag de data hier</a><br />
					<?php } ?>
					
					
				</div>
			</div>

		</div>
	<?php
	
}

?>




