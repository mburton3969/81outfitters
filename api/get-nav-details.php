<?php
error_reporting(0);
include 'connection.php';
$conn = $s_conn;//Make the DB Connection the normal variable...

//Load Variables...
$path = $_REQUEST['path'];

//Get Nav Info...
$lq = "SELECT * FROM `oc_category_path` WHERE `path_id` = '" . $path . "'";
$lg = mysqli_query($conn, $lq) or die($conn->error);
$lr = mysqli_fetch_array($lg);
$ilevel = $lr['level'];
//Set Filters Variable...
$x->filters = '0';
switch($ilevel){
  case 0:
    $x->level = '0';
    //Get Parent Name...
    $plq = "SELECT * FROM `oc_category_description` WHERE `category_id` = '" . $path . "'";
    $plg = mysqli_query($conn, $plq) or die($conn->error);
    $plr = mysqli_fetch_array($plg);
    $x->cat_name = $plr['name'];
    $x->parent_path = $plr['category_id'];
    //Set Filters...
    $x->filters = '';
    break;
  case 1:
    $x->level = '1';
    //Get Parent Path...
    $nlq = "SELECT * FROM `oc_category_path` WHERE `category_id` = '" . $path . "' AND `level` = 0";
    $nlg = mysqli_query($conn, $nlq) or die($conn->error);
    $nlr = mysqli_fetch_array($nlg);
    $cat0 = $nlr['path_id'];
    $x->parent_path = $cat0;
    //Get Parent Cat Name...
    $plq = "SELECT * FROM `oc_category_description` WHERE `category_id` = '" . $cat0 . "'";
    $plg = mysqli_query($conn, $plq) or die($conn->error);
    $plr = mysqli_fetch_array($plg);
    $x->cat_name = $plr['name'];
    //Get Category Name...
    $cnq = "SELECT * FROM `oc_category_description` WHERE `category_id` = '" . $path . "'";
    $cng = mysqli_query($conn, $cnq) or die($conn->error);
    $cnr = mysqli_fetch_array($cng);
    $cn = $cnr['name'];
    //Remove parent description...
    $cn = str_replace('Mens ','',$cn);
    $cn = str_replace('Womens ','',$cn);
    $cn = str_replace('Boys ','',$cn);
    $cn = str_replace('Girls ','',$cn);
    $cn = str_replace('Infants/Toddlers ','',$cn);
    $x->cn = $cn;
    //Get Filter for Category Level...
    $caq = "SELECT * FROM `oc_filter_description` WHERE `filter_group_id` = '9' AND `name` = '" . $cn . "'";
    $cag = mysqli_query($conn, $caq) or die($conn->error);
    $car = mysqli_fetch_array($cag);
    $fid = $car['filter_id'];
    //Add Filters...
    $x->filters .= ',' . $fid;
    break;
  case 2:
    $x->level = '2';
    //Get Parent Path...
    $nlq = "SELECT * FROM `oc_category_path` WHERE `category_id` = '" . $path . "' AND `level` = 0";
    $nlg = mysqli_query($conn, $nlq) or die($conn->error);
    $nlr = mysqli_fetch_array($nlg);
    $cat0 = $nlr['path_id'];
    $x->parent_path = $cat0;
    $x->cat_0_id = $cat0;
    //Get Parent Name...
    $plq = "SELECT * FROM `oc_category_description` WHERE `category_id` = '" . $cat0 . "'";
    $plg = mysqli_query($conn, $plq) or die($conn->error);
    $plr = mysqli_fetch_array($plg);
    $x->cat_0_name = $plr['name'];
    //Get Cat 1 Path...
    $nlq = "SELECT * FROM `oc_category_path` WHERE `category_id` = '" . $path . "' AND `level` = 1";
    $nlg = mysqli_query($conn, $nlq) or die($conn->error);
    $nlr = mysqli_fetch_array($nlg);
    $cat1 = $nlr['path_id'];
    $x->cat_1_id = $cat1;
    //Get Category 1 Name...
    $cnq = "SELECT * FROM `oc_category_description` WHERE `category_id` = '" . $cat1 . "'";
    $cng = mysqli_query($conn, $cnq) or die($conn->error);
    $cnr = mysqli_fetch_array($cng);
    $cn = $cnr['name'];
    $x->cat_1_name = $cn;
    //Remove parent description...
    $cn = str_replace('Mens ','',$cn);
    $cn = str_replace('Womens ','',$cn);
    $cn = str_replace('Boys ','',$cn);
    $cn = str_replace('Girls ','',$cn);
    $cn = str_replace('Infants/Toddlers ','',$cn);
    //Get Filter for Category Level...
    $caq = "SELECT * FROM `oc_filter_description` WHERE `filter_group_id` = '9' AND `name` = '" . $cn . "'";
    $cag = mysqli_query($conn, $caq) or die($conn->error);
    $car = mysqli_fetch_array($cag);
    $fid = $car['filter_id'];
    //Add Filters...
    $x->filters .= ',' . $fid;
    //Get Category 2 Name...
    $cnq = "SELECT * FROM `oc_category_description` WHERE `category_id` = '" . $path . "'";
    $cng = mysqli_query($conn, $cnq) or die($conn->error);
    $cnr = mysqli_fetch_array($cng);
    $cn = $cnr['name'];
    $x->cat_2_name = $cn;
    //Remove parent description...
    $cn = str_replace('Mens ','',$cn);
    $cn = str_replace('Womens ','',$cn);
    $cn = str_replace('Boys ','',$cn);
    $cn = str_replace('Girls ','',$cn);
    $cn = str_replace('Infants/Toddlers ','',$cn);
    //Get Filter for Category Level...
    $caq = "SELECT * FROM `oc_filter_description` WHERE `filter_group_id` = '8' AND `name` = '" . $cn . "'";
    $cag = mysqli_query($conn, $caq) or die($conn->error);
    $car = mysqli_fetch_array($cag);
    $fid = $car['filter_id'];
    //Add Filters...
    $x->filters .= ',' . $fid;
    break;
  default:
    $x->level = 'Error';
}

$res = json_encode($x);
echo $res;
  