<?php
header('Content-Type: application/json');
error_reporting(0);
include 'connection.php';

//Load Variables...
$path_id = $_REQUEST['path'];
$lvl = $_REQUEST['lvl'];

$q = "SELECT * FROM `oc_category_path` WHERE `category_id` = '" . $path_id . "' AND `level` = '" . $lvl . "'";
$g = mysqli_query($s_conn, $q) or die($s_conn->error);
if(mysqli_num_rows($g) > 0){
  $r = mysqli_fetch_array($g);
  $x->response = 'GOOD';
  $x->category_id = $r['path_id'];
}else{
  $x->response = 'ERROR';
  $x->category_id = $path_id;
  $x->message = 'No Category Path Info Found!';
}


$response = json_encode($x, JSON_PRETTY_PRINT);
echo $response;