<?php
// Database configuration
$dbhost = '<your hostname>';
$dbuser = '<database user>';
$dbpass = '<database password>';
$dbname = '<database name>';

// Create a new MySQLi connection
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Prepare and execute SELECT query
$query = 'SELECT ID, timestamp, temperature, humidity FROM DataLake';
$result = $mysqli->query($query);

if (!$result) {
    die('Query error: ' . $mysqli->error);
}

// Fetch data and process it
$dataArray = [];
while ($row = $result->fetch_assoc()) {
    $dataArray[] = [
        'ID'          => $row['ID'],
        'timestamp'   => $row['timestamp'],
        'temperature' => (float) $row['temperature'],
        'humidity'    => (float) $row['humidity'],
    ];
}

// Get the last data entry
$lastData = end($dataArray);

// Extract temperature and humidity arrays
$tempArray = array_column($dataArray, 'temperature');
$humiArray = array_column($dataArray, 'humidity');

// Close the result set
$result->free();

// Prepare and execute DELETE query to remove old data
$deleteQuery = 'DELETE FROM DataLake WHERE timestamp < (NOW() - INTERVAL 2 DAY)';
if (!$mysqli->query($deleteQuery)) {
    die('Delete query error: ' . $mysqli->error);
}

// Close the database connection
$mysqli->close();
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Temperature and Humidity Charts</title>
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</head>
<body>
    <div id="chartContainerTemp" style="height: 250px; width: 50%;"></div>
    <div id="chartContainerHumi" style="height: 250px; width: 50%;"></div>
    <div>Last Data Recorded At: <?php echo htmlspecialchars($lastData['timestamp']); ?></div>

    <script>
        window.onload = function () {
            // Temperature chart data
            var tempDataPoints = <?php echo json_encode(array_values($tempArray), JSON_NUMERIC_CHECK); ?>;
            var tempData = tempDataPoints.map(function (value, index) {
                return { x: index, y: value };
            });

            var tempChart = new CanvasJS.Chart("chartContainerTemp", {
                title: {
                    text: "Temperature"
                },
                axisY: {
                    title: "°C"
                },
                data: [{
                    type: "line",
                    dataPoints: tempData
                }]
            });
            tempChart.render();

            // Humidity chart data
            var humiDataPoints = <?php echo json_encode(array_values($humiArray), JSON_NUMERIC_CHECK); ?>;
            var humiData = humiDataPoints.map(function (value, index) {
                return { x: index, y: value };
            });

            var humiChart = new CanvasJS.Chart("chartContainerHumi", {
                title: {
                    text: "Humidity"
                },
                axisY: {
                    title: "%"
                },
                data: [{
                    type: "line",
                    dataPoints: humiData
                }]
            });
            humiChart.render();
        };
    </script>
</body>
</html>
