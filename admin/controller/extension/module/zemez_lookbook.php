<?php
class ControllerExtensionModuleZemezLookbook extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/zemez_lookbook');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		$this->document->addLink('view/javascript/zemez_lookbook/zemez_lookbook.css', 'stylesheet');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('zemez_lookbook', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->cache->delete('product');

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

		if (isset($this->error['collection_name'])) {
			$data['error_collection_name'] = $this->error['collection_name'];
		} else {
			$data['error_collection_name'] = '';
		}

		if (isset($this->error['collection_image_width'])) {
			$data['error_collection_image_width'] = $this->error['collection_image_width'];
		} else {
			$data['error_collection_image_width'] = '';
		}

		if (isset($this->error['collection_image_height'])) {
			$data['error_collection_image_height'] = $this->error['collection_image_height'];
		} else {
			$data['error_collection_image_height'] = '';
		}

		if (isset($this->error['banner_image_width'])) {
			$data['error_banner_image_width'] = $this->error['banner_image_width'];
		} else {
			$data['error_banner_image_width'] = '';
		}

		if (isset($this->error['banner_image_height'])) {
			$data['error_banner_image_height'] = $this->error['banner_image_height'];
		} else {
			$data['error_banner_image_height'] = '';
		}

		if (isset($this->error['product_image_width'])) {
			$data['error_product_image_width'] = $this->error['product_image_width'];
		} else {
			$data['error_product_image_width'] = '';
		}

		if (isset($this->error['product_image_height'])) {
			$data['error_product_image_height'] = $this->error['product_image_height'];
		} else {
			$data['error_product_image_height'] = '';
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

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/zemez_lookbook', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/zemez_lookbook', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/zemez_lookbook', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/zemez_lookbook', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['collection_name'])) {
			$data['collection_name'] = $this->request->post['collection_name'];
		} elseif (!empty($module_info)) {
			$data['collection_name'] = $module_info['collection_name'];
		} else {
			$data['collection_name'] = '';
		}

		if (isset($this->request->post['display_content'])) {
			$data['display_content'] = $this->request->post['display_content'];
		} elseif (!empty($module_info)) {
			$data['display_content'] = $module_info['display_content'];
		} else {
			$data['display_content'] = '0';
		}

		if (isset($this->request->post['collection_image_width'])) {
			$data['collection_image_width'] = $this->request->post['collection_image_width'];
		} elseif (!empty($module_info)) {
			$data['collection_image_width'] = $module_info['collection_image_width'];
		} else {
			$data['collection_image_width'] = '';
		}

		if (isset($this->request->post['collection_image_height'])) {
			$data['collection_image_height'] = $this->request->post['collection_image_height'];
		} elseif (!empty($module_info)) {
			$data['collection_image_height'] = $module_info['collection_image_height'];
		} else {
			$data['collection_image_height'] = '';
		}

		if (isset($this->request->post['banner_wrapper_class'])) {
			$data['banner_wrapper_class'] = $this->request->post['banner_wrapper_class'];
		} elseif (!empty($module_info)) {
			$data['banner_wrapper_class'] = $module_info['banner_wrapper_class'];
		} else {
			$data['banner_wrapper_class'] = '';
		}

		if (isset($this->request->post['banner_image_width'])) {
			$data['banner_image_width'] = $this->request->post['banner_image_width'];
		} elseif (!empty($module_info)) {
			$data['banner_image_width'] = $module_info['banner_image_width'];
		} else {
			$data['banner_image_width'] = '';
		}

		if (isset($this->request->post['banner_image_height'])) {
			$data['banner_image_height'] = $this->request->post['banner_image_height'];
		} elseif (!empty($module_info)) {
			$data['banner_image_height'] = $module_info['banner_image_height'];
		} else {
			$data['banner_image_height'] = '';
		}

		if (isset($this->request->post['banner_description'])) {
			$data['banner_description'] = $this->request->post['banner_description'];
		} elseif (!empty($module_info)) {
			$data['banner_description'] = $module_info['banner_description'];
		} else {
			$data['banner_description'] = '';
		}

		if (isset($this->request->post['product_image_height'])) {
			$data['product_image_height'] = $this->request->post['product_image_height'];
		} elseif (!empty($module_info)) {
			$data['product_image_height'] = $module_info['product_image_height'];
		} else {
			$data['product_image_height'] = '';
		}

		if (isset($this->request->post['product_image_width'])) {
			$data['product_image_width'] = $this->request->post['product_image_width'];
		} elseif (!empty($module_info)) {
			$data['product_image_width'] = $module_info['product_image_width'];
		} else {
			$data['product_image_width'] = '';
		}

		$this->load->model('catalog/product');

		$data['products'] = array();

		if (!empty($this->request->post['product'])) {
			$products = $this->request->post['product'];
		} elseif (!empty($module_info['product'])) {
			$products = $module_info['product'];
		} else {
			$products = array();
		}

		foreach ($products as $product_id) {
			$product_info = $this->model_catalog_product->getProduct($product_id);

			if ($product_info) {
				$data['products'][] = array(
					'product_id' => $product_info['product_id'],
					'name'       => $product_info['name']
				);
			}
		}

		$this->load->model('tool/image');

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['collection_image'])) {
			$data['collection_image'] = $this->request->post['collection_image'];
		} elseif (!empty($module_info)) {
			$data['collection_image'] = $module_info['collection_image'];
		} else {
			$data['collection_image'] = '';
		}

		if (isset($this->request->post['collection_image']) && is_file(DIR_IMAGE . $this->request->post['collection_image'])) {
			$data['collection_thumb'] = $this->model_tool_image->resize($this->request->post['collection_image'], $this->request->post['collection_image_width'], $this->request->post['collection_image_height']);
			$data['collection_thumb_small'] = $this->model_tool_image->resize($this->request->post['collection_image'], 100, 100);
		} elseif (!empty($module_info['collection_image']) && is_file(DIR_IMAGE . $module_info['collection_image'])) {
			$data['collection_thumb'] = $this->model_tool_image->resize($module_info['collection_image'], $module_info['collection_image_width'], $module_info['collection_image_height']);
			$data['collection_thumb_small'] = $this->model_tool_image->resize($module_info['collection_image'], 100, 100);
		} else {
			$data['collection_thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
			$data['collection_thumb_small'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		if (isset($this->request->post['banner_image'])) {
			$data['banner_image'] = $this->request->post['banner_image'];
		} elseif (!empty($module_info)) {
			$data['banner_image'] = $module_info['banner_image'];
		} else {
			$data['banner_image'] = '';
		}

		if (isset($this->request->post['banner_image']) && is_file(DIR_IMAGE . $this->request->post['banner_image'])) {
			$data['banner_thumb'] = $this->model_tool_image->resize($this->request->post['banner_image'], 100, 100);
		} elseif (!empty($module_info['banner_image']) && is_file(DIR_IMAGE . $module_info['banner_image'])) {
			$data['banner_thumb'] = $this->model_tool_image->resize($module_info['banner_image'], 100, 100);
		} else {
			$data['banner_thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['product_map'])) {
			$data['product_map'] = $this->request->post['product_map'];
		} elseif (!empty($module_info)) {
			$data['product_map'] = $module_info['product_map'];
		} else {
			$data['product_map'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		if (!isset($this->request->get['module_id'])) {
			$data['add_image_link'] = 'index.php?route=extension/module/zemez_lookbook/addImage&user_token=' . $this->session->data['user_token'];
		} else {
			$data['add_image_link'] = 'index.php?route=extension/module/zemez_lookbook/addImage&user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'];
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/zemez_lookbook', $data));
	}

	public function addImage() {
		$this->load->model('tool/image');

		$image        = $this->request->post['image'];
		$image_width  = $this->request->post['image_width'];
		$image_heigth = $this->request->post['image_height'];

		$json = array();

		if (isset($image) && is_file(DIR_IMAGE . $image)) {
			$json['image'] = $this->model_tool_image->resize($image, $image_width, $image_heigth);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/zemez_lookbook')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ( $this->request->post['display_content'] == 0 ) {
			if ( $this->request->post['banner_image'] ) {
				if (!$this->request->post['collection_image_width']) {
					$this->error['collection_image_width'] = $this->language->get('error_collection_image_width');
				}

				if (!$this->request->post['collection_image_height']) {
					$this->error['collection_image_height'] = $this->language->get('error_collection_image_height');
				}
			}
		} else {
			if ( $this->request->post['collection_image'] ) {
				if (!$this->request->post['banner_image_width']) {
					$this->error['banner_image_width'] = $this->language->get('error_banner_image_width');
				}

				if (!$this->request->post['banner_image_height']) {
					$this->error['banner_image_height'] = $this->language->get('error_banner_image_height');
				}
			}
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ((utf8_strlen($this->request->post['collection_name']) < 3) || (utf8_strlen($this->request->post['collection_name']) > 64)) {
			$this->error['collection_name'] = $this->language->get('error_collection_name');
		}

		return !$this->error;
	}
}
