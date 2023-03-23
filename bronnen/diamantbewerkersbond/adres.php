<?php 


include("../../_infra/functions.php");


// first get adamlink adres info
$sparql = "
PREFIX roar: <https://w3id.org/roar#>
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX schema: <https://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX histograph: <http://rdf.histograph.io/>
SELECT ?adres ?bron ?bronlabel ?label (MIN(?wkt) AS ?wkt) WHERE {
  values ?lp { ";

  $sparql .= "<https://adamlink.nl/geo/lp/" . $_GET['lp'] . "> ";

  $sparql .= " }
  values ?bron {
  	<https://adamlink.nl/geo/source/S1>
  	<https://adamlink.nl/geo/source/S2>
  	<https://adamlink.nl/geo/source/S3>
  }
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

$adrslabels = array();
$adreslinks = array();
foreach ($data['results']['bindings'] as $key => $value) {
	$adrslabels[] = $value['label']['value'];
	if(preg_match("/[0-9]{4}/",$value['bronlabel']['value'],$found)){
		$bronlabel = $found[0];
	}else{
		$bronlabel = "1876";
	}
	$adreslinks[] = '<a href="' . $value['adres']['value'] . '">' . $value['label']['value'] . ' (' . $bronlabel . ')</a>';
}
$adrslabels = array_unique($adrslabels);



$sparql = "
PREFIX schema: <https://schema.org/>
PREFIX adbandb: <https://iisg.amsterdam/vocab/adb-andb/>
PREFIX andb: <https://iisg.amsterdam/id/andb/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
SELECT DISTINCT ?resident ?residentlabel 
	(GROUP_CONCAT(DISTINCT ?adreslabel;SEPARATOR=\",\") as ?labels) ?begindate ?birth 
	(MIN(?membersince) AS ?membersince) 
	(GROUP_CONCAT(DISTINCT ?occlabel;SEPARATOR=\",\") AS ?occlabels) 
	WHERE {
  ?adres owl:sameAs <https://iisg.amsterdam/resource/andb/lp/" . $_GET['lp'] . "> .
  ?adres rdfs:label ?adreslabel .
  ?adres adbandb:houseNumber ?nr .
  optional{
  	?adres adbandb:houseNumberAddition ?add .
  }
  ?residency schema:address ?adres .
  optional{
    ?residency adbandb:duration ?duration .
    ?duration <http://www.w3.org/2006/time#hasBeginning> ?begin .
    ?begin <http://www.w3.org/2006/time#inXSDDate> ?begindate .
  }
  ?resident adbandb:inhabits ?residency .
  ?resident rdfs:label ?residentlabel .
  optional{
  	?resident schema:birthDate ?birth .
  }
  optional{
    ?resident <http://www.w3.org/ns/org#hasMembership> ?membership .
    ?membership <http://www.w3.org/ns/org#memberDuring> ?membershipduration .
    ?membershipduration <http://www.w3.org/2006/time#hasBeginning> ?memberbegin .
    ?memberbegin <http://www.w3.org/2006/time#inXSDDate> ?membersince .
    ?membership <http://www.w3.org/ns/org#organization> ?org .
    ?org adbandb:occupation ?occ .
    ?occ rdfs:label ?occlabel .
  }
} group by ?resident ?residentlabel ?begindate ?birth
LIMIT 5000
";

//echo $sparql;
$endpoint = 'https://api.druid.datalegend.net/datasets/andb/ANDB-ADB-all/services/default/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);

$people = array();

foreach ($data['results']['bindings'] as $row) {

	$adressen = explode(",",$row['labels']['value']);
	$adreslabels = array();
	foreach ($adressen as $adres) {
		$adreslabels[] = trim($adres);
	}
	$adreslabels = array_unique($adreslabels);
	$adreslabels = implode(", ", $adreslabels);

	if(isset($row['begindate']['value'])){
		$begin = "[vanaf " . $row['begindate']['value'] . "]";
	}elseif(isset($row['membersince']['value'])){
		$begin = "[werd lid op " . $row['membersince']['value'] . "]";
	}else{
		$begin = "";
	}

	$occs = explode(",",$row['occlabels']['value']);
	$occlabels = array();
	foreach($occs as $occje){
		$occlabels[] = preg_replace("/en$/","er",$occje);
	}

	if(!isset($people[$row['resident']['value']])){
		$people[$row['resident']['value']] = array(
			"name" => $row['residentlabel']['value'],
			"birth" => $row['birth']['value'],
			"occlabels" => implode(", ",$occlabels),
			"stays" => array()
		);
		$people[$row['resident']['value']]['stays'][] = array(
			"begin" => $begin,
			"adreslabels" => $adreslabels
		);

	}
	
}

?>

<h2>Diamantbewerkers op <?= implode(", ",$adrslabels) ?></h2>
<div class="smalladdress"><?= implode(" | ",$adreslinks) ?></div>

<div class="row">

<?php

foreach ($people as $uri => $person) {



	?>
		<div class="col-md-3">
			<div class="personblock">
				<h3><?= $person['name'] ?></h3>

				<?= $person['occlabels'] ?><br />
				geboren op <?= $person['birth'] ?><br /><br />

				<a target="_blank" href="https://diamantbewerkers.nl/en/detail?id=<?= $uri ?>">
					bekijk op diamantbewerkers.nl
				</a><br /><br />

				<?php foreach ($person['stays'] as $stay) { ?>

					<?= $stay['adreslabels'] ?><br />
					<?= $stay['begin'] ?><br />

				<?php } ?>
			</div>

		</div>

	<?php
	
}

?>

</div>