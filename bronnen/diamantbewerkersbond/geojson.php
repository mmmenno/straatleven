<?php


include("../../_infra/functions.php");


// we halen de coordinaten van de lps uit adamlink data, kunnen we zien of het klopt

$sparql = "
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX schema: <https://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX histograph: <http://rdf.histograph.io/>
SELECT ?lp ?wkt WHERE {
  ?adres histograph:liesIn <" . $_GET['street'] . "> .
  ?adres rdfs:label ?label .
  ?adres schema:geoContains ?lp .
  ?lp geo:asWKT ?wkt
}
GROUP BY ?lp ?wkt
LIMIT 1000
";

//echo $sparql;
$endpoint = 'https://data.create.humanities.uva.nl/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);

$lps = array();
foreach($data['results']['bindings'] as $rec){
	$lp = str_replace("https://adamlink.nl/geo/lp/","",$rec['lp']['value']);
	$lps[$lp] = $rec['wkt']['value'];
}

//print_r($lps);



$sparql = "
PREFIX schema: <https://schema.org/>
PREFIX adbandb: <https://iisg.amsterdam/vocab/adb-andb/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
SELECT ?lp (GROUP_CONCAT(DISTINCT ?adreslabel;SEPARATOR=\",\") as ?labels) (count(DISTINCT ?resident) as ?residents) WHERE {
  ?andbstraat owl:sameAs <" . $_GET['street'] . "> .
  ?adres adbandb:street ?andbstraat .
  ?adres rdfs:label ?adreslabel .
  ?adres owl:sameAs ?lp .
  ?residency schema:address ?adres .
  ?resident adbandb:inhabits ?residency .
} 
group by ?lp
LIMIT 1000
";

//echo $sparql;
$endpoint = 'https://api.druid.datalegend.net/datasets/andb/ANDB-ADB-all/services/default/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);


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

	//print_r($value);
	
	$adres = array("type"=>"Feature");

	$lp = str_replace("https://iisg.amsterdam/resource/andb/lp/","",$value['lp']['value']);

	if(!isset($lps[$lp])){
		continue;
	}
	$wkt = $lps[$lp];
	$ll = explode(" ",str_replace(array("POINT(",")"),"",$wkt));
	$adres['geometry'] = array(
		"type" => "Point",
		"coordinates" => array($ll[0],$ll[1])
	);
	$props = array(
		"cnt"=>$value['residents']['value'],
		"labels"=>explode(",",$value['labels']['value']),
		"lp"=>$lp
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