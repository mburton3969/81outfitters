<?php

class ControllerEbayFeedCron extends Controller
{
  
    public function updateEbayListing()
    {
        $this->load->model('ebay_feed/cron');
        $this->model_ebay_feed_cron->updateEbayListing();
        echo "Success";
        die;
    }

    public function on_order_history_add($event, $data)
    {
        if (isset($data[0])) {
            /* Update Order Status Flag */
            $updateSQL = "UPDATE " . DB_PREFIX . "kb_ebay_orders SET is_status_updated = '1' WHERE store_order_id = '" . (int) $data[0] . "'";
            $this->db->query($updateSQL);

            /* Update Product Update Flag */
            $orderProductResult = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int) $data[0] . "'");
            if ($orderProductResult->num_rows > 0) {
                foreach ($orderProductResult->rows as $orderProduct) {
                    $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET revise = '1', status='Updated' WHERE id_product = '" . (int) $orderProduct['product_id'] . "' AND status = 'Listed' AND relist='0' AND end = '0'");
                }
            }
        }
    }

    public function onProductUpdate($event, $data)
    {
        die();
    }

        /* To Sync New Products to eBay */
    public function syncLocal()
    {
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', -1);
        @set_time_limit(0);

        $this->load->model('ebay_feed/cron');
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');

        /* Local Sync */
        $this->model_ebay_feed_cron->syncProductsToModule();
        echo "Success";
        die();
    }
    
    /* To Sync New Products to eBay */
    public function listProductToEbay()
    {
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', -1);
        @set_time_limit(0);

        $this->load->model('ebay_feed/cron');
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');
        
        if(!empty($this->config->get('kbebay_demo_flag')) && $this->config->get('kbebay_demo_flag') == 1) {
            echo "Sorry!!! This operation is now allowed in the demo mode.";
            die();
        }

        /* Local Sync */
        $this->model_ebay_feed_cron->syncProductsToModule();

        //Get All New Products
        if (isset($this->request->get['revise']) && $this->request->get['revise'] != "") {
            $call_name = 'ReviseFixedPriceItem';
            if(isset($this->request->get['id_ebay_profile_products']) && $this->request->get['id_ebay_profile_products'] != "") {
                $profile_products = $this->model_ebay_feed_cron->getReviseProducts($this->request->get['id_ebay_profile_products']);
            } else {
                $profile_products = $this->model_ebay_feed_cron->getReviseProducts();
            }
        } else {
            $call_name = 'AddFixedPriceItem';
            if(isset($this->request->get['id_ebay_profile_products']) && $this->request->get['id_ebay_profile_products'] != "") {
                $profile_products = $this->model_ebay_feed_cron->getAllNewProducts($this->request->get['id_ebay_profile_products']);
            } else {
                $profile_products = $this->model_ebay_feed_cron->getAllNewProducts();
            }
        }
        if (!empty($profile_products)) {
            $config = $this->model_ebay_feed_cron->getConfiguration();
            if ($config['account_type'] == 'sandbox') {
                $sandbox = true;
            } else {
                $sandbox = false;
            }
            $profileDetailsDB = $this->model_ebay_feed_cron->getProfileDetails();
            $profile_details = array();
            if (!empty($profileDetailsDB)) {
                foreach ($profileDetailsDB as $profileDetail) {
                    $profile_details[$profileDetail['id_ebay_profiles']] = $profileDetail;
                }
            }
            foreach ($profile_products as $product) {

                //Get Profile details
                if (!isset($profile_details[$product['id_ebay_profiles']])) {
                    $this->model_ebay_feed_cron->insertError($call_name, '{"ShortMessage":"Supported profile is not avaliable.","LongMessage":"Supported profile is not avaliable.","SeverityCode":"Warning"}', "Failure", $product['id_product'], $product['id_ebay_profiles']);
                    continue;
                }

                $profileDetails = $profile_details[$product['id_ebay_profiles']];

                /* If Store currency is not  present then don't add the product */
                $currencyData = $this->model_ebay_feed_cron->getCurrencyByCode($profileDetails['ebay_currency']);
                if (empty($currencyData)) {
                    $this->model_ebay_feed_cron->insertError($call_name, '{"ShortMessage":"Supported currency is not avaliable.","LongMessage":"Supported currency is not avaliable.","SeverityCode":"Warning"}', "Failure", $product['id_product'], $product['id_ebay_profiles']);
                    continue;
                }

                //Get product details
                $product_details = $this->model_ebay_feed_cron->getProduct($product['id_product'], $profileDetails['ebay_language']);
                if (trim($product_details['sku']) != "") {
                    $sku = $product_details['sku'];
                } else {
                    $sku = $product_details['model'];
                }

                if (trim($product_details['special']) != "") {
                    $price = $product_details['special'];
                } else {
                    $price = $product_details['price'];
                }
                $finalPrice = $price;
                
                if ($profileDetails['price_management'] == 1) {

                    /* Check threashold Condition. If threshold price  */
                    //if ($profileDetails['product_threshold_price'] > $finalPrice) {

                        /* In case of fixed */
                        if ($profileDetails['percentage_fixed'] == 0) {

                            /* In case of increase */
                            if ($profileDetails['increase_decrease'] == 1) {
                                $finalPrice = $finalPrice + $profileDetails['product_price'];
                            } else {
                                $finalPrice = $finalPrice - $profileDetails['product_price'];
                            }
                        } else {
                            /* In case of increase */
                            if ($profileDetails['increase_decrease'] == 1) {
                                $finalPrice = $finalPrice + ($finalPrice * $profileDetails['product_price'] / 100);
                            } else {
                                $finalPrice = $finalPrice - ($finalPrice * $profileDetails['product_price'] / 100);
                            }
                        }
                    //}
                }

                if ($profileDetails['vatenabled'] == 'yes' && $profileDetails['vat_percentage'] != "") {
                    $finalPrice = $finalPrice + ($finalPrice * $profileDetails['vat_percentage'] / 100);
                }


                $product_variations = $this->model_ebay_feed_cron->getVariations($product['id_product']);
                $variations = false;
                $attribute_data = array();
                $variation_data = array();
                if (!empty($product_variations)) {
                    //Get All attributes
                    $pro_attributes = $this->model_ebay_feed_cron->getProductOptions($product['id_product'],$profileDetails['ebay_language']);
                    $g = 0;
                    $group_arr = array();
                    foreach ($pro_attributes as $pro_attribute) {
                        foreach ($pro_attribute['product_option_value'] as $data) {
                            $variation_details = $this->model_ebay_feed_cron->getVariationsDetails($data['option_value_id'], $profileDetails['ebay_language']);
                            if (!in_array($pro_attribute['option_id'], $group_arr)) {
                                $group_arr[] = $pro_attribute['option_id'];
                                $attribute_data[$pro_attribute['name']][] = $variation_details[0]['name'];
                                $g++;
                            } else {
                                $attribute_data[$pro_attribute['name']][] = $variation_details[0]['name'];
                            }
                        }
                    }
                    $variations = true;
                    $i = 0;

					$product_variation_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "kb_ebay_profile_product_options WHERE product_id = " . (int) $product_details['product_id'] . " AND profile_id = '" . (int) $product['id_ebay_profiles'] . "'");
                    foreach ($product_variation_query->rows as $product_variation_combination) {

                        //$product_variation
                        if (!empty($variation_details)) {

                            $variation_data[$i]['sku'] = $product_variation_combination['option_sku'];

                            /* Export Product Refernce to Get Product Options */
                            $productVariationInfo = explode("_", $product_variation_combination['option_sku']);
                            $k = 0;
                            $j = 0;
                            $variationPrice = $price;
                            $quantity = 0;
                            $loop_iteration = 0;
                            foreach ($productVariationInfo as $productVariation) {
                                /* Skip for Product ID as First Element in the Reference is Product ID */
                                if ($j == 0) {
                                    $j++;
                                    continue;
                                }
                                $productVariationData = explode(":", $productVariation); /* OptionID:OptionValueID

                                  /** Get Variations Price for each options & Add Price of each option for combination price. Take lowest Quantity of the Item. For Example for Size M & Color Green, Add the price of M & Green & Take Quanity of the Green if lowetst */
                                foreach ($product_variations as $product_variation) {
                                    if ($product_variation['option_value_id'] == $productVariationData[1]) {
                                        if ($loop_iteration == 0 && $quantity == 0) {
                                            $quantity = $product_variation['quantity'];
                                            $loop_iteration++;
                                        } 
                                        if ($quantity > $product_variation['quantity']) {
                                            $quantity = $product_variation['quantity'];
                                        }
                                        $variationPrice = $variationPrice + $product_variation['price'];
                                        break;
                                    }
                                }

                                $variation_details = $this->model_ebay_feed_cron->getVariationsDetails($productVariationData[1], $profileDetails['ebay_language']); /* Option value is at first index */
                                $variation_data[$i]['combi'][$k]['name'] = $variation_details[0]['name_attr'];
                                $variation_data[$i]['combi'][$k]['value'] = $variation_details[0]['name'];
                                $k++;
                                $j++;
                            }

                            /* Change to Custom Change for Alter Quantity. Disable the same */
                            $variation_data[$i]['quantity'] = $quantity;
                            
                            if($profileDetail['product_quantity'] != "" && $profileDetail['product_quantity'] > 0) {
                                if($variation_data[$i]['quantity'] < $profileDetails['product_quantity']) {
                                    $variation_data[$i]['quantity']  = $variation_data[$i]['quantity'];
                                } else {
                                    $variation_data[$i]['quantity']  = $profileDetail['product_quantity'];
                                }
                            }

                            if ($variation_data[$i]['quantity'] < 0) {
                                $variation_data[$i]['quantity'] = 0;
                            }

                            if ($profileDetails['price_management'] == 1) {

                                /* Check threashold Condition. If threshold price  */
                                if ($profileDetails['product_threshold_price'] > $variationPrice) {
                                    /* In case of fixed */
                                    if ($profileDetails['percentage_fixed'] == 0) {

                                        /* In case of increase */
                                        if ($profileDetails['increase_decrease'] == 1) {
                                            $variationPrice = $variationPrice + $profileDetails['product_price'];
                                        } else {
                                            $variationPrice = $variationPrice - $profileDetails['product_price'];
                                        }
                                    } else {
                                        /* In case of increase */
                                        if ($profileDetails['increase_decrease'] == 1) {
                                            $variationPrice = $variationPrice + ($variationPrice * $profileDetails['product_price'] / 100);
                                        } else {
                                            $variationPrice = $variationPrice - ($variationPrice * $profileDetails['product_price'] / 100);
                                        }
                                    }
                                }
                            }
                            if ($variationPrice < 0) {
                                $variationPrice = 0;
                            }
                            if ($profileDetails['vatenabled'] == 'yes' && $profileDetails['vat_percentage'] != "") {
                                $variationPrice = $variationPrice + ($variationPrice * $profileDetails['vat_percentage'] / 100);
                            }
                            $variationPrice = $this->currency->convert($variationPrice, $this->config->get('config_currency'), $profileDetails['ebay_currency']);
                            $variationPriceFormatted = number_format((float) $variationPrice, 2, '.', '');
                            $variation_data[$i]['price'] = $variationPriceFormatted;
                            $i++;
                        }
                    }
                }

                $get_manufacturer = $this->model_ebay_feed_cron->getManufacturer($product_details['manufacturer_id'], $profileDetails['ebay_language']);
                if (!empty($get_manufacturer)) {
                    $manufacturer = $get_manufacturer['name'];
                } else {
                    $manufacturer = "";
                }
                $title = html_entity_decode($product_details['name']);
                $description = html_entity_decode($product_details['description']);
                $ebay_cat = $profileDetails['ebay_category_id'];

                /* Price Conversion */
                if ($finalPrice < 0) {
                    $finalPrice = 0;
                }
                $finalPrice = $this->currency->convert($finalPrice, $this->config->get('config_currency'), $profileDetails['ebay_currency']);
                $priceFormatted = number_format((float) $finalPrice, 2, '.', '');

                $quantity = $product_details['quantity'] + $profileDetails['product_quantity'];
                /* Change to Custom Change for Alter Quantity. Disable the same */
                $quantity = $product_details['quantity'];
                if($profileDetails['product_quantity'] != "" && $profileDetail['product_quantity'] > 0) {
					if($product_details['quantity'] < $profileDetails['product_quantity']) {
						$quantity = $product_details['quantity'];
					} else {
						$quantity = $profileDetails['product_quantity'];
					}
                }

                if ($quantity < 0) {
                    $quantity = 0;
                }
                
                $html_template = html_entity_decode($profileDetails['html_template']);
                $html_template = str_replace("{product_description}", $description, $html_template);
                $html_template = str_replace("{title}", strip_tags($title), $html_template);
                $html_template = str_replace("{product_id}", $product['id_product'], $html_template);
                $html_template = str_replace("{upc}", $product_details['upc'], $html_template);
                $html_template = str_replace("{model}", $product_details['model'], $html_template);
                $html_template = str_replace("{sku}", $product_details['sku'], $html_template);
                $html_template = str_replace("{ean}", $product_details['ean'], $html_template);
                $html_template = str_replace("{meta_keyword}", $product_details['meta_keyword'], $html_template);
                $html_template = str_replace("{meta_description}", $product_details['meta_description'], $html_template);
                $html_template = str_replace("{meta_title}", $product_details['meta_title'], $html_template);
                if($html_template == "") {
                    $html_template = $description;
                }
                $product_arr['auth_token'] = $profileDetails['token'];
                $product_arr['title'] = strip_tags($title);
                $product_arr['description'] = $html_template;
                $product_arr['final_price'] = $priceFormatted;
                $product_arr['manufacturer'] = $manufacturer;
                $product_arr['ebay_cat'] = $ebay_cat;
                $product_arr['store_category'] = $profileDetails['store_category'];
                $product_arr['cat_map_allowed'] = true;
                $product_arr['country'] = $profileDetails['ebay_iso_code'];
                $product_arr['site_id'] = $profileDetails['ebay_site'];
                $product_arr['abbreviation'] = $profileDetails['site_name'];
                $product_arr['currency'] = $profileDetails['ebay_currency'];
                $product_arr['dispatchtime'] = $profileDetails['dispatch_days'];
                $product_arr['listing_duration'] = $profileDetails['duration'];
                $product_arr['ebay_payment_method'] = $profileDetails['ebay_payment_method'];
                $product_arr['ebay_vat_enabled'] = $profileDetails['vatenabled'];
                $product_arr['ebay_vat_percentage'] = $profileDetails['vat_percentage'];
                $product_arr['paypal_email'] = $config['paypal_email'];
                
                $image = $this->config->get('config_ssl') . 'image/' . str_replace(" ","%20",$product_details['image']);

                //$image = 'https://www.eddiesale.com/image/catalog/1104010032016-Blue-Bhagalpuri-Designer-Anarkali-Suit.jpg';
                $product_arr['imagePath'] = $image;

                /* Additional Images */
                $additional_images = array();
                $additional_images_result = $this->model_catalog_product->getProductImages($product['id_product']);
                if (!empty($additional_images_result)) {
                    foreach ($additional_images_result as $additional_image) {
                        if ($additional_image['image'] != $product_details['image']) {
                            $additional_images[] = $this->config->get('config_ssl') . 'image/' . str_replace(" ","%20",$additional_image['image']);
                            //$additional_images[] = 'https://eddiesale.com/image/' . $additional_image['image'];
                        }
                    }
                }

                $product_arr['additional_images'] = $additional_images;

                $product_arr['upc'] = $product_details['upc'];
                $product_arr['ean'] = $product_details['ean'];
                $product_arr['isbn'] = $product_details['isbn'];
                $product_arr['location'] = $profileDetails['site_name'];
                $product_arr['quantity'] = $quantity;
                $product_arr['condition_id'] = $profileDetails['product_condition'];
                $product_arr['return_policy'] = $profileDetails['return_enable'];
                $product_arr['return_days'] = $profileDetails['return_days'];
                $product_arr['refund_by'] = $profileDetails['refund'];
                $product_arr['return_shipping'] = $profileDetails['return_shipping'];
                $product_arr['return_description'] = $profileDetails['return_description'];
                $product_arr['sku'] = $sku;
                $product_arr['weight'] = $product_details['weight'];
                $product_arr['width'] = $product_details['width'];
                $product_arr['height'] = $product_details['height'];
                $product_arr['depth'] = $product_details['length'];
                $product_arr['weight_unit'] = $product_details['weight_class'];
                $product_arr['dimension_unit'] = $product_details['length_class'];

                if (isset($this->request->get['revise']) && $this->request->get['revise'] != "") {
                    $product_arr['ebay_listiing_id'] = $product['ebay_listiing_id'];
                }

                $shippingData = $this->model_ebay_feed_cron->getShippingProfileDetails($profileDetails['ebay_shipping_profile']);

                $specific_array = array();
                $i = 0;
                // Get Specifics
                $specifics = $this->model_ebay_feed_cron->getProfileSpecifics($product['id_ebay_profiles']);
                foreach ($specifics as $specific) {
                    if ($specific['custom_value_mapped'] != '') {
                        $specific_array[$specific['specific_name']] = $specific['custom_value_mapped'];
                    } else if ($specific['ebay_value_mapped'] != '') {
                        $specific_array[$specific['specific_name']] = $specific['ebay_value_mapped'];
                    } else if ($specific['attribute_mapped'] != 0) {
                        $attribute_value = $this->model_ebay_feed_cron->getAttributeValue($specific['attribute_mapped'], $product['id_product'], $profileDetails['ebay_language']);
                        if ($attribute_value != "") {
                            $specific_array[$specific['specific_name']] = $attribute_value;
                        }
                    } else if ($specific['feature_mapped'] != 0) {
                        $specific_name = "";
                        if ($specific['feature_mapped'] == 1) {
                            $specific_name = $product_details['model'];
                        } else if ($specific['feature_mapped'] == 2) {
                            $specific_name = $product_details['sku'];
                        } else if ($specific['feature_mapped'] == 3) {
                            $specific_name = $product_details['upc'];
                        } else if ($specific['feature_mapped'] == 4) {
                            $specific_name = $product_details['ean'];
                        } else if ($specific['feature_mapped'] == 5) {
                            $specific_name = $product_details['jan'];
                        } else if ($specific['feature_mapped'] == 6) {
                            $specific_name = $product_details['isbn'];
                        } else if ($specific['feature_mapped'] == 7) {
                            $specific_name = $product_details['mpn'];
                        } else if ($specific['feature_mapped'] == 8) {
                            $specific_name = $product_details['location'];
                        } else if ($specific['feature_mapped'] == 9) {
                            $specific_name = $product_details['weight'] . " " . $product_details['weight_class'];
                        } else if ($specific['feature_mapped'] == 10) {
                            $specific_name = $manufacturer;
                        }
                        $specific_array[$specific['specific_name']] = $specific_name;
                    }
                }
				$this->model_ebay_feed_cron->deleteError($call_name, $product['id_product'], $product['id_ebay_profiles']);				
				
                $headers = $this->model_ebay_feed_cron->getEbayHeaders($call_name, $profileDetails['site_id']);
                $json_return = $this->model_ebay_feed_cron->listBulkProductsToEbay($headers, $product_arr, $shippingData, $variation_data, $attribute_data, $specific_array, $variations, $sandbox);
                $array = json_decode($json_return, true);
                if ($array['Ack'] == 'Success' || $array['Ack'] == 'Warnings' || $array['Ack'] == 'Warning') {
                    $request = $call_name;
                    if (isset($array['Errors'])) {
                        $error = json_encode($array['Errors']);
                        $this->model_ebay_feed_cron->insertError($request, $error, $array['Ack'], $product['id_product'], $product['id_ebay_profiles']);
                    }
                    if (isset($array['ItemID'])) {
                        $this->model_ebay_feed_cron->updateListedProduct($product['id_product'], $product['id_ebay_profiles'], $array['ItemID'], 'Listed', $profileDetails['site_id'], $config['account_type']);
                    }
                } else {
                    $request = $call_name;
                    $error = json_encode($array['Errors']);
                    $this->model_ebay_feed_cron->insertError($request, $error, $array['Ack'], $product['id_product'], $product['id_ebay_profiles']);
                }
            }
        }
        echo "Success";
        die();
    }

    public function importCategories()
    {
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', -1);
        @set_time_limit(0);
        $store_id = $this->request->get["site_id"];
        if ($store_id == "") {
            echo "Error";
        } else {
            $call_name = 'GetCategories';
            $info = $this->geteBayInfo($call_name, $store_id);

            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "kb_ebay_categories WHERE ebay_site_id =" . (int) $store_id);
            $categories = $query->rows;
            if (empty($categories)) {
                $category_data = $this->model_ebay_feed_cron->getEbayCategoriesEbay($info['headers'], $info['token'], $info['sandbox']);
                $category_array = json_decode($category_data, true);
                if ($category_array['Ack'] == 'Success') {
                    if (!empty($category_array)) {
                        foreach ($category_array['CategoryArray']['Category'] as $category) {
                            if (!isset($category['LeafCategory'])) {
                                $category['LeafCategory'] = false;
                            }
                            $ebay_bestoffer_enabled = (isset($category['BestOfferEnabled']) && trim($category['BestOfferEnabled']) != '') ? $this->db->escape($category['BestOfferEnabled']) : false;
                            $ebay_autopay_enabled = (isset($category['AutoPayEnabled']) && trim($category['AutoPayEnabled']) != '') ? $this->db->escape($category['AutoPayEnabled']) : false;
                            $insertData = "INSERT INTO " . DB_PREFIX . "kb_ebay_categories SET ebay_categories = " . (int) $category['CategoryID'] . ", ebay_category_name = '" . $this->db->escape($category['CategoryName']) . "', ebay_category_level = " . (int) $category['CategoryLevel'] . ", id_ebay_category_parent =" . (int) $category['CategoryParentID'] . ", ebay_leaf_category ='" . $this->db->escape($category['LeafCategory']) . "', ebay_bestoffer_enabled = '" . $this->db->escape($ebay_bestoffer_enabled) . "', ebay_autopay_enabled = '" . $this->db->escape($ebay_autopay_enabled) . "', ebay_site_id = " . (int) $store_id;
                            $this->db->query($insertData);
                        }
                    }
                    echo "Categories imported succesfully. Please refresh the back page to continue.";
                } else {
                    echo "Error" . $category_array['Errors']['LongMessage'];
                }
            } else {
                echo "Categories already imported";
            }
        }
    }

    public function getStoreCategories()
    {
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', -1);
        @set_time_limit(0);
        $storecategory = array();
        $store_id = $this->request->get["site_id"];
        if ($store_id == "") {
            echo "Error";
        } else {
            $call_name = 'GetStore';
            $info = $this->geteBayInfo($call_name, $store_id);
            $this->db->query("DELETE FROM " . DB_PREFIX . "kb_ebay_store_category WHERE site_id = '" . $store_id . "'");
            $category_data = $this->model_ebay_feed_cron->getStoreCategories($info['headers'], $info['token'], $info['sandbox']);
            $categories = json_decode($category_data, true);
            if ($categories['Ack'] == 'Success') {
                foreach ($categories['Store']['CustomCategories']['CustomCategory'] as $category) {
                    if (isset($category['ChildCategory'])) {
                        foreach ($category['ChildCategory'] as $subcategory) {
                            $storecategory[] = array("id" => $subcategory["CategoryID"], "name" => $category["Name"] . " > " . $subcategory["Name"]);
                        }
                    } else {
                        $storecategory[] = array("id" => $category["CategoryID"], "name" => $category["Name"]);
                    }
                }
                if (!empty($storecategory)) {
                    foreach ($storecategory as $storecat) {
                        $this->db->query('INSERT INTO ' . DB_PREFIX . 'kb_ebay_store_category(ebay_store_category_id, category_name, site_id) VALUES("' . $storecat['id'] . '","' . $this->db->escape($storecat['name']) . '", "' . $store_id . '")');
                    }
                }
                echo "Success";
            } else {
                echo "Error: " . $categories['Errors']['LongMessage'];
            }
        }
        die();
    }

    public function processRelistProducts()
    {
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', -1);
        @set_time_limit(0);

        $this->load->model('ebay_feed/cron');
        
        if(!empty($this->config->get('kbebay_demo_flag')) && $this->config->get('kbebay_demo_flag') == 1) {
            echo "Sorry!!! This operation is now allowed in the demo mode.";
            die();
        }        
        $this->model_ebay_feed_cron->processRelistProducts();
        echo "Success";
        die();
    }

    public function processEndProducts()
    {
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', -1);
        @set_time_limit(0);

        $this->load->model('ebay_feed/cron');
        
        if(!empty($this->config->get('kbebay_demo_flag')) && $this->config->get('kbebay_demo_flag') == 1) {
            echo "Sorry!!! This operation is now allowed in the demo mode.";
            die();
        }
        
        $this->model_ebay_feed_cron->processEndProducts();
        echo "Success";
        die;
    }

    public function processEbayReport()
    {
        $this->load->model('ebay_feed/cron');
        $this->model_ebay_feed_cron->processEbayReport();
        echo "Success";
        die;
    }

    public function processGetOrderFromEbay()
    {
        $this->load->model('ebay_feed/cron');
        $this->model_ebay_feed_cron->processGetOrderFromEbay();
        echo "Success";
        die;
    }

    public function processOrderUpdate()
    {
        $this->load->model('ebay_feed/cron');
        $data = $this->model_ebay_feed_cron->processOrderUpdate();
        echo "Success";
        die;
    }

    private function geteBayInfo($call_name, $siteId)
    {
        $this->load->model('ebay_feed/cron');
        $siteDetails = $this->model_ebay_feed_cron->getEbaySiteById($siteId);
        $token = $siteDetails['token'];
        $config = $this->model_ebay_feed_cron->getConfiguration();
        if ($config['account_type'] == 'sandbox') {
            $sandbox = true;
        } else {
            $sandbox = false;
        }
        $headers = $this->model_ebay_feed_cron->getEbayHeaders($call_name, $siteId);
        return array(
            'headers' => $headers,
            'sandbox' => $sandbox,
            'token' => $token
        );
    }

    /* URL Function to import eBay Shipping methods */

    public function importShippings()
    {
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', -1);
        @set_time_limit(0);
        $this->load->model('ebay_feed/cron');
        $ebay_sites = $this->model_ebay_feed_cron->getEbaySites();
        foreach ($ebay_sites as $site_detail) {
            $site_id = $site_detail['id_ebay_countries'];
            $detail_type = 'ShippingServiceDetails';
            $eBayInfo = $this->geteBayInfo('GeteBayDetails', $site_id);
            /* Get Shipping Methods */
            $ship_data = $this->model_ebay_feed_cron->getEbayDetails($eBayInfo['headers'], $eBayInfo['token'], $detail_type, $eBayInfo['sandbox']);
            $ship_array = json_decode($ship_data, true);
            if (!empty($ship_array)) {
                if (isset($ship_array['ShippingServiceDetails'])) {
                    foreach ($ship_array['ShippingServiceDetails'] as $ship) {
                        if ($ship['ValidForSellingFlow'] == 'true') {
                            $ebay_shipping_name = $ship['ShippingService'];
                            $ebay_shipping_desc = $ship['Description'];
                            $international_service = 0;
                            if (array_key_exists('InternationalService', $ship)) {
                                $international_service = 1;
                            }
                            $shipping_package = '';
                            if (array_key_exists('ShippingPackage', $ship)) {
                                $shipping_package = is_array($ship['ShippingPackage']) ? implode(',', $ship['ShippingPackage']) : $ship['ShippingPackage'];
                            }
                            $service_type = is_array($ship['ServiceType']) ? implode(',', $ship['ServiceType']) : $ship['ServiceType'];
                            $query_get_shipping_method = "SELECT count(*) as result FROM " . DB_PREFIX . "kb_ebay_shipping_methods where ebay_shipping_name = '" . $this->db->escape($ebay_shipping_name) . "' and site_id = " . (int) $site_id;
                            $shipping_exist = $this->db->query($query_get_shipping_method);
                            if ($shipping_exist->row['result'] != '0') {
                                $shippingQuery = "UPDATE " . DB_PREFIX . "kb_ebay_shipping_methods  SET "
                                        . "service_type = '" . $this->db->escape($service_type) . "', "
                                        . "ebay_shipping_desc = '" . $this->db->escape($ebay_shipping_desc) . "', "
                                        . "package_type = '" . $this->db->escape($shipping_package) . "', "
                                        . "international_shipping = '" . $this->db->escape($international_service) . "'"
                                        . " WHERE ebay_shipping_name = '" . $this->db->escape($ebay_shipping_name) . "' and site_id = " . (int) ($site_id);
                            } else {
                                $shippingQuery = "INSERT INTO " . DB_PREFIX . "kb_ebay_shipping_methods  SET "
                                        . "ebay_shipping_name = '" . $this->db->escape($ebay_shipping_name) . "',"
                                        . "ebay_shipping_desc = '" . $this->db->escape($ebay_shipping_desc) . "',"
                                        . "service_type = '" . $this->db->escape($service_type) . "',"
                                        . "package_type = '" . $this->db->escape($shipping_package) . "',"
                                        . "international_shipping = '" . $this->db->escape($international_service) . "',"
                                        . "site_id = '" . $site_id . "'";
                            }
                            $this->db->query($shippingQuery);
                        }
                    }
                }
            }
        }
        die("Success");
    }

    public function getExcludedLocations()
    {
        $detail_type = 'ExcludeShippingLocationDetails';
        $eBayInfo = $this->geteBayInfo('GeteBayDetails', $this->request->get["site_id"]);
        $locationData = $this->model_ebay_feed_cron->getEbayDetails($eBayInfo['headers'], $eBayInfo['token'], $detail_type, $eBayInfo['sandbox']);
        echo json_decode($locationData, true);
        die();
    }

}
