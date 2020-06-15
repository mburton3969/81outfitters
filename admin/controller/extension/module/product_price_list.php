<?php
class ControllerExtensionModuleProductPriceList extends Controller {
  
  public function index() {
		$this->load->language('extension/module/product_price_list');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/home')
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_product_price_list'),
      'href' => $this->url->link('module/product_price_list')
    );
    
    $this->load->model('extension/module/product_price_list');
    
    $pcats = $this->model_extension_module_product_price_list->getParentCategories();
    
    $data['categories'] = array();
    
    foreach ($pcats as $pcat) {
      $lvl = 0;
      $lvl1 = 0;
      $lvl2 = 0;
      $lvl3 = 0;
      $cat_info = $this->model_extension_module_product_price_list->getProductCategoryInfo($pcat['path_id']);
      //echo $cat_info['category_id'] . ' -> ' . $cat_info['name'] . '<br>';
        $data['categories'][] = array(
          'cat_id' => $cat_info['category_id'],
          'cat_name' => $cat_info['name'],
          't1_price' => $cat_info['t1_price'],
          't2_price' => $cat_info['t2_price'],
          't3_price' => $cat_info['t3_price'],
          't4_price' => $cat_info['t4_price'],
          't5_price' => $cat_info['t5_price'],
          'ebay_add' => $cat_info['ebay_add'],
          'input' => 'No',
          'level' => 0
        );
      
      $lvl++;
      $cats1 = $this->model_extension_module_product_price_list->getSubCategories($pcat['path_id'],$lvl);
      foreach ($cats1 as $cat1) {
        $cat_info = $this->model_extension_module_product_price_list->getProductCategoryInfo($cat1['category_id']);
        $input = $this->model_extension_module_product_price_list->isParent($cat1['category_id']);
        $data['categories'][] = array(
            'cat_id' => $cat_info['category_id'],
            'cat_name' => $cat_info['name'],
            't1_price' => $cat_info['t1_price'],
            't2_price' => $cat_info['t2_price'],
            't3_price' => $cat_info['t3_price'],
            't4_price' => $cat_info['t4_price'],
            't5_price' => $cat_info['t5_price'],
            'ebay_add' => $cat_info['ebay_add'],
            'input' => $input,
            'level' => 1
          );
        
        $lvl1++;
        $cats2 = $this->model_extension_module_product_price_list->getSubCategories($cat1['category_id'],$lvl);
        foreach ($cats2 as $cat2) {
          $cat_info = $this->model_extension_module_product_price_list->getProductCategoryInfo($cat2['category_id']);
          $input = $this->model_extension_module_product_price_list->isParent($cat2['category_id']);
          $data['categories'][] = array(
              'cat_id' => $cat_info['category_id'],
              'cat_name' => $cat_info['name'],
              't1_price' => $cat_info['t1_price'],
              't2_price' => $cat_info['t2_price'],
              't3_price' => $cat_info['t3_price'],
              't4_price' => $cat_info['t4_price'],
              't5_price' => $cat_info['t5_price'],
              'ebay_add' => $cat_info['ebay_add'],
              'input' => $input,
              'level' => 2
            );
          
          $lvl++;
          $cats3 = $this->model_extension_module_product_price_list->getSubCategories($cat2['category_id'],$lvl);
          foreach ($cats3 as $cat3) {
            $cat_info = $this->model_extension_module_product_price_list->getProductCategoryInfo($cat3['category_id']);
            $input = $this->model_extension_module_product_price_list->isParent($cat3['category_id']);
            $data['categories'][] = array(
                'cat_id' => $cat_info['category_id'],
                'cat_name' => $cat_info['name'],
                't1_price' => $cat_info['t1_price'],
                't2_price' => $cat_info['t2_price'],
                't3_price' => $cat_info['t3_price'],
                't4_price' => $cat_info['t4_price'],
                't5_price' => $cat_info['t5_price'],
                'ebay_add' => $cat_info['ebay_add'],
                'input' => $input,
                'level' => 3
              );

          }//End Cats3...

        }//End Cats2...
        
		  }//End Cats1...
      
    }//End Parent Cats...
    
    $url = '';
    $data['button_add'] = 'Save';
    $data['form_action'] = $this->url->link('extension/module/product_price_list/save', 'user_token=' . $this->session->data['user_token'] . $url, true);
    $data['add'] = "javascript: document.getElementById('form-product-prices').submit();";
    
    $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/product_price_list', $data));

	}
  
  
  	public function save() {
		$this->load->language('catalog/information');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module/product_price_list');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->model_extension_module_product_price_list->updatePrices($this->request->post);
      //var_dump($this->request->post);
			$this->session->data['success'] = $this->language->get('save_success_text');

			$url = '';
      
			$this->response->redirect($this->url->link('extension/module/product_price_list', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		//$this->getForm();
	}
  
}