$(document).ready(function() {
    refreshMap();
    showBron();

    removeLayers();
    tileLayer = layer1985.addTo(map);
});




function refreshMap(){

    var urlparams = get_query();
    console.log(urlparams['straat'])

    $.ajax({
        type: 'GET',
        url: 'bronnen/beeldbank/geojson.php',
        dataType: 'json',
        data: {
            street: urlparams['straat']
        },
        success: function(jsonData) {
            if (typeof lps !== 'undefined') {
                map.removeLayer(lps);
            }

            lps = L.geoJson(null, {
                pointToLayer: function (feature, latlng) { 

                    var markertitle = feature.properties.cnt + ' afbeeldingen van'
                    if(feature.properties.cnt == 1){
                        var markertitle = feature.properties.cnt + ' afbeelding'
                    }
                    $.each(feature.properties.labels,function(index,value){
                        markertitle += "<br />" + value;

                    });    

                    return new L.CircleMarker(latlng, {
                        color: "#fff",
                        fillColor: "#ff612a",
                        radius:8,
                        weight: 0,
                        opacity: 0.7,
                        fillOpacity: 0.7,
                        clickable: true,
                        title: markertitle
                    });
                },
                style: function(feature) {
                    return {
                        radius: getSize(feature.properties.cnt),
                        clickable: true
                    };
                },
                onEachFeature: function(feature, layer) {
                    layer.on({
                        mouseover: rollover,
                        click: whenClicked
                    });
                }
            }).addTo(map);

            lps.addData(jsonData).bringToFront();

            map.fitBounds(lps.getBounds());

            var geojsonprops = jsonData['properties'];
            console.log(geojsonprops);

            var infotext = "<br />searched for:<br />";
            infotext += "<strong>" + geojsonprops['searchedfor'] + "</strong><br /><br />";
            infotext += "<a target=\"_blank\" href=\"geojson.php?q=" + geojsonprops['searchedfor'] + "\">";
            infotext += geojsonprops['nrfound'] + " addresses</a> located on map, ";
            infotext += "<a target=\"_blank\" href=\"not-shown.php?q=" + geojsonprops['searchedfor'] + "\">";
            infotext += geojsonprops['nrnotfound'] + "";
            infotext += " addresses</a> could - for various reasons - not be shown on map";
            $('#searchinfo').html(infotext);
        },
        error: function() {
            console.log('Error loading data');
        }
    });
}

function getColor(props) {
    return '#ff612a';
}

function showBron() {
    $('#main').load('bronnen/beeldbank/over.php');
}

function getSize(d) {
    return  d > 80 ? 25 :
            d > 60 ? 21 :
            d > 40  ? 17 :
            d > 20  ? 13 :
            d > 10  ? 9 :
            d > 5 ? 7 :
                    5 ; 
}

function rollover() {
    var props = $(this)[0].feature.properties;
    //console.log(props)
    this.bindPopup($(this)[0].options.title)
    this.openPopup();
    var self = this;
    setTimeout(function() {
        self.closePopup();
    },1500);
}

function whenClicked(){
    
    var keys = Object.keys(lps._layers)
    keys.forEach(function(key){
        lps._layers[key].setStyle({ 
            weight: 0,
            opacity: 0.7,
            fillOpacity: 0.7
        })
    })

    $(this)[0].setStyle({
        weight: 4,
        opacity: 1,
        fillOpacity: 1
    });

    var props = $(this)[0].feature.properties;
    $("#main").load('bronnen/beeldbank/adres.php?adressen=' + JSON.stringify(props['adressen']));


}
