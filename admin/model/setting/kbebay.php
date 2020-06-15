<?php

/*
 * This file is used of Add products in the new Amazon tables
 * This file is having all the function which is used in submitting products on Amazon
 * File is added by Ashwani on 23-04-2014 
 */

class ModelSettingKbebay extends Model
{

    public function editSetting($code, $data, $store_id = 0)
    {
        if (VERSION >= '2.2.0.0') {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int) $store_id . "' AND `code` = '" . $this->db->escape($code) . "'");
        } else {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int) $store_id . "' AND `code` = '" . $this->db->escape($code) . "'");
        }

        if (VERSION >= '2.2.0.0') {
            foreach ($data as $key => $value) {
                if (substr($key, 0, strlen($code)) == $code) {
                    if (!is_array($value)) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int) $store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
                    } else {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int) $store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value, true)) . "', serialized = '1'");
                    }
                }
            }
        } else {
            foreach ($data as $key => $value) {
                if (substr($key, 0, strlen($code)) == $code) {
                    if (!is_array($value)) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int) $store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
                    } else {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int) $store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1'");
                    }
                }
            }
        }
    }

    public function getSetting($code, $store_id = 0)
    {
        $setting_data = array();
        if (VERSION >= '2.2.0.0') {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int) $store_id . "' AND `code` = '" . $this->db->escape($code) . "'");
        } else {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int) $store_id . "' AND `code` = '" . $this->db->escape($code) . "'");
        }
        foreach ($query->rows as $result) {
            if (!$result['serialized']) {
                $setting_data[$result['key']] = $result['value'];
            } else {
                if (VERSION >= '2.2.0.0') {
                    $setting_data[$result['key']] = json_decode($result['value'], true);
                } else {
                    $setting_data[$result['key']] = unserialize($result['value']);
                }
            }
        }
        return $setting_data;
    }

    public function getProfileDetails($data, $id_ebay_profiles = '')
    {
        if (!empty($id_ebay_profiles)) {
            $sql = "SELECT p.*,s.description, s.vatenabled FROM " . DB_PREFIX . "kb_ebay_profiles p left join " . DB_PREFIX . "kb_ebay_sites s on p.site_id = s.id_ebay_countries where id_ebay_profiles = " . $id_ebay_profiles;
        } else {
            $sql = "SELECT p.*,s.description, s.vatenabled FROM " . DB_PREFIX . "kb_ebay_profiles p left join " . DB_PREFIX . "kb_ebay_sites s on p.site_id = s.id_ebay_countries where id_ebay_profiles > 0";
        }

        if (isset($data['filter_profile_name']) && !is_null($data['filter_profile_name'])) {
            $sql .= " AND profile_name LIKE '%" . $this->db->escape($data['filter_profile_name']) . "%'";
        }
        if (isset($data['filter_ebay_category']) && !is_null($data['filter_ebay_category'])) {
            $sql .= " AND ebay_catgeory_text LIKE '%" . $this->db->escape(str_replace('&amp;', '&', $data['filter_ebay_category'])) . "%'";
        }

        if (isset($data['filter_store_category']) && !is_null($data['filter_store_category'])) {
            $sql .= " AND store_category_text LIKE '%" . str_replace('>', '&gt;', $data['filter_store_category']) . "%'";
        }
        $sort_data = array(
            'id_ebay_profiles',
            'profile_name',
            'ebay_catgeory_text',
            'ec.category_name',
            'store_category_text',
            'site_id',
            'status',
            'active',
            's.description'
        );
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY id_ebay_profiles";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }
        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getCountriesDetails($data)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_sites  where id_ebay_countries > 0";
        if (isset($data['filter_country_id']) && !is_null($data['filter_country_id'])) {
            $sql .= " AND id_ebay_countries = '" . $this->db->escape($data['filter_country_id']) . "'";
        }
        if (isset($data['filter_country_iso_code']) && !is_null($data['filter_country_iso_code'])) {
            $sql .= " AND abbreviation LIKE '%" . $this->db->escape($data['filter_country_iso_code']) . "%'";
        }

        if (isset($data['filter_site_name']) && !is_null($data['filter_site_name'])) {
            $sql .= " AND site_name LIKE '%" . $this->db->escape($data['filter_site_name']) . "%'";
        }
        $sort_data = array(
            'id_ebay_countries',
            'abbreviation',
            'site_name',
            'enabled',
        );
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY id_ebay_site";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }
        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalCountries($data = array())
    {
        $sql = "SELECT COUNT(DISTINCT id_ebay_countries) AS total FROM " . DB_PREFIX . "kb_ebay_sites where id_ebay_countries > 0";

        if (isset($data['filter_country_id']) && !is_null($data['filter_country_id'])) {
            $sql .= " AND id_ebay_countries = '" . $this->db->escape($data['filter_country_id']) . "'";
        }
        if (isset($data['filter_country_iso_code']) && !is_null($data['filter_country_iso_code'])) {
            $sql .= " AND abbreviation LIKE '%" . $this->db->escape($data['filter_country_iso_code']) . "%'";
        }

        if (isset($data['filter_site_name']) && !is_null($data['filter_site_name'])) {
            $sql .= " AND site_name LIKE '%" . $this->db->escape($data['filter_site_name']) . "%'";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getProfileTotal($data = array())
    {
        $sql = "SELECT COUNT(DISTINCT id_ebay_profiles) AS total FROM " . DB_PREFIX . "kb_ebay_profiles where id_ebay_profiles > 0";

        if (isset($data['filter_profile_name']) && !is_null($data['filter_profile_name'])) {
            $sql .= " AND profile_name LIKE '" . $this->db->escape($data['filter_profile_name']) . "%'";
        }
        if (isset($data['filter_ebay_category']) && !is_null($data['filter_ebay_category'])) {
            $sql .= " AND ebay_catgeory_text LIKE '%" . $this->db->escape($data['filter_ebay_category']) . "%'";
        }

        if (isset($data['filter_store_category']) && !is_null($data['filter_store_category'])) {
            $sql .= " AND store_category_text LIKE '%" . str_replace('>', '&gt;', $data['filter_store_category']) . "%'";
        }

        if (isset($data['filter_ebay_site']) && !is_null($data['filter_ebay_site'])) {
            $sql .= " AND ebay_site LIKE '" . $this->db->escape($data['filter_ebay_site']) . "%'";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getEbayCategories($site_id = 0)
    {
        $query_get_root = 'SELECT ec.id_ebay_categories,ec.ebay_categories,ec.ebay_category_name FROM ' . DB_PREFIX . 'kb_ebay_categories ec WHERE ebay_category_level=1 AND ebay_site_id = ' . (int) $site_id . ' order by ebay_category_name';
        $ebay_root_categories = $this->db->query($query_get_root);
        $ebay_root_categories = $ebay_root_categories->rows;
        array_unshift($ebay_root_categories, array('ebay_categories' => '0', 'ebay_category_name' => $this->language->get('text_ebay_category_select')));
        return $ebay_root_categories;
    }

    public function getEbaySubcategories($cat, $site_id)
    {
        $query_get_subcat = 'SELECT ec.id_ebay_categories,ec.ebay_categories,ec.ebay_category_name FROM ' . DB_PREFIX . 'kb_ebay_categories ec WHERE id_ebay_category_parent=' . (int) $cat . ' AND ebay_categories !=' . (int) $cat . ' AND ebay_site_id = ' . (int) $site_id . ' order by ebay_category_name';
        $ebay_sub_categories = $this->db->query($query_get_subcat);
        $ebay_sub_categories = $ebay_sub_categories->rows;
        $sub_cat = array();
        if (!empty($ebay_sub_categories)) {
            array_unshift($ebay_sub_categories, array('ebay_categories' => '0', 'ebay_category_name' => 'Select Category'));
            return $ebay_sub_categories;
        }
    }

    public function addProfile($data)
    {
        $this->load->model('catalog/category');
        $category_text = array();
        foreach ($data['product_category'] as $category_id) {
            $category_info = $this->model_catalog_category->getCategory($category_id);
            $category_text[] = ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name'];
        }
        $category_text_all = implode(",", $category_text);
        if ($data['ebay']['profile']['return'] == 0) {
            $return_enable = 'ReturnsNotAccepted';
        } else {
            $return_enable = 'ReturnsAccepted';
        }
        $data['product_category'] = implode(",", $data['product_category']);
        if(!isset($data['ebay']['profile']['ebay_store_category'])) {
            $data['ebay']['profile']['ebay_store_category'] = '';
        }
        $this->db->query("INSERT INTO " . DB_PREFIX . "kb_ebay_profiles  set "
                . "profile_name = '" . $this->db->escape($data['ebay']['profile']['profile_title']) . "', "
                . "ebay_site = '" . (int) $data['ebay']['profile']['ebay-sites'] . "', "
                . "ebay_category_id = '" . (int) $data['ebay']['profile']['id_ebay_cat_final'] . "', "
                . "ebay_catgeory_text = '" . $this->db->escape(str_replace('amp;', '', str_replace('&amp;', '&', $data['ebay']['profile']['ebay_cat_text']))) . "', "
                . "store_category_id = '" . $this->db->escape($data['product_category']) . "', "
                . "store_category_text = '" . $this->db->escape(str_replace('&nbsp;&nbsp;&gt;&nbsp;&nbsp;', ' &gt; ', $category_text_all)) . "', "
                . "ebay_currency = '" . $this->db->escape($data['ebay']['profile']['currency']) . "', "
                . "html_template = '" . $this->db->escape($data['ebay']['profile']['html_template']) . "', "
                . "ebay_payment_method = '" . $this->db->escape(implode(",", $data['ebay']['profile']['payment_methods'])) . "', "
                . "ebay_language = '" . $this->db->escape($data['ebay']['profile']['languages']) . "', "
                . "ebay_shipping_profile = '" . $this->db->escape($data['ebay']['profile']['ebay_shipping_profile']) . "', "
                . "duration = '" . $this->db->escape($data['ebay']['profile']['ebay_duration']) . "', "
                . "product_quantity = '" . $this->db->escape($data['ebay']['profile']['product_quantity']) . "', "
                . "dispatch_days = '" . $this->db->escape($data['ebay']['profile']['product_dispatch_time']) . "',  "
                . "product_condition = '" . $this->db->escape($data['ebay']['profile']['ebay_product_condition']) . "', "
                . "status = 'completed', "
                . "vat_percentage = '" . $this->db->escape($data['ebay']['profile']['ebay_vat_percentage']) . "', "
                . "return_enable = '" . $this->db->escape($return_enable) . "', "
                . "return_days = '" . $this->db->escape($data['ebay']['profile']['return_time']) . "', "
                . "refund = '" . $this->db->escape($data['ebay']['profile']['return_type']) . "', "
                . "return_description = '" . $this->db->escape($data['ebay']['profile']['return_description']) . "', "
                . "return_shipping = '" . $this->db->escape($data['ebay']['profile']['return_shipping']) . "', "
                . "price_management = '" . $this->db->escape($data['ebay']['profile']['price_management']) . "', "
                . "increase_decrease = '" . $this->db->escape($data['ebay']['profile']['increase_decrease']) . "', "
                . "product_price = '" . $this->db->escape($data['ebay']['profile']['product_price']) . "', "
                . "product_threshold_price = '" . $this->db->escape($data['ebay']['profile']['product_threshold_price']) . "', "
                . "percentage_fixed = '" . $this->db->escape($data['ebay']['profile']['percentage_fixed']) . "', "
                . "store_category = '" . $this->db->escape($data['ebay']['profile']['ebay_store_category']) . "', "
                . "active = 1,"
                . "site_id = '" . (int) $data['ebay']['profile']['ebay-sites'] . "', "
                . "date_added = NOW(), "
                . "date_modified = NOW()");
        $id = $this->db->getLastId();
        return $id;
    }

    public function editProfile($data)
    {
        $this->load->model('catalog/category');
        $category_text = array();
        foreach ($data['product_category'] as $category_id) {
            $category_info = $this->model_catalog_category->getCategory($category_id);
            $category_text[] = ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name'];
        }
        $category_text_all = implode(",", $category_text);
        if ($data['ebay']['profile']['return'] == 0) {
            $return_enable = 'ReturnsNotAccepted';
        } else {
            $return_enable = 'ReturnsAccepted';
        }
        $data['product_category'] = implode(",", $data['product_category']);
        $this->db->query("update " . DB_PREFIX . "kb_ebay_profiles  set "
                . "profile_name = '" . $this->db->escape($data['ebay']['profile']['profile_title']) . "', "
                . "ebay_category_id = '" . (int) $data['ebay']['profile']['id_ebay_cat_final'] . "', "
                . "ebay_catgeory_text = '" . $this->db->escape(str_replace('amp;', '', str_replace('&amp;', '&', str_replace('&amp;gt;&amp;gt;', '>>', $data['ebay']['profile']['ebay_cat_text'])))) . "', "
                . "store_category_id = '" . $this->db->escape($data['product_category']) . "', "
                . "store_category_text = '" . $this->db->escape(str_replace('&nbsp;&nbsp;&gt;&nbsp;&nbsp;', ' &gt; ', $category_text_all)) . "', "
                . "ebay_currency = '" . $this->db->escape($data['ebay']['profile']['currency']) . "', "
                . "html_template = '" . $this->db->escape($data['ebay']['profile']['html_template']) . "', "
                . "ebay_payment_method = '" . $this->db->escape(implode(",", $data['ebay']['profile']['payment_methods'])) . "', "
                . "ebay_language = '" . $this->db->escape($data['ebay']['profile']['languages']) . "', "
                . "ebay_shipping_profile = '" . $this->db->escape($data['ebay']['profile']['ebay_shipping_profile']) . "', "
                . "duration = '" . $this->db->escape($data['ebay']['profile']['ebay_duration']) . "', "
                . "product_quantity = '" . $this->db->escape($data['ebay']['profile']['product_quantity']) . "', "
                . "dispatch_days = '" . $this->db->escape($data['ebay']['profile']['product_dispatch_time']) . "',  "
                . "product_condition = '" . $this->db->escape($data['ebay']['profile']['ebay_product_condition']) . "', "
                . "status = 'completed', "
                . "vat_percentage = '" . $this->db->escape($data['ebay']['profile']['ebay_vat_percentage']) . "', "
                . "price_management = '" . $this->db->escape($data['ebay']['profile']['price_management']) . "', "
                . "increase_decrease = '" . $this->db->escape($data['ebay']['profile']['increase_decrease']) . "', "
                . "product_price = '" . $this->db->escape($data['ebay']['profile']['product_price']) . "', "
                . "product_threshold_price = '" . $this->db->escape($data['ebay']['profile']['product_threshold_price']) . "', "
                . "percentage_fixed = '" . $this->db->escape($data['ebay']['profile']['percentage_fixed']) . "', "
                . "return_enable = '" . $this->db->escape($return_enable) . "', "
                . "return_days = '" . $this->db->escape($data['ebay']['profile']['return_time']) . "', "
                . "refund = '" . $this->db->escape($data['ebay']['profile']['return_type']) . "', "
                . "return_shipping = '" . $this->db->escape($data['ebay']['profile']['return_shipping']) . "',"
                . "return_description = '" . $this->db->escape($data['ebay']['profile']['return_description']) . "',"
                . "store_category = '" . $this->db->escape($data['ebay']['profile']['ebay_store_category']) . "', "
                . "active = 1,"
                . "date_modified = NOW() "
                . "WHERE id_ebay_profiles = " . $data['ebay']['profile']['id_ebay_profiles']);

        $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET revise = '1', status = 'Updated' WHERE id_ebay_profiles = " . $data['ebay']['profile']['id_ebay_profiles'] . " AND status NOT IN ('New','Deleted','Relist')");

        return $data['ebay']['profile']['id_ebay_profiles'];
    }

    public function getAttributes($data = array())
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "attribute_description WHERE language_id = '" . (int) $this->config->get('config_language_id') . "'";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function checkCountryStatus($id)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_categories WHERE ebay_site_id = '" . (int) $id . "'";
        $query = $this->db->query($sql);
        return $query->num_rows;
    }

    public function getAttributesName($id)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "attribute_description WHERE language_id = '" . (int) $this->config->get('config_language_id') . "' and attribute_id = " . $id;
        $query = $this->db->query($sql);
        return $query->rows[0]['name'];
    }

    public function getOrderStatusById($id)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int) $id . "'";
        $query = $this->db->query($sql);
        return $query->rows[0]['name'];
    }

    public function getProductIdByEbayItemId($item_id)
    {
        $query_get_product = 'SELECT * FROM ' . DB_PREFIX . 'kb_ebay_profile_products epp WHERE ebay_listiing_id =' . $this->db->escape($item_id);
        $query = $this->db->query($query_get_product);
        $prod = $query->rows;
        if (isset($prod[0])) {
            return $prod[0]['id_product'];
        } else {
            return true;
        }
    }

    public function getEbaySitesCount($data = array())
    {
        $sql = "SELECT count(*) as total FROM " . DB_PREFIX . "kb_ebay_sites";
        $query = $this->db->query($sql);
        return $query->row['total'];
    }

    public function getEbaySites($enabled = 1, $data = array())
    {
        if ($enabled == 1) {
            $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_sites WHERE enabled = 1";
        } else {
            $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_sites";
        }

        $sort_data = array(
            'id_ebay_site',
            'abbreviation',
            'site_name',
            'enabled'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY id_ebay_site";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }
        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getSpecificsDetails($id_category, $site_id, $profile_id)
    {
        $query_if_exists = 'SELECT count(*) as count FROM ' . DB_PREFIX . 'kb_ebay_category_specifics  WHERE id_ebay_categories=' . (int) $id_category . ' AND site_id = ' . (int) $site_id;
        $query = $this->db->query($query_if_exists);
        if ($query->row['count'] > 0) {
            $this->createSpecifics($id_category, $profile_id, $site_id);
        } else {
            $this->load->model('setting/kbebay');
            $call_name = 'GetCategorySpecifics';
            $siteDetails = $this->model_setting_kbebay->getEbaySiteById($site_id);
            $token = $siteDetails['token'];
            $config = $this->model_setting_kbebay->getConfiguration();
            if ($config['account_type'] == 'sandbox') {
                $sandbox = true;
            } else {
                $sandbox = false;
            }
            $headers = $this->getEbayHeaders($call_name, $site_id);

            $json_return = $this->getSpecifics($headers, $token, $id_category, $sandbox);
            $data_array = json_decode($json_return, true);
            if ($data_array['Ack'] == 'Success') {
                if (isset($data_array['Recommendations']['NameRecommendation'])) {
                    foreach ($data_array['Recommendations']['NameRecommendation'] as $specific) {
                        $name = $specific['Name'];

                        if (isset($specific['ValidationRules']['MinValues'])) {
                            $is_mandatory = 1;
                        } else {
                            $is_mandatory = 0;
                        }

                        if (isset($specific['ValidationRules']['MaxValues']) && $specific['ValidationRules']['MaxValues'] > 1) {
                            $multiple = 1;
                        } else {
                            $multiple = 0;
                        }

                        $value_array = array();
                        if (isset($specific['ValueRecommendation'])) {
                            if (isset($specific['ValueRecommendation']['Value'])) {
                                $value_array[] = $specific['ValueRecommendation']['Value'];
                            } else {
                                foreach ($specific['ValueRecommendation'] as $value) {
                                    $value_array[] = $value['Value'];
                                }
                            }
                        }
                        $this->db->query("INSERT INTO " . DB_PREFIX . "kb_ebay_category_specifics  set id_ebay_categories = '" . (int) $data_array['Recommendations']['CategoryID'] . "', specific_name = '" . $this->db->escape($name) . "', specific_values = '" . $this->db->escape(json_encode($value_array)) . "', multiple_allowed = '" . (int) $multiple . "', is_mandatory = '" . (int) $is_mandatory . "', site_id = '" . (int) $site_id . "', date_added = NOW()");
                    }
                }
            }
            $this->createSpecifics($id_category, $profile_id, $site_id);
        }
    }

    public function createSpecifics($category, $profile_id, $site_id)
    {
        $query_if_exists = 'SELECT * FROM ' . DB_PREFIX . 'kb_ebay_category_specifics  WHERE id_ebay_categories = ' . (int) $category . ' && site_id = ' . $site_id;
        $query = $this->db->query($query_if_exists);
        $specifics = $query->rows;
        $specs_query = 'SELECT * FROM ' . DB_PREFIX . 'kb_ebay_profile_specifics WHERE id_ebay_profiles =' . (int) $profile_id;
        $specs = $this->db->query($specs_query);
        if ($specs->num_rows > 0) {
            $specifics_data = $specs->rows;
            if (!empty($specifics_data)) {
                foreach ($specifics_data as $spe_d) {
                    if (!empty($specifics)) {
                        foreach ($specifics as &$sp_s) {
                            if ($sp_s['id_ebay_category_specifics'] == $spe_d['id_ebay_category_specifics']) {
                                if ($spe_d['attribute_mapped'] != 0) {
                                    $sp_s['data'] = $this->model_setting_kbebay->getAttributesName($spe_d['attribute_mapped']);
                                } else if ($spe_d['ebay_value_mapped'] != '') {
                                    $sp_s['data'] = $spe_d['ebay_value_mapped'];
                                } else if ($spe_d['feature_mapped'] != 0) {
                                    $sp_s['data'] = $spe_d['feature_mapped'];
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->load->model('setting/kbebay');
        $this->load->language('extension/module/kbebay');
        $id_profile = '';
        $data['product_groups'] = array(
            "1" => $this->language->get('text_specific_model'),
            "2" => $this->language->get('text_specific_sku'),
            "3" => $this->language->get('text_specific_upc'),
            "4" => $this->language->get('text_specific_ean'),
            "5" => $this->language->get('text_specific_jan'),
            "6" => $this->language->get('text_specific_isbn'),
            "7" => $this->language->get('text_specific_mpn'),
            "8" => $this->language->get('text_specific_location'),
            "9" => $this->language->get('text_specific_weight'),
            "10" => $this->language->get('text_specific_manufacturer'),
        );
        $data['attribute_groups'] = $this->model_setting_kbebay->getAttributes();
        $data['id_profile'] = $id_profile;
        $data['text_required_message'] = $this->language->get('text_required_message');
        $data['text_select_value'] = $this->language->get('text_select_value');
        $data['text_product_groups'] = $this->language->get('text_product_groups');
        $data['text_attributes'] = $this->language->get('text_attributes');
        $data['text_ebay_values'] = $this->language->get('text_ebay_values');
        $data['text_error_empty_field'] = $this->language->get('text_error_empty_field');
        $specificArray = array();
        if (!empty($specifics)) {
            foreach ($specifics as $specific) {
                $specific['specific_values'] = json_decode($specific['specific_values'], TRUE);
                $specificArray[] = $specific;
            }
        }
        $data['specifics'] = $specificArray;
        if (VERSION >= 3.0) {
            $data['action'] = $this->url->link('extension/module/kbebay/saveSpecificsData', 'user_token=' . $this->session->data['user_token'], 'SSL');
        } else {
            $data['action'] = $this->url->link('extension/module/kbebay/saveSpecificsData', 'token=' . $this->session->data['token'], 'SSL');
        }
        $data['button_save'] = $this->language->get('button_save');
        $data['id_ebay_profiles'] = $profile_id;
        if (!empty($specifics)) {
            if (VERSION >= '2.2.0.0') {
                $html = $this->response->setOutput($this->load->view('extension/module/kbebay/specifics', $data));
            } else {
                $html = $this->response->setOutput($this->load->view('extension/module/kbebay/specifics.tpl', $data));
            }
        } else {
            $html = 'error';
        }
    }

    public function getSpecifics($headers, $token, $cat_id, $sandbox)
    {
        $xmlFeed = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>
        <GetCategorySpecificsRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">
        <RequesterCredentials>
        <eBayAuthToken>" . $token . "</eBayAuthToken>
        </RequesterCredentials>
        <ErrorLanguage>en_US</ErrorLanguage>
        <WarningLevel>High</WarningLevel>
        <CategorySpecific>
            <CategoryID>" . $cat_id . "</CategoryID>
        </CategorySpecific>
        </GetCategorySpecificsRequest>";
        return $this->sendrequest($xmlFeed, $headers, $sandbox);
    }

    public function getEbayHeaders($call_name, $site_id)
    {
        $headers = array();
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

    public function getConfiguration()
    {
        $this->load->model('setting/kbebay');
        $store_id = $this->config->get('config_store_id');
        if ($store_id) {
            $store_id = $store_id;
        } else {
            $store_id = 0;
        }
        $configuration = array();
        $settings = $this->model_setting_kbebay->getSetting('ebay_general_settings', $store_id);
        if (!empty($settings)) {
            if ($settings['ebay_general_settings']['general']['account_type'] == 0) {
                $sandbox = true;
            } else {
                $sandbox = false;
            }
            $configuration['enable'] = $settings['ebay_general_settings']['general']['enable'];
            $configuration['compat_level'] = 967;
            $configuration['account_type'] = $sandbox;
            $configuration['api_endpoint'] = $sandbox ? 'https://api.sandbox.ebay.com/ws/api.dll' : 'https://api.ebay.com/ws/api.dll';
            $configuration['dev_id'] = $settings['ebay_general_settings']['general']['dev_id'];
            $configuration['app_id'] = $settings['ebay_general_settings']['general']['app_id'];
            $configuration['cert_id'] = $settings['ebay_general_settings']['general']['cert_id'];
            $configuration['ru_name'] = $settings['ebay_general_settings']['general']['ru_name'];
            $configuration['auth_token'] = $settings['ebay_general_settings']['general']['token'];
            $configuration['paypal_email'] = $settings['ebay_general_settings']['general']['paypal_email'];
            $configuration['site_id'] = 0;
        }
        return $configuration;
    }

    public function getAuthToken()
    {
        $store_id = $this->config->get('config_store_id');
        if ($store_id) {
            $store_id = $store_id;
        } else {
            $store_id = 0;
        }
        $this->load->model('setting/kbebay');
        $settings = $this->model_setting_kbebay->getSetting('ebay_general_settings', $store_id);
        return $settings['ebay_general_settings']['general']['token'];
    }

    public function sendrequest($feed, $headers, $sandbox = true)
    {
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

    public function saveProfileSpecifics($data)
    {
        $this->db->query('DELETE FROM ' . DB_PREFIX . 'kb_ebay_profile_specifics  WHERE id_ebay_profiles=' . (int) $data['profile']['specific']['id_ebay_profiles']);

        $sql = 'SELECT * FROM ' . DB_PREFIX . 'kb_ebay_profiles WHERE id_ebay_profiles = ' . (int) $data['profile']['specific']['id_ebay_profiles'];
        $query = $this->db->query($sql);
        if ($query->num_rows > 0) {
            $category = $query->row['ebay_category_id'];
            $query_if_exists = 'SELECT id_ebay_category_specifics, specific_name FROM ' . DB_PREFIX . 'kb_ebay_category_specifics  WHERE id_ebay_categories=' . (int) $category . " && site_id =" . $query->row['ebay_site'];
            $query_exists = $this->db->query($query_if_exists);
            $cat_specifics = $query_exists->rows;

            if (!empty($cat_specifics)) {
                foreach ($cat_specifics as $cat_specific) {
                    $name = $cat_specific['id_ebay_category_specifics'];
                    $value = $data[$name];
                    $value = rtrim($value, "]");
                    $attribute_val = '';
                    $feature_val = '';
                    $ebay_val = '';
                    $custom_val = '';
                    $manufacturer = '';

                    if (strpos($value, 'Ebay[') !== false) {
                        $value = str_replace('Ebay[', '', $value);
                        $attribute_val = '';
                        $feature_val = '';
                        $ebay_val = $value;
                        $custom_val = '';
                        $manufacturer = '';
                    } else if (strpos($value, 'Attributes[') !== false) {
                        $value = str_replace('Attributes[', '', $value);
                        $attribute_val = $value;
                        $feature_val = '';
                        $ebay_val = '';
                        $custom_val = '';
                        $manufacturer = '';
                    } else if (strpos($value, 'Features[') !== false) {
                        $value = str_replace('Features[', '', $value);
                        $attribute_val = '';
                        $feature_val = $value;
                        $ebay_val = '';
                        $custom_val = '';
                        $manufacturer = '';
                    } else if (strpos($value, 'Manufacturer[') !== false) {
                        $value = 1;
                        $attribute_val = '';
                        $feature_val = '';
                        $ebay_val = '';
                        $custom_val = '';
                        $manufacturer = $value;
                    } else if (strpos($value, 'custom') !== false) {
                        $value = $data['custom'][$cat_specific['id_ebay_category_specifics']];
                        $attribute_val = '';
                        $feature_val = '';
                        $ebay_val = '';
                        $custom_val = $value;
                        $manufacturer = '';
                    }
                    $this->db->query("INSERT INTO " . DB_PREFIX . "kb_ebay_profile_specifics (id_ebay_profiles,id_ebay_category_specifics,attribute_mapped,feature_mapped,ebay_value_mapped,custom_value_mapped,manufacturer) values ('" . (int) $data['profile']['specific']['id_ebay_profiles'] . "','" . (int) $cat_specific['id_ebay_category_specifics'] . "','" . (int) $attribute_val . "','" . (int) $feature_val . "','" . $this->db->escape($ebay_val) . "','" . $this->db->escape($custom_val) . "','" . (int) $manufacturer . "')");
                }
            }
        }
    }

    public function getCurrency($id)
    {
        $query_get_products = 'SELECT * FROM ' . DB_PREFIX . 'kb_ebay_sites  WHERE id_ebay_countries = ' . (int) $id . '';
        $result = $this->db->query($query_get_products);
        return $result->row;
    }

    public function getProfileSpecifics($profile_id)
    {
        $query_get_products = 'SELECT ec.*,ecs.specific_name FROM ' . DB_PREFIX . 'kb_ebay_profile_specifics ec, ' . DB_PREFIX . 'kb_ebay_category_specifics ecs WHERE  ecs.id_ebay_category_specifics = ec.id_ebay_category_specifics and id_ebay_profiles =' . (int) $profile_id;
        $result = $this->db->query($query_get_products);
        return $result->rows;
    }

    public function getAttributeValue($attribute_id)
    {
        $query_get_products = 'SELECT * FROM ' . DB_PREFIX . 'option_description ec WHERE option_id =' . (int) $attribute_id;
        $result = $this->db->query($query_get_products);
        return $result->rows;
    }

    public function getCountryId($code)
    {
        $query_get_products = 'SELECT * FROM ' . DB_PREFIX . 'country ec WHERE iso_code_2 ="' . $this->db->escape($code) . '"';
        $result = $this->db->query($query_get_products);
        return $result->rows[0]['country_id'];
    }

    public function getZoneId($code, $id_country)
    {
        $query_get_products = 'SELECT * FROM ' . DB_PREFIX . 'zone ec WHERE code ="' . $this->db->escape($code) . '" and country_id = ' . (int) $id_country;
        $result = $this->db->query($query_get_products);
        return $result->rows[0]['zone_id'];
    }

    public function getTotalProductListed($data, $product_id = '', $fields_list = '*')
    {
        if (!empty($product_id)) {
            $sql = "SELECT count(DISTINCT pl.id_product) AS total FROM " . DB_PREFIX . "kb_ebay_profile_products pl "
                    . "INNER JOIN " . DB_PREFIX . "product p ON (p.product_id = pl.id_product) "
                    . "INNER JOIN " . DB_PREFIX . "product_description pd on (pd.product_id = pl.id_product) "
                    . "INNER JOIN " . DB_PREFIX . "kb_ebay_profiles ep ON (ep.id_ebay_profiles = pl.id_ebay_profiles) "
                    . "WHERE id_product = '" . (int) $product_id . "' AND p.status = 1";
        } else {
            $sql = "SELECT count(DISTINCT pl.id_product) AS total FROM " . DB_PREFIX . "kb_ebay_profile_products pl "
                    . "INNER JOIN " . DB_PREFIX . "product p ON (p.product_id = pl.id_product) "
                    . "INNER JOIN " . DB_PREFIX . "product_description pd on (pd.product_id = pl.id_product) "
                    . "INNER JOIN " . DB_PREFIX . "kb_ebay_profiles ep ON (ep.id_ebay_profiles = pl.id_ebay_profiles) "
                    . "WHERE pl.id_product > 0 "
                    . "AND p.status = 1";
        }

        if (isset($data['filter_product_name']) && !is_null($data['filter_product_name'])) {
            $sql .= " AND pd.name LIKE '%" . $this->db->escape($data['filter_product_name']) . "%'";
        }

        if (isset($data['filter_listing_status']) && ($data['filter_listing_status'] != '0')) {
            if($data['filter_listing_status'] == 'Disabled') {
                $sql .= " AND is_disabled = '1'";
            } else {
                $sql .= " AND pl.status = '" . $this->db->escape($data['filter_listing_status']) . "'";
            }
        }

        if (isset($data['filter_ebay_profile']) && ($data['filter_ebay_profile'] != '0')) {
            $sql .= " AND pl.id_ebay_profiles = '" . $this->db->escape($data['filter_ebay_profile']) . "'";
        }

        $query = $this->db->query($sql);
        return $query->row['total'];
    }

    public function getProductListed($data, $product_id = '')
    {
        if (!empty($product_id)) {
            $sql = "SELECT pl.*, pd.name, p.image, ep.profile_name, es.site_name FROM " . DB_PREFIX . "kb_ebay_profile_products pl "
                    . "INNER JOIN " . DB_PREFIX . "kb_ebay_profiles ep ON (ep.id_ebay_profiles = pl.id_ebay_profiles) "
                    . "INNER JOIN " . DB_PREFIX . "kb_ebay_sites es ON (ep.ebay_site = es.id_ebay_countries) "
                    . "INNER JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id = pl.id_product) "
                    . "INNER JOIN " . DB_PREFIX . "product p ON (p.product_id = pl.id_product) "
                    . "WHERE id_product = '" . (int) $product_id . "' "
                    . "AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' "
                    . "AND p.status = 1";
        } else {
            $sql = "SELECT pl.*, pd.name, p.image, ep.profile_name, es.site_name FROM " . DB_PREFIX . "kb_ebay_profile_products pl "
                    . "INNER JOIN " . DB_PREFIX . "kb_ebay_profiles ep ON (ep.id_ebay_profiles = pl.id_ebay_profiles) "
                    . "INNER JOIN " . DB_PREFIX . "kb_ebay_sites es ON (ep.ebay_site = es.id_ebay_countries) "
                    . "INNER JOIN " . DB_PREFIX . "product_description pd ON (pd.product_id = pl.id_product) "
                    . "INNER JOIN " . DB_PREFIX . "product p ON (p.product_id = pl.id_product) "
                    . "WHERE pl.id_product > 0 "
                    . "AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' "
                    . "AND p.status = 1";
        }

        if (isset($data['filter_product_name']) && !is_null($data['filter_product_name'])) {
            $sql .= " AND pd.name LIKE '%" . $this->db->escape($data['filter_product_name']) . "%'";
        }

        if (isset($data['filter_listing_status']) && ($data['filter_listing_status'] != '0')) {
            if($data['filter_listing_status'] == 'Disabled') {
                $sql .= " AND is_disabled = '1'";
            } else {
                $sql .= " AND pl.status = '" . $this->db->escape($data['filter_listing_status']) . "'";
            }
        }

        if (isset($data['filter_ebay_profile']) && ($data['filter_ebay_profile'] != '0')) {
            $sql .= " AND pl.id_ebay_profiles = '" . $this->db->escape($data['filter_ebay_profile']) . "'";
        }

        $sort_data = array(
            'pl.id_ebay_profile_products',
            'pl.ebay_listiing_id',
            'pd.name',
            'pl.status',
            'pl.date_added',
            'ep.profile_name'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY id_ebay_profile_products";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getTotalOrderListed($data, $order_id = '', $fields_list = '*')
    {
        if (!empty($order_id)) {
            $sql = "SELECT count(id_ebay_orders) as total FROM " . DB_PREFIX . "kb_ebay_orders WHERE store_order_id = '" . (int) $order_id . "'  GROUP BY store_order_id";
        } else {
            $sql = "SELECT count(id_ebay_orders) as total FROM " . DB_PREFIX . "kb_ebay_orders where store_order_id > 0  GROUP BY store_order_id";
        }
        $query = $this->db->query($sql);
        if($query->num_rows > 0) {
            return $query->row['total'];
        } else {
            return 0;
        }
    }

    public function getOrderListed($data, $order_id = '', $fields_list = '*')
    {
        if (!empty($order_id)) {
            $sql = "SELECT o.order_id,eo.ebay_order_id,o.firstname,o.lastname,o.total,o.payment_method,o.date_added FROM " . DB_PREFIX . "order o inner join " . DB_PREFIX . "kb_ebay_orders eo on (o.order_id = eo.store_order_id) WHERE o.order_id = '" . (int) $order_id . "'  GROUP BY store_order_id";
        } else {
            $sql = "SELECT o.order_id,eo.ebay_order_id,o.firstname,o.lastname,o.total,o.payment_method,o.date_added FROM " . DB_PREFIX . "order o inner join " . DB_PREFIX . "kb_ebay_orders eo on (o.order_id = eo.store_order_id) where o.order_id > 0  GROUP BY store_order_id";
        }
        $sort_data = array(
            'o.order_id',
            'eo.ebay_order_id',
            'o.total',
            'o.payment_method',
            'o.date_added',
        );
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY o.order_id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getCategoryFeatures($headers, $token, $category_id, $sandbox, $feature)
    {
        $xmlFeed = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlFeed .= '<GetCategoryFeaturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $xmlFeed .= '<RequesterCredentials>' . "\n";
        $xmlFeed .= '<eBayAuthToken>' . $token . '</eBayAuthToken>' . "\n";
        $xmlFeed .= '</RequesterCredentials>' . "\n";
        $xmlFeed .= '<DetailLevel>ReturnAll</DetailLevel>' . "\n";
        $xmlFeed .= '<CategoryID>' . $category_id . '</CategoryID>' . "\n";
        $xmlFeed .= '<FeatureID>' . $feature . '</FeatureID>' . "\n";
        $xmlFeed .= '<ErrorLanguage>en_US</ErrorLanguage>' . "\n";
        $xmlFeed .= '<WarningLevel>High</WarningLevel>' . "\n";
        $xmlFeed .= '</GetCategoryFeaturesRequest>' . "\n";
        return $this->sendrequest($xmlFeed, $headers, $sandbox);
    }

    public function deleteProfile($profile_id)
    {
        //$this->db->query("DELETE FROM " . DB_PREFIX . "kb_ebay_profiles WHERE id_ebay_profiles = '" . (int) $profile_id . "'");
        //$this->db->query("DELETE FROM " . DB_PREFIX . "kb_ebay_profile_products WHERE id_ebay_profiles = '" . (int) $profile_id . "' AND status IN('New')");
        //$this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET end = '1' WHERE id_ebay_profiles = '" . (int) $profile_id . "' AND status != 'New'");
        $productCount = $this->db->query("SELECT count(*) as productcount FROM " . DB_PREFIX . "kb_ebay_profile_products WHERE id_ebay_profiles = '" . (int) $profile_id . "' AND (ebay_listiing_id != '' AND ebay_listiing_id IS NOT NULL)");
        $productCountResult = $productCount->row;
        if ($productCountResult['productcount'] > 0) {
            return false;
        } else {
            $this->db->query("DELETE FROM " . DB_PREFIX . "kb_ebay_profiles WHERE id_ebay_profiles = '" . (int) $profile_id . "'");
            $this->db->query("DELETE FROM " . DB_PREFIX . "kb_ebay_profile_products WHERE id_ebay_profiles = '" . (int) $profile_id . "'");
            return true;
        }
    }
    
    public function disableProduct($id_ebay_profile_products )
    {
        $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET is_disabled = '1' WHERE id_ebay_profile_products = '" . (int) $id_ebay_profile_products . "'");
        return true;
    }
    
    public function enableProduct($id_ebay_profile_products)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET is_disabled = '0' WHERE id_ebay_profile_products = '" . (int) $id_ebay_profile_products . "'");
        return true;
    }


    public function relistProduct($product_id, $profile_id)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET status = 'Relist', relist = '1' WHERE id_product = '" . (int) $product_id . "' AND id_ebay_profiles= '" . (int) $profile_id . "'");
    }

    public function reviseProduct($product_id, $profile_id = "")
    {
        if($profile_id != "") {
            $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET revise = '1' WHERE id_product = '" . (int) $product_id . "' AND id_ebay_profiles= '" . (int) $profile_id . "' AND status NOT IN ('New')");
            $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET status = 'Updated' WHERE id_product = '" . (int) $product_id . "' AND id_ebay_profiles= '" . (int) $profile_id . "' AND status NOT IN ('New', 'Deleted', 'Relist')");
        } else {
            $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET revise = '1' WHERE id_product = '" . (int) $product_id . "' AND status NOT IN ('New')");
            $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET status = 'Updated' WHERE id_product = '" . (int) $product_id . "' AND status NOT IN ('New', 'Deleted', 'Relist')");
        }
    }

    public function deleteListedProduct($product_id, $profile_id = "")
    {
        if($profile_id != "") {
            $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET status = 'Deleted', end = '1' WHERE id_product = '" . (int) $product_id . "' AND id_ebay_profiles= '" . (int) $profile_id . "'");
        } else {
            $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET status = 'Deleted', end = '1' WHERE id_product = '" . (int) $product_id . "'");
        }
    }

    public function checkProfileCategory($data, $site_id, $id = '')
    {
        if (isset($id) && $id != '') {
            $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_profiles WHERE ebay_site = '" . $site_id . "' AND  id_ebay_profiles != " . (int) $id . " AND (";
        } else {
            $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_profiles WHERE ebay_site = '" . $site_id . "' AND (";
        }
        if (!empty($data)) {
            $count = count($data);
            $i = 1;
            foreach ($data as $category) {
                if ($count > $i) {
                    $sql .= " FIND_IN_SET(" . $category . ", store_category_id) OR ";
                } else {
                    $sql .= " FIND_IN_SET(" . $category . ", store_category_id)";
                }
                $i++;
            }
        }
        $sql .= ")";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getProductError($product_id, $profile_id)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_errors WHERE id_product = " . (int) $product_id . " and error_type = 'Failure' and id_profile = '".$profile_id."'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function getShippingProfile($id_ebay_countries)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_shipping WHERE site_id = " . (int) $id_ebay_countries;
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getShippingProfileData($id_ebay_shipping)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_shipping WHERE id_ebay_shipping = " . (int) $id_ebay_shipping;
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function getShippingProfileList($data = array())
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_shipping ";
        if (isset($data['filter_shipping_profile_name']) && $data['filter_shipping_profile_name'] != '') {
            $sql .=" where shipping_profile_name  LIKE '%" . $data['filter_shipping_profile_name'] . "%'";
        } else {
            $sql .=" where id_ebay_shipping > 0";
        }
        if (isset($data['filter_shipping_profile_site_name']) && $data['filter_shipping_profile_site_name'] != "") {
            $sql .=" and site_id = '" . $data['filter_shipping_profile_site_name'] . "'";
        }

        if (isset($data['filter_international_shipping']) && $data['filter_international_shipping'] != "") {
            $sql .=" and international_shipping_allowed = '" . $data['filter_international_shipping'] . "'";
        }

        $sort_data = array(
            'id_ebay_shipping',
            'shipping_profile_name',
            'site_id',
            'date_upd',
            'international_shipping_allowed'
        );
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY id_ebay_shipping";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getShippingTotalProfiles($data = array())
    {
        $sql = "SELECT count(*) as total FROM " . DB_PREFIX . "kb_ebay_shipping ";
        if (isset($data['filter_shipping_profile_name']) && $data['filter_shipping_profile_name'] != '') {
            $sql .=" where shipping_profile_name  LIKE '%" . $data['filter_shipping_profile_name'] . "%'";
        } else {
            $sql .=" where id_ebay_shipping > 0";
        }
        if (isset($data['filter_shipping_profile_site_name']) && $data['filter_shipping_profile_site_name'] != "") {
            $sql .=" and site_id = '" . $data['filter_shipping_profile_site_name'] . "'";
        }

        if (isset($data['filter_international_shipping']) && $data['filter_international_shipping'] != "") {
            $sql .=" and international_shipping_allowed = '" . $data['filter_international_shipping'] . "'";
        }

        $query = $this->db->query($sql);
        return $query->row['total'];
    }

    public function getEbaySiteById($site_id)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_sites WHERE id_ebay_countries = '" . $site_id . "'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function getShippingMethodsById($site_id)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_shipping_methods WHERE site_id = '" . $site_id . "'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function saveShippingProfile($data)
    {
        //print_r($data); die;
        if (!isset($data['shipping']['package_type'])) {
            $data['shipping']['package_type'] = 'Letter';
        }
        if (!isset($data['shipping']['international_shipping'])) {
            $data['shipping']['international_shipping'] = array();
        }
        if (!isset($data['shipping']['excluded_location'])) {
            $data['shipping']['excluded_location'] = array();
        }

        if ($data['shipping']['id_ebay_shipping'] == "") {
            $this->db->query("INSERT INTO " . DB_PREFIX . "kb_ebay_shipping SET site_id = '" . (int) $data['shipping']['ebay_site'] . "',shipping_profile_name = '" . $this->db->escape($data['shipping']['shipping_profile_name']) . "', `international_shipping_allowed` = '" . (int) $data['shipping']['international_shipping_allowed'] . "', `excluded_location` = '" . json_encode($data['shipping']['excluded_location']) . "',`package_type` = '" . $data['shipping']['package_type'] . "', `postal_code` = '" . $data['shipping']['shipping_postal_code'] . "',`service_type` = '" . $data['shipping']['service_type'] . "',`date_add` = NOW(), `date_upd` = NOW(),`domestic_shipping` = '" . $this->db->escape(json_encode($data['shipping']['domestic_shipping'], true)) . "',`international_shipping` = '" . $this->db->escape(json_encode($data['shipping']['international_shipping'], true)) . "' ");
        } else {
            $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_shipping SET site_id = '" . (int) $data['shipping']['ebay_site'] . "',shipping_profile_name = '" . $this->db->escape($data['shipping']['shipping_profile_name']) . "', `international_shipping_allowed` = '" . (int) $data['shipping']['international_shipping_allowed'] . "', `excluded_location` = '" . json_encode($data['shipping']['excluded_location']) . "',`package_type` = '" . $data['shipping']['package_type'] . "', `postal_code` = '" . $data['shipping']['shipping_postal_code'] . "',`service_type` = '" . $data['shipping']['service_type'] . "',`date_add` = NOW(), `date_upd` = NOW(),`domestic_shipping` = '" . $this->db->escape(json_encode($data['shipping']['domestic_shipping'], true)) . "',`international_shipping` = '" . $this->db->escape(json_encode($data['shipping']['international_shipping'], true)) . "' WHERE id_ebay_shipping = '" . $data['shipping']['id_ebay_shipping'] . "'");
            
            /* Set Updated Flag to true for associated profile products */
            $result = $this->db->query("SELECT * FROM " . DB_PREFIX . "kb_ebay_profiles WHERE ebay_shipping_profile = '" . $data['shipping']['id_ebay_shipping'] . "'");
            if ($result->num_rows > 0) {
                foreach($result->rows as $profile) {
                    $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET revise = '1', status = 'Updated' WHERE id_ebay_profiles = " . (int) $profile['id_ebay_profiles'] . " AND status NOT IN ('New','Deleted','Relist')");
                }
            }
        }
    }

    public function getExcludedLocations($headers, $token, $detail, $sandbox)
    {
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

    public function getEbayAccount($headers, $token, $ru_name, $sandbox)
    {
        $xmlFeed = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlFeed .= '<GetSessionIDRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $xmlFeed .= '<RequesterCredentials>' . "\n";
        $xmlFeed .= '<eBayAuthToken>' . $token . '</eBayAuthToken>' . "\n";
        $xmlFeed .= '</RequesterCredentials>' . "\n";
        $xmlFeed .= '<RuName>' . $ru_name . '</RuName>' . "\n";
        $xmlFeed .= '<ErrorLanguage>en_US</ErrorLanguage>' . "\n";
        $xmlFeed .= '<WarningLevel>High</WarningLevel>' . "\n";
        $xmlFeed .= '</GetSessionIDRequest>' . "\n";
        return $this->sendrequest($xmlFeed, $headers, $sandbox);
    }

    public function getGetTaxTable($headers, $token, $sandbox)
    {
        $xmlFeed = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlFeed .= '<GetTaxTableRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $xmlFeed .= '<RequesterCredentials>' . "\n";
        $xmlFeed .= '<eBayAuthToken>' . $token . '</eBayAuthToken>' . "\n";
        $xmlFeed .= '</RequesterCredentials>' . "\n";
        $xmlFeed .= '<ErrorLanguage>en_US</ErrorLanguage>' . "\n";
        $xmlFeed .= '<DetailLevel>ReturnAll</DetailLevel>' . "\n";
        $xmlFeed .= '<WarningLevel>High</WarningLevel>' . "\n";
        $xmlFeed .= '</GetTaxTableRequest>' . "\n";
        return $this->sendrequest($xmlFeed, $headers, $sandbox);
    }

    public function getVATInfo($headers, $token, $sandbox)
    {
        $xmlFeed = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlFeed .= '<GetUserRequest xmlns="urn:ebay:apis:eBLBaseComponents">' . "\n";
        $xmlFeed .= '<RequesterCredentials>' . "\n";
        $xmlFeed .= '<eBayAuthToken>' . $token . '</eBayAuthToken>' . "\n";
        $xmlFeed .= '</RequesterCredentials>' . "\n";
        $xmlFeed .= '<ErrorLanguage>en_US</ErrorLanguage>' . "\n";
        $xmlFeed .= '<DetailLevel>ReturnAll</DetailLevel>' . "\n";
        $xmlFeed .= '<WarningLevel>High</WarningLevel>' . "\n";
        $xmlFeed .= '</GetUserRequest >' . "\n";
        return $this->sendrequest($xmlFeed, $headers, $sandbox);
    }

    public function getEbayStoreCategory($site_id)
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_store_category WHERE site_id = '" . $site_id . "'";
        //$sql = "SELECT * FROM " . DB_PREFIX . "kb_ebay_store_category";
        $query = $this->db->query($sql);
        if ($query->num_rows > 0) {
            return $query->rows;
        } else {
            return array();
        }
    }

    public function getProductByIdProfileProduct($id_ebay_profile_products)
    {
        $sql = "SELECT pl.*, pd.name, p.image, ep.ebay_site, ep.profile_name, es.site_name FROM " . DB_PREFIX . "kb_ebay_profile_products pl "
                . "left join " . DB_PREFIX . "kb_ebay_profiles ep on (ep.id_ebay_profiles = pl.id_ebay_profiles) "
                . "left join " . DB_PREFIX . "kb_ebay_sites es on (ep.ebay_site = es.id_ebay_countries) "
                . "left join " . DB_PREFIX . "product_description pd on (pd.product_id = pl.id_product) "
                . "left join " . DB_PREFIX . "product p on (p.product_id = pl.id_product) "
                . " WHERE id_ebay_profile_products = '" . (int) $id_ebay_profile_products . "'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function switchProfile($product_id, $profile_id)
    {
        /* Swtich of the profile is allowed between same country profile. If Check if profile belongs to same eBay sites */
        $product_details = $this->getProductByIdProfileProduct($product_id);
        $profile_details = $this->getProfileDetails(array(), $profile_id);
        if (!empty($product_details) && !empty($profile_details)) {
            /* If old profile of the product is same as new product then no need to update the profile; */
            if ($product_details['id_ebay_profiles'] != $profile_details[0]['id_ebay_profiles']) {
                if ($product_details['ebay_site'] == $profile_details[0]['ebay_site']) {
                    $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET id_ebay_profiles = '" . $this->db->escape($profile_id) . "' WHERE id_ebay_profile_products = " . (int) $product_id);

                    /* If product is listed then mark update flag to true; */
                    if ($product_details['ebay_listiing_id'] != "" && $product_details['status'] != "Deleted" && $product_details['relist'] != "1") {
                        $this->db->query("UPDATE " . DB_PREFIX . "kb_ebay_profile_products SET revise = '1', status = 'Updated' WHERE id_ebay_profile_products = " . (int) $product_id);
                    }
                    return false;
                } else {
                    return true;
                }
            }
        } else {
            return true;
        }
    }
}

?>