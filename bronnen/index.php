<?php

include("../_parts/header.php");

$json = file_get_contents("bronnen.json");

$bronnen = json_decode($json,true);



?>






<div class="container-fluid" id="main">

	<h1>Bronnen</h1>

	<div class="row">

	<?php

	foreach ($bronnen as $bron) {



		?>
			<div class="col-md-4">
				<div class="personblock">
					<h3><?= $bron['name'] ?></h3>
					<h4><?= $bron['organisation'] ?></h4>

					<?= $bron['description'] ?> 
					<?php if(strlen($bron['more-info'])){ ?>
						<a href="<?= $bron['more-info'] ?>">Meer informatie</a>
					<?php } ?>
					<br /><br />
					
					<?= $bron['data-search'] ?> 
					<?php if(strlen($bron['data-search-link'])){ ?>
						<a href="<?= $bron['data-search-link'] ?>">Bekijk de data hier</a>.
					<?php } ?>
					<br /><br />
					
					<?= $bron['data-download'] ?> 
					<?php if(strlen($bron['data-download-link'])){ ?>
						<a href="<?= $bron['data-download-link'] ?>">Download de data hier</a>.
					<?php } ?>
					<br /><br />
					
					<?= $bron['data-api'] ?> 
					<?php if(strlen($bron['data-api-link'])){ ?>
						<a href="<?= $bron['data-api-link'] ?>">Bevraag de data hier</a>.
					<?php } ?>
					<br /><br />
					

					
				</div>

			</div>

		<?php
		
	}

	?>

	</div>

</div>


<?php

include("../_parts/footer.php");

?>
