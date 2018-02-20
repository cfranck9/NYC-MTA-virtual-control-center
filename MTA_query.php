<?php

require_once 'vendor/autoload.php';
use transit_realtime\FeedMessage;

date_default_timezone_set('America/New_York');	// otherwise set to PDT for some reason
require("phpsqlajax_dbinfo.php");

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$feed = new FeedMessage();
$feed_code = array("1", "26", "16", "21", "2", "31", "36", "51");
$a = 1;
$dt = ''; // variable to store contents to be written to xml at the end
$dt = $dt . '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$dt = $dt . '<markers>' . "\n";

for ($i = 0; $i <sizeof($feed_code); $i++){
	$data = file_get_contents("http://datamine.mta.info/mta_esi.php?key=<your key>&feed_id=".$feed_code[$i]);
	$feed->parse($data);
	foreach ($feed->getEntityList() as $entity) {
		error_log("trip: " . $entity->getId());
		if ($entity->hasVehicle()) {
			if ($entity->vehicle->current_stop_sequence > 0) {
                $direction = substr($entity->vehicle->trip->trip_id, 10, 1);
				$route_code = $entity->vehicle->trip->route_id . $direction;
				$stop = $entity->vehicle->current_stop_sequence;
				$sql = "SELECT latitude, longitude FROM line_station_coordinates where route_code = '" . $route_code . "' AND station_sequence = " . $stop;
				$result = $conn->query($sql);
				$row = $result->fetch_assoc();
                if (substr($route_code, 1, 1) == 'X') {
                    $direction = 'X';
                }
				$dt = $dt . '<marker id="' . $a++ . '" name="' . $route_code . '" address="" lat="' . $row["latitude"] . '" lng="' . $row["longitude"] . '" type="' . $direction . '" />' . "\n";
			}
		}
	}
}
$dt = $dt . '</markers>';
file_put_contents('train_locations.xml', $dt);
$conn->close();

?>