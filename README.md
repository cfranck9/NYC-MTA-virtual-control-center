# NYC-MTA-virtual-control-center

This PHP-Javascript code is to emulate a control center main display, a big screen showing (quasi-)real-time locations of all trains. It pulls train data from NYC MTA (Metropolitan Transportation Authority) subway feeds and shows on Google map [like this](http://topdori.com/MTA_subway/NYC_subway_positions_v3.html).

## Role of each file

1. *line_station_coordinates.csv* has station ID vs. latitude-longitude pair rows necessary for conversion of ID into coordinates. Rows are grouped by route, where route encodes details such as subway line, direction, regular / express, etc. This file needs to be uploaded to your MySQL database. The raw data published by MTA is available [here](http://web.mta.info/developers/data/nyct/subway/Stations.csv).
2. MySQL authentication / DB info are stored in *phpsqlajax_dbinfo.php*.
3. *MTA_query.php* pulls MTA data through feed links available in [this page](http://datamine.mta.info/list-of-feeds). The route ID, station coordinates and direction for each train is saved to an xml file (train_locations.xml).
4. Javascript code embedded in *NYC_subway_locations.html* reads the xml file and plot each train on the Google map using Google Maps API.
5. *marker_???.png* are symbols for each line. You can find the official color codes [here](http://web.mta.info/developers/resources/line_colors.htm). Note that I am using lighter blue for A/C/E as the original color is too dark to overlay route label. I am uploading them in a separate folder here but they need to be in the same folder as the above files for execution.
6. *MTA_query_test.py* is a short python code I used for quick test & analysis of the data from MTA.

## Notes

1. Shuttle line and Staten Island line are not included.
2. Multiple trains apparently at the terminal stations were excluded for simplicity.
3. random noise were added to latitude and longitude to offset coincident markers (thanks to @brianshim in [this thread](https://github.com/googlemaps/js-marker-clusterer/issues/102)). See lines 66-70 in NYC_subway_locations.html.
4. Add the following require clause to composer.json:
```
"require" : {
		"google/gtfs-realtime-bindings": "0.0.2"
	}
```

## Disclaimer

This is a just-for-fun project at this point. The mapped locations are NOT guaranteed to be precise for a few reasons:
1. Train location data provided by MTA are about 100 seconds old on the average, with 28 seconds of standard deviation. Thus *quasi* but not true real-time.
2. The GTFS data by MTA indicate only the last station the train stopped in the trip at the moment of data request, rather than GPS locations of the train.
3. The station information is given through stop sequence and station ID, which needs some translation. The stop sequence is tricky to interprete due to the complexity of subway operation; the total number of stops for a trip depends on time of a day (late night service), day of a week (weekend schedules are different), rush hour condition (look at 5 / 6 / B / D in Bronx), trip direction (F / N train stops are not symmetrical between uptown and downtown trip in Brooklyn), temporary change due to construction, etc. These are only *partially* reflected in the current version. I am currently examining the interpretation I employed, for further improvement.
