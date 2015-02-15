<!-- made by dustin -->
<!DOCTYPE html> 
<meta charset='utf-8' />
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<link rel="stylesheet" href="stylesheet.css">
<html>
<body>

<div id="map">
<?php
include_once 'includes/sqlconfigs.php';
// Create connection
$conn = new mysqli($servername, $username, $password, 'dashcam');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// convert input from form to latitude and longitude variables
$latlong = $_POST['locationresults'];
list($latitude, $longitude) = explode(" ", $latlong);

$forminputslash = substr_count($latlong, '/');

$forminput = (explode(" ", $latlong));
$forminputnum = count($forminput);
//if statement - greater than 1 = coords

//image storage variable
$imgstore = "http://s3-us-west-1.amazonaws.com/dashcam-bucket/images";

if ($forminputnum > 1) {

// sql query to to find locations within .05km of exact match/input

$sql = "SELECT * FROM (SELECT z.id, z.SourceFile, z.lat, z.lng, z.Time, z.Date, p.radius, p.distance_unit * DEGREES(ACOS(COS(RADIANS(p.latpoint)) * COS(RADIANS(z.lat)) * COS(RADIANS(p.longpoint - z.lng)) + SIN(RADIANS(p.latpoint)) * SIN(RADIANS(z.lat)))) AS distance FROM imagedata AS z JOIN (SELECT $latitude AS latpoint, $longitude AS longpoint, 0.05 AS radius, 111.045 AS distance_unit) AS p WHERE z.lat BETWEEN p.latpoint - (p.radius / p.distance_unit) AND p.latpoint + (p.radius / p.distance_unit) AND z.lng BETWEEN p.longpoint - (p.radius / (p.distance_unit * COS(RADIANS(p.latpoint)))) AND p.longpoint + (p.radius / (p.distance_unit * COS(RADIANS(p.latpoint))))) AS d WHERE distance <= radius ORDER BY distance";
$result = $conn->query($sql);
$resultnumber = 0;
$a = "[";
$c = "];";
$b = "";

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
	$resultnumber++;

	$id = "" . $row["id"]. "";
        $lat = "" . $row["lat"]. "";
        $lng = "" . $row["lng"]. "";
        $date = "" . $row["Date"]. "";
        $time = "" . $row["Time"]. "";
        $largeimg = "" . $row["SourceFile"]. "";

        $filename = substr("$row[SourceFile]",-8);
        $string = "$row[SourceFile]";
        $filepath = explode("/", $string);

        $b .= "[\"<a href=$imgstore/$largeimg><img src=$imgstore/$filepath[0]/_thumbs/$filename style=width:256px;height:144px></a><br><b>Date: </b>$date <b>Time: </b>$time <br><b>Location: </b>$lat $lng <br><b>ID: </b>$id\"," . $row["lat"] .  "," . $row["lng"]. "],";	
    }

} else {
    echo "<center>0 Results</center>";
}
$nearbycoords = $a . $b . $c;

// webpage
echo "<link rel='stylesheet' href='leaflet/leaflet.css' />";
echo "<script src='leaflet/leaflet.js'></script>";
echo "<script>";
echo "var map = L.map('map', { zoomControl:false } ).setView([$latitude, $longitude], 19);";
echo "L.tileLayer('https://{s}.tiles.mapbox.com/v3/{id}/{z}/{x}/{y}.png', {";
echo "maxZoom: 30,";
//echo "attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +' <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' + 'Imagery © <a href="http://mapbox.com">Mapbox</a>',";
echo "id: 'barnettd.kcn6bd59'";
echo "}).addTo(map);";

echo "var RedIcon = L.Icon.Default.extend({options: {iconUrl: 'leaflet/images/marker-icon-red.png' }});";
echo "var redIcon = new RedIcon();";

echo "var planes = $nearbycoords";

echo "for (var i = 0; i < planes.length; i++) {marker = new L.marker([planes[i][1],planes[i][2]]).bindPopup(planes[i][0]).addTo(map);}";


echo "var markerLocation = new L.LatLng($latitude, $longitude);";
echo "var marker = new L.Marker(markerLocation, {icon: redIcon});";
echo "         map.addLayer(marker);";

//bind popups to the red marker
echo "marker.bindPopup('<a href=$imgstore/$largeimg><img src=$imgstore/$filepath[0]/_thumbs/$filename style=width:256px;height:144px></a><br><b>Date: </b>$date <b>Time: </b>$time <br><b>Location: </b>$lat $lng <br><b>ID: </b>$id').openPopup()";
echo "</script>";
}

// ID section
else {

$sql = "SELECT * FROM imagedata WHERE `id`=$forminput[0]";
$result = $conn->query($sql);
$resultnumber = 0;
$a = "[";
$c = "];";
$b = "";

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
///       $resultnumber++;

        $id = "" . $row["id"]. "";
        $lat = "" . $row["lat"]. "";
        $lng = "" . $row["lng"]. "";
        $date = "" . $row["Date"]. "";
        $time = "" . $row["Time"]. "";
        $largeimg = "" . $row["SourceFile"]. "";

        $filename = substr("$row[SourceFile]",-8);
        $string = "$row[SourceFile]";
        $filepath = explode("/", $string);

//        $b .= "[\"<a href=/$largeimg><img src=/$filepath[0]/$filepath[1]/_thumbs/$filename style=width:256px;height:144px></a><br><b>Date: </b>$date <b>Time: </b>$time <br><b>Location: </b>$lat $lng <br><b>ID: </b>$id\"," . $row["lat"] .  "," . $row["lng"]. "],";
    }

} else {
    echo "<center>0 Results</center>";
}
$nearbycoords = $a . $b . $c;

// webpage
echo "<link rel='stylesheet' href='leaflet/leaflet.css' />";
echo "<script src='leaflet/leaflet.js'></script>";
echo "<script>";
echo "var map = L.map('map', { zoomControl:false } ).setView([$lat, $lng], 17);";
echo "L.tileLayer('https://{s}.tiles.mapbox.com/v3/{id}/{z}/{x}/{y}.png', {";
echo "maxZoom: 30,";
echo "id: 'barnettd.kcn6bd59'";
echo "}).addTo(map);";

echo "var RedIcon = L.Icon.Default.extend({options: {iconUrl: 'leaflet/images/marker-icon-red.png' }});";
echo "var redIcon = new RedIcon();";

echo "var planes = $nearbycoords";

echo "for (var i = 0; i < planes.length; i++) {marker = new L.marker([planes[i][1],planes[i][2]]).bindPopup(planes[i][0]).addTo(map);}";

echo "var markerLocation = new L.LatLng($lat, $lng);";
echo "var marker = new L.Marker(markerLocation, {icon: redIcon});";
echo "         map.addLayer(marker);";

//bind popups to the red marker
echo "marker.bindPopup('<a href=$imgstore/$largeimg><img src=$imgstore/$filepath[0]/_thumbs/$filename style=width:256px;height:144px></a><br><b>Date: </b>$date <b>Time: </b>$time <br><form id=\'map_form\' action=\'dblocation3.php\' method=\'post\'><label><b>Location: </b></label></><input type=\'hidden\' name=\'locationresults\' value=\'$lat $lng\' /><a href=\"javascript:{}\" onclick=\"document.getElementById(\'map_form\').submit(); return false;\">$lat $lng</a></form><b>ID: </b>$id').openPopup()";

echo "</script>";

// end of main if statement
}
?>

</div>
<div id="form">
<form action='dblocation3.php' method='post'><Label>Search by ID or Location </label>
<input type='text' name='locationresults' value='' input size="28">
<input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;"/>
</form>
<p>
<form action='index.html' method='post'>
<Label></label>
<input type='submit' name='resetmap' value='Reset'>
</form>
</p>
</div>
<div id="legend">
Near Match:<img src='leaflet/images/marker-icon.png' height='23'/> Exact Match:<img src='leaflet/images/marker-icon-red.png' height='24'/>
</div>
</body>
</html>
