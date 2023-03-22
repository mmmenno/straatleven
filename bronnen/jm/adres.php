<?php 


include("../../_infra/functions.php");


/*
$opts = [
    "http" => [
        "method" => "GET",
        "header" => "Authorization: Bearer rz7Z7GGZuf6KGKL8rMSzImIvNalyX6uGWmAbcWBTrGa6IcWGLa\r\n"
    ]
];

$context = stream_context_create($opts);

$json = file_get_contents($url, false, $context);

*/

$url = "http://jck.nodegoat.io/data/project/2752/type/10340/scope/1/object/?search=https://adamlink.nl/geo/address/" . $_GET['adres'];

$token = "rz7Z7GGZuf6KGKL8rMSzImIvNalyX6uGWmAbcWBTrGa6IcWGLa";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$json = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo $json;

echo $info;

$data = json_decode($json,true);

//print_r($data);

$persons = array();

foreach($data['data']['objects'] as $adres){

	// als we ook buurpanden krijgen, daaraan voorbijgaan
	if(isset($adres['object_definitions'][45197])){
		if(!preg_match("/" . $_GET['adres'] . "/",$adres['object_definitions'][45197]['object_definition_value'])){
			continue;
		}
	}


	foreach($adres['cross_referenced'] as $person){

		$p = array();
		$p['shortBio'] = "";
		$p['sources'] = "";

		//print_r($person);

		$p['literalName'] = $person['object']['object_name'];

		foreach($person['object_definitions'] as $defkey => $def){
			if($defkey == 29165){
				$p['baseSurname'] = $def['object_definition_value'];
			}
			if($defkey == 29166){
				$p['givenName'] = $def['object_definition_value'];
			}
			if($defkey == 36594){
				$p['shortBio'] = $def['object_definition_value'];
			}
			if($defkey == 31115){
				$p['sources'] = urlstolinks($def['object_definition_value']);
			}
		}

		/*$newsources = array();
		$sources = urlstolinks($p['sources']);
		foreach($sources as $s){
			//('Baansgracht', [url=https://archief.amsterdam/indexen/deeds/ea3dd8fd-ee1f-4e2e-a28e-b4f060f5b7d5]Kwijtschelding[/url])
			if(preg_match("/(([^\[]+)?,? ?\[url=([^\[]+)\]([^\[]+)\[\/url\])/",$s,$found)){
				$html = str_replace($found[0],"",$s);
			}else{
				$html = $s;
			}
			
			$newsources[] = $html;
		}
		$p['nsources'] = implode("\n<br />",$newsources);
		*/

		$p['birthyear'] = "?";
		$p['deathyear'] = "?";
			
		foreach($person['object_subs'] as $sub){
			if($sub['object_sub']['object_sub_details_id'] == 11010){
				$p['birthdate'] = array(
					"start" => substr($sub['object_sub']['object_sub_date_start'],0,4),
					"end" => substr($sub['object_sub']['object_sub_date_end'],0,4)
				);
				$p['birthyear'] = $p['birthdate']['start'] . "/" . $p['birthdate']['end'];
				if($p['birthdate']['start'] == $p['birthdate']['end']){
					$p['birthyear'] = $p['birthdate']['start'];
				}
			}
			if($sub['object_sub']['object_sub_details_id'] == 11012){
				$p['deathdate'] = array(
					"start" => substr($sub['object_sub']['object_sub_date_start'],0,4),
					"end" => substr($sub['object_sub']['object_sub_date_end'],0,4)
				);
				$p['deathyear'] = $p['deathdate']['start'] . "/" . $p['deathdate']['end'];
				if($p['deathdate']['start'] == $p['deathdate']['end']){
					$p['deathyear'] = $p['deathdate']['start'];
				}
			}
		}


		//print_r($p);
		$persons[] = $p;
	}
}

function urlstolinks($str){

	$patterns = array();
	$patterns[0] = '/\[url=/';
	$patterns[1] = '/\[\/url\]/';
	$patterns[2] = '/\]/';
	$replacements = array();
	$replacements[2] = '<a href="';
	$replacements[0] = '</a>';
	$replacements[1] = '">';
	return preg_replace($patterns, $replacements, $str);

}

?>

<h2>Persoonsreconstructies JM op dit adres</h2>

<div class="row">

<?php

$nr = count($persons);
$q1 = floor($nr/4);
$q2 = ceil($nr/2);
$q3 = $nr - floor($nr/4);

$i = 0;

foreach ($persons as $key => $person) {

	$i++;

	if($i == 1){
		echo '<div class="col-md-3">';
	}

	?>
		
			<div class="personblock">
				<h3><?= $person['literalName'] ?></h3>

				(<?= $person['birthyear'] ?> - <?= $person['deathyear'] ?>)
				<p><?= $person['shortBio'] ?></p>
				
				bronnen:
				<p class="evensmaller"><?= nl2br($person['sources']) ?></p>
			</div>

	<?php
		
	if($i == $q1 || $i == $q2 || $i == $q3){
		echo '</div><div class="col-md-3">';
	}
	if($i == $nr){
		echo '</div>';
	}
}

?>

</div>