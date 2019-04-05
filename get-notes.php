<?php
$members = json_decode(urldecode($_POST['data']), true);

$con = mysqli_connect('localhost', 'curl', 'H8cwZbNm46y7Ft3n', 'curl');
if (mysqli_connect_errno($con)) file_put_contents ("error-log.txt", "Failed to connect to MySQL: ".mysqli_connect_error()."\n", 8);

foreach ($members as $i => $member) {
    $members[$i]['notes'] = mysqli_query($con,"SELECT notes FROM players WHERE ID='$member[id]'")->fetch_object()->notes;
}
mysqli_close($con);

echo json_encode($members);