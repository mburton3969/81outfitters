<?php
include '../connection.php';
$conn = $s_conn;

$q = "SELECT * FROM `oc_product_description`";
$g = mysqli_query($conn, $q) or die($conn->error);
$i = 1;
while($r = mysqli_fetch_array($g)){
  $cq = "SELECT * FROM `oc_product_to_category` WHERE `product_id` = '" . $r['product_id'] . "'";
  $cg = mysqli_query($conn, $cq) or die($conn->error);
  if(mysqli_num_rows($cg) <= 0){
    echo $i . ' -> ' . $r['product_id'] . ' -> ' . $r['name'] . '<br>';
    $i++;
    
    //Add Categories...
    $iq = "INSERT INTO `oc_product_to_category` (`product_id`,`category_id`)
           VALUES
           ('" . $r['product_id'] . "','252'),
           ('" . $r['product_id'] . "','254'),
           ('" . $r['product_id'] . "','281'),
           ('" . $r['product_id'] . "','371')";
    //mysqli_query($conn, $iq) or die($conn->error);
  }
  
}