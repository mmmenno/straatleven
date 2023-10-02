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
$endpoint = 'https://data.create.humanities.uva.nl/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);

$adreslabels = array();
$adreslinks = array();
foreach ($data['results']['bindings'] as $key => $value) {
	$adreslabels[] = $value['label']['value'];
	$adreslinks[] = '<a href="' . $value['adres']['value'] . '">' . $value['label']['value'] . ' (' . $value['bronlabel']['value'] . ')</a>';
}
$adreslabels = array_unique($adreslabels);

$sparql = "
PREFIX rico: <https://www.ica.org/standards/RiC/ontology#>
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX schemahttp: <http://schema.org/>
PREFIX schemahttps: <https://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX saa: <https://data.archief.amsterdam/ontology#>
PREFIX hg: <http://rdf.histograph.io/>
PREFIX rt: <https://ams-migrate.memorix.io/resources/recordtypes/>

SELECT * WHERE {
  VALUES ?aladr { ";

  foreach($addresses as $adr){
  	$sparql .= "<" . $adr . "> ";
  }

  $sparql .= " }
  ?bbrec saa:hasOrHadSubjectAddress ?aladr .
  ?bbrec a rt:Image .
  ?bbrec schemahttp:thumbnailUrl ?thumb .
  ?bbrec rico:title ?title .
  ?bbrec rico:creationDate/rico:textualValue ?datum .
} 
LIMIT 1000
";

//echo $sparql;
$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


$afbeeldingen = array();

foreach ($data['results']['bindings'] as $row) {

	$afb = array("bbrec" => str_replace("https://ams-migrate.memorix.io/resources/records/","https://archief.amsterdam/beeldbank/detail/",$row['bbrec']['value']));

	if(!isset($row['datum']['value'])){
		$row['datum']['value'] = "";
	}
	$afb['datum'] = $row['datum']['value'];
	$afb['thumb'] = $row['thumb']['value'];
	$afb['titel'] = $row['title']['value'];

	
	$afbeeldingen[] = $afb;
}

?>

<h2>Afbeeldingen van <?= implode(", ",$adreslabels) ?></h2>
<div class="smalladdress"><?= implode(" | ",$adreslinks) ?></div>
<div class="row">

<?php

foreach ($afbeeldingen as $uri => $img) {



	?>
		<div class="col-md-3">
			<div class="personblock">
				
				<a target="_blank" href="<?= $img['bbrec'] ?>"><img src="<?= $img['thumb'] ?>" /></a>

				<p><?= $img['titel'] ?></p>


				<?= $img['datum'] ?> <a target="_blank" href="<?= $img['bbrec'] ?>">
					bekijk bij het Stadsarchief
				</a><br /><br />

				
			</div>

		</div>

	<?php
	
}

?>

</div>