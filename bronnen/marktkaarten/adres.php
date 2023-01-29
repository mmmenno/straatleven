<?php 


include("../../_infra/functions.php");

$addresses = json_decode($_GET['adressen'],true);

$sparql = "
PREFIX schema: <http://schema.org/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX saa: <https://data.archief.amsterdam/ontology#>
PREFIX rico: <https://www.ica.org/standards/RiC/ontology#>
PREFIX dcterms: <http://purl.org/dc/terms/>
PREFIX pnv: <https://w3id.org/pnv#>
SELECT ?deed ?givenName ?baseSurname ?surnamePrefix ?birthDate ?vergunningdatum ?standplaats WHERE {
  VALUES ?aladr { ";

  foreach($addresses as $adr){
  	$sparql .= "<" . $adr . "> ";
  }

  $sparql .= " }
  ?adr owl:sameAs ?aladr .
  ?deed saa:isAssociatedWithModernAddress ?adr .
  ?adr dcterms:title ?adrstr .
  ?deed rico:hasOrHadSubject/saa:relatedPersonObservation ?po .
  optional{
  	?deed saa:hasSpecification/saa:specificationTextualValue ?standplaats .
  }
  optional{
    ?deed rico:normalizedDateValue ?vergunningdatum .
  }
  optional{
  	?po pnv:hasName/pnv:givenName ?givenName .
  }
  ?po pnv:hasName/pnv:baseSurname ?baseSurname .
  optional{
  	?po pnv:hasName/pnv:surnamePrefix ?surnamePrefix .
  }
  optional{
  	?po schema:birthDate ?birthDate .
  }
  
} 
";

//echo $sparql;
$endpoint = 'https://api.druid.datalegend.net/datasets/menno/Streetlife/services/Streetlife/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


$people = array();

foreach ($data['results']['bindings'] as $row) {

	$person = array("deed" => str_replace("https://ams-migrate.memorix.io/resources/records/","https://archief.amsterdam/indexen/deeds/",$row['deed']['value']));

	if(!isset($row['surnamePrefix']['value'])){
		$row['surnamePrefix']['value'] = "";
	}
	if(!isset($row['givenName']['value'])){
		$row['givenName']['value'] = "";
	}
	if(!isset($row['vergunningdatum']['value'])){
		$row['vergunningdatum']['value'] = "";
	}
	if(!isset($row['standplaats']['value'])){
		$row['standplaats']['value'] = "";
	}
	
	if(!isset($row['birthDate']['value'])){
		$row['birthDate']['value'] = "";
	}
	
	$person['name'] = $row['givenName']['value'] . " " . trim($row['surnamePrefix']['value'] . " " . $row['baseSurname']['value']);

	$person['birth'] = $row['birthDate']['value'];
	$person['standplaats'] = $row['standplaats']['value'];
	$person['vergunningdatum'] = $row['vergunningdatum']['value'];
	
	$people[] = $person;
}

?>

<h2>Marktkaarthouders op dit adres</h2>

<div class="row">

<?php

foreach ($people as $uri => $person) {



	?>
		<div class="col-md-3">
			<div class="personblock">
				<h3><?= $person['name'] ?></h3>

				geboren op <?= $person['birth'] ?><br /><br />
				standplaats vergunning: <?= $person['standplaats'] ?><br />
				datum vergunning: <?= $person['vergunningdatum'] ?><br /><br />

				<a target="_blank" href="<?= $person['deed'] ?>">
					bekijk bij het Stadsarchief
				</a><br /><br />

				
			</div>

		</div>

	<?php
	
}

?>

</div>