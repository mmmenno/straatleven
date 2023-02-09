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
						todo: personen JM
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
					<option value="woningkaarten" <?php if(isset($bron) && $bron == "woningkaarten"){ echo "selected"; } ?>>
						todo: woningkaarten
					</option>
					<option value="register-1864-1874" <?php if(isset($bron) && $bron == "register-1864-1874"){ echo "selected"; } ?>>
						todo: register 1864-1874
					</option>
					<option value="err-inboedels" <?php if(isset($bron) && $bron == "err-inboedels"){ echo "selected"; } ?>>
						todo: ERR inboedels
					</option>
				</select>

				<button class="btn btn-primary">toon op kaart</button>

			</form>


		</div>
	</div>
</div>


<?php if(isset($bron)){ ?>
	<div id="map"></div>
<?php } ?>


<div class="container-fluid" id="main">
	<h1>Straatleven</h1>
	<h2>Bronnen bij adressen in de Jodenbreestraat en de Nieuwe Amstelstraat</h2>

	<div class="row">
		
		<div class="col-md-4">
			<div class="personblock">
				<h3>Bewoners van het Joods kwartier</h3>
				
				<p></p>
				
			</div>
		</div>

		<div class="col-md-4">
			<div class="personblock">
				<h3>Verschillende bronnen verbonden</h3>
				
				<p></p>
				
			</div>
		</div>

		<div class="col-md-4">
			<div class="personblock">
				<h3>Een prototype voor de hele stad</h3>
				
				<p></p>
				
			</div>
		</div>


	</div>

	<div class="row">
		
		<div class="col-md-4">
			<div class="personblock">
				<h3>Persoonsreconstructies op basis van vermeldingen</h3>
				
				<p></p>
				
			</div>
		</div>

		<div class="col-md-4">
			<div class="personblock">
				<h3>Aanpak</h3>
				
				<p></p>
				
			</div>
		</div>

	</div>

</div>


<script src="_assets/js/map.js"></script>

<?php if(isset($bron)){ ?>
	<script src="bronnen/<?= $bron ?>/bron.js"></script>
<?php } ?>

<?php

include("_parts/footer.php");

?>
