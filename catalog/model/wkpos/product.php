<?php
class ModelWkposProduct extends Model {
/**
 * fetches the popular products according to limit
 * @param  int $limit contains the number of products to return
 * @return array        returns the product ids of popular products
 */
	public function getPopularProducts($limit) {
		$query = $this->db->query("SELECT op.product_id, SUM(op.quantity) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "wkpos_products wp ON (wp.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND wp.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND wp.outlet_id = '" . (int)$this->session->data['wkpos_outlet'] . "' GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit);

		return $query->rows;
	}

	public function getTotalProducts($outlet_id = 0) {
		$sql = "SELECT DISTINCT count(wp.product_id) as total FROM " . DB_PREFIX . "wkpos_products wp LEFT JOIN " . DB_PREFIX . "product p ON (wp.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND wp.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND wp.outlet_id = '" . (int)$outlet_id . "'";

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
/**
 * In this function, if you pass the product id then it will return the detail of that product, otherwise, it will return all the products
 * @param  integer $product_id contains the product's id
 * @return array              contains the detail of a product or contain the products
 */
	public function getProduct($product_id = 0, $start = 0, $outlet_id = 0) {
		$sql = "SELECT pd.name AS name, p.product_id, p.points, p.subtract, p.minimum, p.status,  p.model, p.sku, p.image, p.price, p.tax_class_id, p.weight, wp.quantity, wp.unit_price_status, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, p.sort_order FROM " . DB_PREFIX . "wkpos_products wp LEFT JOIN " . DB_PREFIX . "product p ON (wp.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id)  WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND wp.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if ($outlet_id) {
			$sql .= " AND wp.outlet_id = '" . (int)$outlet_id . "'";
		}

		if ($product_id) {
			$sql .= " AND p.product_id = '" . (int)$product_id . "'";
		} else {
			$sql .= " ORDER BY p.date_added ASC";
			$sql .= " LIMIT " . $start . ", 373";
		}

		$query = $this->db->query($sql);

		if ($product_id) {
			if ($query->num_rows) {

				return array(
					'product_id'       => $query->row['product_id'],
					'name'             => $query->row['name'],
					'description'      => '',
					'model'            => $query->row['model'],
					'quantity'         => $query->row['quantity'],
					'image'            => $query->row['image'],
					'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
					'special'          => $query->row['special'],
					'reward'           => $query->row['reward'],
					'points'           => $query->row['points'],
					'tax_class_id'     => ($query->row['tax_class_id']) ? $query->row['tax_class_id'] : 0,
					'subtract'         => $query->row['subtract'],
					'minimum'          => $query->row['minimum'],
					'sort_order'       => $query->row['sort_order'],
					'status'           => $query->row['status'],
					'date_added'       => '',
					'date_modified'    => '',
					'viewed'           => '',
					'options'		   => 1,
					'group_wise_price'	=> $this->customerGroupWisePrice($query->row['product_id'])
				);
			} else {
				return false;
			}
		} else {
			if ($query->num_rows) {

				$product_data = array();

				foreach ($query->rows as $query_row_key => $query_row_value) {
					$product_data[$query_row_key] = $query_row_value;
					$product_data[$query_row_key]['group_wise_price'] = $this->customerGroupWisePrice($query_row_value['product_id']);
					$product_data[$query_row_key]['options'] = 1;
				}

				return $product_data;
			} else {
				return false;
			}
		}
	}

	public function customerGroupWisePrice($product_id) {
		$query = $this->db->query("SELECT customer_group_id, price FROM " . DB_PREFIX . "product_special where product_id = '" .(int)$product_id. "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ")->rows;

		$group_wise_price = array();

		if($query && !empty($query)) {
				foreach ($query as $query_key => $query_value) {
						$group_wise_price[$query_value['customer_group_id']] = $query_value['price'];
				}
		} else {
				$discount = $this->db->query("SELECT customer_group_id, price,quantity FROM " . DB_PREFIX . "product_discount where product_id = '" .(int)$product_id. "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity ASC")->rows;
				$i=0;
				foreach ($discount as $query_key => $query_value) {
						$group_wise_price[$i] = array('price'=> $query_value['price'],'quantity' =>$query_value['quantity'],'customer_group_id'=>$query_value['customer_group_id']);
						$i++;
				}
		}

		return $group_wise_price;
	}

/**
 * Fetches the outlets as per the user id
 * @param  integer $user_id contains the id of the cashier
 * @return integer          returns the id of the outlet
 */
	public function getOutlet($user_id) {
		$query = $this->db->query("SELECT outlet_id FROM " . DB_PREFIX . "wkpos_user WHERE user_id = '" . (int)$user_id . "'")->row;

		return $query['outlet_id'];
	}
/**
 * Returns the information on an outlet
 * @param  integer $outlet_id contains the outlet id
 * @return array            returns the information of an outlet
 */
	public function getOutletInfo($outlet_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "wkpos_outlet WHERE outlet_id = '" . (int)$outlet_id . "'")->row;

		return $query;
	}
/**
 * Returns the suppliers associated with the product
 * @param  integer $product_id contains the product's id
 * @return array             returns the array containing the suppliers information
 */
  public function getProductSuppliers($product_id) {
    $sql = "SELECT ws.supplier_id as id, CONCAT(ws.firstname, ' ', ws.lastname) as name, wsp.min_quantity as min, wsp.max_quantity as max FROM " . DB_PREFIX . "wkpos_supplier ws LEFT JOIN " . DB_PREFIX . "wkpos_supplier_product wsp ON (ws.supplier_id = wsp.supplier_id) WHERE wsp.product_id = '" . $product_id . "' AND ws.status = '1' AND wsp.status = '1'";
    $query = $this->db->query($sql);

    return $query->rows;
  }
/**
 * Returns the total number of supply requests on a particular product
 * @param  integer $product_id contains the product's id
 * @return array             returns the array containing the total count of requests
 */
	public function getTotalProductRequests($product_id) {
		$query = $this->db->query("SELECT count(*) as total FROM " . DB_PREFIX . "wkpos_request_info WHERE product_id = '" . $product_id . "' AND status = '0' AND cancel = '0'")->row;

		return $query;
	}
/**
 * Returns all the existing tax classes
 * @return array returns the array containing all tax classes
 */
	public function getTaxClasses() {
		$tax_class_data = $this->cache->get('tax_class');

		if (!$tax_class_data) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tax_class");

			$tax_class_data = $query->rows;

			$this->cache->set('tax_class', $tax_class_data);
		}

		return $tax_class_data;
	}
	public function getCategories($parent_id = 0, $start = 0) {
		$sql = "SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)";
	//	$sql .= " LIMIT " . $start . ", 100";
	  return $this->db->query($sql)->rows;

	}
}
