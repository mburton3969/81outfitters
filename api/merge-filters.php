<?php
include 'connection.php';
$conn = $s_conn;

if($_REQUEST['cat_id'] == ''){
  echo '<h2>No Category ID in Query String...</h2>';
}else{
  
  $fq = "SELECT * FROM `oc_filter_description` WHERE `language_id` = '1'";
  $fg = mysqli_query($conn, $fq) or die($conn->error);
  while($fr = mysqli_fetch_array($fg)){
    
    //Check if exists...
    $eq = "SELECT * FROM `oc_category_filter` 
          WHERE `category_id` = '" . mysqli_real_escape_string($conn, $_REQUEST['cat_id']) . "'
          AND `filter_id` = '" . $fr['filter_id'] . "'";
    $eg = mysqli_query($conn, $eq) or die($conn->error);
    
    if(mysqli_num_rows($eg) > 0){
      
      //Already Exists...
      echo 'Filter: ' . $fr['name'] . ' exists in this Category.<br>';
      
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
              '" . mysqli_real_escape_string($conn, $_REQUEST['cat_id']) . "'
              )";
      mysqli_query($conn, $iq) or die($conn->error);
      
      echo 'Filter: ' . $fr['name'] . ' Added to this Category.<br>';
      
    }
    
  }
  
}