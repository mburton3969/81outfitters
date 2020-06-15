<?php

class ModelEbayFeedCron extends Model {

    public function getAllNewProducts($profile_product_id = "") {
        if ($profile_product_id != "") {
            $query_get_products = "SELECT * FROM " . DB_PREFIX . "kb_ebay_profile_products pl "
                    . "INNER JOIN " . DB_PREFIX . "kb_ebay_profiles ep ON (ep.id_ebay_profiles = pl.id_ebay_profiles) "
                    . "INNER JOIN " . DB_PREFIX . "product p ON (p.product_id = pl.id_product) "
                    . "WHERE pl.id_product > 0 "
                    . "AND p.status = 1 "
                    . "AND (pl.ebay_listiing_id IS NULL OR ebay_listiing_id = '') "
                    . "AND pl.is_disabled = '0' "
                    . "AND id_ebay_profile_products = " . (int) $profile_product_id;
        } else {
            $query_get_products = "SELECT * FROM " . DB_PREFIX . "kb_ebay_profile_products pl "
                    . "INNER JOIN " . DB_PREFIX . "kb_ebay_profiles ep ON (ep.id_ebay_profiles = pl.id_ebay_profiles) "
                    . "INNER JOIN " . DB_PREFIX . "product p ON (p.product_id = pl.id_product) "
                    . "WHERE pl.id_product > 0 "
                    . "AND p.status = 1 "
                    . "AND (pl.ebay_listiing_id IS NULL OR ebay_listiing_id = '') "
                    . "AND pl.is_disabled = '0' "
                    . "ORDER BY id_ebay_profile_products ASC";
        }
        $result = $this->db->query($query_get_products);
        return $result->rows;
    }

    public function getReviseProducts($profile_product_id = "") {
        if ($profile_product_id != "") {
            $query_get_products = "SELECT * FROM " . DB_PREFIX . "kb_ebay_profile_products pl "
                    . "INNER JOIN " . DB_PREFIX . "kb_ebay_profiles ep ON (ep.id_ebay_profiles = pl.id_ebay_profiles) "
                    . "INNER JOIN " . DB_PREFIX . "product p ON (p.product_id = pl.id_product) "
                    . "WHERE pl.id_product > 0 "
                    . "AND p.status = '1' "
                    . "AND pl.is_disabled = '0' "
                    . "AND id_ebay_profile_products = " . (int) $profile_product_id;
        } else {
            $query_get_products = "SELECT * FROM " . DB_PREFIX . "kb_ebay_profile_products pl "
                    . "INNER JOIN " . DB_PREFIX . "kb_ebay_profiles ep ON (ep.id_ebay_profiles = pl.id_ebay_profiles) "
                    . "INNER JOIN " . DB_PREFIX . "product p ON (p.product_id = pl.id_product) "
                    . "WHERE pl.id_product > 0 "
                    . "AND revise = '1' "
                    . "AND p.status = '1' "
                    . "AND pl.is_disabled = '0' "
                    . "ORDER BY id_ebay_profile_products ASC";
        }
        $result = $this->db->query($query_get_products);
        if ($result->num_rows > 0) {
            return $result->rows;
        } else {
            return false;
        }
    }

    public function syncProductsToModule() {
        $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_profile_products SET local_sync_flag = "1"');

        $profileQuery = 'SELECT * FROM ' . DB_PREFIX . 'kb_ebay_profiles WHERE status = "completed" AND active = "1"';
        $profile_data = $this->db->query($profileQuery);

        if ($profile_data->num_rows > 0) {

            foreach ($profile_data->rows as $profile) {

                /* Add Inactive & Disabled Product Condition */
                $product_result = $this->db->query("SELECT p.product_id, p.model FROM " . DB_PREFIX . "product p, " . DB_PREFIX . "product_to_category p2c WHERE p.product_id = p2c.product_id and p2c.category_id in(" . $profile['store_category_id'] . ") AND p.status = 1");
                foreach ($product_result->rows as $product) {

                    $variation_flag = false;
                    $productOptions = array();

                    $variations = $this->getVariations($product['product_id']);
                    if (!empty($variations)) {
                        $variation_flag = true;

                        /* To Generate the Correct Option Combination, Key is required instead of index. So added option_ as text in the key. Later Removed at the time of DB INSERT */
                        foreach ($variations as $variation) {
                            $productOptions['option_' . $variation['option_id']][] = $variation['option_value_id'];
                        }

                        /* Generate All Combinations of the Variations. Option_ID & Option Values. 
                          [1option] => 43
                          [5option] => 42
                          [2option] => 45
                         */
                        $variations_combinations = $this->get_combinations($productOptions);
                    }

                    /* Check in the profile product table. If exist, Insert only new variations else all variations */
                    $kb_profile_product_result = $this->db->query("SELECT id_product, product_reference FROM " . DB_PREFIX . "kb_ebay_profile_products epp INNER JOIN " . DB_PREFIX . "kb_ebay_profiles ep ON epp.id_ebay_profiles = ep.id_ebay_profiles WHERE epp.id_ebay_profiles = '" . (int) $profile['id_ebay_profiles'] . "' AND id_product = " . $product['product_id'] . " AND ep.ebay_site = '" . $profile['ebay_site'] . "'");
                    $product_reference = $product['model'];
                    if ($kb_profile_product_result->num_rows == 0) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "kb_ebay_profile_products SET "
                                . "id_ebay_profiles = '" . (int) $profile['id_ebay_profiles'] . "', "
                                . "id_product = '" . (int) $product['product_id'] . "', "
                                . "id_product_attribute = 0, "
                                . "product_reference = '" . $this->db->escape($product_reference) . "', "
                                . "status = 'New', "
                                . "date_added = now()");
                    } else {
                        $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET "
                                . "product_reference = '" . $this->db->escape($product_reference) . "', "
                                . "local_sync_flag = '0' "
                                . "WHERE id_ebay_profiles = '" . (int) $profile['id_ebay_profiles'] . "' "
                                . "AND id_product = " . (int) $product['product_id']);

                        $this->db->query('DELETE FROM ' . DB_PREFIX . 'kb_ebay_profile_product_options '
                                . "WHERE profile_id = '" . (int) $profile['id_ebay_profiles'] . "' "
                                . "AND product_id = " . (int) $product['product_id']);
                    }

                    /* If product variation exists insert only non added items else all items */
                    if ($variation_flag == true) {
                        foreach ($variations_combinations as $variation) {
                            $product_reference = $product['product_id'];
                            foreach ($variation as $key_option_id => $key_option_value) {
                                $option_id = str_replace("option_", "", $key_option_id);
                                $product_reference = $product_reference . "_" . $option_id . ":" . $key_option_value;
                            }
                            $this->db->query("INSERT INTO " . DB_PREFIX . "kb_ebay_profile_product_options "
                                    . "SET profile_id = '" . (int) $profile['id_ebay_profiles'] . "', "
                                    . "product_id = '" . (int) $product['product_id'] . "', "
                                    . "option_sku = '" . $this->db->escape($product_reference) . "'");
                        }
                    }
                }
            }
        }
        /* If any item is having local_sync_flag = 1 then that item needs to be deleted (This situation will be occured in case of store categories changes of the profile */
        $this->db->query('DELETE FROM ' . DB_PREFIX . 'kb_ebay_profile_products WHERE local_sync_flag = "1" AND  (ebay_listiing_id = "" OR ebay_listiing_id IS NULL)');
        $product_to_delete_result = $this->db->query("SELECT * FROM " . DB_PREFIX . "kb_ebay_profile_products WHERE local_sync_flag = '1'");
        if ($product_to_delete_result->num_rows > 0) {
            //TODO Delete Items Option from the kb_ebay_profile_product_options table as well */
            /* If there are listed item then set end flag for those items & then execute endProduct function to delete the same from eBay */
            $this->db->query('UPDATE ' . DB_PREFIX . 'kb_ebay_profile_products SET status = "Deleted", end = "1" WHERE local_sync_flag = "1"');
            $this->processEndProducts();

            /* Final delete those item from our DB as well */
            $this->db->query('DELETE FROM ' . DB_PREFIX . 'kb_ebay_profile_products WHERE local_sync_flag = "1"');
        }
    }

    function get_combinations($arrays) {
        $result = array(array());
        foreach ($arrays as $property => $property_values) {
            $tmp = array();
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, array($property => $property_value));
                }
            }
            $result = $tmp;
        }
        return $result;
    }

    public function getConfiguration() {
        $this->load->model('ebay_feed/cron');
        $store_id = $this->config->get('config_store_id');
        if ($store_id) {
            $store_id = $store_id;
        } else {
            $store_id = 0;
        }
        $settings = $this->model_ebay_feed_cron->getSetting('ebay_general_settings', $store_id);
        if ($settings['ebay_general_settings']['general']['account_type'] == 0) {
            $sandbox = true;
        } else {
            $sandbox = false;
        }
        $configuration = array();
        $configuration['enable'] = $settings['ebay_general_settings']['general']['enable'];
        if ($settings['ebay_general_settings']['general']['account_type']) {
            $type = 'live';
        } else {
            $type = 'sandbox';
        }
        $configuration['account_type'] = $type;
        $configuration['compat_level'] = 967;
        $configuration['api_endpoint'] = $sandbox ? 'https://api.sandbox.ebay.com/ws/api.dll' : 'https://api.ebay.com/ws/api.dll';
        $configuration['dev_id'] = $settings['ebay_general_settings']['general']['dev_id'];
        $configuration['app_id'] = $settings['ebay_general_settings']['general']['app_id'];
        $configuration['cert_id'] = $settings['ebay_general_settings']['general']['cert_id'];
        $configuration['ru_name'] = $settings['ebay_general_settings']['general']['ru_name'];
        $configuration['auth_token'] = $settings['ebay_general_settings']['general']['token'];
        $configuration['paypal_email'] = $settings['ebay_general_settings']['general']['paypal_email'];
        $configuration['site_id'] = 0;
        return $configuration;
    }

    public function getProfileDetails() {
        $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_profiles p INNER JOIN " . DB_PREFIX . "kb_ebay_sites s on p.site_id = s.id_ebay_countries  WHERE p.status = 'completed' and p.active = 1";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getCurrency($id) {
        $query_get_products = 'SELECT * FROM ' . DB_PREFIX . 'kb_ebay_sites  WHERE id_ebay_countries = ' . (int) $id . '';
        $result = $this->db->query($query_get_products);
        return $result->row;
    }

    public function getManufacturer($manufacturer_id, $language_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer m WHERE m.manufacturer_id = '" . (int) $manufacturer_id . "'");
        return $query->row;
    }

    public function getCurrencyById($currency) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "currency WHERE currency_id = '" . $this->db->escape($currency) . "'");

        return $query->row;
    }

    public function getCurrencyByCode($currency) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "currency WHERE code = '" . $this->db->escape($currency) . "'");

        return $query->row;
    }

    public function getVariations($product_id) {
        $query_check_variation = 'SELECT * FROM ' . DB_PREFIX . 'product_option_value sa WHERE product_id =' . (int) $product_id . ' AND option_value_id != 0 ORDER BY option_id ASC, option_value_id ASC';
        $result = $this->db->query($query_check_variation);
        return $result->rows;
    }

    public function getVariationsDetails($id, $language_id) {
        $query = $this->db->query('SELECT ovd.*,od.name as name_attr FROM ' . DB_PREFIX . 'option_value_description ovd, ' . DB_PREFIX . 'option_description od WHERE od.option_id = ovd.option_id and ovd.option_value_id =' . (int) $id . ' and ovd.language_id = "' . (int) $language_id . '" and od.language_id = "' . (int) $language_id . '"');
        return $query->rows;
    }

    public function getNewProductByVariations($id) {
        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'kb_ebay_profile_products epp WHERE id_product_attribute = ' . (int) $id);
        return $query->rows;
    }

    public function getProfileSpecifics($profile_id) {
        $query_get_products = 'SELECT ec.*,ecs.specific_name FROM ' . DB_PREFIX . 'kb_ebay_profile_specifics ec, ' . DB_PREFIX . 'kb_ebay_category_specifics ecs WHERE  ecs.id_ebay_category_specifics = ec.id_ebay_category_specifics and id_ebay_profiles =' . (int) $profile_id;
        $result = $this->db->query($query_get_products);
        return $result->rows;
    }

    public function getAttributeValue($attribute_id, $product_id, $language_id) {
        $query_get_products = 'SELECT * FROM ' . DB_PREFIX . 'product_attribute ec WHERE attribute_id =' . (int) $attribute_id . " && product_id = " . $product_id . " && language_id = " . $language_id;
        $result = $this->db->query($query_get_products);
        if ($result->num_rows > 0) {
            return $result->row['text'];
        } else {
            return "";
        }
    }

    public function getProduct($product_id, $language_id) {
        $query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND pr.customer_group_id = '" . (int) $this->config->get('config_customer_group_id') . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int) $language_id . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int) $language_id . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int) $language_id . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int) $product_id . "' AND pd.language_id = '" . (int) $language_id . "' AND p.status = '1' AND p.date_available <= NOW()");

        if ($query->num_rows) {
            return array(
                'product_id' => $query->row['product_id'],
                'name' => $query->row['name'],
                'description' => $query->row['description'],
                'meta_title' => $query->row['meta_title'],
                'meta_description' => $query->row['meta_description'],
                'meta_keyword' => $query->row['meta_keyword'],
                'tag' => $query->row['tag'],
                'model' => $query->row['model'],
                'sku' => $query->row['sku'],
                'upc' => $query->row['upc'],
                'ean' => $query->row['ean'],
                'jan' => $query->row['jan'],
                'isbn' => $query->row['isbn'],
                'mpn' => $query->row['mpn'],
                'location' => $query->row['location'],
                'quantity' => $query->row['quantity'],
                'stock_status' => $query->row['stock_status'],
                'image' => $query->row['image'],
                'manufacturer_id' => $query->row['manufacturer_id'],
                'manufacturer' => $query->row['manufacturer'],
                'price' => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
                'special' => $query->row['special'],
                'reward' => $query->row['reward'],
                'points' => $query->row['points'],
                'tax_class_id' => $query->row['tax_class_id'],
                'date_available' => $query->row['date_available'],
                'weight' => number_format((float) $query->row['weight'], 2, '.', ''),
                'weight_class_id' => $query->row['weight_class_id'],
                'weight_class' => $query->row['weight_class'],
                'length' => number_format((float) $query->row['length'], 2, '.', ''),
                'width' => number_format((float) $query->row['width'], 2, '.', ''),
                'height' => number_format((float) $query->row['height'], 2, '.', ''),
                'length_class_id' => $query->row['length_class_id'],
                'length_class' => $query->row['length_class'],
                'subtract' => $query->row['subtract'],
                'rating' => round($query->row['rating']),
                'reviews' => $query->row['reviews'] ? $query->row['reviews'] : 0,
                'minimum' => $query->row['minimum'],
                'sort_order' => $query->row['sort_order'],
                'status' => $query->row['status'],
                'date_added' => $query->row['date_added'],
                'date_modified' => $query->row['date_modified'],
                'viewed' => $query->row['viewed']
            );
        } else {
            return false;
        }
    }

    public function getEbayHeaders($call_name, $site_id) {
        $config = $this->getConfiguration();
        $compat_level = $config['compat_level'];
        $dev_id = $config['dev_id'];
        $app_id = $config['app_id'];
        $cert_id = $config['cert_id'];

        $headers = array(
            'X-EBAY-API-COMPATIBILITY-LEVEL: ' . $compat_level,
            'X-EBAY-API-DEV-NAME: ' . $dev_id,
            'X-EBAY-API-APP-NAME: ' . $app_id,
            'X-EBAY-API-CERT-NAME: ' . $cert_id,
            'X-EBAY-API-CALL-NAME: ' . $call_name,
            'X-EBAY-API-SITEID: ' . $site_id,
        );
        return $headers;
    }

    public function getEbaySiteById($site_id) {
        $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_sites WHERE id_ebay_countries = '" . $site_id . "'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function getEbaySiteByCode($site_id) {
        $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_sites s LEFT JOIN " . DB_PREFIX . "currency c ON s.currency_iso_code = c.code  WHERE abbreviation = '" . $site_id . "' || site_name = '" . $site_id . "'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function getEbaySites() {
        $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_sites WHERE enabled = 1";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function listBulkProductsToEbay($headers, $product_data, $shipping_data, $variation_data, $attribute_data, $specific_array, $variations = true, $sandbox = true) {
        $xmlFeed = '';
        $xmlFeed = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        if (isset($product_data['ebay_listiing_id'])) {
            $xmlFeed .= '<ReviseFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        } else {
            $xmlFeed .= '<AddFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        }
        $xmlFeed .= '<RequesterCredentials>' . "\n";
        $xmlFeed .= '<eBayAuthToken>' . $product_data['auth_token'] . '</eBayAuthToken>' . "\n";
        $xmlFeed .= '</RequesterCredentials>' . "\n";
        $xmlFeed .= '<ErrorLanguage>en_US</ErrorLanguage>' . "\n";
        $xmlFeed .= '<WarningLevel>High</WarningLevel>' . "\n";
        $xmlFeed .= '<Item>' . "\n";
        if (isset($product_data['ebay_listiing_id'])) {
            $xmlFeed .= '<ItemID>' . $product_data['ebay_listiing_id'] . '</ItemID>' . "\n";
        }
        if (!$variations && isset($product_data['sku']) && trim($product_data['sku']) != '') {
            $xmlFeed .= '<SKU>' . $product_data['sku'] . '</SKU>' . "\n";
            //$xmlFeed .= '<InventoryTrackingMethod>SKU</InventoryTrackingMethod>' . "\n";
        }
        $xmlFeed .= '<Title>' . substr($product_data['title'], 0, 80) . '</Title>' . "\n";
        if (trim($product_data['description']) != '') {
            $xmlFeed .= '<Description><![CDATA[' . str_replace('&nbsp;', '', $product_data['description']) . ']]></Description>' . "\n";
        } else {
            $xmlFeed .= '<Description>Sample Description</Description>' . "\n";
        }
        $xmlFeed .= '<PrimaryCategory>' . "\n";
        $xmlFeed .= '<CategoryID>' . $product_data['ebay_cat'] . '</CategoryID>' . "\n";
        $xmlFeed .= '</PrimaryCategory>' . "\n";
        if (!$variations) {
            $xmlFeed .= '<StartPrice>' . $product_data['final_price'] . '</StartPrice>' . "\n";
        }

        if ($product_data['store_category'] != "") {
            $xmlFeed .= '<Storefront>' . "\n";
            $xmlFeed .= '<StoreCategoryID>' . $product_data['store_category'] . '</StoreCategoryID>' . "\n";
            $xmlFeed .= '</Storefront>' . "\n";
        }

        $xmlFeed .= '<CategoryMappingAllowed>' . $product_data['cat_map_allowed'] . '</CategoryMappingAllowed>' . "\n";
        $xmlFeed .= '<ConditionID>' . $product_data['condition_id'] . '</ConditionID>' . "\n";
        $xmlFeed .= '<Country>' . $product_data['country'] . '</Country>' . "\n";
        $xmlFeed .= '<Currency>' . $product_data['currency'] . '</Currency>' . "\n";
        $xmlFeed .= '<DispatchTimeMax>' . $product_data['dispatchtime'] . '</DispatchTimeMax>' . "\n";
        $xmlFeed .= '<ListingDuration>' . $product_data['listing_duration'] . '</ListingDuration>' . "\n";
        $xmlFeed .= '<ListingType>FixedPriceItem</ListingType>' . "\n";

        $paypal_exist = false;
        $payment_methods = explode(",", $product_data['ebay_payment_method']);
        foreach ($payment_methods as $payment_method) {
            $xmlFeed .= '<PaymentMethods>' . $payment_method . '</PaymentMethods>' . "\n";
            if ($payment_method == 'PayPal') {
                $paypal_exist = true;
            }
        }
        if ($paypal_exist) {
            $xmlFeed .= '<PayPalEmailAddress>' . $product_data['paypal_email'] . '</PayPalEmailAddress>' . "\n";
        }

        $xmlFeed .= '<PictureDetails>' . "\n";
        $xmlFeed .= '<GalleryType>Gallery</GalleryType>' . "\n";
        $xmlFeed .= '<PictureURL>' . $product_data['imagePath'] . '</PictureURL>' . "\n";
        if (!empty($product_data['additional_images'])) {
            foreach ($product_data['additional_images'] as $additional_image) {
                $xmlFeed .= '<PictureURL>' . $additional_image . '</PictureURL>' . "\n";
            }
        }

        $xmlFeed .= '</PictureDetails>' . "\n";
        $xmlFeed .= '<Location>' . $product_data['location'] . '</Location>' . "\n";
        $xmlFeed .= '<ProductListingDetails>' . "\n";
        if (!$variations && isset($product_data['upc']) && trim($product_data['upc']) != '') {
            $xmlFeed .= '<UPC>' . $product_data['upc'] . '</UPC>' . "\n";
        } else {
            $xmlFeed .= '<UPC>Does not apply</UPC>' . "\n";
        }

        if (!$variations && isset($product_data['ean']) && trim($product_data['ean']) != '') {
            $xmlFeed .= '<EAN>' . $product_data['ean'] . '</EAN>' . "\n";
        } else if (!$variations && isset($product_data['ean']) && trim($product_data['ean']) == '') {
            $xmlFeed .= '<EAN>Does not apply</EAN>' . "\n";
        }
        if (!$variations && isset($product_data['isbn']) && trim($product_data['isbn']) != '') {
            $xmlFeed .= '<ISBN>' . $product_data['isbn'] . '</ISBN>' . "\n";
        }
        $xmlFeed .= '<IncludeStockPhotoURL>true</IncludeStockPhotoURL>' . "\n";
        $xmlFeed .= '<IncludePrefilledItemInformation>true</IncludePrefilledItemInformation>' . "\n";
        $xmlFeed .= '<UseFirstProduct>true</UseFirstProduct>' . "\n";
        $xmlFeed .= '<UseStockPhotoURLAsGallery>true</UseStockPhotoURLAsGallery>' . "\n";
        $xmlFeed .= '<ReturnSearchResultOnDuplicates>true</ReturnSearchResultOnDuplicates>' . "\n";
        $xmlFeed .= '</ProductListingDetails>' . "\n";
        if (!$variations) {
            $xmlFeed .= '<Quantity>' . $product_data['quantity'] . '</Quantity>' . "\n";
        }
        $xmlFeed .= '<ReturnPolicy>' . "\n";
        $xmlFeed .= '<ReturnsAcceptedOption>' . $product_data['return_policy'] . '</ReturnsAcceptedOption>' . "\n";
        if ($product_data['return_policy'] == 'ReturnsAccepted') {
            $xmlFeed .= '<RefundOption>' . $product_data['refund_by'] . '</RefundOption>' . "\n";
            $xmlFeed .= '<ReturnsWithinOption>' . $product_data['return_days'] . '</ReturnsWithinOption>' . "\n";

            /* Return Desciption is avaliable only for Germany (DE) 77, Austria (AT) 16, France (FR) 71, Italy (IT) 101, Spain (ES) 186 */
            if (in_array($product_data['site_id'], array(77, 16, 71, 101, 186))) {
                $xmlFeed .= '<Description>' . $product_data['return_description'] . '</Description>' . "\n";
            }
            $xmlFeed .= '<ShippingCostPaidByOption>' . $product_data['return_shipping'] . '</ShippingCostPaidByOption>' . "\n";
        }
        $xmlFeed .= '</ReturnPolicy>' . "\n";

        $service_type = $shipping_data['service_type'];
        $international_shipping_allowed = $shipping_data['international_shipping_allowed'];
        $domestic_shippings = json_decode($shipping_data['domestic_shipping'], true);
        $international_shippings = json_decode($shipping_data['international_shipping'], true);
        $excluded_locations = json_decode($shipping_data['excluded_location'], true);
        $weight_unit = $product_data['weight_unit'];
        $dimension_unit = $product_data['dimension_unit'];
        $package_type = $shipping_data['package_type'];
        $postal_code = $shipping_data['postal_code'];

        $xmlFeed .= '<ShippingDetails>' . "\n";
        if ($service_type == 'Calculated') {
            $xmlFeed.="<CalculatedShippingRate>" . "\n";
            $xmlFeed.="<OriginatingPostalCode>" . $postal_code . "</OriginatingPostalCode>" . "\n";
            $xmlFeed.="<ShippingPackage>" . $package_type . "</ShippingPackage>" . "\n";
            $xmlFeed.="<PackageDepth unit='" . $dimension_unit . "'>" . $product_data['depth'] . "</PackageDepth>" . "\n";
            $xmlFeed.="<PackageLength unit='" . $dimension_unit . "'>" . $product_data['height'] . "</PackageLength>" . "\n";
            $xmlFeed.="<PackageWidth unit='" . $dimension_unit . "'>" . $product_data['width'] . "</PackageWidth>" . "\n";
            $xmlFeed.="<WeightMajor unit='" . $weight_unit . "'>" . $product_data['weight'] . "</WeightMajor>" . "\n";
            $xmlFeed.="<WeightMinor unit='" . $weight_unit . "'>0</WeightMinor>";
            $xmlFeed.="</CalculatedShippingRate>" . "\n";
        }
        if (!empty($excluded_locations)) {
            foreach ($excluded_locations as $key => $excluded_location) {
                if (trim($excluded_location) != "") {
                    $xmlFeed.="<ExcludeShipToLocation>" . $excluded_location . "</ExcludeShipToLocation>" . "\n";
                }
            }
        }
        foreach ($domestic_shippings as $key => $domestic_shipping) {
            $xmlFeed.="<ShippingServiceOptions>" . "\n";
            if ($domestic_shipping['free_shipping_allowed'] == '1') {
                $xmlFeed.="<FreeShipping>true</FreeShipping>" . "\n";
            }
            $xmlFeed.="<ShippingService>" . $domestic_shipping['service'] . "</ShippingService>" . "\n";
            $xmlFeed.="<ShippingServicePriority>" . $domestic_shipping['priority'] . "</ShippingServicePriority>" . "\n";
            if (($service_type == 'Flat') && ($domestic_shipping['free_shipping_allowed'] != '1')) {
                $xmlFeed.="<ShippingServiceCost>" . $domestic_shipping['service_cost'] . "</ShippingServiceCost>" . "\n";
                $xmlFeed.="<ShippingServiceAdditionalCost>" . $domestic_shipping['additional_cost'] . "</ShippingServiceAdditionalCost>" . "\n";
            }
            $xmlFeed.="</ShippingServiceOptions>";
        }
        if ($international_shipping_allowed == '1') {
            foreach ($international_shippings as $key => $international_shipping) {
                $xmlFeed.="<InternationalShippingServiceOption>" . "\n";
                $xmlFeed.="<ShippingService>" . $international_shipping['service'] . "</ShippingService>" . "\n";
                $xmlFeed.="<ShippingServicePriority>" . $international_shipping['priority'] . "</ShippingServicePriority>" . "\n";
                if ($service_type == 'Flat') {
                    $xmlFeed.="<ShippingServiceCost>" . $international_shipping['service_cost'] . "</ShippingServiceCost>" . "\n";
                    $xmlFeed.="<ShippingServiceAdditionalCost>" . $international_shipping['additional_cost'] . "</ShippingServiceAdditionalCost>" . "\n";
                }
                $shipping_locations = $international_shipping['location'];
                if (!empty($shipping_locations)) {
                    foreach ($shipping_locations as $key => $shipping_location) {
                        $xmlFeed.="<ShipToLocation>" . $shipping_location . "</ShipToLocation>" . "\n";
                    }
                }
                $xmlFeed.="</InternationalShippingServiceOption>" . "\n";
            }
        }
        $xmlFeed.='<ShippingType>' . $service_type . '</ShippingType>';
        $xmlFeed .= '</ShippingDetails>' . "\n";

        if ($product_data['ebay_vat_enabled'] == 'yes' && $product_data['ebay_vat_percentage'] != "") {
            $xmlFeed .= '<VATDetails>' . "\n";
            $xmlFeed .= '<VATPercent>' . $product_data['ebay_vat_percentage'] . '</VATPercent>' . "\n";
            $xmlFeed .= '</VATDetails>' . "\n";
        }

        $xmlFeed .= '<Site>' . $product_data['abbreviation'] . '</Site>' . "\n";
        if ($variations) {
            $xmlFeed .= '<Variations>';
            $xmlFeed .= '<VariationSpecificsSet>';
            $var_spec_arr = array();
            foreach ($attribute_data as $key => $attr) {
                $var_spec_arr[] = $key;
                $xmlFeed .= '<NameValueList>';
                $xmlFeed .= '<Name>' . $key . '</Name>';
                foreach ($attr as $val) {
                    if ($key == "Brand" && $val == "") {
                        $xmlFeed .= '<Value>Unbranded</Value>';
                    } else {
                        $xmlFeed .= '<Value>' . $val . '</Value>';
                    }
                }
                $xmlFeed .= '</NameValueList>';
            }
            $xmlFeed .= '</VariationSpecificsSet>';
            foreach ($variation_data as $variation) {
                $xmlFeed .= '<Variation>';
                $xmlFeed .= '<SKU>' . $variation['sku'] . '</SKU>';
                $xmlFeed .= '<StartPrice>' . $variation['price'] . '</StartPrice>';
                $xmlFeed .= '<VariationProductListingDetails>';
                $xmlFeed .= '<EAN>Does not apply</EAN>' . "\n";
                $xmlFeed .= '</VariationProductListingDetails>';
                $xmlFeed .= '<Quantity>' . $variation['quantity'] . '</Quantity>';
                $xmlFeed .= '<VariationSpecifics>';
                foreach ($variation['combi'] as $combinations) {
                    $xmlFeed .= '<NameValueList>';
                    $xmlFeed .= '<Name>' . $combinations['name'] . '</Name>';
                    $xmlFeed .= '<Value>' . $combinations['value'] . '</Value>';
                    $xmlFeed .= '</NameValueList>';
                }
                $xmlFeed .= '</VariationSpecifics>';
                $xmlFeed .= '</Variation>';
            }
            $xmlFeed .= '</Variations>';
        }
        if (!empty($specific_array)) {
            $xmlFeed .= '<ItemSpecifics>' . "\n";
            foreach ($specific_array as $feature => $feature_value) {
                if (!$variations) {
                    $xmlFeed .= '<NameValueList>' . "\n";
                    $xmlFeed .= '<Name>' . $feature . '</Name>' . "\n";
                    if ($feature == "Brand" && $feature_value == "") {
                        $xmlFeed .= '<Value>Unbranded</Value>' . "\n";
                    } else {
                        $xmlFeed .= '<Value>' . $feature_value . '</Value>' . "\n";
                    }
                    $xmlFeed .= '</NameValueList>' . "\n";
                } else {
                    if (!in_array($feature, $var_spec_arr)) {
                        $xmlFeed .= '<NameValueList>' . "\n";
                        $xmlFeed .= '<Name>' . $feature . '</Name>' . "\n";
                        if ($feature == "Brand" && $feature_value == "") {
                            $xmlFeed .= '<Value>Unbranded</Value>' . "\n";
                        } else {
                            $xmlFeed .= '<Value>' . $feature_value . '</Value>' . "\n";
                        }
                        $xmlFeed .= '</NameValueList>' . "\n";
                    }
                }
            }
            $xmlFeed .= '</ItemSpecifics>' . "\n";
        }
        $xmlFeed .= '</Item>' . "\n";
        if (isset($product_data['ebay_listiing_id'])) {
            $xmlFeed .= '</ReviseFixedPriceItemRequest>' . "\n";
        } else {
            $xmlFeed .= '</AddFixedPriceItemRequest>' . "\n";
        }

        if (isset($this->request->get['debug']) && $this->request->get['debug'] == 1) {
            echo $xmlFeed;
        }

        if (isset($this->request->get['die']) && $this->request->get['die'] == 1) {
            die();
        }

        return $this->sendrequest($xmlFeed, $headers, $sandbox);
    }

    public function insertError($type, $error, $error_type, $product_id = "", $profile_id = "") {
        //Delete all specifics of this profile
        $this->db->query("DELETE FROM `" . DB_PREFIX . "kb_ebay_errors` WHERE id_product = '" . (int) $product_id . "' AND  id_profile = '" . $profile_id . "' AND `request_name` = '" . $this->db->escape($type) . "'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "kb_ebay_errors SET request_name = '" . $this->db->escape($type) . "', `error` = '" . $this->db->escape($error) . "', `error_type` = '" . $this->db->escape($error_type) . "', `id_product` = '" . (int) $product_id . "', `id_profile` = '" . (int) $profile_id . "', date_added = now()");
        return true;
    }

    public function deleteError($type, $product_id, $profile_id) {
        //Delete all errors of this product.
        $this->db->query("DELETE FROM `" . DB_PREFIX . "kb_ebay_errors` WHERE id_product = '" . (int) $product_id . "' AND  id_profile = '" . $profile_id . "'");
        return true;
    }

    public function updateListedProduct($product_id, $profile_id, $item_id, $status, $site_id, $type) {
        $eBaySiteURL = array(
            'live' => array(
                '0' => 'https://www.ebay.com/itm/', //US
                '2' => 'https://www.ebay.ca/itm/', //CA
                '3' => 'https://www.ebay.co.uk/itm/', //UK
                '15' => 'https://www.ebay.com.au/itm/', //AU
                '16' => 'https://www.ebay.at/itm/', //AT
                '23' => 'https://www.befr.ebay.be/itm/', //BEFR
                '71' => 'https://www.ebay.fr/itm/', //FR
                '77' => 'https://www.ebay.de/itm/', //DE
                '101' => 'https://www.ebay.it/itm/', //IT
                '123' => 'https://www.benl.ebay.be/itm/', //BENL
                '146' => 'https://www.ebay.nl/itm/', //NL
                '186' => 'https://www.ebay.es/itm/', //ES
                '193' => 'https://www.ebay.ch/itm/', //CH
                '201' => 'https://www.ebay.com.hk/itm/', //HK
                '203' => 'https://www.ebay.in/itm/', //IN
                '205' => 'https://www.ebay.ie/itm/', //IE
                '207' => 'https://www.ebay.com.my/itm/', //MY
                '211' => 'https://www.ebay.ph/itm/', //PH
                '212' => 'https://www.ebay.pl/itm/', //PL 
                '216' => 'https://www.ebay.com.sg/itm/', //SG
            ),
            'sandbox' => array(
                '0' => 'http://cgi.sandbox.ebay.com/itm/', //US
                '2' => 'http://cgi.sandbox.ebay.ca/itm/', //CA
                '3' => 'http://cgi.sandbox.ebay.co.uk/itm/', //UK
                '15' => 'http://cgi.sandbox.ebay.com.au/itm/', //AU
                '16' => 'http://cgi.sandbox.ebay.at/itm/', //AT
                '23' => 'http://cgi.sandbox.befr.ebay.be/itm/', //BEFR
                '71' => 'http://cgi.sandbox.ebay.fr/itm/', //FR
                '77' => 'http://cgi.sandbox.ebay.de/itm/', //DE
                '101' => 'http://cgi.sandbox.ebay.it/itm/', //IT
                '123' => 'http://cgi.sandbox.benl.ebay.be/itm/', //BENL
                '146' => 'http://cgi.sandbox.ebay.nl/itm/', //NL
                '186' => 'http://cgi.sandbox.ebay.es/itm/', //ES
                '193' => 'http://cgi.sandbox.ebay.ch/itm/', //CH
                '201' => 'http://cgi.sandbox.ebay.com.hk/itm/', //HK Sandbox Not AV
                '203' => 'http://cgi.sandbox.ebay.in/itm/', //IN
                '205' => 'http://cgi.sandbox.ebay.ie/itm/', //IE
                '207' => 'http://cgi.sandbox.ebay.com.my/itm/', //MY Sandbox Not AV
                '211' => 'http://cgi.sandbox.ebay.ph/itm/', //PH Sandbox Not AV
                '212' => 'http://cgi.sandbox.ebay.pl/itm/', //PL 
                '216' => 'http://cgi.sandbox.ebay.com.sg/itm/', //SG Sandbox Not AV
            )
        );
        $item_url = $eBaySiteURL[$type][$site_id] . $item_id;
        $query_get_products = 'UPDATE ' . DB_PREFIX . 'kb_ebay_profile_products set status = "' . $this->db->escape($status) . '", ebay_listiing_id = "' . $this->db->escape($item_id) . '", item_url = "' . $item_url . '", relist = "0", end = "0", revise="0" WHERE  id_product = ' . (int) $product_id . ' AND id_ebay_profiles =' . (int) $profile_id;
        $this->db->query($query_get_products);
    }

    public function getSetting($code, $store_id = 0) {
        $setting_data = array();
        if (VERSION >= '2.0.0.0') {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int) $store_id . "' AND `code` = '" . $this->db->escape($code) . "'");
        } else {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int) $store_id . "' AND `group` = '" . $this->db->escape($code) . "'");
        }
        foreach ($query->rows as $result) {
            if (!$result['serialized']) {
                $setting_data[$result['key']] = $result['value'];
            } else {
                if (VERSION >= '2.0.3.1') {
                    $setting_data[$result['key']] = json_decode($result['value'], true);
                } else {
                    $setting_data[$result['key']] = unserialize($result['value']);
                }
            }
        }
        return $setting_data;
    }

    public function sendrequest($feed, $headers, $sandbox = true) {
        $api_endpoint = $sandbox ? 'https://api.sandbox.ebay.com/ws/api.dll' : 'https://api.ebay.com/ws/api.dll';
        $connection = curl_init();
        curl_setopt($connection, CURLOPT_URL, $api_endpoint);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($connection, CURLOPT_POST, 1);
        curl_setopt($connection, CURLOPT_POSTFIELDS, $feed);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($connection);
        curl_close($connection);
        $xml = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        return $json;
    }

    public function processRelistProducts() {
        $query_get_items = 'SELECT * FROM ' . DB_PREFIX . 'kb_ebay_profile_products pp WHERE relist= "1" AND is_disabled = "0" GROUP BY id_product, id_ebay_profiles';
        $query = $this->db->query($query_get_items);
        $call_name = 'RelistFixedPriceItem';
        if ($query->num_rows > 0) {
            $get_items = $query->rows;
            foreach ($get_items as $item) {
                $profile = 'SELECT site_id, ebay_site, active FROM ' . DB_PREFIX . 'kb_ebay_profiles WHERE id_ebay_profiles = ' . (int) $item['id_ebay_profiles'];
                $query = $this->db->query($profile);
                $profiledata = $query->row;
                if ($profiledata['active'] == 0) {
                    continue;
                }

                $siteDetails = $this->getEbaySiteById($profiledata['ebay_site']);
                $token = $siteDetails['token'];
                $headers = $this->getEbayHeaders($call_name, $profiledata['ebay_site']);
                $config = $this->getConfiguration();
                if ($config['account_type'] == 'sandbox') {
                    $sandbox = true;
                } else {
                    $sandbox = false;
                }
                $json_return = $this->relistEbayProducts($headers, $token, $item['ebay_listiing_id'], $sandbox);
                $array = json_decode($json_return, true);
                if ($array['Ack'] == 'Success' || $array['Ack'] == 'Warnings' || $array['Ack'] == 'Warning') {
                    $request = $call_name;
                    if (isset($array['Errors'])) {
                        $error = json_encode($array['Errors']);
                        $this->model_ebay_feed_cron->insertError($request, $error, $array['Ack'], $item['id_product'], $item['id_ebay_profiles']);
                    }
                    /* Don't Set Revise to 0 for Relist. Because Information of the product might have updated so need to revise as well after relisting */
                    $this->updateListedProduct($item['id_product'], $item['id_ebay_profiles'], $array['ItemID'], 'Listed', $profiledata['ebay_site'], $config['account_type']);

                    $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET date_added = '" . date("Y-m-d H:i:s") . "', ebay_status = 'Active' WHERE id_product = " . (int) $item['id_product'] . " AND id_ebay_profiles = '" . $item['id_ebay_profiles'] . "'");
                    $this->db->query("DELETE FROM " . DB_PREFIX . "kb_ebay_errors WHERE id_product = '" . (int) $item['id_product'] . "' AND id_profile = '" . $item['id_ebay_profiles'] . "'");
                    /* id_product is not unique becase same product can be multiple profile (Different country profile */
                } else {
                    $request = $call_name;
                    $error = json_encode($array['Errors']);
                    $this->model_ebay_feed_cron->insertError($request, $error, $array['Ack'], $item['id_product'], $item['id_ebay_profiles']);
                }
            }
        }
    }

    public function getAuthToken() {
        $store_id = $this->config->get('config_store_id');
        if ($store_id) {
            $store_id = $store_id;
        } else {
            $store_id = 0;
        }
        $this->load->model('ebay_feed/cron');
        $settings = $this->model_ebay_feed_cron->getSetting('ebay_general_settings', $store_id);
        return $settings['ebay_general_settings']['general']['token'];
    }

    public function relistEbayProducts($headers, $auth_token, $ebay_listing_id, $sandbox) {
        $xmlFeed = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlFeed .= '<RelistFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $xmlFeed .= '<RequesterCredentials>' . "\n";
        $xmlFeed .= '<eBayAuthToken>' . $auth_token . '</eBayAuthToken>' . "\n";
        $xmlFeed .= '</RequesterCredentials>' . "\n";
        $xmlFeed .= '<ErrorLanguage>en_US</ErrorLanguage>' . "\n";
        $xmlFeed .= '<WarningLevel>High</WarningLevel>' . "\n";
        $xmlFeed .= '<Item>' . "\n";
        $xmlFeed .= '<ItemID>' . $ebay_listing_id . '</ItemID>' . "\n";
        $xmlFeed .= '</Item>' . "\n";
        $xmlFeed .= '</RelistFixedPriceItemRequest>' . "\n";
        return $this->sendrequest($xmlFeed, $headers, $sandbox);
    }

    public function processEndProducts() {
        $this->load->model('ebay_feed/cron');
        $query_get_items = 'SELECT id_product, ebay_listiing_id, id_ebay_profiles FROM ' . DB_PREFIX . 'kb_ebay_profile_products pp WHERE end= "1" GROUP BY id_product, id_ebay_profiles';
        $query = $this->db->query($query_get_items);
        $get_items = $query->rows;
        $call_name = 'EndItems';

        if (!empty($get_items)) {
            foreach ($get_items as $item) {
                $profile = 'SELECT site_id, ebay_site, active FROM ' . DB_PREFIX . 'kb_ebay_profiles WHERE id_ebay_profiles = ' . (int) $item['id_ebay_profiles'];
                $query = $this->db->query($profile);
                $profiledata = $query->row;
                if ($profiledata['active'] == 0) {
                    continue;
                }

                $siteDetails = $this->getEbaySiteById($profiledata['ebay_site']);
                $token = $siteDetails['token'];
                $headers = $this->getEbayHeaders($call_name, $profiledata['ebay_site']);
                $config = $this->getConfiguration();
                if ($config['account_type'] == 'sandbox') {
                    $sandbox = true;
                } else {
                    $sandbox = false;
                }
                $data[] = $item;
                $json_return = $this->deleteProductsFromEbay($headers, $data, $token, $sandbox);
                $array = json_decode($json_return, true);

                if ($array['Ack'] == 'Success' || $array['Ack'] == 'Warnings' || $array['Ack'] == 'Warning' || $array['Ack'] == 'PartialFailure') {
                    if (isset($array['Errors'])) {
                        $error = json_encode($array['Errors']);
                        $this->model_ebay_feed_cron->insertError($call_name, $error, $array['Ack'], '');
                    } else {
                        $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET status = 'New', ebay_listiing_id = '', revise = '0', relist = '0', end = '0', ebay_status = '', is_disabled = '1' WHERE id_product = " . (int) $item['id_product'] . " AND id_ebay_profiles = '" . $item['id_ebay_profiles'] . "'");
                        $this->db->query("DELETE FROM " . DB_PREFIX . "kb_ebay_errors WHERE id_product = '" . (int) $item['id_product'] . "' AND id_profile = '" . $item['id_ebay_profiles'] . "'");
                    }
                } else {
                    if (isset($array['EndItemResponseContainer'])) {
                        foreach ($array['EndItemResponseContainer'] as $response) {
                            if (isset($response['Errors'])) {
                                $error = json_encode($response['Errors']);
                                $this->model_ebay_feed_cron->insertError($call_name, $error, $array['Ack'], $item['id_product'], $item['id_ebay_profiles']);
                            } else {
                                if (isset($response['ShortMessage']) && $response['ShortMessage'] == "The auction has been closed.") {
                                    $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET status = 'New', revise = '0', relist = '0', end = '0', ebay_status = '', is_disabled = '1' WHERE id_product = " . (int) $item['id_product'] . " AND id_ebay_profiles = '" . $item['id_ebay_profiles'] . "'");
                                    $this->db->query("DELETE FROM " . DB_PREFIX . "kb_ebay_errors WHERE id_product = '" . (int) $item['id_product'] . "' AND id_profile = '" . $item['id_ebay_profiles'] . "'");
                                }
                            }
                        }
                    } else {
                        $error = json_encode($array['Errors']);
                        $this->model_ebay_feed_cron->insertError($call_name, $error, $array['Ack'], '');
                    }
                }
            }
        }
    }

    public function deleteProductsFromEbay($headers, $delete_arr, $auth_token, $sandbox) {
        if (!empty($delete_arr)) {
            $xmlFeed = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xmlFeed .= '<EndItemsRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
            $i = 1;
            foreach ($delete_arr as $item) {
                $xmlFeed .= '<EndItemRequestContainer>' . "\n";
                $xmlFeed .= '<MessageID>' . $item['id_product'] . '</MessageID>' . "\n";
                $xmlFeed .= '<EndingReason>NotAvailable</EndingReason>' . "\n";
                $xmlFeed .= '<ItemID>' . $item['ebay_listiing_id'] . '</ItemID>' . "\n";
                $xmlFeed .= '</EndItemRequestContainer>' . "\n";
                $i++;
            }
            $xmlFeed .= '<RequesterCredentials>' . "\n";
            $xmlFeed .= '<eBayAuthToken>' . $auth_token . '</eBayAuthToken>' . "\n";
            $xmlFeed .= '</RequesterCredentials>' . "\n";
            $xmlFeed .= '<ErrorLanguage>en_US</ErrorLanguage>' . "\n";
            $xmlFeed .= '<WarningLevel>High</WarningLevel>' . "\n";
            $xmlFeed .= '</EndItemsRequest>';
            return $this->sendrequest($xmlFeed, $headers, $sandbox);
        }
    }

    public function processEbayReport() {
        $this->load->model('ebay_feed/cron');
        $allprofiles = 'SELECT * FROM ' . DB_PREFIX . 'kb_ebay_profiles GROUP by site_id';
        $query = $this->db->query($allprofiles);
        $allprofilesdata = $query->rows;
        foreach ($allprofilesdata as $profile) {
            $query_date = 'SELECT date_added FROM ' . DB_PREFIX . 'kb_ebay_profile_products epp WHERE id_ebay_profiles = "' . $profile['id_ebay_profiles'] . '" ORDER BY date_added ASC LIMIT 0,1';
            $query = $this->db->query($query_date);

            /* Contine only if profile_product table is having the products for that profile */
            if ($query->num_rows > 0) {
                $date = date('Y-m-d H:i:s');
                //$date_start = date("Y-m-d", strtotime($query->row['date_added']));
                $date_start = date("Y-m-d", strtotime($date . " -365 days"));
                $call_name = 'GetSellerList';

                $start = strtotime($date_start);
                $end = strtotime($date);
                $days_between = ceil(abs($end - $start) / 86400);

                $siteDetails = $this->getEbaySiteById($profile['site_id']);
                $token = $siteDetails['token'];
                $headers = $this->getEbayHeaders($call_name, $profile['site_id']);
                $config = $this->getConfiguration();
                if ($config['account_type'] == 'sandbox') {
                    $sandbox = true;
                } else {
                    $sandbox = false;
                }
                if ($days_between > 120) {
                    $actual_count = floor($days_between / 120) + 1;
                } else {
                    $actual_count = 1;
                }
                for ($i = 1; $i <= $actual_count; $i++) {
                    $start_date = $date_start;
                    if ($actual_count > 1) {
                        $end_date = date("Y-m-d", strtotime($start_date . " +120 days"));
                    } else {
                        $end_date = date("Y-m-d H:i:s");
                        $end_date = date("Y-m-d", strtotime($end_date . " +5 day"));
                    }
                  $date = date('Y-m-d H:i:s');
                  $start_date = date("Y-m-d", strtotime($date . " -120 days"));
                  $end_date = date("Y-m-d", strtotime($date . " +0 days"));
                    echo $start_date . '<br>';
                    echo $end_date . '<br>';
                    $json_return = $this->getProductsStatusReport($headers, $start_date, $end_date, $token, $sandbox);
                    $array = json_decode($json_return, true);
                    echo $json_return;
                    if ($array['Ack'] == 'Success' || $array['Ack'] == 'Warnings' || $array['Ack'] == 'Warning') {
                        $request = $call_name;
                        if (isset($array['Errors'])) {
                            $error = json_encode($array['Errors']);
                            $this->model_ebay_feed_cron->insertError($request, $error, $array['Ack'], '');
                        }
                        if (!empty($array['ItemArray']['Item'])) {
                            foreach ($array['ItemArray']['Item'] as $item) {
                                if (isset($item['SellingStatus']['ListingStatus'])) {
                                    if (isset($item['ListingDetails']['EndingReason'])) {
                                        $update1 = "UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET ebay_status = 'Ended', status = 'Deleted'";
                                    } else if ($item['SellingStatus']['ListingStatus'] == 'Active') {
                                        $update1 = "UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET ebay_status = '" . $this->db->escape($item['SellingStatus']['ListingStatus']) . "', status = 'Listed'";
                                    } else if ($item['SellingStatus']['ListingStatus'] == 'Ended' || $item['SellingStatus']['ListingStatus'] == 'Completed') {
                                        $update1 = "UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET ebay_status = '" . $this->db->escape($item['SellingStatus']['ListingStatus']) . "', status = 'Deleted'";
                                    } else {
                                        $update1 = "UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET ebay_status = '" . $this->db->escape($item['SellingStatus']['ListingStatus']) . "'";
                                    }
                                    $update1 .= " WHERE ebay_listiing_id = '" . $this->db->escape($item['ItemID']) . "'";
                                    $this->db->query($update1);
                                }
                            }
                        }
                    } else {
                        $request = $call_name;
                        $error = json_encode($array['Errors']);
                        $this->model_ebay_feed_cron->insertError($request, $error, $array['Ack'], '');
                    }
                }
            }
        }
    }

    public function getProductsStatusReport($headers, $start_date, $end_date, $token, $sandbox) {
        $xmlFeed = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlFeed .= '<GetSellerListRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $xmlFeed .= '<RequesterCredentials>' . "\n";
        $xmlFeed .= '<eBayAuthToken>' . $token . '</eBayAuthToken>' . "\n";
        $xmlFeed .= '</RequesterCredentials>' . "\n";
        $xmlFeed .= '<ErrorLanguage>en_US</ErrorLanguage>' . "\n";
        $xmlFeed .= '<WarningLevel>High</WarningLevel>' . "\n";
        $xmlFeed .= '<GranularityLevel>Coarse</GranularityLevel>' . "\n";
        $xmlFeed .= '<StartTimeFrom>' . $start_date . '</StartTimeFrom> ' . "\n";
        $xmlFeed .= '<StartTimeTo>' . $end_date . '</StartTimeTo>' . "\n";
        $xmlFeed .= '<IncludeWatchCount>true</IncludeWatchCount>' . "\n";
        $xmlFeed .= '<Pagination>' . "\n";
        $xmlFeed .= '<EntriesPerPage>200</EntriesPerPage>' . "\n";
        $xmlFeed .= '</Pagination>' . "\n";
        $xmlFeed .= '</GetSellerListRequest>' . "\n";
        return $this->sendrequest($xmlFeed, $headers, $sandbox);
    }

    public function processGetOrderFromEbay() {
        $this->load->model('catalog/product');
        $this->load->model('ebay_feed/cron');
        $allprofiles = 'SELECT * FROM ' . DB_PREFIX . 'kb_ebay_profiles GROUP BY site_id';
        $query = $this->db->query($allprofiles);
        $allprofilesdata = $query->rows;
        foreach ($allprofilesdata as $profile) {
            $call_name = 'GetOrders';
            $current_date = date("Y-m-d H:i:s");
            $start_date = date("Y-m-d", strtotime($current_date . " -2 day"));
            $end_date = date("Y-m-d", strtotime($current_date . " +2 day"));

            $siteDetails = $this->getEbaySiteById($profile['site_id']);
            $token = $siteDetails['token'];
            $headers = $this->getEbayHeaders($call_name, $profile['site_id']);
            $config = $this->getConfiguration();
            if ($config['account_type'] == 'sandbox') {
                $sandbox = true;
            } else {
                $sandbox = false;
            }

            $json_return = $this->getOrderFromEbay($headers, $start_date, $end_date, $token, $sandbox);
            $array = json_decode($json_return, true);
            if ($array['Ack'] == 'Success' || $array['Ack'] == 'Warnings' || $array['Ack'] == 'Warning') {
                $request = $call_name;
                if (isset($array['Errors'])) {
                    $error = json_encode($array['Errors']);
                    $this->model_ebay_feed_cron->insertError($request, $error, '');
                }
                if (!empty($array['OrderArray'])) {
                    /* In case of single order, Index is not present */
                    $ebayOrders = array();
                    if (!isset($array['OrderArray']['Order'][0])) {
                        $ebayOrders[0] = $array['OrderArray']['Order'];
                    } else {
                        $ebayOrders = $array['OrderArray']['Order'];
                    }
                    if ($ebayOrders) {
                        foreach ($ebayOrders as $ebay_order) {
                            $if_order_exists = 'SELECT * FROM ' . DB_PREFIX . 'kb_ebay_orders sa WHERE ebay_order_id = "' . $this->db->escape($ebay_order["OrderID"]) . '"';
                            $query = $this->db->query($if_order_exists);
                            $order_data = $query->rows;
                            if (!empty($order_data)) {
                                //do nothing
                            } else {
                                $array_order_details = array();
                                $ebay_order_transcation['TransactionArray']['Transaction'] = array();
                                if (isset($ebay_order['TransactionArray']['Transaction']['Buyer']['Email'])) {
                                    $email = $ebay_order['TransactionArray']['Transaction']['Buyer']['Email'];
                                } else {
                                    $email = $ebay_order['TransactionArray']['Transaction'][0]['Buyer']['Email'];
                                }

                                /* In case Shipping Details are blank, Skip the order creation */
                                if (empty($ebay_order['ShippingAddress']['Name'])) {
                                    continue;
                                }

                                /* In order is marked as Shipped on the eBay then don't import the same */
                                if (isset($ebay_order['ShippedTime'])) {
                                    continue;
                                }

                                $array_order_details['customer']['email'] = $email;
                                $array_order_details['customer']['firstname'] = $ebay_order['ShippingAddress']['Name'];
                                $array_order_details['customer']['lastname'] = "";


                                $array_order_details['customer']['address1'] = $ebay_order['ShippingAddress']['Street1'];
                                if (is_array($ebay_order['ShippingAddress']['Street2'])) {
                                    $ebay_order['ShippingAddress']['Street2'] = '';
                                }
                                $array_order_details['customer']['address2'] = $ebay_order['ShippingAddress']['Street2'];
                                $array_order_details['customer']['postcode'] = $ebay_order['ShippingAddress']['PostalCode'];
                                $array_order_details['customer']['city'] = $ebay_order['ShippingAddress']['CityName'];
                                if (is_array($ebay_order['ShippingAddress']['Phone'])) {
                                    $ebay_order['ShippingAddress']['Phone'] = '';
                                }
                                $array_order_details['customer']['phone_mobile'] = $ebay_order['ShippingAddress']['Phone'];
                                $id_country = $this->getCountryId($ebay_order['ShippingAddress']['Country']);
                                $id_zone = $this->getZoneId($ebay_order['ShippingAddress']['StateOrProvince'], $id_country);
                                $array_order_details['customer']['id_state'] = $id_zone;
                                $array_order_details['customer']['id_country'] = $id_country;
                                $array_order_details['customer']['state'] = $ebay_order['ShippingAddress']['StateOrProvince'];
                                $array_order_details['customer']['country'] = $ebay_order['ShippingAddress']['CountryName'];

                                //TransactionSiteID

                                $array_order_details['order']['id_language'] = $this->config->get('config_language_id');
                                $array_order_details['order']['name_carrier'] = $ebay_order['ShippingServiceSelected']['ShippingService'];
                                $array_order_details['order']['payment_method'] = $ebay_order['CheckoutStatus']['PaymentMethod'];
                                $array_order_details['order']['id_warehouse'] = 0;
                                $array_order_details['order']['cart_recyclable'] = 0;
                                $array_order_details['order']['cart_gift'] = 0;
                                $store_id = $this->config->get('config_store_id');
                                if (!$store_id) {
                                    $store_id = 0;
                                }
                                $array_order_details['order']['id_shop'] = $store_id;

                                $order_settings = $this->getSetting('ebay_order_settings', $store_id);
                                if ($ebay_order['CheckoutStatus']['eBayPaymentStatus'] == 'NoPaymentFailure') {
                                    $array_order_details['order']['current_state'] = $order_settings['ebay_order_settings']['order']['default_status'];
                                } else {
                                    $array_order_details['order']['current_state'] = $order_settings['ebay_order_settings']['order']['default_status'];
                                }

                                $array_order_details['order']['total_paid_real'] = 0.00;
                                $array_order_details['order']['total_products_wt'] = $ebay_order["Total"];
                                $array_order_details['order']['total_discounts_tax_excl'] = 0.00;
                                $array_order_details['order']['total_discounts_tax_incl'] = 0.00;
                                $array_order_details['order']['total_shipping_tax_excl'] = 0.00;
                                $array_order_details['order']['total_shipping_tax_incl'] = 0.00;

                                $array_order_details['order']['total_wrapping_tax_excl'] = 0.00;
                                $array_order_details['order']['total_wrapping_tax_incl'] = 0.00;
                                $array_order_details['order']['total_paid_tax_excl'] = $ebay_order["Total"];
                                $array_order_details['order']['total_paid_tax_incl'] = $ebay_order["Total"];
                                $array_order_details['order']['invoice_date'] = '0000:00:00';
                                $array_order_details['order']['delivery_date'] = '0000:00:00';

                                $all_products = array();
                                $line_items = array();
                                $array_order_details['order']['total_products'] = 0;
                                if (!isset($ebay_order['TransactionArray']['Transaction'][0])) {
                                    $ebay_order_transcation['TransactionArray']['Transaction'][0] = $ebay_order['TransactionArray']['Transaction'];
                                } else {
                                    $ebay_order_transcation['TransactionArray']['Transaction'] = $ebay_order['TransactionArray']['Transaction'];
                                }
                                if (isset($ebay_order_transcation['TransactionArray']['Transaction'][0])) {
                                    foreach ($ebay_order_transcation['TransactionArray']['Transaction'] as $item) {
                                        $products = array();
                                        $itemid = $item['Item']['ItemID'];
                                        $siteDetails = $this->getEbaySiteByCode($item['Item']['Site']);
                                        $array_order_details['order']['store_name'] = $siteDetails['description'];
                                        $array_order_details['order']['currency_iso_code'] = $siteDetails['currency_iso_code'];
                                        $array_order_details['order']['currency_id'] = $siteDetails['currency_id'];
                                        $array_order_details['order']['conversion'] = $siteDetails['value'];
                                        $array_order_details['order']['site_id'] = $siteDetails['id_ebay_countries'];
                                        $line_items[] = $item['OrderLineItemID'];
                                        $array_order_details['order']['total_products'] += $item['QuantityPurchased'];
                                        $sku = '';
                                        if (isset($item['Item']['SKU'])) {
                                            $sku = $item['Item']['SKU'];
                                        }
                                        $products['product_id'] = $this->getProductIdByEbayItemId($itemid, $sku);
                                        $products['quantity'] = $item['QuantityPurchased'];

                                        $product_details = $this->model_catalog_product->getProduct($products['product_id']);
                                        if ($product_details) {
                                            $products['name'] = $product_details['name'];
                                            $products['weight'] = $product_details['weight'];
                                            $products['model'] = $this->getProductSku($products['product_id']);
                                        } else {
                                            $products['name'] = $item['Item']['Title'];
                                            $products['weight'] = 0;
                                            $products['model'] = "";
                                        }
                                        $products['price'] = $item['TransactionPrice'];
                                        $products['quantity'] = $item['QuantityPurchased'];
                                        $products['reward'] = 0.00;
                                        $products['tax'] = $item['Taxes']['TotalTaxAmount'];
                                        $products['total'] = $item['QuantityPurchased'] * $item['TransactionPrice'];

                                        //If order is for products with variation
                                        if (isset($item['Variation'])) {
                                            $products['product_attribute'] = true;
                                            $productVariationInfo = explode("_", $item['Variation']['SKU']);

                                            $j = 0;
                                            $k = 0;
                                            $variation_data = array();
                                            /* In case hypen (-) is not present. Means product is added from somehwhere else then it will not be present. In the case In else condition, Simply Added Name Variations */
                                            if (count($productVariationInfo) > 1) {
                                                foreach ($productVariationInfo as $productVariation) {
                                                    /* Skip for Product ID as First Element in the Reference is Product ID */
                                                    if ($j == 0) {
                                                        $j++;
                                                        continue;
                                                    }
                                                    $productVariationData = explode(":", $productVariation);
                                                    /* In case colon (:) is not present. Means product is added from somehwhere else then in else condition add options from the returned data itself with option id as 0 */
                                                    if (count($productVariationData) > 1) {
                                                        $variation_details = $this->model_ebay_feed_cron->getVariationsDetails($productVariationData[1], $this->config->get('config_language_id'));
                                                        if (isset($variation_details[0]['name_attr'])) {
                                                            $variation_data['combination'][$k]['name'] = $variation_details[0]['name_attr'];
                                                            $variation_data['combination'][$k]['option_id'] = $productVariationData[0];
                                                            $variation_data['combination'][$k]['value'] = $variation_details[0]['name'];
                                                            $variation_data['combination'][$k]['option_value_id'] = $productVariationData[1];
                                                        } else {
                                                            $variation_data['combination'][$k]['name'] = $item['Variation']['VariationSpecifics']['NameValueList'][$k]['Name'];
                                                            $variation_data['combination'][$k]['option_id'] = 0;
                                                            $variation_data['combination'][$k]['value'] = $item['Variation']['VariationSpecifics']['NameValueList'][$k]['Value'];
                                                            $variation_data['combination'][$k]['option_value_id'] = 0;
                                                        }
                                                    } else {
                                                        $variation_data['combination'][$k]['name'] = $item['Variation']['VariationSpecifics']['NameValueList'][$k]['Name'];
                                                        $variation_data['combination'][$k]['option_id'] = 0;
                                                        $variation_data['combination'][$k]['value'] = $item['Variation']['VariationSpecifics']['NameValueList'][$k]['Value'];
                                                        $variation_data['combination'][$k]['option_value_id'] = 0;
                                                    }
                                                    $k++;
                                                    $j++;
                                                }
                                            } else {
                                                $k = 0;
                                                foreach ($item['Variation']['VariationSpecifics']['NameValueList'] as $name => $valueList) {
                                                    $variation_data['combination'][$k]['name'] = $name;
                                                    $variation_data['combination'][$k]['option_id'] = 0;
                                                    $variation_data['combination'][$k]['value'] = $valueList;
                                                    $variation_data['combination'][$k]['option_value_id'] = 0;
                                                    $k++;
                                                }
                                            }
                                            $products['variation_data'] = $variation_data;

                                            $products['product_sku'] = $item['Variation']['SKU'];
                                        } else {
                                            $products['product_attribute'] = false;
                                        }
                                        $all_products[] = $products;
                                    }
                                }
                                $array_order_details['products'] = $all_products;

                                $order_data = $this->writeOrderIntoDb($array_order_details, $ebay_order);
                                if (isset($order_data['success'])) {
                                    foreach ($line_items as $key => $l_items) {
                                        $insert_querry = 'INSERT INTO ' . DB_PREFIX . 'kb_ebay_orders set ebay_order_id = "' . $this->db->escape($ebay_order["OrderID"]) . '", ebay_line_item_id = "' . $this->db->escape($l_items) . '", store_order_id = ' . (int) $order_data['success']['order_id'] . ', ebay_site = "' . $array_order_details['order']['site_id'] . '" ,ebay_order_status = "' . $this->db->escape($ebay_order["OrderStatus"]) . '", order_ebay_data = "' . $this->db->escape(json_encode($array, true)) . '", date_added = now()';
                                        $this->db->query($insert_querry);
                                    }
                                } else {
                                    $request = $call_name;
                                    $error = json_encode($array['Errors']);
                                    $this->model_ebay_feed_cron->insertError($request, $error, '');
                                }
                            }
                        }
                    }
                }
            } else {
                $request = $call_name;
                $error = json_encode($array['Errors']);
                $this->model_ebay_feed_cron->insertError($request, $error, $array['Ack'], '');
            }
        }
    }

    public function getOrderFromEbay($headers, $start_date, $end_date, $token, $sandbox) {
        $xmlFeed = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlFeed .= '<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $xmlFeed .= '<RequesterCredentials>' . "\n";
        $xmlFeed .= '<eBayAuthToken>' . $token . '</eBayAuthToken>' . "\n";
        $xmlFeed .= '</RequesterCredentials>' . "\n";
        $xmlFeed .= '<ErrorLanguage>en_US</ErrorLanguage>' . "\n";
        $xmlFeed .= '<WarningLevel>High</WarningLevel>' . "\n";
        $xmlFeed .= '<CreateTimeFrom>' . $start_date . '</CreateTimeFrom>' . "\n";
        $xmlFeed .= '<CreateTimeTo>' . $end_date . '</CreateTimeTo>' . "\n";
        $xmlFeed .= '<OrderRole>Seller</OrderRole>' . "\n";
        $xmlFeed .= '<OrderStatus>Completed</OrderStatus>' . "\n";
        $xmlFeed .= '</GetOrdersRequest>' . "\n";
        return $this->sendrequest($xmlFeed, $headers, $sandbox);
    }

    public function getCountryId($code) {
        $query_get_products = 'SELECT * FROM ' . DB_PREFIX . 'country ec WHERE iso_code_2 ="' . $this->db->escape($code) . '"';
        $result = $this->db->query($query_get_products);
        if (!empty($result->row)) {
            return $result->row['country_id'];
        } else {
            return false;
        }
    }

    public function getZoneId($code, $id_country) {
        $query_get_products = 'SELECT * FROM ' . DB_PREFIX . 'zone ec WHERE code ="' . $this->db->escape($code) . '" and country_id = ' . (int) $id_country;
        $result = $this->db->query($query_get_products);
        if (!empty($result->row)) {
            return $result->row['zone_id'];
        } else {
            return false;
        }
    }

    public function getProductIdByEbayItemId($item_id, $sku) {
        $query_get_product = 'SELECT * FROM ' . DB_PREFIX . 'kb_ebay_profile_products epp WHERE ebay_listiing_id =' . $this->db->escape($item_id);
        $query = $this->db->query($query_get_product);
        if (!empty($query->row)) {
            return $query->row['id_product'];
        } else {
            if (!empty($sku)) {
                $query_get_product = "SELECT * FROM " . DB_PREFIX . "product WHERE model = '" . $this->db->escape($sku) . "'";
                $query = $this->db->query($query_get_product);
                if (!empty($query->row)) {
                    return $query->row['product_id'];
                } else {
                    return 0;
                }
            } else {
                return 0;
            }
        }
    }

    public function writeOrderIntoDb($array_order_details, $data) {
        $country_info = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = " . (int) $array_order_details['customer']['id_country']);
        $shipping_zone_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE zone_id = " . (int) $array_order_details['customer']['id_state']);

        if ($shipping_zone_query->row) {
            $shipping_zone_id = $shipping_zone_query->row['zone_id'];
            $payment_zone_id = $shipping_zone_query->row['zone_id'];
            $shipping_zone = $shipping_zone_query->row['name'];
            $payment_zone = $shipping_zone_query->row['name'];
        } else {
            $shipping_zone_id = 0;
            $payment_zone_id = 0;
            $shipping_zone = $array_order_details['customer']['state'];
            $payment_zone = $array_order_details['customer']['state'];
        }

        if ($country_info->row) {
            $shipping_country_id = $country_info->row['country_id'];
            $payment_country_id = $country_info->row['country_id'];
            $shipping_country = $country_info->row['name'];
            $shipping_address_format = $country_info->row['address_format'];
            $payment_country = $country_info->row['name'];
            $payment_address_format = $country_info->row['address_format'];
        } else {
            $shipping_country_id = 0;
            $payment_country_id = 0;
            $shipping_country = $array_order_details['customer']['country'];
            $shipping_address_format = "";
            $payment_country = $array_order_details['customer']['country'];
            $payment_address_format = "";
        }
        $order['store_name'] = $array_order_details['order']['store_name'];
        $order['store_url'] = '';
        $order['customer_id'] = 0;
        $order['firstname'] = $array_order_details['customer']['firstname'];
        $order['lastname'] = $array_order_details['customer']['lastname'];
        $order['telephone'] = $array_order_details['customer']['phone_mobile'];
        $order['fax'] = '';
        $order['email'] = $array_order_details['customer']['email'];
        $order['payment_firstname'] = $array_order_details['customer']['firstname'];
        $order['payment_lastname'] = $array_order_details['customer']['lastname'];
        $order['payment_company'] = '';
        $order['payment_company_id'] = '';
        $order['payment_tax_id'] = '';
        $order['payment_address_1'] = $array_order_details['customer']['address1'];
        $order['payment_address_2'] = $array_order_details['customer']['address2'];
        $order['payment_postcode'] = $array_order_details['customer']['postcode'];
        $order['payment_city'] = $array_order_details['customer']['city'];
        $order['payment_zone_id'] = $payment_zone_id;
        $order['payment_zone'] = $payment_zone;
        $order['payment_country_id'] = $payment_country_id;
        $order['payment_country'] = $payment_country;
        $order['payment_address_format'] = $payment_address_format;
        $order['payment_method'] = $array_order_details['order']['payment_method'];
        $order['payment_code'] = '';
        $order['shipping_firstname'] = $array_order_details['customer']['firstname'];
        $order['shipping_lastname'] = $array_order_details['customer']['lastname'];
        $order['shipping_company'] = '';
        $order['shipping_address_1'] = $array_order_details['customer']['address1'];
        $order['shipping_address_2'] = $array_order_details['customer']['address2'];
        $order['shipping_postcode'] = $array_order_details['customer']['postcode'];
        $order['shipping_city'] = $array_order_details['customer']['city'];
        $order['shipping_zone_id'] = $shipping_zone_id;
        $order['shipping_zone'] = $shipping_zone;
        $order['shipping_country_id'] = $shipping_country_id;
        $order['shipping_country'] = $shipping_country;
        $order['shipping_address_format'] = $shipping_address_format;
        $order['shipping_method'] = $array_order_details['order']['name_carrier'];
        $order['shipping_code'] = '';
        $order['total'] = $array_order_details['order']['total_paid_tax_excl'];
        $order['comment'] = '';
        $order['order_status_id'] = $array_order_details['order']['current_state'];
        $order['affiliate_id'] = 0;
        $order['affiliate_firstname'] = '';
        $order['affiliate_lastname'] = '';
        $order['commission'] = 0;
        $order['language_id'] = '1';
        $order['currency_id'] = $array_order_details['order']['currency_id'];
        $order['currency_code'] = $array_order_details['order']['currency_iso_code'];
        $order['currency_value'] = 1;
        $order['ip'] = '';
        $order['forwarded_ip'] = '';
        $order['user_agent'] = '';
        $order['accept_language'] = '';
        $order['date_added'] = date("Y-m-d H:i:s");
        $order['date_modified'] = date("Y-m-d H:i:s");
        $order['store_id'] = 0;
        $order['total_quantity'] = $array_order_details['order']['total_products'];
        $order_id = $this->createOrder($order);
        if ($order_id) {
            $this->insertOrderTotal($data, $order_id, $array_order_details['order']['currency_iso_code']);
            $this->createOrderProducts($array_order_details, $order_id);
            $this->addHistory($order['order_status_id'], $order_id);
        }
        $success = array(
            'message' => "Order created successfully.",
            'order_id' => $order_id,
        );
        $result_array['success'] = $success;
        return $result_array;
    }

    public function createOrder($data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "order` SET invoice_prefix = '".$this->db->escape($this->config->get('config_invoice_prefix'))."', store_id = '" . $data['store_id'] . "', store_name = '" . $data['store_name'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $data['fax'] . "', payment_firstname = '" . $this->db->escape($data['payment_firstname']) . "', payment_lastname = '" . $this->db->escape($data['payment_lastname']) . "', payment_company = '" . $data['payment_company'] . "', payment_address_1 = '" . $this->db->escape($data['payment_address_1']) . "', payment_address_2 = '" . $this->db->escape($data['payment_address_2']) . "', payment_city = '" . $this->db->escape($data['payment_city']) . "', payment_postcode = '" . $this->db->escape($data['payment_postcode']) . "', payment_country = '" . $this->db->escape($data['payment_country']) . "', payment_country_id = '" . (int) $data['payment_country_id'] . "', payment_zone = '" . $data['payment_zone'] . "', payment_zone_id = '" . $data['payment_zone_id'] . "', payment_address_format = '" . $this->db->escape($data['payment_address_format']) . "', payment_method = '" . $this->db->escape($data['payment_method']) . "', payment_code = '" . $data['payment_code'] . "', shipping_firstname = '" . $this->db->escape($data['shipping_firstname']) . "', shipping_lastname = '" . $this->db->escape($data['shipping_lastname']) . "', shipping_company = '" . $data['shipping_company'] . "', shipping_address_1 = '" . $this->db->escape($data['shipping_address_1']) . "', shipping_address_2 = '" . $this->db->escape($data['shipping_address_2']) . "', shipping_city = '" . $this->db->escape($data['shipping_city']) . "', shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . "', shipping_country = '" . $this->db->escape($data['shipping_country']) . "', shipping_country_id = '" . (int) $data['shipping_country_id'] . "', shipping_zone = '" . $data['shipping_zone'] . "', shipping_zone_id = '" . $data['shipping_zone_id'] . "', shipping_address_format = '" . $this->db->escape($data['shipping_address_format']) . "', shipping_method = '" . $this->db->escape($data['shipping_method']) . "', shipping_code = '" . $this->db->escape($data['shipping_code']) . "', comment = '" . $this->db->escape($data['comment']) . "', total = '" . $data['total'] . "',order_status_id = '" . $data['order_status_id'] . "', affiliate_id  = '" . $data['affiliate_id'] . "', language_id = '" . (int) $data['language_id'] . "', currency_id = '" . (int) $data['currency_id'] . "', currency_code = '" . $this->db->escape($data['currency_code']) . "', currency_value = '" . (float) $data['currency_value'] . "', date_added = '" . $data['date_added'] . "', date_modified = '" . $data['date_modified'] . "'");
        $inserted_id = $this->db->getLastId();
        if ($inserted_id > 0) {
            return $inserted_id;
        } else {
            return false;
        }
    }

    public function insertOrderTotal($data, $order_id, $currency) {
        $total_data = array();
        if ($data['IsMultiLegShipping'] == "true") {
            $total_data[0] = array(
                'code' => 'shipping',
                'title' => (string) $data['MultiLegShippingDetails']['SellerShipmentToLogisticsProvider']['ShippingServiceDetails']['ShippingService'],
                'text' => $this->currency->format((string) $data['MultiLegShippingDetails']['SellerShipmentToLogisticsProvider']['ShippingServiceDetails']['TotalShippingCost'], $currency),
                'value' => (string) $data['MultiLegShippingDetails']['SellerShipmentToLogisticsProvider']['ShippingServiceDetails']['TotalShippingCost'],
                'sort_order' => '3'
            );
        } else {
            $total_data[0] = array(
                'code' => 'shipping',
                'title' => (string) $data['ShippingServiceSelected']['ShippingService'],
                'text' => $this->currency->format((string) $data['ShippingServiceSelected']['ShippingServiceCost'], $currency),
                'value' => (string) $data['ShippingServiceSelected']['ShippingServiceCost'],
                'sort_order' => '3'
            );
        }
        $sub_total = (string) $data['Subtotal'];
        $total_data[1] = array(
            'code' => 'sub_total',
            'title' => 'Sub-Total',
            'text' => $this->currency->format($sub_total, $currency),
            'value' => $sub_total,
            'sort_order' => '1'
        );
        if (isset($data['ShippingDetails']['SalesTax']['SalesTaxAmount']) && (float) $data['ShippingDetails']['SalesTax']['SalesTaxAmount'] > (float) 0.0) {
            $tax = (float) @$data['ShippingDetails']['SalesTax']['SalesTaxAmount'];
            $total_data[3] = array(
                'code' => 'tax',
                'title' => 'Sales Tax',
                'text' => $this->currency->format($tax, $currency),
                'value' => $tax,
                'sort_order' => '4'
            );
        } else {
            if (isset($data['ShippingDetails']['SalesTax']['SalesTaxAmount'])) {
                $tax = $data['ShippingDetails']['SalesTax']['SalesTaxAmount'];
            } else {
                $tax = 0;
            }
        }

        $total = (string) $data['Subtotal'] + $total_data[0]['value'] + $tax;
        $total_data[2] = array(
            'code' => 'total',
            'title' => 'Total',
            'text' => $this->currency->format(max(0, $total), $currency),
            'value' => max(0, $total),
            'sort_order' => '9'
        );
        $order_query = $this->db->query("SELECT  order_id FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int) $order_id . "'");
        if ($order_query->num_rows == 0) {
            if (isset($total_data)) {
                foreach ($total_data as $order_total) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET order_id = '" . (int) $order_id . "', code = '" . $this->db->escape($order_total['code']) . "', title = '" . $this->db->escape($order_total['title']) . "', `value` = '" . (float) $order_total['value'] . "', sort_order = '" . (int) $order_total['sort_order'] . "'");
                }
            }
        }
    }

    public function createOrderProducts($data, $order_id) {
        foreach ($data['products'] as $value) {
            $order_product['order_id'] = $order_id;
            $order_product['product_id'] = $value['product_id'];
            $order_product['quantity'] = $value['quantity'];
            $order_product['name'] = $value['name'];
            $order_product['model'] = $value['model'];
            $order_product['price'] = $value['price'];
            $order_product['total'] = $value['total'];
            $order_product['tax'] = $value['tax'];
            $order_product['reward'] = 0;
            $this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET  order_id = '" . (int) $order_product['order_id'] . "', product_id = '" . (int) $order_product['product_id'] . "', name = '" . $this->db->escape($order_product['name']) . "', model = '" . $this->db->escape($order_product['model']) . "', quantity = '" . (int) $order_product['quantity'] . "', price = '" . (float) $order_product['price'] . "', total = '" . (float) $order_product['total'] . "', tax = '" . (float) $order_product['tax'] . "', reward = '" . (int) $order_product['reward'] . "'");
            $order_product_id = $this->db->getLastId();
            if ($order_product_id) {
                $this->createOrderOptions($value, $order_id, $order_product_id);
            }
            $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int) $order_product['quantity'] . ") WHERE product_id = '" . (int) $order_product['product_id'] . "' AND subtract = '1'");
            $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET revise = 1 WHERE id_product = '" . (int) $order_product['product_id'] . "'");
        }
        return true;
    }

    public function addHistory($order_status_id, $order_id) {
        $isExists = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_history where order_id = '" . (int) $order_id . "' AND order_status_id = '" . (int) $order_status_id . "'");
        if ($isExists->num_rows == 0) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int) $order_id . "', order_status_id = '" . (int) $order_status_id . "', notify = '', comment = '', date_added = '" . date("Y-m-d H:i:s") . "'");
            $this->db->getLastId();
        }
        return true;
    }

    public function getProductName($product_id) {
        $order_query = $this->db->query("SELECT name FROM `" . DB_PREFIX . "product_description` WHERE product_id = '" . $product_id . "'");
        if (!empty($order_query->row)) {
            return $order_query->row['name'];
        }
        return false;
    }

    public function getProductSku($product_id) {
        $order_query = $this->db->query("SELECT model, sku FROM `" . DB_PREFIX . "product` WHERE product_id = '" . $product_id . "'");
        if (!empty($order_query->row)) {
            if ($order_query->row['model'] != "") {
                return $order_query->row['model'];
            } else {
                return $order_query->row['sku'];
            }
        }
        return false;
    }

    public function createOrderOptions($data, $order_id, $order_product_id) {
        if (isset($data['product_attribute']) == 1) {
            if (isset($data['variation_data']['combination'])) {
                foreach ($data['variation_data']['combination'] as $combination) {
                    $order_product_option_id = $this->get_product_option_id($data['product_id'], $combination['option_id']);
                    $order_product_option_value_id = $this->get_product_option_value_id($data['product_id'], $combination['option_value_id']);

                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET order_id = '" . (int) $order_id . "', order_product_id = '" . (int) $order_product_id . "', product_option_id = '" . (int) $order_product_option_id . "', product_option_value_id = '" . (int) $order_product_option_value_id . "', name = '" . $this->db->escape($combination['name']) . "', `value` = '" . $this->db->escape($combination['value']) . "', `type` = 'select'");
                    $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity - " . (int) $data['quantity'] . ")  WHERE product_option_value_id = '" . (int) $order_product_option_value_id . "' AND subtract = '1'");
                }
            }
        }
        return true;
    }

    public function getProductOptionID($name) {
        $order_query = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name = '" . $name . "'");
        return $order_query->row['option_id'];
    }

    public function get_option_values_id($name, $id) {
        $order_query = $this->db->query("SELECT option_value_id FROM `" . DB_PREFIX . "option_value_description` WHERE name = '" . $name . "' and option_id = " . (int) $id);
        return $order_query->row['option_value_id'];
    }

    public function get_product_option_id($product_id, $option_id) {
        $order_query = $this->db->query("SELECT product_option_id FROM `" . DB_PREFIX . "product_option` WHERE product_id = '" . $product_id . "' and option_id = '" . $option_id . "'");
        if (!empty($order_query->row)) {
            return $order_query->row['product_option_id'];
        } else {
            return false;
        }
    }

    public function get_product_option_value_id($product_id, $option_value_id) {
        $order_query = $this->db->query("SELECT product_option_value_id FROM `" . DB_PREFIX . "product_option_value` WHERE product_id = '" . $product_id . "' and option_value_id = '" . $option_value_id . "'");
        if (!empty($order_query->row)) {
            $order_query->row['product_option_value_id'];
        } else {
            return false;
        }
    }

    public function processOrderUpdate() {
        $this->load->model('ebay_feed/cron');
        $call_name = 'CompleteSale';
        $store_id = $this->config->get('config_store_id');
        if (!$store_id || $store_id == 'select') {
            $store_id = 0;
        }
        $order_settings = $this->getSetting('ebay_order_settings', $store_id);
        //Get all orders for status update
        //$cancelStatus = $order_settings['ebay_order_settings']['order']['cancel_status'];
        $shippedStatus = $order_settings['ebay_order_settings']['order']['shipped_status'];
        $get_orders_query = 'SELECT eo.*, o.order_status_id FROM ' . DB_PREFIX . 'kb_ebay_orders eo ,' . DB_PREFIX . 'order o WHERE o.order_id = eo.store_order_id AND is_status_updated = "1" AND (o.order_status_id IN(' . implode(",", $shippedStatus) . '))';
        $query = $this->db->query($get_orders_query);
        $get_orders = $query->rows;
        if (!empty($get_orders)) {

            foreach ($get_orders as $order) {
                $siteDetails = $this->getEbaySiteById($order['ebay_site']);
                $token = $siteDetails['token'];
                $headers = $this->getEbayHeaders($call_name, $order['ebay_site']);
                $config = $this->getConfiguration();
                if ($config['account_type'] == 'sandbox') {
                    $sandbox = true;
                } else {
                    $sandbox = false;
                }
                $json_return = $this->updateOrderStatus($headers, $token, $order, false, $shippedStatus, $sandbox);
                $array = json_decode($json_return, true);

                if ($array['Ack'] == 'Success' || $array['Ack'] == 'Warnings' || $array['Ack'] == 'Warning') {
                    $request = $call_name;
                    if (isset($array['Errors'])) {
                        $error = json_encode($array['Errors']);
                        $this->model_ebay_feed_cron->insertError($request, $error, $array['Ack'], $order['id_ebay_orders']);
                    }
                    $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_orders SET is_status_updated = '0' WHERE id_ebay_orders = '" . $this->db->escape($order['id_ebay_orders']) . "'");
                } else {
                    $request = $call_name;
                    $error = json_encode($array['Errors']);
                    $this->model_ebay_feed_cron->insertError($request, $error, $array['Ack'], $order['id_ebay_orders']);
                }
            }
        }
    }

    public function updateOrderStatus($headers, $token, $order, $paidStatus, $shippedStatus, $sandbox) {
        $xmlFeed = '';
        $xmlFeed .= '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlFeed .= '<CompleteSaleRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $xmlFeed .= '<RequesterCredentials>' . "\n";
        $xmlFeed .= '<eBayAuthToken>' . $token . '</eBayAuthToken>' . "\n";
        $xmlFeed .= '</RequesterCredentials>' . "\n";
        $xmlFeed .= '<ErrorLanguage>en_US</ErrorLanguage>' . "\n";
        $xmlFeed .= '<WarningLevel>High</WarningLevel>' . "\n";
        $xmlFeed .= '<OrderID>' . $order['ebay_order_id'] . '</OrderID>' . "\n";
        $xmlFeed .= '<OrderLineItemID>' . $order['ebay_line_item_id'] . '</OrderLineItemID>' . "\n";
//        if ($order['order_status_id'] == $paidStatus) {
//            $xmlFeed .= '<Paid>true</Paid>' . "\n";
//            $xmlFeed .= '<Shipped>false</Shipped>' . "\n";
//        } else 
        $xmlFeed .= '<Shipped>true</Shipped>' . "\n";
        $xmlFeed .= '</CompleteSaleRequest>' . "\n";
        return $this->sendrequest($xmlFeed, $headers, $sandbox);
    }

    /* Get Shipping Methods of the site */

    public function getEbayDetails($headers, $token, $detail, $sandbox) {
        $xmlFeed = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlFeed .= '<GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $xmlFeed .= '<RequesterCredentials>' . "\n";
        $xmlFeed .= '<eBayAuthToken>' . $token . '</eBayAuthToken>' . "\n";
        $xmlFeed .= '</RequesterCredentials>' . "\n";
        $xmlFeed .= '<DetailName>' . $detail . '</DetailName>' . "\n";
        $xmlFeed .= '<ErrorLanguage>en_US</ErrorLanguage>' . "\n";
        $xmlFeed .= '<WarningLevel>High</WarningLevel>' . "\n";
        $xmlFeed .= '</GeteBayDetailsRequest>' . "\n";
        return $this->sendrequest($xmlFeed, $headers, $sandbox);
    }

    /* Get eBay Categories */

    public function getEbayCategoriesEbay($headers, $token, $sandbox) {
        $xmlFeed = '';
        $xmlFeed .= '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlFeed .= '<GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $xmlFeed .= '<RequesterCredentials> ' . "\n";
        $xmlFeed .= '<eBayAuthToken>' . $token . '</eBayAuthToken>' . "\n";
        $xmlFeed .= '</RequesterCredentials>' . "\n";
        $xmlFeed .= '<DetailLevel>ReturnAll</DetailLevel> and <ViewAllNodes>true</ViewAllNodes>' . "\n";
        $xmlFeed .= '</GetCategoriesRequest>' . "\n";
        return $this->sendrequest($xmlFeed, $headers, $sandbox);
    }

    public function getShippingProfileDetails($shipping_profile_id) {
        $query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'kb_ebay_shipping WHERE id_ebay_shipping =' . (int) $shipping_profile_id);
        return $query->row;
    }

    public function getProductAttributes($product_id, $langauge_id) {
        $product_attribute_group_data = array();

        $product_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int) $product_id . "' AND agd.language_id = '" . (int) $langauge_id . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");

        foreach ($product_attribute_group_query->rows as $product_attribute_group) {
            $product_attribute_data = array();

            $product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int) $product_id . "' AND a.attribute_group_id = '" . (int) $product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int) $langauge_id . "' AND pa.language_id = '" . (int) $langauge_id . "' ORDER BY a.sort_order, ad.name");

            foreach ($product_attribute_query->rows as $product_attribute) {
                $product_attribute_data[] = array(
                    'attribute_id' => $product_attribute['attribute_id'],
                    'name' => $product_attribute['name'],
                    'text' => $product_attribute['text']
                );
            }

            $product_attribute_group_data[] = array(
                'attribute_group_id' => $product_attribute_group['attribute_group_id'],
                'name' => $product_attribute_group['name'],
                'attribute' => $product_attribute_data
            );
        }

        return $product_attribute_group_data;
    }

    public function getStoreCategories($headers, $token, $sandbox) {
        $xmlFeed = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlFeed .= '<GetStoreRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $xmlFeed .= '<RequesterCredentials>' . "\n";
        $xmlFeed .= '<eBayAuthToken>' . $token . '</eBayAuthToken>' . "\n";
        $xmlFeed .= '</RequesterCredentials>' . "\n";
        $xmlFeed .= '<ErrorLanguage>en_US</ErrorLanguage>' . "\n";
        $xmlFeed .= '<WarningLevel>High</WarningLevel>' . "\n";
        $xmlFeed .= '</GetStoreRequest>' . "\n";
        return $this->sendrequest($xmlFeed, $headers, $sandbox);
    }

    public function getProductOptions($product_id, $language_id) {
        $product_option_data = array();

        $product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int) $product_id . "' AND od.language_id = '" . (int) $language_id . "' ORDER BY o.sort_order");

        foreach ($product_option_query->rows as $product_option) {
            $product_option_value_data = array();

            $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int) $product_id . "' AND pov.product_option_id = '" . (int) $product_option['product_option_id'] . "' AND ovd.language_id = '" . (int) $language_id . "' ORDER BY ov.sort_order");

            foreach ($product_option_value_query->rows as $product_option_value) {
                $product_option_value_data[] = array(
                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                    'option_value_id' => $product_option_value['option_value_id'],
                    'name' => $product_option_value['name'],
                    'image' => $product_option_value['image'],
                    'quantity' => $product_option_value['quantity'],
                    'subtract' => $product_option_value['subtract'],
                    'price' => $product_option_value['price'],
                    'price_prefix' => $product_option_value['price_prefix'],
                    'weight' => $product_option_value['weight'],
                    'weight_prefix' => $product_option_value['weight_prefix']
                );
            }

            $product_option_data[] = array(
                'product_option_id' => $product_option['product_option_id'],
                'product_option_value' => $product_option_value_data,
                'option_id' => $product_option['option_id'],
                'name' => $product_option['name'],
                'type' => $product_option['type'],
                'value' => $product_option['value'],
                'required' => $product_option['required']
            );
        }

        return $product_option_data;
    }

}

?>