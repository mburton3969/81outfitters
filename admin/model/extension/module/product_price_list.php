<?php
class ModelExtensionModuleProductPriceList extends Model {
 
  public function getProductCategories($level) {
    if($level == 'ALL'){
		  $query = $this->db->query("SELECT *, FROM " . DB_PREFIX . "category WHERE 1");
    }else{
		  $query = $this->db->query("SELECT DISTINCT path_id, FROM " . DB_PREFIX . "category_path WHERE level = '" . (int)$level . "'");
    }

		return $query->rows;
	}
  
  public function getParentCategories() {
		$query = $this->db->query("SELECT DISTINCT path_id FROM " . DB_PREFIX . "category_path WHERE level = '0'");

		return $query->rows;
	}
  
  public function getProductCategoryInfo($cat_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$cat_id . "'");

		return $query->row;
	}
  
  public function getSubCategories($cat_id,$lvl) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category AS cp LEFT JOIN " . DB_PREFIX . "category_description AS cd ON cp.category_id = cd.category_id WHERE parent_id = '" . (int)$cat_id . "'");

		return $query->rows;
	}
  
  public function isParent($cat_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category WHERE parent_id = '" . (int)$cat_id . "'");
    if($query->num_rows > 0){
      return 'No';
    }else{
      return 'Yes';
    }
	}
  
  public function updatePrices($formData) {
    //phpinfo();
    //echo count($formData) . '<br>';
    //var_dump($formData);
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description");
    $cats = $query->rows;
    foreach($cats as $cat){
      $id = $cat['category_id'];
      if(isset($formData[$id.'_t1'])){
        $this->db->query("UPDATE " . DB_PREFIX . "category_description SET t1_price = '" . $this->db->escape($formData[$id.'_t1']) . "', t2_price = '" . $this->db->escape($formData[$id.'_t2']) . "', t3_price = '" . $this->db->escape($formData[$id.'_t3']) . "', t4_price = '" . $this->db->escape($formData[$id.'_t4']) . "', t5_price = '" . $this->db->escape($formData[$id.'_t5']) . "', ebay_add = '" . $this->db->escape($formData[$id.'_ebay']) . "' WHERE category_id = '" . $id . "'");
      }
    }
		return $query->rows;
	}
  
}