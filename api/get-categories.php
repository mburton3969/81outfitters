<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
error_reporting(0);
include 'connection.php';

//Load Variables...
$api_key = mysqli_real_escape_string($s_conn, $_REQUEST['api_key']);
$parent_id = $_REQUEST['parent_id'];

//Check API Key for Validity...
$aq = "SELECT * FROM `oc_api` WHERE `key` = '" . $api_key . "'";
$ag = mysqli_query($s_conn, $aq) or die($s_conn->error);
if(mysqli_num_rows($ag) <= 0 && 1 == 2){
  $x->status = 'ERROR';
  $x->message = 'API Key Invalid...';
}else{
  
  $x->status = 'GOOD';

  //Get all products...
  $q = "SELECT `oc_category`.`category_id` as `ID`,`oc_category_description`.`name` as `name` 
        FROM `oc_category` 
        LEFT JOIN `oc_category_description` 
        ON `oc_category`.`category_id` = `oc_category_description`.`category_id`
        WHERE `oc_category`.`parent_id` = '" . $parent_id . "' 
        AND `oc_category`.`category_id` != '311' 
        AND `oc_category`.`category_id` != '304' 
        AND `oc_category`.`category_id` != '289'
        ORDER BY `oc_category_description`.`name` ASC";
  $g = mysqli_query($s_conn, $q) or die($s_conn->error);
  $x->categories = [];
  while($r = mysqli_fetch_array($g)){
    $p->category_id = $r['ID'];
    $p->category_name = $r['name'];
    array_push($x->categories, $p);
    $p = '';
  }
  
}

$response = json_encode($x, JSON_PRETTY_PRINT);
echo $response;