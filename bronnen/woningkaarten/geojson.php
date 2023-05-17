<?php


include("../../_infra/functions.php");


// we halen de coordinaten van de adressen uit adamlink data, kunnen we zien of het klopt

$sparql = "
PREFIX roar: <https://w3id.org/roar#>
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX schema: <https://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX histograph: <http://rdf.histograph.io/>
SELECT ?adres (MIN(?wkt) AS ?wkt) WHERE {
  VALUES ?bron { <https://adamlink.nl/geo/source/S1> <https://adamlink.nl/geo/source/S2> }
  ?adres histograph:liesIn <" . $_GET['street'] . "> .
  ?adres roar:documentedIn ?bron .
  ?adres rdfs:label ?label .
  ?adres schema:geoContains ?lp .
  ?lp geo:asWKT ?wkt
}
GROUP BY ?adres
";

//echo $sparql;
$endpoint = 'https://data.create.humanities.uva.nl/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);

$adressen = array();
foreach($data['results']['bindings'] as $rec){
	$adres = str_replace("https://adamlink.nl/geo/address/","",$rec['adres']['value']);
	$adressen[$adres] = $rec['wkt']['value'];
}

//print_r($adressen);



$sparql = "
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX saa: <https://data.archief.amsterdam/ontology#>
PREFIX rico: <https://www.ica.org/standards/RiC/ontology#>
PREFIX dcterms: <http://purl.org/dc/terms/>
SELECT ?aladr (GROUP_CONCAT(DISTINCT ?adrstr;SEPARATOR=\",\") as ?labels) (COUNT(?deed) AS ?nr) WHERE {
  ?deed saa:isOrWasAlsoIncludedIn <https://ams-migrate.memorix.io/resources/records/7fa55372-ff89-3224-e053-b784100a61db> .
  ?deed saa:isAssociatedWithModernAddress ?adr .
  ?adr a saa:Address .
  ?adr dcterms:title ?adrstr .
  ?adr saa:street <" . $_GET['street'] . "> .
  ?adr owl:sameAs ?aladr .
} 
GROUP BY ?aladr limit 1000
";

//echo $sparql;
//die;
$endpoint = 'https://api.druid.datalegend.net/datasets/menno/Streetlife/services/Streetlife/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);

$combined = array(); // er zitten 1909 en 1943 adressen in, en we willen dat bij elkaar

foreach ($data['results']['bindings'] as $key => $value) {

  $adr = str_replace("https://adamlink.nl/geo/address/","",$value['aladr']['value']);

  if(!isset($adressen[$adr])){
    continue;
  }

  $wkt = $adressen[$adr];
  if(!isset($combined[$wkt])){
    $combined[$wkt] = array(
      "cnt" => $value['nr']['value'],
      "labels" => explode(",",$value['labels']['value']),
      "adressen" => array("https://adamlink.nl/geo/address/" . $adr)
    );
  }else{
    $combined[$wkt]['cnt'] = $combined[$wkt]['cnt'] + $value['nr']['value'];
    
    $combined[$wkt]['labels'][] = $value['labels']['value'];
    $combined[$wkt]['labels'] = array_unique($combined[$wkt]['labels']);

    $combined[$wkt]['adressen'][] = "https://adamlink.nl/geo/address/" . $adr;
    $combined[$wkt]['adressen'] = array_unique($combined[$wkt]['adressen']);

  }
}

//print_r($combined);
//die;


$colprops = array(
	"nrfound" => count($data['results']['bindings'])
);

$contextjson = '{
    "geojson": "https://purl.org/geojson/vocab#",
    "Feature": "geojson:Feature",
    "FeatureCollection": "geojson:FeatureCollection",
    "GeometryCollection": "geojson:GeometryCollection",
    "LineString": "geojson:LineString",
    "MultiLineString": "geojson:MultiLineString",
    "MultiPoint": "geojson:MultiPoint",
    "MultiPolygon": "geojson:MultiPolygon",
    "Point": "geojson:Point",
    "Polygon": "geojson:Polygon",
    "bbox": {
      "@container": "@list",
      "@id": "geojson:bbox"
    },
    "coordinates": {
      "@container": "@list",
      "@id": "geojson:coordinates"
    },
    "features": {
      "@container": "@set",
      "@id": "geojson:features"
    },
    "geometry": "geojson:geometry",
    "id": "@id",
    "properties": "geojson:properties",
    "type": "@type",
    "description": "http://purl.org/dc/terms/description",
    "title": "http://purl.org/dc/terms/title",
    "label": "http://schema.org/name",
	"occupants": { "@reverse": "http://schema.org/address" }
}';
$context = json_decode($contextjson);

$fc = array("@context"=>$context,"type"=>"FeatureCollection", "properties"=>$colprops, "features"=>array());

foreach ($combined as $key => $value) {

	//print_r($value);
	
	$adres = array("type"=>"Feature");

	$wkt = $key;
	$ll = explode(" ",str_replace(array("POINT(",")"),"",$wkt));
	$adres['geometry'] = array(
		"type" => "Point",
		"coordinates" => array($ll[0],$ll[1])
	);
	$props = array(
		"cnt" => $value['cnt'],
		"labels" => $value['labels'],
		"adressen" => $value['adressen']
	);
	$adres['properties'] = $props;
	$fc['features'][] = $adres;
	
}

//echo $i;
//print_r($streetlist);
//die;

$geojson = json_encode($fc);

header('Content-Type: application/json');
echo $geojson;










?>