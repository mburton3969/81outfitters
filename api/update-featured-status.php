<?php
error_reporting(0);
include 'connection.php';
$conn = $s_conn;

//Load Variables...
$pid = $_REQUEST['pid'];
$status = $_REQUEST['status'];

$q = "UPDATE `oc_product_description` SET `featured` = '" . $status . "' WHERE `product_id` = '" . $pid . "'";
mysqli_query($conn, $q) or die($conn->error);

if($status == 'Yes'){
  $fcq = "SELECT * FROM `oc_product_to_category` WHERE `category_id` = '608' AND `product_id` = '" . $pid . "'";
  $fcg = mysqli_query($conn, $fcq) or die($conn->error);
  if(mysqli_num_rows($fcg) <= 0){
    $iq = "INSERT INTO `oc_product_to_category` (`category_id`,`product_id`) VALUES ('608','" . $pid . "')";
    mysqli_query($conn, $iq) or die($conn->error);
    $x->debug = 'Item Inserted to DB';
  }else{
    $x->debug = 'Item Already Exists in DB';
  }
}else{
  $dq = "DELETE FROM `oc_product_to_category` WHERE `category_id` = '608' AND `product_id` = '" . $pid . "'";
  mysqli_query($conn, $dq) or die($conn->error);
  $x->debug = 'Item Deleted From DB';
}

$x->response = 'GOOD';

$res = json_encode($x);
echo $res;