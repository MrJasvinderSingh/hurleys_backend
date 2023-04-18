<?php
include_once("common.php");
$lat = '34.66600823';
$long = '-77.351545';
//$sql = "select * from `millitarylocations`";
$resultdata = array();
//$sql = "SELECT id, building,  latitude, longitude, 111.045 * DEGREES(ACOS(COS(RADIANS($lat))
// * COS(RADIANS(latitude))
// * COS(RADIANS(longitude) - RADIANS($long))
// + SIN(RADIANS($lat))
// * SIN(RADIANS(latitude))))
// AS distance
//FROM `millitarylocations`
//ORDER BY distance ASC
//LIMIT 0,5;";

$sqlLat = "SELECT id, building,  latitude, longitude, ABS($lat - `latitude`) as distance  FROM `militarylocations` ORDER BY distance ASC LIMIT 2 ";
$sqlLong = "SELECT id, building,  latitude, longitude, ABS($long - `longitude`) as distance  FROM `militarylocations` ORDER BY distance ASC LIMIT 2 ";
$query1 = $obj->MySQLSelect($sqlLat);
$query2 = $obj->MySQLSelect($sqlLong);


$query = array_merge($query1, $query2);
//print_r($query); exit;
//
//
//
//print_r($query);
//$query[0]['id'];
//$query[0]['latitude'];
//$query[0]['longitude'];
//$query[0]['distance'];






function getAddress($latitude,$longitude){
    if(!empty($latitude) && !empty($longitude)){
        //Send request and receive json data by address
        $geocodeFromLatLong = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($latitude).','.trim($longitude).'&sensor=false'); 
        $output = json_decode($geocodeFromLatLong);
        $status = $output->status;
        //Get address from json data
        $address = ($status=="OK")?$output->results[1]->formatted_address:'';
        //Return address of the given latitude and longitude
        if(!empty($address)){
            return $address;
        }else{
            return false;
        }
    }else{
        return false;   
    }
}


//$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$long&sensor=true&key=";
//$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//if(curl_errno($ch)) {  echo 'Curl error: '.curl_error($ch); }
//$result = curl_exec($ch);
//if($result === false) {  echo "Error in cURL : " . curl_error($ch); }
//curl_close($ch);
//
//
$result = '{ "plus_code" : { "compound_code" : "MJ8X+C9 Jacksonville, NC, USA", "global_code" : "8764MJ8X+C9" }, "results" : [ { "address_components" : [ { "long_name" : "233-267", "short_name" : "233-267", "types" : [ "street_number" ] }, { "long_name" : "F Street", "short_name" : "F St", "types" : [ "route" ] }, { "long_name" : "Jacksonville", "short_name" : "Jacksonville", "types" : [ "locality", "political" ] }, { "long_name" : "Camp Lejeune", "short_name" : "Camp Lejeune", "types" : [ "administrative_area_level_3", "political" ] }, { "long_name" : "Onslow County", "short_name" : "Onslow County", "types" : [ "administrative_area_level_2", "political" ] }, { "long_name" : "North Carolina", "short_name" : "NC", "types" : [ "administrative_area_level_1", "political" ] }, { "long_name" : "United States", "short_name" : "US", "types" : [ "country", "political" ] }, { "long_name" : "28546", "short_name" : "28546", "types" : [ "postal_code" ] } ], "formatted_address" : "233-267 F St, Jacksonville, NC 28546, USA", "geometry" : { "location" : { "lat" : 34.6660202, "lng" : -77.3527691 }, "location_type" : "ROOFTOP", "viewport" : { "northeast" : { "lat" : 34.66736918029149, "lng" : -77.35142011970849 }, "southwest" : { "lat" : 34.6646712197085, "lng" : -77.35411808029151 } } }, "place_id" : "ChIJs7q6q78FqYkRyWKcXtOO3X0", "plus_code" : { "compound_code" : "MJ8W+CV Jacksonville, North Carolina, United States", "global_code" : "8764MJ8W+CV" }, "types" : [ "doctor", "establishment", "health", "point_of_interest" ] }, { "address_components" : [ { "long_name" : "261", "short_name" : "261", "types" : [ "street_number" ] }, { "long_name" : "F Street", "short_name" : "F St", "types" : [ "route" ] }, { "long_name" : "Jacksonville", "short_name" : "Jacksonville", "types" : [ "locality", "political" ] }, { "long_name" : "Camp Lejeune", "short_name" : "Camp Lejeune", "types" : [ "administrative_area_level_3", "political" ] }, { "long_name" : "Onslow County", "short_name" : "Onslow County", "types" : [ "administrative_area_level_2", "political" ] }, { "long_name" : "North Carolina", "short_name" : "NC", "types" : [ "administrative_area_level_1", "political" ] }, { "long_name" : "United States", "short_name" : "US", "types" : [ "country", "political" ] }, { "long_name" : "28546", "short_name" : "28546", "types" : [ "postal_code" ] } ], "formatted_address" : "261 F St, Jacksonville, NC 28546, USA", "geometry" : { "location" : { "lat" : 34.6661401, "lng" : -77.35170649999999 }, "location_type" : "RANGE_INTERPOLATED", "viewport" : { "northeast" : { "lat" : 34.6674890802915, "lng" : -77.35035751970848 }, "southwest" : { "lat" : 34.6647911197085, "lng" : -77.3530554802915 } } }, "place_id" : "EiUyNjEgRiBTdCwgSmFja3NvbnZpbGxlLCBOQyAyODU0NiwgVVNBIhsSGQoUChIJI6-uqL8FqYkR0xxyDMq9TdoQhQI", "types" : [ "street_address" ] }, { "address_components" : [ { "long_name" : "Jacksonville", "short_name" : "Jacksonville", "types" : [ "locality", "political" ] }, { "long_name" : "Onslow County", "short_name" : "Onslow County", "types" : [ "administrative_area_level_2", "political" ] }, { "long_name" : "North Carolina", "short_name" : "NC", "types" : [ "administrative_area_level_1", "political" ] }, { "long_name" : "United States", "short_name" : "US", "types" : [ "country", "political" ] } ], "formatted_address" : "Jacksonville, NC, USA", "geometry" : { "bounds" : { "northeast" : { "lat" : 34.815196, "lng" : -77.307847 }, "southwest" : { "lat" : 34.6383188, "lng" : -77.47990089999999 } }, "location" : { "lat" : 34.7540524, "lng" : -77.4302414 }, "location_type" : "APPROXIMATE", "viewport" : { "northeast" : { "lat" : 34.815196, "lng" : -77.307847 }, "southwest" : { "lat" : 34.6383188, "lng" : -77.47990089999999 } } }, "place_id" : "ChIJI2Z0chwFqYkRQS8FP-shj1A", "types" : [ "locality", "political" ] }, { "address_components" : [ { "long_name" : "28546", "short_name" : "28546", "types" : [ "postal_code" ] }, { "long_name" : "Jacksonville", "short_name" : "Jacksonville", "types" : [ "locality", "political" ] }, { "long_name" : "North Carolina", "short_name" : "NC", "types" : [ "administrative_area_level_1", "political" ] }, { "long_name" : "United States", "short_name" : "US", "types" : [ "country", "political" ] } ], "formatted_address" : "Jacksonville, NC 28546, USA", "geometry" : { "bounds" : { "northeast" : { "lat" : 34.987304, "lng" : -77.2513979 }, "southwest" : { "lat" : 34.663367, "lng" : -77.4959691 } }, "location" : { "lat" : 34.8425818, "lng" : -77.4013403 }, "location_type" : "APPROXIMATE", "viewport" : { "northeast" : { "lat" : 34.987304, "lng" : -77.2513979 }, "southwest" : { "lat" : 34.663367, "lng" : -77.4959691 } } }, "place_id" : "ChIJF06_8xwaqYkRwgdmyWQHpCo", "types" : [ "postal_code" ] }, { "address_components" : [ { "long_name" : "Camp Lejeune", "short_name" : "Camp Lejeune", "types" : [ "administrative_area_level_3", "political" ] }, { "long_name" : "Onslow County", "short_name" : "Onslow County", "types" : [ "administrative_area_level_2", "political" ] }, { "long_name" : "North Carolina", "short_name" : "NC", "types" : [ "administrative_area_level_1", "political" ] }, { "long_name" : "United States", "short_name" : "US", "types" : [ "country", "political" ] } ], "formatted_address" : "Camp Lejeune, NC, USA", "geometry" : { "bounds" : { "northeast" : { "lat" : 34.748563, "lng" : -77.156425 }, "southwest" : { "lat" : 34.48425510000001, "lng" : -77.607468 } }, "location" : { "lat" : 34.6250544, "lng" : -77.4013403 }, "location_type" : "APPROXIMATE", "viewport" : { "northeast" : { "lat" : 34.748563, "lng" : -77.156425 }, "southwest" : { "lat" : 34.48425510000001, "lng" : -77.607468 } } }, "place_id" : "ChIJhV2rMJwIqYkRlk31IbkwU7o", "types" : [ "administrative_area_level_3", "political" ] }, { "address_components" : [ { "long_name" : "Onslow County", "short_name" : "Onslow County", "types" : [ "administrative_area_level_2", "political" ] }, { "long_name" : "North Carolina", "short_name" : "NC", "types" : [ "administrative_area_level_1", "political" ] }, { "long_name" : "United States", "short_name" : "US", "types" : [ "country", "political" ] } ], "formatted_address" : "Onslow County, NC, USA", "geometry" : { "bounds" : { "northeast" : { "lat" : 34.984463, "lng" : -77.0966049 }, "southwest" : { "lat" : 34.399705, "lng" : -77.685288 } }, "location" : { "lat" : 34.6540094, "lng" : -77.4701972 }, "location_type" : "APPROXIMATE", "viewport" : { "northeast" : { "lat" : 34.984463, "lng" : -77.0966049 }, "southwest" : { "lat" : 34.399705, "lng" : -77.685288 } } }, "place_id" : "ChIJLWtLVQkIqYkRX3OJ2tLpSP4", "types" : [ "administrative_area_level_2", "political" ] }, { "address_components" : [ { "long_name" : "North Carolina", "short_name" : "NC", "types" : [ "administrative_area_level_1", "political" ] }, { "long_name" : "United States", "short_name" : "US", "types" : [ "country", "political" ] } ], "formatted_address" : "North Carolina, USA", "geometry" : { "bounds" : { "northeast" : { "lat" : 36.5881568, "lng" : -75.400119 }, "southwest" : { "lat" : 33.7528778, "lng" : -84.32186899999999 } }, "location" : { "lat" : 35.7595731, "lng" : -79.01929969999999 }, "location_type" : "APPROXIMATE", "viewport" : { "northeast" : { "lat" : 36.5881568, "lng" : -75.400119 }, "southwest" : { "lat" : 33.7528778, "lng" : -84.32186899999999 } } }, "place_id" : "ChIJgRo4_MQfVIgRGa4i6fUwP60", "types" : [ "administrative_area_level_1", "political" ] }, { "address_components" : [ { "long_name" : "United States", "short_name" : "US", "types" : [ "country", "political" ] } ], "formatted_address" : "United States", "geometry" : { "bounds" : { "northeast" : { "lat" : 71.5388001, "lng" : -66.885417 }, "southwest" : { "lat" : 18.7763, "lng" : 170.5957 } }, "location" : { "lat" : 37.09024, "lng" : -95.712891 }, "location_type" : "APPROXIMATE", "viewport" : { "northeast" : { "lat" : 71.5388001, "lng" : -66.885417 }, "southwest" : { "lat" : 18.7763, "lng" : 170.5957 } } }, "place_id" : "ChIJCzYy5IS16lQRQrfeQ5K5Oxw", "types" : [ "country", "political" ] } ], "status" : "OK" }';

$Fresults = json_decode($result, true); 
function distance($lat1, $lon1, $lat2, $lon2, $unit) {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}

$finalresult = array();
//echo $status = $Fresults['status'];
$count = 1;
foreach($Fresults['results'] as $addData)
{
    if($count < 10)
    {
        $distnce = 0 ;
//    $finalresult[] = array(
//        'address'=>$addData['formatted_address'],
//        'latitude'=>$addData['geometry']['location']['lat'],
//        'longitude'=>$addData['geometry']['location']['lng'],
//        'distance'=> distance($lat, $long, $addData['geometry']['location']['lat'], $addData['geometry']['location']['lng'], 'K')
//    );
  
    $distnce = distance($lat, $long, $addData['geometry']['location']['lat'], $addData['geometry']['location']['lng'], 'K');
            
    if( ($count == 1) && ($query[0]['distance'] <= $distnce))
    {
        $resultdata[] = array(
           'address'=>$query[0]['building'],
       'latitude'=>$query[0]['latitude'],
        'longitude'=>$query[0]['longitude'],
        'distance'=> $query[0]['distance']

        );
    }
    
         $resultdata[] = array(
                'address'=>$addData['formatted_address'],
       'latitude'=>$addData['geometry']['location']['lat'],
        'longitude'=>$addData['geometry']['location']['lng'],
        'distance'=> $distnce
                 );
    
           
    }
    $count++;
}
 echo "<pre>"; print_r($resultdata); echo "</pre><br/>";
?>
