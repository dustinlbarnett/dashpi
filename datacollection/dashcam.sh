#!/bin/bash

#    dashcam.sh collects image and location data from a Raspberry Pi and writes it to a file.
#    More information can be found here: https://github.com/dustinlbarnett/dashpi
#    Copyright (C) 2015 Dustin Barnett
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.


# Dependencies:
# gpsd
# gpsd-clients
# raspistill
# bc
# BU-353 GPS Adapter

# Instructions:
# Adjust DESTINATION and RASPISTILL variables below for you environment
# Place script at /home/pi/
# Make sure it's executable using chmod +x dashcam.sh
# Edit crontab and add "@reboot /home/pi/dashcam.sh"
#
# Every time rasberry pi is started the script will:
# 1. Attempt to get current time from GPS and set local clock
# 2. Create dashcam directory and data directories under that based on date
# 3. Get gps data by running gpspipe and saving nmea data to file at gps_tracks directory
# 4. Starts loop that takes pictures using raspistill
#    - Takes picture at 1080x1920 with 1 second delay using raspistill
#    - Gets latest $GPGGA sentence from .nmea file @ gps_tracks
#    - Converts nmea coordinates to lat/lng decimal
#    - writes coordinates, filename, and time to imagedata.csv
#    - Starts over with no sleep. Seems to be about 2 seconds probably due to how long it takes to write image file.
#    - stops at 9999 pictures. To go higher must ajust printf command.


# Change this to where you want the dashcam directory
DESTINATION=/home/pi/
# raspistill command. Adjust for quality settings or image rotation as needed.
RASPISTILL="raspistill -t 1000 -n -q 14 -vf -hf -h 1080 -w 1920 -o"

#Stop script if BU-353 GPS is not connected.
if ! lsusb | grep -wq "PL2303"; then
    echo "$DATETIME GPS not detected, exiting..."
    exit 1
    else
    echo "$DATETIME GPS detected"
fi

# Get GPS Time
# Checks if GPS is fixed by getting mode from gpsd output sentences. Mode less than 2 is low quality fix.

ATTEMPT=0
GPSFIX=0
TIMESET=0
MODE=0

while [ $TIMESET -lt 1 ]
        do
	while [ $ATTEMPT -lt 12 ]
		do
		if [ $GPSFIX -lt 1 ]
		then
			while [ $MODE -lt 2 ]
			do
				MODE=`gpspipe -w | head -10 | grep TPV | sed -r 's/.*"mode":([0-9]).*/\1/' | head -1`
				GPSFIX=$MODE
				let "ATTEMPT=ATTEMPT+1"
				TIMESET=10
				sleep 5
			done
		else
			echo "GPS fixed, getting time..."
			GPSDATE=`gpspipe -w | head -10 | grep TPV | sed -r 's/.*"time":"([^"]*)".*/\1/' | head -1`
			echo "date -s $GPSDATE"
			date -s $GPSDATE
			TIMESET=1
			ATTEMPT=100
		fi
	done
done

if [ $ATTEMPT = 100 ]
then
	echo "Success"
else
	echo "Unable to get time from GPS device."
fi


#set date/time variables
TIMESTAMP=$(date +"%H-%M")
DATESTAMP=$(date +"%Y-%m-%d")
DATETIME="$DATESTAMP"_"$TIMESTAMP"

# Create base directories if they don't exist
mkdir -p "$DESTINATION"/dashcam
mkdir -p "$DESTINATION"/dashcam/images/"$DATETIME"
mkdir -p "$DESTINATION"/dashcam/gps_tracks

# collect gps stream. kill gpspipe if running
killall gpspipe
gpspipe -r -o "$DESTINATION"/dashcam/gps_tracks/"$DATETIME".nmea &

# Create gps trip directory base on date/time
mkdir -p "$DESTINATION"/dashcam/images/"$DATETIME"

echo "Filename,Time,Latitude,Longitude" > "$DESTINATION"/dashcam/images/"$DATETIME"/imagedata.csv

#Starting raspistill loop.
j=0
while [ $j -lt 9999 ]; do
 let j=j+1
 FILENAME="`printf %04d $j`.jpg"
 $RASPISTILL "$DESTINATION"/dashcam/images/"$DATETIME"/"$FILENAME"
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
# sleep 1
done
