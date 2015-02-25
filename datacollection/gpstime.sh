#!/bin/bash

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
