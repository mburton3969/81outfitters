<?php
include 'connection.php';
$conn = $s_conn;

//Build Filter array...
$filters1 = explode(',','631');
if (($key = array_search('0', $filters1)) !== false) {
 unset($filters1[$key]);
}
$filters2 = explode(',','0,929,1024');
if (($key = array_search('0', $filters2)) !== false) {
 unset($filters2[$key]);
}
$filters_array = array_merge($filters1,$filters2);

$sql = "SELECT * FROM oc_product p LEFT JOIN oc_product_to_category p2c ON p.product_id = p2c.product_id WHERE p2c.category_id = '371'";
$query = mysqli_query($conn, $sql) or die($conn->error);
//$prods = mysqli_fetch_array($query);
$product_count = 0;
while($prod = mysqli_fetch_array($query)){
  $sql = "SELECT * FROM oc_product_filter WHERE product_id = '" . $prod['product_id'] . "'";
  $query = mysqli_query($conn, $sql) or die($conn->error);
  $filters = mysqli_fetch_array($query);
  
  $pFilters = [];
  while($filter = mysqli_fetch_array($query)){
    array_push($pFilters, $filter['filter_id']);
  }
  
  $add_product = true;
  foreach($filters_array as $f){
    if(in_array($f, $pFilters)){
      //$add_product = true;
    }else{
      $add_product = false;
    }
  }
  if($add_product == true){
    $product_count++;
  }
  
}
echo $product_count;