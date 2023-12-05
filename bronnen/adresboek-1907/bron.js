$(document).ready(function() {
    refreshMap();
    showBron();

    removeLayers();
    tileLayer = layer1909.addTo(map);
});




function refreshMap(){

    var urlparams = get_query();
    //console.log(urlparams['straat'])

    $.ajax({
        type: 'GET',
        url: 'bronnen/adresboek-1907/geojson.php',
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

                    var markertitle = feature.properties.occupants.length + ' vermeldingen'
                    if(feature.properties.cnt == 1){
                        var markertitle = feature.properties.occupants.length + ' vermelding'
                    }
                    markertitle += "<br />op dit adres";
                    
                    return new L.CircleMarker(latlng, {
                        color: "#fff",
                        fillColor: "#c70030",
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
                        radius: getSize(feature.properties.occupants.length),
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
    return '#a50026';
}

function showBron() {
    $('#main').load('bronnen/adresboek-1907/over.php');
}

function getSize(d) {
    return  d > 5 ? 20 :
            d > 4 ? 17 :
            d > 3  ? 14 :
            d > 2  ? 11 :
            d > 1 ? 8 :
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
    //console.log(props);

    $("#main").load('bronnen/adresboek-1907/adres.php?occupants=' + encodeURIComponent(JSON.stringify(props)));


}
