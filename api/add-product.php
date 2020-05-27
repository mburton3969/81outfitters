<?php
//header('Content-Type: application/json');
error_reporting(0);
include 'connection.php';

//Load Variables...
$api_key = mysqli_real_escape_string($s_conn, $_REQUEST['api_key']);

//Check API Key for Validity...
$aq = "SELECT * FROM `oc_api` WHERE `key` = '" . $api_key . "'";
$ag = mysqli_query($s_conn, $aq) or die($s_conn->error);
if(mysqli_num_rows($ag) <= 0){
  
  $x->status = 'ERROR';
  $x->message = 'API Key Invalid...';
  
}else{
  $x->response = 'GOOD';
  //Check if product UPC Exists in Database...
  $cq = "SELECT * FROM `oc_product` WHERE `upc` = '" . mysqli_real_escape_string($s_conn,$product_code) . "'";
  $cg = mysqli_query($s_conn, $cq) or die($s_conn->error);
  if(mysqli_num_rows($cg) > 0){
    
    //Adjust Inventory Level up by the quantity...
    $iuq = "UPDATE `oc_product` SET `quantity` = `quantity` + " . intval($product_quantity) . " WHERE `upc` = '" . mysqli_real_escape_string($s_conn,$product_code) . "'";
    mysqli_query($s_conn, $iuq) or die($s_conn->error);
    $x->message = 'Product already exists in database. Inventory level adjusted';
    
  }else{
    $x->message = '';
    
    //Get or add manufacturer ID...
    $mq = "SELECT * FROM `oc_manufacturer WHERE `name` LIKE '%" . mysqli_real_escape_string($s_conn,$product_brand) . "%'";
    $mg = mysqli_query($s_conn, $mq) or die($s_conn->error);
    if(mysqli_num_rows($mg) > 0){
      $product_manufacturer_id = $mr['manufacturer_id'];
    }else{
      $miq = "INSERT INTO `oc_manufacturer` (`name`,`sort_order`) VALUES ('" . mysqli_real_escape_string($s_conn,$product_brand) . "','0')";
      mysqli_query($s_conn, $miq) or die($s_conn->error);
      $product_manufacturer_id = $s_conn->insert_id;
    }
    
    
    //Insert into the products table...
    $iq = "INSERT INTO `oc_product`
          (
          `model`,
          `upc`,
          `quantity`,
          `stock_status_id`,
          `image`,
          `manufacturer_id`,
          `shipping`,
          `price`,
          `tax_class_id`,
          `date_available`,
          `weight`,
          `weight_class_id`,
          `length`,
          `width`,
          `height`,
          `length_class_id`,
          `subtract`,
          `minimum`,
          `status`,
          `date_added`,
          `date_modified`
          )
          VALUES
          (
          'Classic',
          '" . mysqli_real_escape_string($s_conn,$product_code) . "',
          '" . mysqli_real_escape_string($s_conn,$product_quantity) . "',
          '7',
          '" . mysqli_real_escape_string($s_conn,$product_img1) . "',
          '" . $product_manufacturer_id . "',
          '1',
          '" . mysqli_real_escape_string($s_conn,$product_price) . "',
          '9',
          CURRENT_DATE,
          '" . mysqli_real_escape_string($s_conn,$pkg_weight) . "',
          '5',
          '" . mysqli_real_escape_string($s_conn,$product_pkg_length) . "',
          '" . mysqli_real_escape_string($s_conn,$product_pkg_width) . "',
          '" . mysqli_real_escape_string($s_conn,$product_pkg_depth) . "',
          '3',
          '1',
          '1',
          '1',
          CURRENT_DATE,
          CURRENT_DATE
          )";
    $ig = mysqli_query($s_conn, $iq) or die($s_conn->error);
    $new_product_id = $s_conn->insert_id;
    $x->product_id = $new_product_id;
    
    
    //Insert Product Description Details...
    $diq = "INSERT INTO `oc_product_description` 
            (
            `product_id`,
            `language_id`,
            `name`,
            `description`,
            `tag`,
            `meta_title`,
            `meta_description`,
            `meta_keyword`
            )
            VALUES
            (
            '" . $new_product_id . "',
            '1',
            '" . mysqli_real_escape_string($s_conn,$product_title) . "',
            '" . mysqli_real_escape_string($s_conn,htmlentities($product_description)) . "',
            '" . mysqli_real_escape_string($s_conn,$product_title) . " | 81 Outfitters',
            '" . mysqli_real_escape_string($s_conn,$product_title) . " | 81 Outfitters',
            '" . mysqli_real_escape_string($s_conn,$product_title) . " | 81 Outfitters'
            )";
    mysqli_query($s_conn, $diq) or die($s_conn->error);
    
    
    //Insert Product Attributes...
    $is_array = explode(',',$_REQUEST['item_specifics_array']);
    foreach($is_array as $is){
        if($_REQUEST['product_'.$is] != ''){
            
          //Check if Attribute Exists...
          $aq = "SELECT * FROM `oc_attribute_description` WHERE `language_id` = '1' AND `name` LIKE '%" . mysqli_real_escape_string($s_conn,str_replace('_',' ',$is)) . "%'";
          $ag = mysqli_query($s_conn, $aq) or die($s_conn->error);
          if(mysqli_num_rows($ag) <= 0){
            //Get new Attribute ID...
            $aiq = "INSERT INTO `oc_attribute` (`attribute_group_id`,`sort_order`) VALUES ('6','0')";
            $aig = mysqli_query($s_conn, $aiq) or die($s_conn->error);
            $new_attribute_id = $s_conn->insert_id;
            //Add Attribute Details...
            $adiq = "INSERT INTO `oc_attribute_description` (`attribute_id`,`language_id`,`name`) VALUES ('" . $new_attribute_id . "','1','" . mysqli_real_escape_string($s_conn,str_replace('_',' ',$is)) . "')";
            $adig = mysqli_query($s_conn, $adiq) or die($s_conn->error);
          }else{
            //Get Current Attribute ID...
            $ar = mysqli_fetch_array($ag);
            $new_attribute_id = $ar['attribute_id'];
          }
          
          //Insert Attribute Info for Product...
          $paiq = "INSERT INTO `oc_product_attribute`
                    (
                    `product_id`,
                    `attribute_id`,
                    `language_id`,
                    `text`
                    )
                    VALUES
                    (
                    '" . $new_product_id . "',
                    '" . $new_attribute_id . "',
                    '1',
                    '" . mysqli_real_escape_string($s_conn,$_REQUEST['product_'.$is]) . "'
                    )";
          
        }
    }//End foreach loop on attributes...
    
    //Insert All additional images to the system...
    $new_img_array = array_shift($img_array);
    foreach($new_img_array as $img){
      $iiq = "INSERT INTO `oc_product_image` (`product_id`,`image`,`sort_order`) VALUES ('" . $new_product_id . "','" . $img . "','0')";
      mysqli_query($s_conn, $iiq) or die($s_conn->error);
    }
    
    //Setup the Product Filters...
    
    
  }//End If Item Exists Check...
  
}//End API Key Check...

$store_response = json_encode($x, JSON_PRETTY_PRINT);
echo $store_response;
//echo $response;