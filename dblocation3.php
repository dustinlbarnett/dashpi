<!-- made by dustin -->
<!DOCTYPE html> 
<head>
<meta charset='utf-8' />
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<link rel="stylesheet" href="stylesheet.css">
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-42819035-7', 'auto');
  ga('send', 'pageview');

</script>
<link href="video-js/video-js.css" rel="stylesheet">
<script src="video-js/video.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script>
<link rel="stylesheet" href="/resources/demos/style.css">
<script>$(function() {$( "#dialog" ).dialog({width: 795, autoOpen: false});$( "#opener" ).click(function() {$( "#dialog" ).dialog( "open" );});});</script>
<html>
<body>

<div id="map">
<?php
include_once 'includes/sqlconfigs.php';
session_start();
$videopath = "http://s3-us-west-1.amazonaws.com/dashcam-bucket/videos/";
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
	//$_SESSION['videofile'] = $filepath[0];
	$videofile = $filepath[0];
	

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
echo "attribution: 'Map data &copy; <a href=\"http://openstreetmap.org\">OpenStreetMap</a> contributors, ' +' <a href=\"http://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>, ' + 'Imagery © <a href=\"http://mapbox.com\">Mapbox</a>',";
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
echo "marker.bindPopup('<a href=$imgstore/$largeimg><img src=$imgstore/$filepath[0]/_thumbs/$filename style=width:256px;height:144px></a><br><b>Video: </b><a href=\"videojs.php\" target=\"_blank\" \"toolbar=no, scrollbars=no, resizable=no, width=768, height=432\">$filepath[0]</a><br><b>Date: </b>$date <b>Time: </b>$time <br><b>Location: </b>$lat $lng <br><b>ID: </b>$id').openPopup()";

echo "</script>";

}

// Search by ID section
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
	//$_SESSION['videofile'] = $filepath[0];
	$videofile = $filepath[0];

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
echo "attribution: 'Map data &copy; <a href=\"http://openstreetmap.org\">OpenStreetMap</a> contributors, ' +' <a href=\"http://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>, ' + 'Imagery © <a href=\"http://mapbox.com\">Mapbox</a>',";
echo "id: 'barnettd.kcn6bd59'";
echo "}).addTo(map);";

echo "var RedIcon = L.Icon.Default.extend({options: {iconUrl: 'leaflet/images/marker-icon-red.png' }});";
echo "var redIcon = new RedIcon();";

echo "var planes = $nearbycoords";

echo "for (var i = 0; i < planes.length; i++) {marker = new L.marker([planes[i][1],planes[i][2]]).bindPopup(planes[i][0]).addTo(map);}";

echo "var markerLocation = new L.LatLng($lat, $lng);";
echo "var marker = new L.Marker(markerLocation, {icon: redIcon});";
echo "         map.addLayer(marker);";

//bind popups to the red marker - search by ID
echo "marker.bindPopup('<a href=$imgstore/$largeimg><img src=$imgstore/$filepath[0]/_thumbs/$filename style=width:256px;height:144px></a><br><b>Video: </b><a id=\"opener\" runat=\"server\" href=\"#\">$filepath[0]</a><br><form id=\'map_form_date\' action=\'dbdate.php\' method=\'post\'><label><b>Date: </b></label></><input type=\'hidden\' name=\'locationresults\' value=\'$date\' /><a href=\"javascript:{}\" onclick=\"document.getElementById(\'map_form_date\').submit(); return false;\">$date</a></form><b>Time: </b>$time <br><form id=\'map_form\' action=\'dblocation3.php\' method=\'post\'><label><b>Location: </b></label></><input type=\'hidden\' name=\'locationresults\' value=\'$lat $lng\' /><a href=\"javascript:{}\" onclick=\"document.getElementById(\'map_form\').submit(); return false;\">$lat $lng</a></form><b>ID: </b>$id').openPopup()";

echo "</script>";

//this will be link to video window: 
// <a id=\"opener\" runat=\"server\" href=\"#\">$filepath[0]</a>

// video dialog box
echo "<div id=\"dialog\" title=\"\"><video id=\"my_video_1\" class=\"video-js vjs-default-skin\" controls preload=\"auto\" width=\"768\" height=\"432\" data-setup='{ \"playbackRates\": [0.25, 0.5, 1, 1.5, 2] }'><source src=\"$videopath$videofile.mp4\" type='video/mp4'></video></div>";

// end of main if statement
}
?>

</div>
<div id="searchpanel">
<p>
<form action="dblocation3.php" method="post">
    <Label>Search by ID or Location </label><br>
    <input type="text" name="locationresults" input size="28" value="">
</p>
<p>
<label>Date</label><br>
    <input type="text" value=""/>
</p>
<p>
<label>Results Within:</label>
    <select name="distance">
        <option value="1">1000m</option>
        <option value=".5" selected>500m</option>
        <option value=".25">250m</option>
        <option value=".1">100m</option>
    </select>
</p>
<p>
<input type="submit" value="Search" />
</form>
<form action="index.html">
<input type="submit" value="Reset Map">
</form>
</p>
</form>
</div>
<div id="legend">
Near Match:<img src='leaflet/images/marker-icon.png' height='23'/> Exact Match:<img src='leaflet/images/marker-icon-red.png' height='24'/>
</div>
<div id="notes">
<b>Notes</b><br>
- Dates currently not working. Don't use them.<br>
- Range filter does nothing.<br>
- Map tracks not up to date.<br>
</div>
</body>
</html>
