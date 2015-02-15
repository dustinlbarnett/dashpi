<?php
include_once '../includes/sqlconfig2.php';

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$sql = "SELECT GPSPosition FROM imagedata";
$result = mysqli_query($conn, $sql);


//if(isset($_POST['results']) && $_POST['results'] != -1){
if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while($row = mysqli_fetch_assoc($result)) {

$trimmedArray = array_map('trim', $row);
    $emptyRemoved = array_filter($trimmedArray);
//print_r(array_filter($row));  
//echo $row["GPSPosition"];
echo $emptyRemoved["GPSPosition"]; 
 echo "<br>";
    }
} else {
    echo "0 results";
}

mysqli_close($conn);
?> 
