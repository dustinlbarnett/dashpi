<?php
session_start();
$videopath = "http://s3-us-west-1.amazonaws.com/dashcam-bucket/videos/";
$videofile = $_SESSION['videofile'];
echo "<!DOCTYPE html><html><head><meta charset=utf-8 /><title>$videofile</title><link href=\"video-js/video-js.css\" rel=\"stylesheet\"><script src=\"video-js/video.js\"></script></head><body><video id=\"my_video_1\" class=\"video-js vjs-default-skin\" controls preload=\"auto\" width=\"768\" height=\"432\" data-setup='{ \"playbackRates\": [0.25, 0.5, 1, 1.5, 2] }'><source src=\"$videopath$videofile.mp4\" type='video/mp4'></video><script></script></body></html>";
echo "$videopath$videofile.mp4";
?>
