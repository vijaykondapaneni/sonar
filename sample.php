 <?php
$servername = "wac-reports-test-db.cluster-ctcsmr785ze2.us-east-1.rds.amazonaws.com";
$username = "awsdilan";
$password = "7cx8Ym9rH6dB4naf";
$dbname = "wacReport_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM mill_all_sdk_config_details";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["id"]. " - Name: " . $row["salon_name"]. " " . $row["salon_id"]. "<br>";
    }
} else {
    echo "0 results";
}
$conn->close();
?> 