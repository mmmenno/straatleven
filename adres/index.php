<?php

if(isset($_GET['adres'])){
  $adres = $_GET['adres'];
}else{
  $adres = "https://adamlink.nl/geo/address/A8536";
}

include("../_parts/header.php");
include("../_infra/functions.php");

// de adressen op locatiepunten bij dit adres

$sparql = "
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX schema: <https://schema.org/>
PREFIX roar: <https://w3id.org/roar#>
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX schemahttp: <http://schema.org/>
PREFIX schemahttps: <https://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX saa: <https://data.archief.amsterdam/ontology#>
PREFIX hg: <http://rdf.histograph.io/>
PREFIX rt: <https://ams-migrate.memorix.io/resources/recordtypes/>

SELECT * WHERE {
  VALUES ?adres { <" . $adres . "> }
  ?adres schema:geoContains ?lp .
  ?adres hg:liesIn ?straat .
  ?adres rdfs:label ?adreslabel .
  ?adres roar:documentedIn ?bron .
  ?bron rdfs:label ?bronlabel .
  ?adres sem:hasEarliestBeginTimeStamp ?begin .
  ?lp schema:geoWithin ?anderadres .
  ?anderadres rdfs:label ?anderadreslabel .
  ?anderadres sem:hasEarliestBeginTimeStamp ?anderadresbegin .
} 
order by ?anderadresbegin
LIMIT 1000";

$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

$thisaddress = array();
$otheraddresses = array();
$lpids = array();

foreach ($data['results']['bindings'] as $row) {

  $thisaddress = array(
    "uri" => $row['adres']['value'],
    "label" => $row['adreslabel']['value'],
    "bron" => $row['bronlabel']['value'],
    "straat" => $row['straat']['value'],
    "year" => substr($row['begin']['value'],0,4)
  );

  if($row['anderadres']['value'] != $adres){
    $otheraddresses[$row['anderadres']['value']] = array(
      "uri" => $row['anderadres']['value'],
      "label" => $row['anderadreslabel']['value'],
      "year" => substr($row['anderadresbegin']['value'],0,4)
    );
  }

  $lpid = str_replace("https://adamlink.nl/geo/lp/","",$row['lp']['value']);
  $lpids[$lpid] = $lpid;
}

//print_r($lpids);




// floorlevels met altlabels
$sparql = "
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
SELECT * WHERE {
  ?concept skos:altLabel ?alt .
  ?concept skos:prefLabel ?pref .
  ?concept skos:related <https://vocab.amsterdamtimemachine.nl/toevoegingen/verdiepingen>
}";

$endpoint = 'https://api.druid.datalegend.net/datasets/menno/Streetlife/services/Streetlife/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

$floorlevellabels = array();
foreach ($data['results']['bindings'] as $row) {
  $floorlevellabels[$row['alt']['value']] = $row['pref']['value'];
}




// Woningkaarten, floorlevels
$sparql = "
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX schema: <http://schema.org/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX saa: <https://data.archief.amsterdam/ontology#>
PREFIX saarecord: <https://ams-migrate.memorix.io/resources/records/>
PREFIX dcterms: <http://purl.org/dc/terms/>
PREFIX pnv: <https://w3id.org/pnv#>
SELECT ?deed ?adrstr ?floor ?floorconcept ?floorlabel WHERE {
  VALUES ?aladr { <" . $thisaddress['uri'] . "> ";

  foreach($otheraddresses as $k => $v){
    $sparql .= "<" . $k . "> ";
  }

  $sparql .= " }
  ?adr owl:sameAs ?aladr .
  ?deed saa:isAssociatedWithModernAddress ?adr .
  ?deed saa:isOrWasAlsoIncludedIn saarecord:7fa55372-ff89-3224-e053-b784100a61db .
  ?adr dcterms:title ?adrstr .
  optional{
    ?adr schema:floorLevel ?floor .
    optional{
      ?floorconcept skos:altLabel ?floor .
      ?floorconcept skos:prefLabel ?floorlabel . 
    }
  }
} 
";

//echo $sparql;
$endpoint = 'https://api.druid.datalegend.net/datasets/menno/Streetlife/services/Streetlife/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

$floors = array();

foreach ($data['results']['bindings'] as $row) {

  if(isset($row['floorconcept']['value'])){
    $floors[$row['floorlabel']['value']][] = array(
      "link" => str_replace("https://ams-migrate.memorix.io/resources/records/","https://archief.amsterdam/indexen/deeds/",$row['deed']['value']),
      "label" => $row['adrstr']['value'],
      "bron" => "woningkaart",
      "periode" => "1924-1989"
    );
  }else{
    $floors['onbekend of hele huis'][] = array(
      "link" => str_replace("https://ams-migrate.memorix.io/resources/records/","https://archief.amsterdam/indexen/deeds/",$row['deed']['value']),
      "label" => $row['adrstr']['value'],
      "bron" => "woningkaart",
      "periode" => "1924-1989"
    );
  }

}


// Joods Monument, floorlevels

$sparql = "
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX schemahttp: <http://schema.org/>
PREFIX schemahttps: <https://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX roar: <https://w3id.org/roar#>
SELECT ?jmloc ?adrstr ?floor ?floorconcept ?floorlabel ?jmp ?jmplabel WHERE {
  VALUES ?aladr { <" . $thisaddress['uri'] . "> ";

  foreach($otheraddresses as $k => $v){
    $sparql .= "<" . $k . "> ";
  }

  $sparql .= " }
  ?jmloc schemahttps:address ?aladr .
  ?jmloc rdfs:label ?adrstr .
  ?jmloc roar:documentedIn <https://www.joodsmonument.nl/> .
  optional{
    ?jmloc schemahttps:floorLevel ?floorconcept .
    ?floorconcept skos:prefLabel ?floorlabel .
  }
  ?jmp roar:hasLocation/rdf:value ?jmloc .
  ?jmp rdfs:label ?jmplabel .
} 
";

//echo $sparql;
$endpoint = 'https://api.druid.datalegend.net/datasets/menno/Streetlife/services/Streetlife/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


foreach ($data['results']['bindings'] as $row) {

  if(isset($row['floorconcept']['value'])){
    $floors[$row['floorlabel']['value']][] = array(
      "link" => $row['jmp']['value'],
      "label" => $row['jmplabel']['value'],
      "bron" => "Joods Monument",
      "periode" => "1940-1945"
    );
  }else{
    $floors['onbekend of hele huis'][] = array(
      "link" => $row['jmp']['value'],
      "label" => $row['jmplabel']['value'],
      "bron" => "Joods Monument",
      "periode" => "1940-1945"
    );
  }

}


// ERR, en kijken of we daar floorlevels van kunnen maken
$sparql = "
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX schemahttp: <http://schema.org/>
PREFIX schemahttps: <https://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX roar: <https://w3id.org/roar#>
SELECT ?errloc ?errdoc ?adrstr (GROUP_CONCAT(?errplabel;separator=\" | \") AS ?persons)  WHERE {
  VALUES ?aladr { <" . $thisaddress['uri'] . "> ";

  foreach($otheraddresses as $k => $v){
    $sparql .= "<" . $k . "> ";
  }

  $sparql .= " }
  ?errloc schemahttps:address ?aladr .
  ?errloc rdfs:label ?adrstr .
  ?errloc roar:documentedIn ?errdoc .
  ?errdoc schemahttps:isPartOf <https://data.niod.nl/temp-archiefid/093a>
  optional{
    ?errp roar:hasLocation/rdf:value ?errloc .
    ?errp rdfs:label ?errplabel .
  }
} group by ?errloc ?errdoc ?adrstr
";

//echo $sparql;
$endpoint = 'https://api.druid.datalegend.net/datasets/menno/Streetlife/services/Streetlife/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


foreach ($data['results']['bindings'] as $row) {

  $names = "";
  if(strlen($row['persons']['value'])){
    $names = " (" . $row['persons']['value'] . ")";
  }

  $found = false;
  preg_match("/ [0-9]+( |\.)(.+)$/",$row['adrstr']['value'],$found);
  if($found && isset( $floorlevellabels[$found[2]] )){
    $floors[$floorlevellabels[$found[2]]][] = array(
      "link" => "../bronnen/err-inboedels/document.php?doc=" . $row['errdoc']['value'],
      "label" => $row['adrstr']['value'] . $names,
      "bron" => "ERR",
      "periode" => "1942-1943"
    );
  }else{
    $floors['onbekend of hele huis'][] = array(
      "link" => "../bronnen/err-inboedels/document.php?doc=" . $row['errdoc']['value'],
      "label" => $row['adrstr']['value'] . $names,
      "bron" => "ERR",
      "periode" => "1942-1943"
    );
  }

}


// Diamantwerkers, en kijken of we daar floorlevels van kunnen maken
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
  VALUES ?lp { ";
  foreach($lpids as $k => $v){
    $sparql .= "<https://iisg.amsterdam/resource/andb/lp/" . $v . "> ";
  }
  $sparql .= " }
  ?adres owl:sameAs ?lp .
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

foreach ($data['results']['bindings'] as $row) {


  $name = "";
  if(isset($row['residentlabel']['value'])){
    $name = "" . $row['residentlabel']['value'] . " | ";
  }

  $dates = array();
  if(isset($row['birth'])){
    $dates[] = "geboren " . substr($row['birth']['value'],0,4);
  }
  if(isset($row['membersince'])){
    $dates[] = "lid sinds " . substr($row['membersince']['value'],0,4);
  }

  $adreslabels = explode(",",$row['labels']['value']); // snap niet waarom query meer dan 1 adres zou opleveren

  $found = false;
  preg_match("/ [0-9]+( |\.)(.+)$/",trim($adreslabels[0]),$found);

  if($found && isset( $floorlevellabels[$found[2]] )){
    $floors[$floorlevellabels[$found[2]]][] = array(
      "link" => "https://diamantbewerkers.nl/en/detail?id=" . $row['resident']['value'],
      "label" => $name . $adreslabels[0],
      "bron" => "ANDB",
      "periode" => implode(", ",$dates)
    );
  }else{
    $floors['onbekend of hele huis'][] = array(
      "link" => "https://diamantbewerkers.nl/en/detail?id=" . $row['resident']['value'],
      "label" => $name . $adreslabels[0],
      "bron" => "ANDB",
      "periode" => implode(", ",$dates)
    );
  }

}


ksort($floors);
?>






<div class="container-fluid" id="main">

  <h1><?= $thisaddress['label'] ?></h1>

  <div class="row">

    <div class="col-md-6">
      <h4>Deze specifieke adresobservatie:</h4>
      <ul>
        <li>Adamlink adres: <a href="<?= $thisaddress['uri'] ?>"><?= $thisaddress['uri'] ?></a></li>
        <li>Adamlink straat: <a href="<?= $thisaddress['straat'] ?>"><?= $thisaddress['straat'] ?></a></li>
        <li>Bron: <?= $thisaddress['bron'] ?></li>
      </ul>
    </div>

    <div class="col-md-6">
      <h4>Ook op deze locatie waargenomen adressen:</h4>
      <ul>
      <?php foreach ($otheraddresses as $key => $value) { ?>
        <li><?= $value['year'] ?> | <?= $value['label'] ?> | <a href="<?= $value['uri'] ?>"><?= $value['uri'] ?></a></li>
      <?php } ?>
      </ul>
    </div>

</div>


  <h2>Per verdieping of wooneenheid</h2>

  <div class="row">

    <?php

    foreach ($floors as $floorlabel => $floordata) {



      ?>
        <div class="col-md-4">
          <div class="personblock">
            <h3><?= $floorlabel ?></h3>

            <?php foreach ($floordata as $obs) { ?>
              <a target="_blank" href="<?= $obs['link'] ?>"><?= $obs['label'] ?></a><br />
              <div class="evensmaller" style="padding-bottom: 8px;"><?= $obs['bron'] ?> | <?= $obs['periode'] ?></div>
            <?php } ?>

            
          </div>

        </div>

      <?php
      
    }

    ?>

  </div>

  <h2>Afbeeldingen</h2>

  <div class="row">

    <div class="col-md-4">
      

    </div>

  </div>


  <h2>Personen</h2>

  <div class="row">

    <div class="col-md-4">
      

    </div>

  </div>

  

</div>


<?php

include("../_parts/footer.php");

?>
