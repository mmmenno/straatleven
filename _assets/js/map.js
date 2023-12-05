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

    $("#layerlinks a").on( "click", function(e) {
        e.preventDefault();
        layerid = $(this).attr("id");
        //console.log(layerid);

        removeLayers();

        if(layerid == "layer1876"){
            tileLayer = layer1876.addTo(map);
        }
        if(layerid == "layer1909"){
            tileLayer = layer1909.addTo(map);
        }
        if(layerid == "layer1943"){
            tileLayer = layer1943.addTo(map);
        }
        if(layerid == "layer1985"){
            tileLayer = layer1985.addTo(map);
        }
        
    });
    

});

function removeLayers(){
    if(map.hasLayer(layer1876)){
        map.removeLayer(layer1876);
    }
    if(map.hasLayer(layer1909)){
        map.removeLayer(layer1909);
    }
    if(map.hasLayer(layer1943)){
        map.removeLayer(layer1943);
    }
    if(map.hasLayer(layer1985)){
        map.removeLayer(layer1985);
    }
}

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

    overviewLayer = L.tileLayer('https://tiles.stadiamaps.com/tiles/stamen_toner_lite/{z}/{x}/{y}{r}.{ext}', {
        minZoom: 0,
        maxZoom: 20,
        attribution: '&copy; <a href="https://www.stadiamaps.com/" target="_blank">Stadia Maps</a> &copy; <a href="https://www.stamen.com/" target="_blank">Stamen Design</a> &copy; <a href="https://openmaptiles.org/" target="_blank">OpenMapTiles</a> &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        ext: 'png'
    }).addTo(map);
    

    baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        minZoom: 15,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    layer1876 = L.tileLayer('https://images.huygens.knaw.nl/webmapper/maps/loman/{z}/{x}/{y}.jpeg', {
        attribution: 'map provided by HicSuntLeones',
        maxZoom: 19,
        minZoom:13
    });

    layer1909 = L.tileLayer('https://tiles.create.humanities.uva.nl/atm/publieke-werken-1909/{z}/{x}/{y}.png', {
        attribution: 'map provided by Bert Spaan',
        maxZoom: 19,
        minZoom:13
    });

    layer1943 = L.tileLayer('https://tiles.create.humanities.uva.nl/atm/publieke-werken-1943/{z}/{x}/{y}.png', {
        attribution: 'map provided by Bert Spaan',
        maxZoom: 19,
        minZoom:13
    });

    layer1985 = L.tileLayer('https://tiles.create.humanities.uva.nl/atm/publieke-werken-1985/{z}/{x}/{y}.png', {
        attribution: 'map provided by Bert Spaan',
        maxZoom: 19,
        minZoom:13
    });

    tileLayer = layer1943.addTo(map);

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
              //console.log(geojsonprops);

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
