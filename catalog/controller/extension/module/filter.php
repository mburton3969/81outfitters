<?php
class ControllerExtensionModuleFilter extends Controller {
	public function index() {
		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		$category_id = end($parts);

		$this->load->model('catalog/category');

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if ($category_info) {
			$this->load->language('extension/module/filter');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
      

			$data['action'] = str_replace('&amp;', '&', $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url));

			if (isset($this->request->get['filter'])) {
				$data['filter_category'] = explode(',', $this->request->get['filter']);
			} else {
				$data['filter_category'] = array();
			}

			$this->load->model('catalog/product');

			$data['filter_groups'] = array();

			//$filter_groups = $this->model_catalog_category->getCategoryFilters($category_id);
      $filter_groups = $this->model_catalog_category->getCategoriesByProducts($category_id);

			if ($filter_groups) {
				foreach ($filter_groups as $filter_group) {
					$childen_data = array();

          if(!empty($this->request->get['filter'])){
            $fgs_data = $this->request->get['filter'];
          }else{
            $fgs_data = '';
          }
          
					foreach ($filter_group['filter'] as $filter) {
						$filter_data = array(
							'filter_category_id' => $category_id,
							'filter_filter'      => $filter['filter_id'],
              'other_filters'      => $fgs_data
						);
            
            //if($fgs_data != '' && $fgs_data != '0'){
              //$name_data = $filter['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getCustomTotal($filter_data) . ')' : '');
            //}else{
              //$name_data = $filter['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : '');
            //}
            $name_data = $filter['name'];
            //echo $filter['filter_id'] . ' - ' . $name_data . '<br>';
						$childen_data[] = array(
							'filter_id' => $filter['filter_id'],
							//'name'      => $filter['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
              //'name'      => $filter['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getCustomTotal($filter_data) . ')' : ''),
              'name'        => $name_data,
              'filter_cat' => $filter['filter_cat']
						);
					}

					$data['filter_groups'][] = array(
						'filter_group_id' => $filter_group['filter_group_id'],
						'name'            => $filter_group['name'],
						'filter'          => $childen_data
					);
				}

				return $this->load->view('extension/module/filter', $data);
			}
		}
	}
}