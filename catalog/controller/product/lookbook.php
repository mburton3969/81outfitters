<?php
class ControllerProductLookbook extends Controller {
	public function index() {
		$this->load->language('product/lookbook');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
			);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('product/lookbook')
			);

		$data['heading_title'] = $this->language->get('heading_title');

		$this->load->model('catalog/lookbook');
		$this->load->model('setting/module');

		$data['modules'] = [];

		$modules = $this->model_catalog_lookbook->loadModules();

		if ($modules) {
			foreach ($modules as $module) {

				$setting_info = $this->model_setting_module->getModule($module);

				if ($setting_info && $setting_info['status']) {
					$module_data = $this->load->controller('extension/module/zemez_lookbook', $setting_info);

					if ($module_data) {
						$data['modules'][] = $module_data;
					}
				}
			}
		}

		$data['text_empty'] = $this->language->get('text_empty');

		$data['button_continue'] = $this->language->get('button_continue');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('product/lookbook', $data));
	}

	public function collection() {

		$this->load->language('product/lookbook');

		$this->load->model('catalog/lookbook');
		$this->load->model('setting/module');

		$setting_info = $this->model_setting_module->getModule(isset($this->request->get['collection_id']) ? $this->request->get['collection_id'] : 0);
		$module_id    = $this->model_catalog_lookbook->getModuleId(isset($setting_info['name']) ? $setting_info['name'] : 0);

		$data['module'] = false;

		if ($setting_info && $setting_info['status'] && $module_id) {
			$module_data = $this->load->controller('extension/module/zemez_lookbook', $setting_info);

			if ($module_data) {
				$data['module'] = $module_data;
			}
		}

		$this->document->setTitle($setting_info ? $setting_info['name'] : $this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
			);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('product/lookbook')
			);

		if (!empty($setting_info) && $module_id) {
			$data['breadcrumbs'][] = array(
				'text' => $setting_info['collection_name'],
				'href' => $this->url->link('product/lookbook/collection', 'collection_id=' . $this->request->get['collection_id'])
				);
		}

		$data['heading_title']   = ($setting_info && $module_id) ? $setting_info['collection_name'] : $this->language->get('heading_title');
		
		$data['text_empty']      = $this->language->get('text_empty');
		
		$data['button_continue'] = $this->language->get('button_continue');
		
		$data['column_left']     = $this->load->controller('common/column_left');
		$data['column_right']    = $this->load->controller('common/column_right');
		$data['content_top']     = $this->load->controller('common/content_top');
		$data['content_bottom']  = $this->load->controller('common/content_bottom');
		$data['footer']          = $this->load->controller('common/footer');
		$data['header']          = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('product/lookbook_collection', $data));
	}
}
