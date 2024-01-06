<html>
<head> This is NOT PRODUCTION </head>
<body>
<h1> All times are GMT. </h1>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<?php
/*
// Literally using an example from the php manual to start with.
$db = new SQLite3('../data/snews_cs/db/snews_db.sqlite');

$results = $db->query('SELECT * FROM all_mgs');

while ($row = $results->fetchArray()) {
	var_dump($row);
}
*/
$beatsdir = '../data/snews_cs/snewsbeats/';

// init htmloutput var
$htmloutput="";
$lastdata = array('detector' => 0, 'time' => '0');

$dirlist = scandir($beatsdir, SCANDIR_SORT_DESCENDING);
foreach($dirlist as $fn) {
	if (preg_match('/^\d{2}-\d{2}-\d{2}_heartbeat_log\.json$/', $fn)) {
		$json = file_get_contents("$beatsdir/$fn");
		$json_data = json_decode(json_decode($json,true)); 
		$datasize = sizeof(get_object_vars($json_data->{'Received Times'}));

		// This one was an exercize in avoiding looping through the json 
		// structure.  It's ugly and wierd, and I think looping would be
		// simpler. I kind of hate this.  Sorry.
		$detectors = array_unique(json_decode(json_encode($json_data->{'Detector'}),true));

		foreach ($detectors as &$det) {
			// Give the javascript function names something unique so 
			// I don't unintentionally clobber with two different "chart0-0" 
			// names for example..
			$chartid = hash('sha256', rand(1,9999).$det);
			$htmloutput .= '
			<script type="text/javascript">
			google.charts.load(\'current\', {\'packages\':[\'corechart\']});
			google.charts.setOnLoadCallback(draw'.$chartid.');
			function draw'.$chartid.'() {
			var data = google.visualization.arrayToDataTable([
			';
			$htmloutput .= "['";
			$htmloutput .= "Time";
			$htmloutput .= "', '";
			$htmloutput .= "Time (seconds) since last beat";
			$htmloutput .=  "'], \n";
			for ($x = 0; $x <= $datasize-1; $x++) {
				if ( $json_data->{'Detector'}->{$x} == $det ) {
					$timestamp = $json_data->{'Received Times'}->{$x};
					$htmloutput .= "\t\t\t['";
					$htmloutput .= date("Y-m-d H:i:s",$timestamp/1000);
					$htmloutput .= "', ";
					$htmloutput .= $json_data->{'Time After Last'}->{$x};
					$htmloutput .= "]";
					if ($x < $datasize) {
						$htmloutput .= ", \n";
					}
					if ($timestamp > $lastdata['time']) {
						$lastdata = ['detector' => $det, 'time' => $timestamp];
					}
					
				} else {
					continue;
				}
			}
			$htmloutput .= "\t\t]);\n";
			$htmloutput .= '
			var options = {
			  title: \'Detector '.$det. ' Performance on '.date("Y-m-d",$timestamp/1000). '\',
			  curveType: \'function\',
			  legend: { position: \'bottom\' }
			};

			var chart = new google.visualization.LineChart(document.getElementById(\'chart'.$chartid.'\'));

			  chart.draw(data, options);
			}
			  </script>
			<div id="chart'.$chartid.'" style="width: 1200px; height: 600px"></div>
			';
		}
	}
}
echo "<br>";
$timecmp = time() - ($lastdata['time']/1000);
if ($timecmp < 7200) {
	echo "<h3><font color=\"green\">The system is probably up. <br>
		Last heartbeat data from: <br>"
		. $lastdata['detector'] . " at " 
		. date("Y-m-d H:i:s",$lastdata['time']/1000) ." GMT"
		." </font></h3>";
} else {
	echo "<body bgcolor=\"yellow\"> 
		<h3><font color=\"red\">The system is probably down. <br>
		Last heartbeat data from: <br>"
		.$lastdata['detector'] . " at " 
		.date("Y-m-d H:i:s",$lastdata['time']/1000) ." GMT"
		."  Come on, fhqwhgads!!</font></h3>";
}
echo $htmloutput;
?>
</body>
</html>
