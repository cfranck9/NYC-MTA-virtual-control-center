<?php

require_once 'vendor/autoload.php';
use transit_realtime\FeedMessage;

date_default_timezone_set('America/New_York');	// otherwise set to PDT for some reason

require("phpsqlajax_dbinfo.php");
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$feed = new FeedMessage();
$feed_code = array("1", "26", "16", "21", "2", "31", "36", "51");//("1", "26", "16", "21", "2", "11", "31", "36", "51");
$a = 1;
$dt = ''; // variable to store contents to be written to xml at the end
$dt = $dt . '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$dt = $dt . '<markers>' . "\n";

for ($i = 0; $i <sizeof($feed_code); $i++){
	$data = file_get_contents("http://datamine.mta.info/mta_esi.php?key=35b55687a860700df2100335c299636b&feed_id=".$feed_code[$i]);
	$feed->parse($data);
	// $route_code; 	// route number + X (for express) + N/S (bound)
	foreach ($feed->getEntityList() as $entity) {
		error_log("trip: " . $entity->getId());
		if ($entity->hasVehicle()) {
			if ($entity->vehicle->current_stop_sequence > 0) {
                $direction = substr($entity->vehicle->trip->trip_id, 10, 1);
				$route_code = $entity->vehicle->trip->route_id . $direction;
				$stop = $entity->vehicle->current_stop_sequence;
				// echo "route:" . $route_code . " stop #: " . $stop . "<br>";

				$sql = "SELECT latitude, longitude FROM line_station_coordinates where route_code = '" . $route_code . "' AND station_sequence = " . $stop;
				$result = $conn->query($sql);
				// while($row = $result->fetch_assoc()) {
				// 	echo " (lat.: " . $row["latitude"] . " lon.: " . $row["longitude"] . ")<br>";
				// }
				$row = $result->fetch_assoc();
                // echo htmlentities('<marker');	// to properly handle "<" symbol, only for echo command (not for $dt)
                if (substr($route_code, 1, 1) == 'X') {
                    $direction = 'X';
                }
				$dt = $dt . '<marker id="' . $a++ . '" name="' . $route_code . '" address="" lat="' . $row["latitude"] . '" lng="' . $row["longitude"] . '" type="' . $direction . '" />' . "\n";
			}
		}
	}
// echo date("H:i:s", time())."<br>";
// echo "all done";
}
$dt = $dt . '</markers>';
file_put_contents('train_locations.xml', $dt);
$conn->close();

?>