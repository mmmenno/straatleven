<?php


include("../../_infra/functions.php");



$sparql = "
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX schemahttp: <http://schema.org/>
PREFIX schemahttps: <https://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX saa: <https://data.archief.amsterdam/ontology#>
PREFIX hg: <http://rdf.histograph.io/>
PREFIX rt: <https://ams-migrate.memorix.io/resources/recordtypes/>

SELECT ?adr ?label (MIN(?wkt) AS ?wkt) (COUNT(?bbrec) AS ?bbcount) WHERE {
  ?bbrec saa:hasOrHadSubjectAddress ?adr .
  ?adr hg:liesIn <" . $_GET['street'] . "> .
  ?adr rdfs:label ?label .
  ?adr schemahttps:geoContains ?lp .
  ?lp geo:asWKT ?wkt .
  ?bbrec a rt:Image .
  ?bbrec schemahttp:thumbnailUrl ?thumb .
} 
GROUP BY ?adr ?label
LIMIT 1000
";

//echo $sparql;
//die;
$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);

$combined = array(); // er zitten adressen uit verschillende perioden op hetzelfde punt in, en we willen dat bij elkaar

foreach ($data['results']['bindings'] as $key => $value) {

  $adr = str_replace("https://adamlink.nl/geo/address/","",$value['adr']['value']);

  $wkt = $value['wkt']['value'];
  if(!isset($combined[$wkt])){
    $combined[$wkt] = array(
      "cnt" => $value['bbcount']['value'],
      "labels" => array($value['label']['value']),
      "adressen" => array("https://adamlink.nl/geo/address/" . $adr)
    );
  }else{
    $combined[$wkt]['cnt'] = $combined[$wkt]['cnt'] + $value['bbcount']['value'];
    
    $combined[$wkt]['labels'][] = $value['label']['value'];
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