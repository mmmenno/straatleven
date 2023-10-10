<?php 


include("../../_infra/functions.php");

$addresses = json_decode($_GET['adressen'],true);

$sparql = "
PREFIX roar: <https://w3id.org/roar#>
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX schema: <https://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX histograph: <http://rdf.histograph.io/>
SELECT ?adres ?bron ?bronlabel ?label (MIN(?wkt) AS ?wkt) WHERE {
  VALUES ?adres { ";

  foreach($addresses as $adr){
  	$sparql .= "<" . $adr . "> ";
  }

  $sparql .= " }
  ?adres roar:documentedIn ?bron .
  ?bron rdfs:label ?bronlabel .
  ?adres rdfs:label ?label .
  ?adres schema:geoContains ?lp .
  ?lp geo:asWKT ?wkt
}
GROUP BY ?adres ?bron ?bronlabel ?label
";

//echo $sparql;
$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);

$adreslabels = array();
$adreslinks = array();
foreach ($data['results']['bindings'] as $key => $value) {
	$adreslabels[] = $value['label']['value'];
	if(preg_match("/[0-9]{4}/",$value['bronlabel']['value'],$found)){
		$bronlabel = $found[0];
	}else{
		$bronlabel = "1876";
	}
	$adreslinks[] = '<a href="adres/?adres=' . $value['adres']['value'] . '">' . $value['label']['value'] . ' (' . $bronlabel . ')</a>';
}
$adreslabels = array_unique($adreslabels);

$sparql = "
PREFIX schema: <http://schema.org/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX saa: <https://data.archief.amsterdam/ontology#>
PREFIX rico: <https://www.ica.org/standards/RiC/ontology#>
PREFIX dcterms: <http://purl.org/dc/terms/>
PREFIX pnv: <https://w3id.org/pnv#>
SELECT ?deed ?adrstr WHERE {
  VALUES ?aladr { ";

  foreach($addresses as $adr){
  	$sparql .= "<" . $adr . "> ";
  }

  $sparql .= " }
  ?adr owl:sameAs ?aladr .
  ?deed saa:isAssociatedWithModernAddress ?adr .
  ?adr dcterms:title ?adrstr .
  
  
} 
";

//echo $sparql;
$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


$kaarten = array();

foreach ($data['results']['bindings'] as $row) {

	$kaart = array("deed" => str_replace("https://ams-migrate.memorix.io/resources/records/","https://archief.amsterdam/indexen/deeds/",$row['deed']['value']));

	$kaart['label'] = $row['adrstr']['value'];

	$kaarten[] = $kaart;
}

?>

<h2>Woningkaarten van <?= implode(", ",$adreslabels) ?></h2>
<div class="smalladdress"><?= implode(" | ",$adreslinks) ?> [klik om gegevens uit verschillende bronnen bij dit adres te bekijken]</div>
<div class="row">

<?php

foreach ($kaarten as $kaart) {



	?>
		<div class="col-md-3">
			<div class="personblock">
				<h3><?= $kaart['label'] ?></h3>

				<a target="_blank" href="<?= $kaart['deed'] ?>">
					bekijk bij het Stadsarchief
				</a><br /><br />

				
			</div>

		</div>

	<?php
	
}

?>

</div>