$(document).ready(function() {
    refreshMap();
    showBron();
});




function refreshMap(){

    var urlparams = get_query();
    console.log(urlparams['straat'])

    if(urlparams['straat']=="https://adamlink.nl/geo/street/jodenbreestraat/2158"){
        var geojsonfile = "jodenbreestraat.geojson";
    }else{
        var geojsonfile = "nieuwe-amstelstraat.geojson";
    }

    $.ajax({
        type: 'GET',
        url: 'bronnen/jm/data/' + geojsonfile,
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

                    var markertitle = feature.properties.cnt + ' personen'
                    if(feature.properties.cnt == 1){
                        var markertitle = feature.properties.cnt + ' persoon'
                    }
                    markertitle += "<br />" + feature.properties.label;

                    return new L.CircleMarker(latlng, {
                        color: "#fff",
                        fillColor: "#469d3a",
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

            
        },
        error: function() {
            console.log('Error loading data');
        }
    });
}

function getColor(props) {
    return '#9b289c';
}

function showBron() {
    $('#main').load('bronnen/jm/over.php');
}

function getSize(d) {
    return  d > 20 ? 20 :
            d > 15 ? 17 :
            d > 10  ? 14 :
            d > 5  ? 11 :
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
    $("#main").load('bronnen/jm/adres.php?adres=' + props['adresid']);


}
