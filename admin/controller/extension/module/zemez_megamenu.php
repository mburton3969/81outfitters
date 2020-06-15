<?php

class ControllerExtensionModuleZemezMegaMenu extends Controller
{
	private $error = array();

	public function index()
	{
		$this->load->language('extension/module/zemez_megamenu');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');
		$this->load->model('catalog/category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('zemez_megamenu', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['columns'])) {
			$data['error_columns'] = $this->error['columns'];
		} else {
			$data['error_columns'] = '';
		}

		if (isset($this->error['columns-per-row'])) {
			$data['error_columns_per_row'] = $this->error['columns-per-row'];
		} else {
			$data['error_columns_per_row'] = '';
		}

		if (isset($this->error['image_size'])) {
			$data['error_image_size'] = $this->error['image_size'];
		} else {
			$data['error_image_size'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
			);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/zemez_megamenu', 'user_token=' . $this->session->data['user_token'], true)
			);

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/zemez_megamenu', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/zemez_megamenu', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info['status'])) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info['name'])) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		$this->load->model('localisation/language');
		$this->load->model('tool/image');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['menu_item'])) {
			$data['menu_item'] = $this->request->post['menu_item'];
		} elseif (!empty($module_info['menu_item'])) {
			$data['menu_item'] = $module_info['menu_item'];
		} else {
			$data['menu_item'] = '';
		}

		if (!empty($module_info) && isset($module_info['menu_item'])) {
			foreach ($module_info['menu_item'] as $menu_item) {
				if (is_file(DIR_IMAGE . $menu_item['image'])) {
					$image_thumb = $this->model_tool_image->resize($menu_item['image'], 100, 100);
				} else {
					$image_thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
				}
				$data['image_thumb'][] = $image_thumb;
			}
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$data['modules'] = $this->model_setting_module->getModules();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $key => $value) {
			if ($categories[$key]['parent_id'] != 0) {
				unset($categories[$key]);
			}
		}
		$data['categories']  = $categories;

		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/zemez_megamenu', $data));
	}

	protected function validate()
	{
		if (!$this->user->hasPermission('modify', 'extension/module/zemez_megamenu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (isset($this->request->post['menu_item'])) {
			foreach ($this->request->post['menu_item'] as $menu_item) {
				if (isset($menu_item['columns'])) {

					if (!is_numeric($menu_item['columns']) || $menu_item['columns'] < 0 || $menu_item['columns'] > 12) {
						$this->error['columns'] = $this->language->get('error_columns');
					}

					if (!is_numeric($menu_item['columns-per-row']) || $menu_item['columns-per-row'] < 0 || $menu_item['columns-per-row'] > 12) {
						$this->error['columns-per-row'] = $this->language->get('error_columns-per-row');
					}

					if (!empty($menu_item['image']) && (empty($menu_item['image_width']) || empty($menu_item['image_height']) || !is_numeric($menu_item['image_width']) || !is_numeric($menu_item['image_height']))) {
						$this->error['image_size'] = $this->language->get('error_image_size');
					}
				}
			}
		}
		return !$this->error;
	}
}