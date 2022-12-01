<?php 


include("../../_infra/functions.php");


$sparql = "
PREFIX schema: <https://schema.org/>
PREFIX adbandb: <https://iisg.amsterdam/vocab/adb-andb/>
PREFIX andb: <https://iisg.amsterdam/id/andb/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
SELECT DISTINCT ?resident ?residentlabel (GROUP_CONCAT(DISTINCT ?adreslabel;SEPARATOR=\",\") as ?labels) ?begindate  WHERE {
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
} group by ?resident ?residentlabel ?begindate
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
	}else{
		$begin = "";
	}

	if(!isset($people[$row['resident']['value']])){
		$people[$row['resident']['value']] = array(
			"name" => $row['residentlabel']['value'],
			"stays" => array()
		);
		$people[$row['resident']['value']]['stays'][] = array(
			"begin" => $begin,
			"adreslabels" => $adreslabels
		);

	}
	
}

?>

<h2>Diamantbewerkers op dit adres</h2>

<div class="row">

<?php

foreach ($people as $uri => $person) {



	?>
		<div class="col-md-3">
			<div class="personblock">
				<h3><?= $person['name'] ?></h3>

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