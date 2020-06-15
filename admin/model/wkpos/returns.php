<?php
class ModelWkposReturns extends Model {
  public function getPOSReturns($data = array()) {
    $sql = "SELECT *, CONCAT(r.firstname, ' ', r.lastname) AS customer, (SELECT rs.name FROM " . DB_PREFIX . "return_status rs WHERE rs.return_status_id = r.return_status_id AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "') AS status FROM `" . DB_PREFIX . "wkpos_return` wr LEFT JOIN `" . DB_PREFIX . "return` r ON (r.return_id = wr.return_id)";

    $implode = array();

    if (!empty($data['filter_return_id'])) {
      $implode[] = "r.return_id = '" . (int)$data['filter_return_id'] . "'";
    }

    if (!empty($data['filter_order_id'])) {
      $implode[] = "r.order_id = '" . (int)$data['filter_order_id'] . "'";
    }

    if (!empty($data['filter_customer'])) {
      $implode[] = "CONCAT(r.firstname, ' ', r.lastname) LIKE '" . $this->db->escape($data['filter_customer']) . "%'";
    }

    if (!empty($data['filter_product'])) {
      $implode[] = "r.product = '" . $this->db->escape($data['filter_product']) . "'";
    }

    if (!empty($data['filter_model'])) {
      $implode[] = "r.model = '" . $this->db->escape($data['filter_model']) . "'";
    }

    if (!empty($data['filter_status'])) {
      $implode[] = "r.return_status_id = '" . (int)$data['filter_status'] . "'";
    }

    if (!empty($data['filter_date_added'])) {
      $implode[] = "DATE(r.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
    }

    if (!empty($data['filter_date_modified'])) {
      $implode[] = "DATE(r.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
    }

    if ($implode) {
      $sql .= " WHERE " . implode(" AND ", $implode);
    }

    $sort_data = array(
      'r.return_id',
      'r.order_id',
      'customer',
      'r.product',
      'r.model',
      'status',
      'r.date_added',
      'r.date_modified'
    );

    if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
      $sql .= " ORDER BY " . $data['sort'];
    } else {
      $sql .= " ORDER BY r.return_id";
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

      $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
    }

    $query = $this->db->query($sql);

    return $query->rows;
  }
  public function getTotalPOSReturns($data = array()) {
    $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "wkpos_return` wr LEFT JOIN `" . DB_PREFIX . "return` r ON (r.return_id = wr.return_id)";

		$implode = array();

		if (!empty($data['filter_return_id'])) {
			$implode[] = "r.return_id = '" . (int)$data['filter_return_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$implode[] = "CONCAT(r.firstname, ' ', r.lastname) LIKE '" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_order_id'])) {
			$implode[] = "r.order_id = '" . $this->db->escape($data['filter_order_id']) . "'";
		}

		if (!empty($data['filter_product'])) {
			$implode[] = "r.product = '" . $this->db->escape($data['filter_product']) . "'";
		}

		if (!empty($data['filter_model'])) {
			$implode[] = "r.model = '" . $this->db->escape($data['filter_model']) . "'";
		}

		if (!empty($data['filter_return_status_id'])) {
			$implode[] = "r.return_status_id = '" . (int)$data['filter_return_status_id'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(r.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$implode[] = "DATE(r.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
  }
}
