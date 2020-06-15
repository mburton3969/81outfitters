<?php
class ModelWkposCredit extends Model {

  public function addCredit($customer_id, $description = '', $credit = '', $order_id = 0) {
    $this->db->query("INSERT INTO " . DB_PREFIX . "wkpos_credit SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$order_id . "', amount = '" . (int)$credit . "', description = '" . $this->db->escape($description) . "', date_added = NOW()");
  }

  public function getCredits($customer_id, $start, $limit) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "wkpos_credit WHERE customer_id = '" . (int)$customer_id . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
  }
  public function getTotalCredits($customer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "wkpos_credit WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row['total'];
	}

	public function getCreditTotal($customer_id) {
		$query = $this->db->query("SELECT SUM(amount) AS total FROM " . DB_PREFIX . "wkpos_credit WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row['total'];
	}

}
