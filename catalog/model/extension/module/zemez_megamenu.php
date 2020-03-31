<?php
class ModelExtensionModuleZemezMegamenu extends Model {
	public function getModuleCode($module_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "module WHERE module_id = '" . (int)$module_id . "'");
		if ($query->row) {
			return $query->row['code'];
		} else {
			return array();
		}
	}
}