<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>jQuery UI Dialog - Default functionality</title>
<link href="video-js/video-js.css" rel="stylesheet">
  <script src="video-js/video.js"></script>
<?php
echo "<link rel=\"stylesheet\" href=\"//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css\">
<script src=\"//code.jquery.com/jquery-1.10.2.js\"></script>
<script src=\"//code.jquery.com/ui/1.11.3/jquery-ui.js\"></script>
<link rel=\"stylesheet\" href=\"/resources/demos/style.css\">
<script>$(function() {\$( \"#dialog\" ).dialog({width: 795, autoOpen: false});\$( \"#opener\" ).click(function() {\$( \"#dialog\" ).dialog( \"open\" );});});</script>
</head>
<body>
<a id=\"opener\" runat=\"server\" href=\"#\">Open Video</a>
<div id=\"dialog\" title=\"\">
<video id=\"my_video_1\" class=\"video-js vjs-default-skin\" controls preload=\"auto\" width=\"768\" height=\"432\" 
  data-setup='{ \"playbackRates\": [0.25, 0.5, 1, 1.5, 2] }'>
    <source src=\"2014-03-03_15-12.mp4\" type='video/mp4'>
  </video>
</div>";
?>
</body>
</html>
