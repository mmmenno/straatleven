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
SELECT ?adres ?loc ?doc ?doctype ?adreslabel ?loclabel ?roomslabel ?rooms ?inventory (GROUP_CONCAT(?pname;separator=\" | \") AS ?pnames) WHERE {
  VALUES ?adres { ";

  foreach($addresses as $adr){
  	$sparql .= "<" . $adr . "> ";
  }

  $sparql .= " }
  ?loc schema:address ?adres .
  ?loc roar:documentedIn ?doc .
  ?doc schema:isPartOf <https://data.niod.nl/temp-archiefid/093a> .
  ?doc schema:additionalType ?doctype .
  ?adres rdfs:label ?adreslabel .
  ?loc rdfs:label ?loclabel .
  optional{
  	?loc schema:numberOfRooms/rdfs:label ?roomslabel .
  }
  optional{
  	?loc schema:numberOfRooms/rdf:value ?rooms .
  }
  optional{
  	?loc schema:description ?inventory .
  }
  optional{
  	?p roar:hasLocation/rdf:value ?loc .
  	?p rdfs:label ?pname .
  }
} 
GROUP BY  ?adres ?loc ?doc ?doctype ?adreslabel ?loclabel ?roomslabel ?rooms ?inventory 
LIMIT 1000
";

//echo $sparql;
$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


$docs = array();

foreach ($data['results']['bindings'] as $row) {

	if(!isset($row['pnames']['value'])){
		$row['pnames']['value'] = "";
	}
	if(!isset($row['inventory']['value'])){
		$row['inventory']['value'] = "";
	}
	if(!isset($row['rooms']['value'])){
		$row['rooms']['value'] = "";
	}
	if(!isset($row['roomslabel']['value'])){
		$row['roomslabel']['value'] = "";
	}
	
	if(!isset($row['birthDate']['value'])){
		$row['birthDate']['value'] = "";
	}
	
	$doc['pnames'] = $row['pnames']['value'];

	$doc['doc'] = $row['doc']['value'];
	$doc['doctype'] = $row['doctype']['value'];
	$doc['loclabel'] = $row['loclabel']['value'];
	$doc['rooms'] = $row['rooms']['value'];
	$doc['roomslabel'] = $row['roomslabel']['value'];
	$doc['adreslabel'] = $row['adreslabel']['value'];
	$doc['adres'] = $row['adres']['value'];
	$doc['inventory'] = $row['inventory']['value'];
	
	$docs[] = $doc;
}

//print_r($docs);

?>

<h2>ERR documenten op <?= $docs[0]['adreslabel'] ?></h2>
<div class="smalladdress">
	<a href="adres/?adres=<?= $docs[0]['adres'] ?>"><?= $docs[0]['adreslabel'] ?></a> [klik om gegevens uit verschillende bronnen bij dit adres te bekijken]
</div>
<div class="row">

<?php

foreach ($docs as $doc) {



	?>
		<div class="col-md-4">
			<div class="personblock">
				<h3><?= $doc['loclabel'] ?></h3>

				een <a target="_blank" href="bronnen/err-inboedels/document.php?doc=<?= $doc['doc'] ?>"><?= $doc['doctype'] ?></a><br /><br />
				
				<strong>personen:</strong>
        <?= $doc['pnames'] ?><br />
				
				<strong>aantal kamers:</strong>
        <?= $doc['rooms'] ?><br />

        <strong>beschrijving:</strong>
        <?= $doc['roomslabel'] ?><br /><br />

        <strong>inboedel:</strong><br />
        <?= $doc['inventory'] ?>

				
				
			</div>

		</div>

	<?php
	
}

?>

</div>