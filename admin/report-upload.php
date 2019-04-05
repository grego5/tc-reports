<?php
$error = '';
$path = 'report/reports/';
$name = $_POST['name'];
$data = urldecode($_POST['data']);

if (file_exists($path.$name)) $error = "File name already exists";
if (!$error) {
    file_put_contents($path.$name, $data);
}
echo json_encode(array(
    'status' => $error ? 'error' : 'ok',
    'error'  => $error
));