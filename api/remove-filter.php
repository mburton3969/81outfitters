<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
error_reporting(0);
include 'connection.php';

//Load Variables...
$fid = $_REQUEST['fid'];

$q = "DELETE FROM `oc_filter` WHERE `filter_id` = '" . $fid . "'";
$g = mysqli_query($s_conn, $q) or die($s_conn->error);

$q = "DELETE FROM `oc_filter_description` WHERE `filter_id` = '" . $fid . "'";
$g = mysqli_query($s_conn, $q) or die($s_conn->error);

$q = "DELETE FROM `oc_category_filter` WHERE `filter_id` = '" . $fid . "'";
$g = mysqli_query($s_conn, $q) or die($s_conn->error);

$x->response = 'GOOD';


$response = json_encode($x, JSON_PRETTY_PRINT);
echo $response;