<?php
header('Content-Type: application/json');
error_reporting(0);
include 'connection.php';

//Load Variables...
$fid = $_REQUEST['fid'];

$q = "SELECT * FROM `oc_filter_description` WHERE `filter_id` = '" . $fid . "'";
$g = mysqli_query($s_conn, $q) or die($s_conn->error);
if(mysqli_num_rows($g) > 0){
  $r = mysqli_fetch_array($g);
  $x->filter_id = $r['filter_id'];
  $x->filter_name = $r['name'];
}else{
  $x->filter_id = '';
  $x->filter_name = '';
}


$response = json_encode($x, JSON_PRETTY_PRINT);
echo $response;