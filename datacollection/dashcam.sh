#!/bin/bash

# Change this to where you want the dashcam directory created
DESTINATION=/home/pi/

#Stop script if GPS is not connected.
if ! lsusb | grep -wq "PL2303"; then
    echo "$DATETIME GPS not detected, exiting..."
    exit 1
    else
    echo "$DATETIME GPS detected"
fi

# Get GPS Time using bash script. 
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
gpspipe -r -o "$DESTINATION"/dashcam/gps_tracks/"$DATETIME".nmea &

# Create gps trip directory base on date/time
mkdir -p "$DESTINATION"/dashcam/images/"$DATETIME"

echo "Filename,Time,Latitude,Longitude" > "$DESTINATION"/dashcam/images/"$DATETIME"/imagedata.csv

#Starting raspistill loop. Loop seems to work better than timelapse. Does exposure adjust in timelapse mode?
j=0
while [ $j -lt 9999 ]; do
 let j=j+1
 FILENAME="`printf %04d $j`.jpg"
 #raspistill -t 1000 -n -q 14 -h 1080 -w 1920 -o "$DESTINATION"/dashcam/images/"$DATETIME"/"`printf %04d $j`.jpg"
 raspistill -t 1000 -n -q 14 -vf -hf -h 1080 -w 1920 -o "$DESTINATION"/dashcam/images/"$DATETIME"/"$FILENAME"
 GPSTIMESTAMP=$(sed -n '/^$GPGGA/p' "$DESTINATION"/dashcam/gps_tracks/"$DATETIME".nmea | tail -n 1)
 GPSTIME=$(echo "$GPSTIMESTAMP" | awk -F ',' '{print $2}')
 NMEALAT=$(echo "$GPSTIMESTAMP" | awk -F ',' '{print $3}')
 NMEALATDIR=$(echo "$GPSTIMESTAMP" | awk -F ',' '{print $4}')
 NMEALNG=$(echo "$GPSTIMESTAMP" | awk -F ',' '{print $5}')
 NMEALNGDIR=$(echo "$GPSTIMESTAMP" | awk -F ',' '{print $6}')

 if [ ${#NMEALAT} -gt 9 ]; then
# echo "LAT Greater than 9"
  LATTEMP=$(echo $NMEALAT | cut -b 1-3)
  LATTEMP2=$(echo $NMEALAT | cut -b 4-)
  LATRESULT=`echo "scale =6; $LATTEMP + ($LATTEMP2/60)" | bc -l`
 else
# echo "LAT 9 or lower"
  LATTEMP=$(echo $NMEALAT | cut -b 1-2)
  LATTEMP2=$(echo $NMEALAT | cut -b 3-)
  LATRESULT=`echo "scale =6; $LATTEMP + ($LATTEMP2/60)" | bc -l`
 fi

 if [ ${#NMEALNG} -gt 9 ]; then
# echo "LNG Greater than 9"
  LNGTEMP=$(echo $NMEALNG | cut -b 1-3)
  LNGTEMP2=$(echo $NMEALNG | cut -b 4-)
  LNGRESULT=`echo "scale =6; $LNGTEMP + ($LNGTEMP2/60)" | bc -l`
 else
 # echo "LNG 9 or lower"
  LNGTEMP=$(echo $NMEALNG | cut -b 1-2)
  LNGTEMP2=$(echo $NMEALNG | cut -b 3-)
  LNGRESULT=`echo "scale =6; $LNGTEMP + ($LNGTEMP2/60)" | bc -l`
 fi
echo "$DATETIME/$FILENAME,$GPSTIME,$LATRESULT $NMEALATDIR,$LNGRESULT $NMEALNGDIR" >> "$DESTINATION"/dashcam/images/"$DATETIME"/imagedata.csv
# echo $FILENAME,$GPSTIMESTAMP >> "$DESTINATION"/dashcam/images/"$DATETIME"/imagedata.csv
# sleep 1
done

