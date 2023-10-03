<?php 

include("../../_parts/header.php");

include("../../_infra/functions.php");

if(isset($_GET['doc'])){
	$doc = $_GET['doc'];
}else{
	$doc = "https://data.niod.nl/temp-documentid/NL-AsdNIOD_093a_01_00025";
}

$sparql = "
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX schema: <https://schema.org/>
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX roar: <https://w3id.org/roar#>
PREFIX pnv: <https://w3id.org/pnv#>
SELECT * WHERE {
  VALUES ?doc { <" . $doc . "> }
  ?doc schema:additionalType ?doctype .
  ?loc roar:documentedIn ?doc .
  ?loc a roar:LocationObservation .
  ?loc rdfs:label ?loclabel .
  optional{
    ?loc schema:address ?aladr .
  }
  optional{
    ?loc schema:numberOfRooms/rdfs:label ?roomslabel .
  }
  optional{
    ?loc schema:numberOfRooms/rdf:value ?roomsnr .
  }
  optional{
    ?loc schema:description ?locdesc .
  }
  optional{
    ?po roar:hasLocation/rdf:value ?loc .
    ?po a roar:PersonObservation .
    ?po rdfs:label ?poname .
    optional{
      ?po pnv:hasName/pnv:givenName ?givenname .
    }
    optional{
      ?po pnv:hasName/pnv:surnamePrefix ?surnameprefix .
    }
    optional{
      ?po pnv:hasName/pnv:baseSurname ?basesurname .
    }
  }
}
";

$endpoint = 'https://api.druid.datalegend.net/datasets/menno/Streetlife/services/Streetlife/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


$persons = array();
$loc = array(
		"roomslabel" => "",
		"roomsnr" => "",
		"inboedel" => "",
		"adres" => ""
);

foreach ($data['results']['bindings'] as $row) {

	$doc = array(
		"identifier" => str_replace("https://data.niod.nl/temp-documentid/","",$row['doc']['value']),
		"doctype" => $row['doctype']['value']
	);
	$loc["label"] = $row['loclabel']['value'];
	
	if(isset($row['roomslabel']['value'])){
		$loc['roomslabel'] = $row['roomslabel']['value'];
	}

	if(isset($row['roomsnr']['value'])){
		$loc['roomsnr'] = $row['roomsnr']['value'];
	}

	if(isset($row['locdesc']['value'])){
		$loc['inboedel'] = $row['locdesc']['value'];
	}

	if(isset($row['aladr']['value'])){
		$loc['adres'] = $row['aladr']['value'];
	}

	if(isset($row['po']['value'])){
		$person = array("label" => $row['poname']['value']);
		$persons[] = $person;
	}
	
	
}

?>


<div class="container-fluid" id="main">

  <h1>Document <?= $doc['identifier'] ?></h1>

  <p>Een document van het type '<?= $doc['doctype'] ?>' uit <a target="_blank" href="https://www.archieven.nl/mi/298/?mivast=298&mizig=210&miadt=298&micode=093a&miview=inv2">NIOD archief 093a</a>.</p>

  <div class="row">

    
    <div class="col-md-4">
      <div class="personblock">
        <h3>Woning <?= $loc['label'] ?></h3>

        <strong>aantal kamers:</strong><br />
        <?= $loc['roomsnr'] ?> kamers<br /><br />

        <strong>beschrijving:</strong><br />
        <?= $loc['roomslabel'] ?><br /><br />

        <strong>adres:</strong><br />
        <a href="../../adres/?adres=<?= $loc['adres'] ?>"><?= $loc['adres'] ?></a><br />

        
      </div>
    </div>

    <div class="col-md-4">
      <div class="personblock">
        <h3>Inboedel</h3>

        <?= $loc['inboedel'] ?>

        
      </div>
    </div>


    <div class="col-md-4">
      <div class="personblock">
        <h3>Personen</h3>

        <?php foreach($persons as $person){ ?>
        	<strong><?= $person['label'] ?></strong><br />
        <?php } ?>
        

        <p class="small">Om de privacy van eventueel nog levende personen te respecteren worden hier alleen personen getoond die op het Joods Monument zijn teruggevonden. Op de ERR documenten zijn over het algemeen alleen namen vermeld, geboortedatums ontbreken. Gematched is daarom op de combinatie adres en naam.</p>
        
      </div>
    </div>



  </div>

  

</div>



<?php

include("../../_parts/footer.php");

?>