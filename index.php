<?php

include("_parts/header.php");

if(!isset($_GET['straat'])){
	$_GET['straat'] = "https://adamlink.nl/geo/street/jodenbreestraat/2158";
}
if(!isset($_GET['bron'])){
	//$bron = "diamantbewerkersbond";
}else{
	$bron = $_GET['bron'];
}

?>


<div class="container-fluid" id="subheader">
	<div class="row">
		<div class="col-md-12">
			<p>kies een straat en een bron:</p>

			<form method="get" action="/">
				
				<select name="straat" class="form-select" style="width:30%; display: inline;">
					<option value="https://adamlink.nl/geo/street/nieuwe-amstelstraat/3138" <?php if($_GET['straat'] == "https://adamlink.nl/geo/street/nieuwe-amstelstraat/3138"){ echo "selected"; } ?>>
					Nieuwe Amstelstraat</option>
					<option value="https://adamlink.nl/geo/street/jodenbreestraat/2158" <?php if($_GET['straat'] == "https://adamlink.nl/geo/street/jodenbreestraat/2158"){ echo "selected"; } ?>>
					Jodenbreestraat</option>
				</select>
				
				<select name="bron" class="form-select" style="width:30%; display: inline;">
					<option value="jhm-personen" <?php if(isset($bron) && $bron == "jhm-personen"){ echo "selected"; } ?>>
						personen JHM
					</option>
					<option value="diamantbewerkersbond" <?php if(isset($bron) && $bron == "diamantbewerkersbond"){ echo "selected"; } ?>>
						diamantbewerkersbond
					</option>
					<option value="marktkaarten" <?php if(isset($bron) && $bron == "marktkaarten"){ echo "selected"; } ?>>
						marktkaarten
					</option>
					<option value="adresboek-1907" <?php if(isset($bron) && $bron == "adresboek-1907"){ echo "selected"; } ?>>
						adresboek 1907
					</option>
				</select>

				<button class="btn btn-primary">toon op kaart</button>

			</form>


		</div>
	</div>
</div>



<div id="map"></div>



<div class="container-fluid" id="main">
</div>


<script src="_assets/js/map.js"></script>

<?php if(isset($bron)){ ?>
	<script src="bronnen/<?= $bron ?>/bron.js"></script>
<?php } ?>

<?php

include("_parts/footer.php");

?>
