<?php 


include("../../_infra/functions.php");

$addresses = json_decode($_GET['adressen'],true);

$sparql = "PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX bag: <http://bag.basisregistraties.overheid.nl/def/bag#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX schema: <https://schema.org/>
PREFIX roar: <https://w3id.org/roar#>
SELECT ?adres ?adreslabel ?loclabel ?p ?pname ?bdate ?bplace ?ddate ?dplace WHERE {
  VALUES ?adres { ";

  foreach($addresses as $adr){
  	$sparql .= "<" . $adr . "> ";
  }

  $sparql .= " }
  ?loc schema:address ?adres .
  ?loc roar:documentedIn <https://www.joodsmonument.nl/> .
  ?adres rdfs:label ?adreslabel .
  ?loc rdfs:label ?loclabel .
  ?p roar:hasLocation/rdf:value ?loc .
  ?p rdfs:label ?pname .
  ?p schema:birthDate ?bdate .
  ?p schema:birthPlace ?bplace .
  optional{
    ?p schema:deathDate ?ddate .
  }
  optional{
    ?p schema:deathPlace ?dplace .
  }
} 
LIMIT 1000
";

//echo $sparql;
$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


$persons = array();

foreach ($data['results']['bindings'] as $row) {

	if(!isset($row['pname']['value'])){
		$row['pname']['value'] = "";
	}
	if(!isset($row['ddate']['value'])){
		$row['ddate']['value'] = "";
	}
	if(!isset($row['dplace']['value'])){
		$row['dplace']['value'] = "";
	}
	
	$p = array();
	$p['pname'] = $row['pname']['value'];
	$p['p'] = $row['p']['value'];
	$p['bplace'] = $row['bplace']['value'];
	$parts = explode("-",$row['bdate']['value']);
	if(count($parts)==3){
		$p['bdate'] = $parts[2] . "-" . $parts[1] . "-" . $parts[0];
	}else{
		$p['bdate'] = $row['bdate']['value'];
	}
	$parts = explode("-",$row['ddate']['value']);
	if(count($parts)==3){
		$p['ddate'] = $parts[2] . "-" . $parts[1] . "-" . $parts[0];
	}else{
		$p['ddate'] = $row['ddate']['value'];
	}
	$p['dplace'] = $row['dplace']['value'];
	$p['loclabel'] = $row['loclabel']['value'];
	$p['adreslabel'] = $row['adreslabel']['value'];
	$p['adres'] = $row['adres']['value'];
	
	$persons[] = $p;
}

//print_r($persons);

if(count($persons)==1){
	$enkelofmeervoud = "persoon";
}else{
	$enkelofmeervoud = "personen";
}

?>

<h2><?= count($persons) ?> Joods Monument <?= $enkelofmeervoud ?> op <?= $persons[0]['adreslabel'] ?></h2>
<div class="smalladdress">
	<a href="adres/?adres=<?= $persons[0]['adres'] ?>"><?= $persons[0]['adreslabel'] ?></a> [klik om gegevens uit verschillende bronnen bij dit adres te bekijken]
</div>
<div class="row">

<?php

foreach ($persons as $p) {



	?>
		<div class="col-md-4">
			<div class="personblock">
				<h3><?= $p['pname'] ?></h3>

				<?= $p['loclabel'] ?><br /><br />

				<?= $p['bdate'] ?>, <?= $p['bplace'] ?> - <?= $p['ddate'] ?>, <?= $p['dplace'] ?><br /><br />

				<a target="_blank" href="<?= $p['p'] ?>">bekijk op Joods Monument</a>
				
				
				
				
			</div>

		</div>

	<?php
	
}

?>

</div>