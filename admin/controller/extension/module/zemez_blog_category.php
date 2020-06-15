<?php
class ControllerExtensionModuleZemezBlogCategory extends Controller {

	private $error = array();

	public function index() {
		$url = $this->request->get['route'];
		if($this->checkDatabase()) {

			$this->load->language('simple_blog/install');

			$this->document->setTitle($this->language->get('error_database'));

			$data['install_database']     = $this->url->link('simple_blog/install/installDatabase', 'user_token=' . $this->session->data['user_token'] . '&url=' . $url, true);
			
			$data['text_install_message'] = $this->language->get('text_install_message');
			
			$data['text_upgread']         = $this->language->get('text_upgread');
			
			$data['error_database']       = $this->language->get('error_database');
			
			$data['breadcrumbs']          = array();

			$data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
				'separator' => false
				);

			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('simple_blog/notification', $data));

		} else {
			$this->getData();
		}	
	}

	public function checkDatabase() {
		$database_not_found = $this->load->controller('simple_blog/install/validateTable');

		if(!$database_not_found) {
			return true;
		}
		return false;
	}

	public function getData() {
		$this->load->language('extension/module/zemez_blog_category');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('zemez_blog_category', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['user_token'] = $this->session->data['user_token'];

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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => false
			);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => ' :: '
			);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/blog_category', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => ' :: '
			);

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/zemez_blog_category', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/zemez_blog_category', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['category_search_article'])) {
			$data['category_search_article'] = $this->request->post['category_search_article'];
		} elseif (!empty($module_info)) {
			$data['category_search_article'] = $module_info['category_search_article'];
		} else {
			$data['category_search_article'] = '';
		}

		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/zemez_blog_category', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/zemez_blog_category')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>