$(document).ready(function() {

    var urlparams = get_query();
    if ( typeof urlparams['straat'] !== 'undefined') {
        createMap();
    }

    document.body.onkeydown = function(e){
        if(e.keyCode == 32){
            e.preventDefault();
            tileLayer.setOpacity(0)
        }
    };
    document.body.onkeyup = function(e){
        if(e.keyCode == 32){
            tileLayer.setOpacity(1)
        }
    };
    

});

function createMap(){

    $('#subheader').css("padding-top","0");
    center = [52.370216, 4.895168];
    zoomlevel = 14;
    
    map = L.map('map', {
      center: center,
      zoom: zoomlevel,
      minZoom: 1,
      maxZoom: 19,
      scrollWheelZoom: true,
      zoomControl: false
    });

    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);

    overviewLayer = L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/toner-lite/{z}/{x}/{y}{r}.{ext}', {
        attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        subdomains: 'abcd',
        minZoom: 0,
        maxZoom: 15,
        ext: 'png'
    }).addTo(map);
    

    baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        minZoom: 15,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);


    tileLayer = L.tileLayer('https://images.huygens.knaw.nl/webmapper/maps/pw-1909/{z}/{x}/{y}.png', {
        attribution: 'map provided by HicSuntLeones',
        maxZoom: 19,
        minZoom:15
    }).addTo(map);

}

function get_query(){
    var url = document.location.href;
    var qs = url.substring(url.indexOf('?') + 1).split('&');
    for(var i = 0, result = {}; i < qs.length; i++){
        qs[i] = qs[i].split('=');
        result[qs[i][0]] = decodeURIComponent(qs[i][1]);
    }
    return result;
}


  

  function refreshMapoud(){

      


    $.ajax({
          type: 'GET',
          url: 'geojson.php',
          dataType: 'json',
          data: {
            q: "<?= $_GET['q'] ?>"
          },
          success: function(jsonData) {
            if (typeof lps !== 'undefined') {
              map.removeLayer(lps);
            }

            lps = L.geoJson(null, {
              pointToLayer: function (feature, latlng) {                    
                  return new L.CircleMarker(latlng, {
                      color: "#a50026",
                      radius:8,
                      weight: 0,
                      opacity: 0.6,
                      fillOpacity: 0.6,
                      clickable: true
                  });
              },
              onEachFeature: function(feature, layer) {
                layer.on({
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

  function getSize(props) {

    if(props['nlabel'] == null){
      return 6;
    }

    return 6;
  }

function whenClicked(){
   $("#intro").hide();

   var props = $(this)[0].feature.properties;
   console.log(props);
   $("#straatlabel").html('<h2><a target="_blank" href="' + props['wdid'] + '">' + props['label'] + '</a></h2>');

   var occupants = "";
   $.each(props['occupants'],function(index,value){
    occupants += "<a class=\"" + value['part'] + "\" target=\"_blank\" href=\"" + value['scan'] + "\">" + value['label'] + "</a><br />";
    occupants += "<span class=\"small\">[" + value['ocr'] + "]</span><br />";

   });
   $("#occupants").html(occupants);
    
    
}
