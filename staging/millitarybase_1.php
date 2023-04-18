<?php
include_once("common.php");
$lat = '34.66600823';
$long = '-77.351545';
//$sql = "select * from `millitarylocations`";

$sql = "SELECT id, building,  latitude, longitude, 111.045 * DEGREES(ACOS(COS(RADIANS($lat))
 * COS(RADIANS(latitude))
 * COS(RADIANS(longitude) - RADIANS($long))
 + SIN(RADIANS($lat))
 * SIN(RADIANS(latitude))))
 AS distance
FROM `millitarylocations`
ORDER BY distance ASC
LIMIT 0,5;";

$query = $obj->MySQLSelect($sql);




//print_r($query);





?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Military Base</title>
    <style>
      #right-panel {
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }

      #right-panel select, #right-panel input {
        font-size: 15px;
      }

      #right-panel select {
        width: 100%;
      }

      #right-panel i {
        font-size: 12px;
      }
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #map {
        height: 100%;
        float: left;
        width: 70%;
        height: 100%;
      }
      #right-panel {
        margin: 20px;
        border-width: 2px;
        width: 20%;
        height: 400px;
        float: left;
        text-align: left;
        padding-top: 0;
      }
      #directions-panel {
        margin-top: 10px;
/*        background-color: #FFEE77;*/
        padding: 10px;
        overflow: scroll;
        height: 174px;
      }
    </style>
  </head>
  <body>
    <div id="map"></div>
    <div id="right-panel">
    
    <div id="directions-panel"></div>
    </div>
    <script>
      function initMap() {
        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 12,
          center: {lat: <?php echo $lat ?>, lng: <?php echo $long ?>}
        });
        directionsDisplay.setMap(map);

        
          calculateAndDisplayRoute(directionsService, directionsDisplay);
        
      }

      function calculateAndDisplayRoute(directionsService, directionsDisplay) {
        var waypts = [];
        var checkboxArray = document.getElementById('waypoints');
        <?php      foreach ($query as $quee): ?>
            waypts.push({
              location: new google.maps.LatLng(<?php echo $quee['latitude']; ?>, <?php echo$quee['longitude']; ?>),
              stopover: true
            });
         <?php      endforeach; ?> 

        directionsService.route({
          origin: new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $long; ?>),
          destination: new google.maps.LatLng(<?php echo '34.67632222'; ?>, <?php echo '-77.37546667'; ?>),
          waypoints: waypts,
          optimizeWaypoints: true,
          travelMode: 'DRIVING'
        }, function(response, status) {
          if (status === 'OK') {
            directionsDisplay.setDirections(response);
            var route = response.routes[0];
            var summaryPanel = document.getElementById('directions-panel');
            summaryPanel.innerHTML = '';
            // For each route, display summary information.
            for (var i = 0; i < route.legs.length; i++) {
              var routeSegment = i + 1;
              summaryPanel.innerHTML += '<b>Route Segment: ' + routeSegment +
                  '</b><br>';
              summaryPanel.innerHTML += route.legs[i].start_address + ' to ';
              summaryPanel.innerHTML += route.legs[i].end_address + '<br>';
              summaryPanel.innerHTML += route.legs[i].distance.text + '<br><br>';
              
              
              var calDis = route.legs[i].distance.text;
              distancecal = calDis.substring(0, calDis.length - 3);
              
              timetaken = 
              alert(distancecal + route.legs[i].duration.text);
            }
          } else {
            window.alert('Directions request failed due to ' + status);
          }
        });
      }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=&callback=initMap">
    </script>
  </body>
</html>

