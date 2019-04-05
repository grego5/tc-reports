<?php
$id = $_POST['id'];
$notes = $_POST['notes'];

$con=mysqli_connect('localhost', 'curl', 'H8cwZbNm46y7Ft3n', 'curl');
if (mysqli_connect_errno($con)) echo "Failed to connect to MySQL: " . mysqli_connect_error();
$sql="UPDATE players SET notes='$notes' WHERE ID='$id';";
if (!mysqli_query($con,$sql)) die('Error: ' . mysqli_error());

mysqli_close($con);
?>