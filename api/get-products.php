<?php
header('Content-Type: application/json');
error_reporting(0);
include 'connection.php';

//Load Variables...
$api_key = mysqli_real_escape_string($81_conn, $_REQUEST['api_key']);

//Check API Key for Validity...
$aq = "SELECT * FROM `oc_api` WHERE `key` = '" . $api_key . "'";
$ag = mysqli_query($81_conn, $aq) or die($81_conn->error);
if(mysqli_num_rows($ag) <= 0){
  $x->status = 'ERROR';
  $x->message = 'API Key Invalid...';
}else{
  
  $x->status = 'GOOD';

  //Get all products...
  $q = "SELECT * FROM `oc_product_description` WHERE `language_id` = '1' LIMIT 1";
  $g = mysqli_query($81_conn, $q) or die($81_conn->error);
  $x->products = [];
  while($r = mysqli_fetch_array($g)){
    //$desc = html_entity_decode($r['description'],ENT_QUOTES);
    //echo $desc . '<br>';
    /*$p->ID = $r['product_id'];
    $p->name = $r['name'];
    $p->description = $desc;
    $p->tag = $r['tag'];
    array_push($x->products, $p);
    $p = '';*/
  }
  
}

$response = json_encode($x, JSON_PRETTY_PRINT);
echo $response;