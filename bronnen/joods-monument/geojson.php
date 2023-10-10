<?php


include("../../_infra/functions.php");


$sparql = "
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX bag: <http://bag.basisregistraties.overheid.nl/def/bag#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX schema: <https://schema.org/>
PREFIX roar: <https://w3id.org/roar#>
SELECT ?adres ?huisnr ?huisletter (COUNT(DISTINCT(?loc)) AS ?nr) (MIN(?wkt) AS ?wkt) WHERE {
  ?loc roar:documentedIn <https://www.joodsmonument.nl/> .
  ?loc schema:address ?adres .
  ?adres <http://rdf.histograph.io/liesIn> <" . $_GET['street'] . "> .
  ?adres bag:huisnummer ?huisnr .
  optional {
    ?adres bag:huisletter ?huisletter .
  }
  ?adres schema:geoContains ?lp .
  ?lp geo:asWKT ?wkt .
  ?loc rdfs:label ?loclabel .
} 
GROUP BY ?adres ?huisnr ?huisletter
LIMIT 1000
";

//echo $sparql;
//die;
$endpoint = 'https://api.lod.uba.uva.nl/datasets/ATM/ATM-KG/services/ATM-KG/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


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

foreach ($data['results']['bindings'] as $key => $value) {

  $adr = str_replace("https://adamlink.nl/geo/address/","",$value['adres']['value']);

  $huisletter = "";
	if(isset($value['huisletter']['value'])){
    $huisletter = $value['huisletter']['value'];
  }
	
	$adres = array("type"=>"Feature");

	$wkt = $value['wkt']['value'];
	$ll = explode(" ",str_replace(array("POINT(",")"),"",$wkt));
	$adres['geometry'] = array(
		"type" => "Point",
		"coordinates" => array($ll[0],$ll[1])
	);
	$props = array(
		"cnt" => $value['nr']['value'],
		"huisnr" => $value['huisnr']['value'],
    "huisletter" => $huisletter,
    "adressen" => array($value['adres']['value'])
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