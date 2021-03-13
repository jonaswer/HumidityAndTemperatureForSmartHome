<?php


$dbhost = "<your hostname>";
$dbuser = "<select dabase user>";
$dbpass = "<database passwort>";
$db = "<database name>";

$conn = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);

if ($conn)
{
        //echo "connected";
}

$query = "SELECT * FROM DataLake";

$sqlQuery = mysqli_query($conn, $query);

if (!$sqlQuery)
{
        echo "SQL query error";
}

$query2 = "DELETE FROM DataLake WHERE timestamp  < (NOW() - INTERVAL 2 DAY)";

$sqlQuery2 = mysqli_query($conn, $query2);

if (!$sqlQuery2) {
        echo "SQL query2 error";
}

class DataSet
{
        public $dataArray = array();
        public $lastData = array();
        public $xAxis = array();
        public $tempArray = array();
        public $humiArray = array();

        function __construct($sqlQuery){
                while ($row = mysqli_fetch_array($sqlQuery)){
                        $dataArray[]=array($row["ID"],$row["timestamp"],$row["temperature"],$row["humidity"]);
                        $this->dataArray = $dataArray;
                        }
                $this->lastData = end($this->dataArray);
                $this->tempArray = array_column($this->dataArray, '2');
                $this->humiArray = array_column($this->dataArray, '3');
                $this->xAxis = array_column($this->dataArray, '0');

                }

        function getData(){
                return $this->dataArray;
        }
        function getTemp(){
                return $this->tempArray;
        }
        function getHumi(){
                return $this->humiArray;
        }
        function getLastData(){
                return $this->lastData;
        }

}

$Data = new DataSet($sqlQuery);
$conn -> close();

?>

<!DOCTYPE HTML>
<html>
<head>
<script>
window.onload = function () {

//temperature chart
var data = <?php echo json_encode($Data->getTemp(), JSON_NUMERIC_CHECK); ?>;
data = data.map(function (row, index) {
    return {
        x: index,
        y: row
    };
});

var chart = new CanvasJS.Chart("chartContainer", {
    title: {
        text: "Temperature"
    },
    axisY: {
        title: "°C"
    },
    data: [{
        type: "line",
        dataPoints: data
    }]
});

chart.render();

//humidity chart
var data2 = <?php echo json_encode($Data->getHumi(), JSON_NUMERIC_CHECK); ?>;
data2 = data2.map(function (row, index) {
    return {
        x: index,
        y: row
    };
});

var chart2 = new CanvasJS.Chart("chartContainer2", {
    title: {
        text: "Humidity"
    },
    axisY: {
        title: "[%]"
    },
    data: [{
        type: "line",
        dataPoints: data2
    }]
});

chart2.render();

}

</script>

</head>
<body>
<div id="chartContainer" style="height: 250px; width: 50%;"></div>
<div id="chartContainer2" style="height: 250px; width: 50%;"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<div> Letzte Datenaufnahme um: <?php print_r($Data->getLastData()[1]) ?> </div>
</body>
</html>
