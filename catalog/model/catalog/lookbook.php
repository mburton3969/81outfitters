<?php
class ModelCatalogLookbook extends Model {
	public function loadModules() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "module WHERE code = 'zemez_lookbook'");

		if ($query->rows) {
			foreach ($query->rows as $row) {
				$module_id[] = $row['module_id'];
			}
		} else {
			$module_id = [];
		}

		return $module_id;
	}

	public function getModuleId($name) {
		$query = $this->db->query("SELECT `module_id` FROM " . DB_PREFIX . "module WHERE code = 'zemez_lookbook' AND name='" . $name . "'");

		if ($query->row) {
			return $query->row['module_id'];
		} else {
			return false;
		}
	}
}