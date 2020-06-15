<?php
class ModelWkposOrder extends Model {
  /**
  * Fetches the last 200 orders to be shown in POS
  * @param  integer $start contains the start limit
  * @param  integer $limit contains the last limit
  * @return array         returns the array of orders
  */
  public function getOrders($user_id, $start = 0, $limit = 200) {
    if ($start < 0) {
      $start = 0;
    }

    if ($limit < 1) {
      $limit = 1;
    }

    $query = $this->db->query("SELECT o.order_id, o.firstname, o.lastname, os.name as status,o.email,o.telephone, o.date_added, o.total, o.currency_code, o.currency_value, wuo.order_note, CONCAT(wu.firstname, ' ', wu.lastname) as username FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) RIGHT JOIN " . DB_PREFIX . "wkpos_user_orders wuo ON (o.order_id = wuo.order_id) LEFT JOIN " . DB_PREFIX . "wkpos_user wu ON (wuo.user_id = wu.user_id) WHERE o.order_status_id > '0' AND o.store_id = '" . (int)$this->config->get('config_store_id') . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' AND wuo.user_id = '" . (int)$user_id . "' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit);

    return $query->rows;
  }
  /**
  * returns the details of an order
  * @param  integer $order_id contains the id of an order
  * @return array           return the details of an order
  */
  public function getOrder($order_id) {
    $order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "' AND order_status_id > '0'");

    if ($order_query->num_rows) {
      $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

      if ($country_query->num_rows) {
        $payment_iso_code_2 = $country_query->row['iso_code_2'];
        $payment_iso_code_3 = $country_query->row['iso_code_3'];
      } else {
        $payment_iso_code_2 = '';
        $payment_iso_code_3 = '';
      }

      $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

      if ($zone_query->num_rows) {
        $payment_zone_code = $zone_query->row['code'];
      } else {
        $payment_zone_code = '';
      }

      $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

      if ($country_query->num_rows) {
        $shipping_iso_code_2 = $country_query->row['iso_code_2'];
        $shipping_iso_code_3 = $country_query->row['iso_code_3'];
      } else {
        $shipping_iso_code_2 = '';
        $shipping_iso_code_3 = '';
      }

      $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

      if ($zone_query->num_rows) {
        $shipping_zone_code = $zone_query->row['code'];
      } else {
        $shipping_zone_code = '';
      }

      return array(
        'order_id'                => $order_query->row['order_id'],
        'invoice_no'              => $order_query->row['invoice_no'],
        'invoice_prefix'          => $order_query->row['invoice_prefix'],
        'store_id'                => $order_query->row['store_id'],
        'store_name'              => $order_query->row['store_name'],
        'store_url'               => $order_query->row['store_url'],
        'customer_id'             => $order_query->row['customer_id'],
        'firstname'               => $order_query->row['firstname'],
        'lastname'                => $order_query->row['lastname'],
        'telephone'               => $order_query->row['telephone'],
        'fax'                     => $order_query->row['fax'],
        'email'                   => $order_query->row['email'],
        'payment_firstname'       => $order_query->row['payment_firstname'],
        'payment_lastname'        => $order_query->row['payment_lastname'],
        'payment_company'         => $order_query->row['payment_company'],
        'payment_address_1'       => $order_query->row['payment_address_1'],
        'payment_address_2'       => $order_query->row['payment_address_2'],
        'payment_postcode'        => $order_query->row['payment_postcode'],
        'payment_city'            => $order_query->row['payment_city'],
        'payment_zone_id'         => $order_query->row['payment_zone_id'],
        'payment_zone'            => $order_query->row['payment_zone'],
        'payment_zone_code'       => $payment_zone_code,
        'payment_country_id'      => $order_query->row['payment_country_id'],
        'payment_country'         => $order_query->row['payment_country'],
        'payment_iso_code_2'      => $payment_iso_code_2,
        'payment_iso_code_3'      => $payment_iso_code_3,
        'payment_address_format'  => $order_query->row['payment_address_format'],
        'payment_method'          => $order_query->row['payment_method'],
        'shipping_firstname'      => $order_query->row['shipping_firstname'],
        'shipping_lastname'       => $order_query->row['shipping_lastname'],
        'shipping_company'        => $order_query->row['shipping_company'],
        'shipping_address_1'      => $order_query->row['shipping_address_1'],
        'shipping_address_2'      => $order_query->row['shipping_address_2'],
        'shipping_postcode'       => $order_query->row['shipping_postcode'],
        'shipping_city'           => $order_query->row['shipping_city'],
        'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
        'shipping_zone'           => $order_query->row['shipping_zone'],
        'shipping_zone_code'      => $shipping_zone_code,
        'shipping_country_id'     => $order_query->row['shipping_country_id'],
        'shipping_country'        => $order_query->row['shipping_country'],
        'shipping_iso_code_2'     => $shipping_iso_code_2,
        'shipping_iso_code_3'     => $shipping_iso_code_3,
        'shipping_address_format' => $order_query->row['shipping_address_format'],
        'shipping_method'         => $order_query->row['shipping_method'],
        'comment'                 => $order_query->row['comment'],
        'total'                   => $order_query->row['total'],
        'order_status_id'         => $order_query->row['order_status_id'],
        'language_id'             => $order_query->row['language_id'],
        'currency_id'             => $order_query->row['currency_id'],
        'currency_code'           => $order_query->row['currency_code'],
        'currency_value'          => $order_query->row['currency_value'],
        'date_modified'           => $order_query->row['date_modified'],
        'date_added'              => $order_query->row['date_added'],
        'ip'                      => $order_query->row['ip']
      );
    } else {
      return false;
    }
  }
/**
 * decreases the pos product quantity
 * @param  integer $order_id contains the order id
 * @return null           none
 */
	public function decreasePOSQuantity($order_id, $outlet_id) {
		// Stock subtraction
    if(!$this->config->get('wkpos_show_whole')) {
      $order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

  		foreach ($order_product_query->rows as $order_product) {
  			$this->db->query("UPDATE " . DB_PREFIX . "wkpos_products SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND outlet_id = '" . $outlet_id . "'");

  		}
    }
	}
  /**
  * adds the pos data for the order
  * @param array $pos_data contains the pos order data
  */
  public function addUserOrders($pos_data) {
    $user = $this->db->query("SELECT CONCAT(firstname, ' ', lastname) as name FROM " . DB_PREFIX . "wkpos_user WHERE user_id = '" . (int)$pos_data['user_id'] . "'")->row;
    $description = 'Order ID #'.$pos_data['order_id'];
    $this->db->query("INSERT INTO " . DB_PREFIX . "wkpos_user_orders SET order_id = '" . (int)$pos_data['order_id'] . "', user_id = '" . (int)$pos_data['user_id'] . "', user_name = '" . $user['name'] . "', order_note = '" . $pos_data['note'] . "', txn_id = '" . $pos_data['txn_id'] . "'");
    if (isset($pos_data['credit']) && $pos_data['credit'] > 0) {
      $this->db->query("INSERT INTO " . DB_PREFIX . "wkpos_credit SET amount=" . -(float)$pos_data['credit'] . ", customer_id=".(int)$pos_data['customer_id'] .", description = '" . $description . "', date_added = NOW()");
    }
    if (isset($pos_data['add_credit']) && $pos_data['add_credit'] > 0) {
      $this->db->query("INSERT INTO " . DB_PREFIX . "wkpos_credit SET amount=" . (float)$pos_data['add_credit'] . ", customer_id=".(int)$pos_data['customer_id'] .", description = '" . $description . "', date_added = NOW()");
    }
  }
  public function addOrderWeight($order_data,$data,$order_product_id,$order_id) {
       $record = $this->db->query("SELECT unit_price_status FROM " . DB_PREFIX . "wkpos_products WHERE product_id = '" . (int)$data['product_id'] . "'")->row;
       if(isset($record['unit_price_status']) && $record['unit_price_status']) {
         $product_weight = $this->db->query("SELECT weight FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$data['product_id'] . "'")->row;
         if(isset($data['weight_unit']) && isset($product_weight['weight'])) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "wkpos_order_product_weight SET weight_unit='" . $data['weight_unit'] . "', customer_id=". $order_data['customer_id'] .", weight = '" . ($data['weight']) . "', order_product_id = '" . (int)$order_product_id . "', order_id = ".(int)$order_id ." , product_id = ".(int)$data['product_id'] ."");
         }
       }

  }
  public  function getproductWeight($product_id, $order_id,$order_product_id) {
     $product_weight = $this->db->query("SELECT CONCAT(weight, ' ', weight_unit) as weight FROM " . DB_PREFIX . "wkpos_order_product_weight WHERE product_id = '" . (int)$product_id . "' AND order_id='" . (int)$order_id . "' AND order_product_id='" . (int)$order_product_id . "'")->row;
     if(isset($product_weight['weight'])) {
       return $product_weight['weight'];
     } else {
       return false;
     }
  }

}
