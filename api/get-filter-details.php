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
  $cat = 'No';
  if($r['filter_group_id'] == '9'){//Category
    $cat = 'Yes';
    $cat_lvl = '1';
  }
  if($r['filter_group_id'] == '8'){//Item
    $cat = 'Yes';
    $cat_lvl = '2';
  }
  if($r['filter_group_id'] == '11'){//Type
    $cat = 'Yes';
    $cat_lvl = '3';
  }
  $x->category = $cat;
  $x->cat_level = $cat_lvl;
}else{
  $x->filter_id = '';
  $x->filter_name = '';
  $x->category = '';
  $x->cat_level = '';
}


$response = json_encode($x, JSON_PRETTY_PRINT);
echo $response;