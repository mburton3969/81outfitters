<?php
	class ModelWkposReturn extends Model {
		public function addReturn($data) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "return` SET order_id = '" . (int)$data['order_id'] . "', customer_id = " . (int)$data['customer_id'] . ", firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', product_id = ".(int)$data['product_id'].",  product = '" . $this->db->escape($data['product']) . "', model = '" . $this->db->escape($data['model']) . "', quantity = '" . (int)$data['quantity'] . "', opened = '" . (int)$data['opened'] . "', return_reason_id = '" . (int)$data['return_reason_id'] . "', return_status_id = '" . (int)$this->config->get('config_return_status_id') . "', return_action_id = ".(int)$data['return_action_id'].", comment = '" . $this->db->escape($data['comment']) . "', date_ordered = '" . $this->db->escape($data['date_ordered']) . "', date_added = NOW(), date_modified = NOW()");
			$return_id =  $this->db->getLastId();

			$this->db->query("INSERT INTO `" . DB_PREFIX . "wkpos_return` SET return_id =" .(int)$return_id. ", product_id = ".(int)$data['product_id']."");

      // Add Credit to customer account
			if ($data['return_action_id'] == 2) {
				if ($this->db->query("SELECT * FROM " . DB_PREFIX . "wkpos_credit WHERE customer_id= ". (int)$data['customer_id'] ."")->row) {
					$this->db->query("UPDATE " . DB_PREFIX . "wkpos_credit SET amount=(amount +".(float)$data['credit_amount'].") WHERE customer_id= ". (int)$data['customer_id'] ."");
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "wkpos_credit SET customer_id=".(int)$data['customer_id'].", amount=".(float)$data['credit_amount']."");
				}
			}
		}
		public function getReturns($user_id) {
			$sql = "SELECT *, rr.name AS return_reason, ra.name AS return_action, rs.name AS return_status  FROM `" . DB_PREFIX . "wkpos_return` wr LEFT JOIN `" . DB_PREFIX . "return` r ON r.return_id = wr.return_id LEFT JOIN `" . DB_PREFIX . "wkpos_user_orders` wuo ON r.order_id = wuo.order_id LEFT JOIN " . DB_PREFIX . "return_reason rr ON r.return_reason_id = rr.return_reason_id LEFT JOIN " . DB_PREFIX . "return_status rs ON r.return_status_id = rs.return_status_id LEFT JOIN " . DB_PREFIX . "return_action ra ON r.return_action_id = ra.return_action_id WHERE wuo.user_id =".(int)$user_id." AND ra.language_id = ". (int)$this->config->get('config_language_id')." AND rs.language_id = ".(int)$this->config->get('config_language_id'). " AND rr.language_id = ".(int)$this->config->get('config_language_id'). " ORDER BY r.return_id DESC";
			return $this->db->query($sql)->rows;

		}

		public function getReturnActions() {
			return $this->db->query("SELECT * FROM " . DB_PREFIX . "return_action WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY name LIMIT 0, 20")->rows;
		}
		public function getReturnedProduct($order_id, $product_id) {
			return $this->db->query("SELECT * FROM `" . DB_PREFIX . "return` r LEFT JOIN `" . DB_PREFIX . "wkpos_return` wr ON  r.return_id = wr.return_id WHERE r.order_id = " . (int)$order_id . " AND r.product_id =" . (int)$product_id ." ")->row;
		}
	}
 ?>
