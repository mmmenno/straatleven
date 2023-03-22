<?php

include("_parts/header.php");

if(!isset($_GET['straat'])){
	$_GET['straat'] = "https://adamlink.nl/geo/street/jodenbreestraat/2158";
}
if(!isset($_GET['bron'])){
	//$bron = "diamantbewerkersbond";
}else{
	$bron = $_GET['bron'];
}

?>


<div class="container-fluid" id="subheader">
	<div class="row">
		<div class="col-md-12">
			<p>kies een straat en een bron:</p>

			<form method="get" action="/">
				
				<select name="straat" class="form-select" style="width:30%; display: inline;">
					<option value="https://adamlink.nl/geo/street/nieuwe-amstelstraat/3138" <?php if($_GET['straat'] == "https://adamlink.nl/geo/street/nieuwe-amstelstraat/3138"){ echo "selected"; } ?>>
					Nieuwe Amstelstraat</option>
					<option value="https://adamlink.nl/geo/street/jodenbreestraat/2158" <?php if($_GET['straat'] == "https://adamlink.nl/geo/street/jodenbreestraat/2158"){ echo "selected"; } ?>>
					Jodenbreestraat</option>
				</select>
				
				<select name="bron" class="form-select" style="width:30%; display: inline;">
					<option value="jm" <?php if(isset($bron) && $bron == "jm"){ echo "selected"; } ?>>
						persoonsreconstructies JM
					</option>
					<option value="diamantbewerkersbond" <?php if(isset($bron) && $bron == "diamantbewerkersbond"){ echo "selected"; } ?>>
						diamantbewerkersbond
					</option>
					<option value="marktkaarten" <?php if(isset($bron) && $bron == "marktkaarten"){ echo "selected"; } ?>>
						marktkaarten
					</option>
					<option value="adresboek-1907" <?php if(isset($bron) && $bron == "adresboek-1907"){ echo "selected"; } ?>>
						adresboek 1907
					</option>
					<option value="woningkaarten" <?php if(isset($bron) && $bron == "woningkaarten"){ echo "selected"; } ?>>
						todo: woningkaarten
					</option>
					<option value="bevolkingsregister-1864-1874" <?php if(isset($bron) && $bron == "bevolkingsregister-1864-1874"){ echo "selected"; } ?>>
						bevolkingsregister 1864-1874
					</option>
					<option value="err-inboedels" <?php if(isset($bron) && $bron == "err-inboedels"){ echo "selected"; } ?>>
						todo: ERR inboedels
					</option>
				</select>

				<button class="btn btn-primary">toon op kaart</button>

			</form>


		</div>
	</div>
</div>


<?php if(isset($bron)){ ?>
	<div id="map"></div>
<?php } ?>


<div class="container-fluid" id="main">
	<h1>Straatleven</h1>
	<h2>Vier eeuwen bronnen over bewoners van de Joodse buurt van Amsterdam. Per straat te benaderen, per pand te bekijken. Disclaimer: deze applicatie is nog volop in ontwikkeling!</h2>

	<div class="row">
		
		<div class="col-md-4">
			<div class="personblock">
				<h3>Over de Joodse buurt</h3>
				
				<p>Amsterdam is al eeuwenlang het centrum van Joods leven in Nederland. De Joodse aanwezigheid in Amsterdam begon rond 1600 toen grotere groepen uit Spaanstalige gebieden en uit Antwerpen een veilig heenkomen zochten. Deze Sefardische Joden werden weliswaar geconfronteerd met een aantal beperkingen (men kon bijvoorbeeld geen lid van de gilden worden) maar later vond er een wettelijke gelijkstelling en emancipatie van de Joodse bevolking plaats. Joden hadden een bijnaam voor de stad: Mokum, het Jiddisje woord voor plaats. Voor de Tweede Wereldoorlog woonden in Amsterdam ongeveer 79.000 joden met hun eigen typische beroepen, verenigingen, gewoonten, markten en hun eigen buurten. Het Joodse leven gaf de hoofdstad een heel eigen gezicht. Joden leefden aanvankelijk geïsoleerd maar in de 19e en 20e eeuw traden ze steeds meer naar buiten. Na de oorlog ging het joods leven op veel kleinere schaal verder.</p>
				
				<p>Lees meer over de geschiedenis van de Joodse buurt op de website van het <a href="https://jck.nl/nl/longread/geschiedenis-van-de-joden-amsterdam">Joods Cultureel Kwartier</a>.</p>
				
			</div>
		</div>

		<div class="col-md-4">
			<div class="personblock">
				<h3>Bronnen verbonden</h3>
				
				<p>Dit project is het resultaat van een samenwerking van het Joods Museum en de Amsterdam Time Machine (ATM), een onderzoeksprogramma van de Universiteit van Amsterdam (UvA). De organisaties hebben de handen ineengeslagen om databronnen uit verschillende archieven en collecties over Joods Amsterdam te verbinden. We zijn in deze samenwerking gestart met twee straten: de Jodenbreestraat en de Nieuwe Amstelstraat. Er wordt hierbij gebruikt gemaakt van een eigen dataverzameling van het Joods Museum (opgeslagen in de database nodegoat), de geo-infrastructuur van <a href="https://adamlink.nl/">Adamlink</a>, de ervaring uit eerdere pilots met de ATM en datasets van het Stadsarchief Amsterdam, het Niod en het IISG. Zie voor een overzicht van de datasets de sectie ‘databronnen’ op deze site. Met deze samenwerking doen we ervaring op met het visualiseren van databronnen vanuit verschillende instellingen, voor een publiek van geïnteresseerde Amsterdammers en bezoekers aan het Joods Cultureel Kwartier (JCK). Deze ervaring zal enerzijds benut worden om de mogelijkheden van het combineren van linked data bronnen in de praktijk te demonstreren en anderzijds om te werken aan een publieksapplicatie voor bewoners en bezoekers van het Joods Cultureel Kwartier. Ook wordt nadrukkelijk gekeken naar de mogelijkheden om dit type applicatie op te schalen naar grotere delen van Amsterdam. Voor deze pilot is aansluiting gezocht bij het co-creatie project <a href="https://niw.nl/licht-op-vlooienburg/">Licht op Vlooienburg</a> van de UvA (hoogleraar Bart Wallet en projectleider Julia van der Krieke), het JCK, Aanpak Binnenstad en het Stadsarchief Amsterdam. Dit project stelt zich ten doel vijf eeuwen Joodse aanwezigheid op een creatieve wijze zichtbaarder te maken.</p>
				
			</div>
		</div>

		<div class="col-md-4">
			<div class="personblock">
				<h3>Een tijdmachine voor Amsterdam</h3>
				
				<p>De Amsterdam Time Machine (ATM) is een open onderzoeksinfrastructuur over de geschiedenis van Amsterdam. In deze ‘Google Earth van het verleden’ zullen gebruikers uiteindelijk in staat zijn terug in de tijd te reizen en door de stad te navigeren op het niveau van buurten, straten en huizen. De ATM is onderdeel van de Europese Time Machine organisatie – een alliantie van bijna 600 organisaties uit 40 landen. Onder leiding van de UvA wordt samengewerkt met partners uit het veld van onderzoek, erfgoed, de creatieve industrie en met maatschappelijke organisaties. Het doel is alle digitale historische data van de stad en haar inwoners met elkaar te verbinden en te koppelen aan historische kaarten en 3D modellen. Met deze pilot willen we onderzoeken hoe dit type website opgeschaald kan worden naar een groter deel van Amsterdam. Tevens creëren we een ‘proeftuin’ waar medewerkers van instellingen kunnen zien hoe hun data er uitziet, op de kaart, en hoe de data te verbinden is. De ATM wil met haar projecten bijdragen aan de toekomstige inrichting van de stad onder het motto ‘Understanding the past to help shape the future’.</p>
				
			</div>
		</div>


	</div>

	

</div>


<script src="_assets/js/map.js"></script>

<?php if(isset($bron)){ ?>
	<script src="bronnen/<?= $bron ?>/bron.js"></script>
<?php } ?>

<?php

include("_parts/footer.php");

?>
