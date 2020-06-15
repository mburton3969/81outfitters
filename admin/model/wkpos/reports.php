<?php
class ModelWkposReports extends Model {
    public function getProducts($data = array()) {
      $sub_query = "(SELECT SUM(quantity) AS sold_total FROM " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "wkpos_user_orders wuo ON (op.order_id = wuo.order_id) WHERE op.product_id = wp.product_id) AS sold";
      $sql = "SELECT DISTINCT wp.product_id, wp.quantity,".$sub_query.", pd.name, p.model, wo.name AS outlet, CONCAT(ws.firstname,' ',ws.lastname) AS supplier FROM " . DB_PREFIX . "wkpos_products wp LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = wp.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "wkpos_outlet wo ON (wp.outlet_id = wo.outlet_id) LEFT JOIN " . DB_PREFIX . "wkpos_supplier_product wsp ON (wp.product_id = wsp.product_id) LEFT JOIN " . DB_PREFIX . "wkpos_supplier ws ON (wsp.supplier_id = ws.supplier_id) WHERE pd.language_id = " . (int)$this->config->get('config_language_id') . " AND wp.status = '1'";

      if (!empty($data['outlet_id'])) {
        $sql .= " AND wo.outlet_id = " . (int)$data['outlet_id'] . "";
      }
      if (!empty($data['supplier_id'])) {
        $sql .= " AND wsp.supplier_id = " . (int)$data['supplier_id'] . "";
      }
      if (!empty($data['limit']) && (int)$data['limit'] > 1) {
        $limit = (int)$data['limit'];
      } else {
        $limit = 20;
      }
      if (isset($data['start']) && (int)$data['start'] > 0) {
        $start = $data['start'];
      } else {
        $start = 0;
      }

      $sql .= " ORDER BY pd.name ASC LIMIT " . (int)$start . ", " . (int)$limit . "";
      return $this->db->query($sql)->rows;
    }

    public function getTotalProducts($data = array()) {
      $sub_query = "(SELECT SUM(quantity) AS sold_total FROM " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "wkpos_user_orders wuo ON (op.order_id = wuo.order_id) WHERE op.product_id = wp.product_id) AS sold";
      $sql = "SELECT DISTINCT wp.product_id, wp.quantity,".$sub_query.", pd.name, p.model, wo.name AS outlet, CONCAT(ws.firstname,' ',ws.lastname) AS supplier FROM " . DB_PREFIX . "wkpos_products wp LEFT JOIN " . DB_PREFIX . "product p ON (p.product_id = wp.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "wkpos_outlet wo ON (wp.outlet_id = wo.outlet_id) LEFT JOIN " . DB_PREFIX . "wkpos_supplier_product wsp ON (wp.product_id = wsp.product_id) LEFT JOIN " . DB_PREFIX . "wkpos_supplier ws ON (wsp.supplier_id = ws.supplier_id) WHERE pd.language_id = " . (int)$this->config->get('config_language_id') . " AND wp.status = '1'";
      if (!empty($data['outlet_id'])) {
        $sql .= " AND wo.outlet_id = " . (int)$data['outlet_id'] . "";
      }
      if (!empty($data['supplier_id'])) {
        $sql .= " AND wsp.supplier_id = " . (int)$data['supplier_id'] . "";
      }
      return count($this->db->query($sql)->rows);
    }

    public function getSales($data = array()) {
      $sql = "SELECT DISTINCT o.order_id, wuo.*, o.total, o.payment_method, wo.name AS outlet, o.payment_code, o.currency_code, DATE(o.date_added) AS date_added, os.name AS order_status, wo.name AS outlet, CONCAT(o.firstname, ' ', o.lastname) AS customer, o.email FROM " . DB_PREFIX . "wkpos_user_orders wuo LEFT JOIN `" . DB_PREFIX . "order` o ON (wuo.order_id = o.order_id) LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) LEFT JOIN " . DB_PREFIX . "wkpos_user wu ON (wuo.user_id = wu.user_id) LEFT JOIN " . DB_PREFIX . "wkpos_outlet wo ON(wu.outlet_id = wo.outlet_id) WHERE os.order_status_id > 0 AND os.language_id = " . (int)$this->config->get('config_language_id') . "";
      if (!empty($data['user_id'])) {
        $sql .= " AND wu.user_id=".(int)$data['user_id'];
      }
      if (!empty($data['outlet_id'])) {
        $sql .= " AND wo.outlet_id=".(int)$data['outlet_id'];
      }
      if (!empty($data['payment'])) {
        $sql .= " AND o.payment_code='".$data['payment']."'";
      }
      if (!empty($data['customer_id'])) {
        $sql .= " AND o.customer_id=".(int)$data['customer_id'];
      }
      if (!empty($data['order_mode']) && $data['order_mode'] == 'offline') {
        $sql .= " AND wuo.txn_id != 0";
      }
      if (!empty($data['order_mode']) && $data['order_mode'] == 'online') {
        $sql .= " AND wuo.txn_id = 0";
      }

      if (!empty($data['date_from']) && !empty($data['date_to'])) {
         $sql .= " AND DATE(o.date_added) BETWEEN '" . date($this->db->escape($data['date_from'])) . "' AND '" . date($this->db->escape($data['date_to']))."'";
      }

      if (!empty($data['year'])) {
         $sql .= " AND YEAR(o.date_added) = ". $data['year'] ."";
         if (!empty($data['month'])) {
            $sql .= " AND MONTH(o.date_added) = '". $data['month'] ."' ";
         }
      }
      if (empty($data['year']) && !empty($data['month'])) {
        $sql .= " AND YEAR(o.date_added) = YEAR(NOW()) AND MONTH(o.date_added) = '".$data['month']."'";
      }

      if (!empty($data['limit']) && (int)$data['limit'] > 1) {
        $limit = (int)$data['limit'];
      } else {
        $limit = 20;
      }
      if (isset($data['start']) && (int)$data['start'] > 0) {
        $start = $data['start'];
      } else {
        $start = 0;
      }
      $sql .= " ORDER BY o.date_added DESC LIMIT " . (int)$start . ", " . (int)$limit . "";
      return $this->db->query($sql)->rows;
    }

    public function getTotalSales($data = array()) {
      $sql = "SELECT DISTINCT o.order_id, wuo.*, o.total, wo.name AS outlet, o.payment_method, o.currency_code, DATE(o.date_added) AS date_added, os.name AS order_status, wo.name AS outlet, CONCAT(o.firstname, ' ', o.lastname) AS customer, o.email FROM " . DB_PREFIX . "wkpos_user_orders wuo LEFT JOIN `" . DB_PREFIX . "order` o ON (wuo.order_id = o.order_id) LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) LEFT JOIN " . DB_PREFIX . "wkpos_user wu ON (wuo.user_id = wu.user_id) LEFT JOIN " . DB_PREFIX . "wkpos_outlet wo ON(wu.outlet_id = wo.outlet_id) WHERE os.order_status_id > 0 AND os.language_id = " . (int)$this->config->get('config_language_id') . "";
      if (!empty($data['user_id'])) {
        $sql .= " AND wu.user_id=".(int)$data['user_id'];
      }
      if (!empty($data['outlet_id'])) {
        $sql .= " AND wo.outlet_id=".(int)$data['outlet_id'];
      }
      if (!empty($data['payment'])) {
        $sql .= " AND o.payment_code='".$data['payment']."'";
      }
      if (!empty($data['customer_id'])) {
        $sql .= " AND o.customer_id=".(int)$data['customer_id'];
      }
      if (!empty($data['order_mode']) && $data['order_mode'] == 'offline') {
        $sql .= " AND wuo.txn_id != 0";
      }
      if (!empty($data['order_mode']) && $data['order_mode'] == 'online') {
        $sql .= " AND wuo.txn_id = 0";
      }

      if (!empty($data['date_from']) && !empty($data['date_to'])) {
         $sql .= " AND DATE(o.date_added) BETWEEN '" . date($this->db->escape($data['date_from'])) . "' AND '" . date($this->db->escape($data['date_to']))."'";
      }

      if (!empty($data['year'])) {
         $sql .= " AND YEAR(o.date_added) = ". $data['year'] ."";
         if (!empty($data['month'])) {
            $sql .= " AND MONTH(o.date_added) = '". $data['month'] ."' ";
         }
      }
      if (empty($data['year']) && !empty($data['month'])) {
        $sql .= " AND YEAR(o.date_added) = YEAR(NOW()) AND MONTH(o.date_added) = '".$data['month']."'";
      }

      return count($this->db->query($sql)->rows);
    }

    public function getOrderCustomers($data = array()) {
      $sql = "SELECT DISTINCT c.customer_id, CONCAT(c.firstname,' ', c.lastname) AS name, c.email FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "order o ON (o.customer_id = c.customer_id) ";
      if (!empty($data['filter_customer'])) {
        $sql .= "WHERE CONCAT(c.firstname,' ', c.lastname) LIKE '%". $this->db->escape($data['filter_customer']) ."%' OR c.email LIKE '%". $this->db->escape($data['filter_customer']) ."%'";
      }
      return $this->db->query($sql)->rows;
    }
}
