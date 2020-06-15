<?php
include 'connection.php';
$conn = $s_conn;

$q = "SELECT * FROM `oc_category_description`";
$g = mysqli_query($conn, $q) or die($conn->error);
while($r = mysqli_fetch_array($g)){
 
  //Loop through all filters to add...
  $fq = "SELECT * FROM `oc_filter_description` WHERE `filter_group_id` = '4' AND `filter_category` != ''";
  $fg = mysqli_query($conn, $fq) or die($conn->error);
  while($fr = mysqli_fetch_array($fg)){
    //Check if exists...
    $eq = "SELECT * FROM `oc_category_filter` 
          WHERE `category_id` = '" . mysqli_real_escape_string($conn, $r['category_id']) . "'
          AND `filter_id` = '" . $fr['filter_id'] . "'";
    $eg = mysqli_query($conn, $eq) or die($conn->error);

    if(mysqli_num_rows($eg) > 0){

      $er = mysqli_fetch_array($eg);
      //Already Exists...
      //echo 'Filter: ' . $fr['name'] . ' exists in Category: ' . $r['name'] . '.<br>';

    }else{

      //Does NOT Exist -- Adding...
      $iq = "INSERT INTO `oc_category_filter`
              (
              `filter_id`,
              `category_id`
              )
              VALUES
              (
              '" . $fr['filter_id'] . "',
              '" . mysqli_real_escape_string($conn, $r['category_id']) . "'
              )";
      mysqli_query($conn, $iq) or die($conn->error);

      echo 'Filter: ' . $fr['name'] . ' Added to Category: ' . $r['name'] . '.<br>';

    }
    
  }
  
}