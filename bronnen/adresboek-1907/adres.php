<?php 




//echo $_GET['occupants'];

$data = json_decode($_GET['occupants'],true);

//print_r($data);


$people = array();

foreach ($data['occupants'] as $row) {

	$part = "";
	if($row['part'] == "alphabetical"){
		$part = "alfabetisch register";
	}
	if($row['part'] == "profession"){
		$part = "beroepenregister";
	}
	if($row['part'] == "streets"){
		$part = "stratenregister";
	}

	if(!isset($people[$row['id']])){
		$people[$row['id']] = array(
			"label" => $row['label'],
			"scan" => $row['scan'],
			"ocr" => $row['ocr'],
			"part" => $part
		);

	}
	
}

?>

<h2>Vermeldingen in Adresboek 1907 op dit adres</h2>

<div class="row">

<?php

foreach ($people as $uri => $person) {



	?>
		<div class="col-md-3">
			<div class="personblock">
				<h3><?= $person['label'] ?></h3>

				in: <?= $person['part'] ?><br />

				ocr: <?= $person['ocr'] ?><br />

				<a target="_blank" href="<?= $person['scan'] ?>">
					bekijk in adresboek
				</a><br /><br />

			</div>

		</div>

	<?php
	
}

?>

</div>