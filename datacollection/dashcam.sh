#!/bin/bash

# Change this to where you want the destination files
DESTINATION=/home/pi/

#Stop script if GPS is not connected.
if ! lsusb | grep -wq "PL2303"; then
    echo "$DATETIME GPS not detected, exiting..."
    exit 1
    else
    echo "$DATETIME GPS detected"
fi

# Get GPS Time 
sudo ./gpstime.sh

#set date/time variables
TIMESTAMP=$(date +"%H-%M")
DATESTAMP=$(date +"%Y-%m-%d")
DATETIME="$DATESTAMP"_"$TIMESTAMP"

# Create base directories if they don't exist
mkdir -p "$DESTINATION"/dashcam
mkdir -p "$DESTINATION"/dashcam/images/"$DATETIME"
mkdir -p "$DESTINATION"/dashcam/gps_tracks

# collect gps stream. kill gpspip if running
killall gpspipe
gpspipe -r -l -o "$DESTINATION"/dashcam/gps_tracks/"$DATETIME".nmea &

# Create gps trip directory base on date/time
mkdir -vp "$DESTINATION"/dashcam/images/"$DATETIME"

#Starting raspistill loop. Loop seems to work better than timelapse. Does exposure adjust in timelapse mode?
j=0
while [ $j -lt 9999 ]; do
 let j=j+1
 #raspistill -t 1000 -n -q 14 -h 1080 -w 1920 -o "$DESTINATION"/dashcam/images/"$DATETIME"/"`printf %04d $j`.jpg"
 raspistill -t 1000 -n -q 14 -vf -hf -h 1080 -w 1920 -o "$DESTINATION"/dashcam/images/"$DATETIME"/"`printf %04d $j`.jpg"
# sleep 1 # no need for sleep if taking a picture every 1 second, camera stays activated for 1 second before taking picture.
done
