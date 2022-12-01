<?php


include("../../_infra/functions.php");


// ophalen en uitspugen om cors problemen te vermijden
$url = "https://addressbooks.amsterdamtimemachine.nl/geojson.php?q=" . urlencode($_GET['street']);
// we halen de coordinaten van de lps uit adamlink data, kunnen we zien of het klopt

$geojson = file_get_contents($url);

header('Content-Type: application/json');
echo $geojson;
die;






?>