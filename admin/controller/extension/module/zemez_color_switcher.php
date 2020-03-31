<?php
class ControllerExtensionModuleZemezColorSwitcher extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/zemez_color_switcher');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_setting_setting->editSetting('module_zemez_color_switcher', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
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
			'href' => $this->url->link('extension/module/zemez_color_switcher', 'user_token=' . $this->session->data['user_token'], true)
			);

		$data['action'] = $this->url->link('extension/module/zemez_color_switcher', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_zemez_color_switcher_status'])) {
			$data['module_zemez_color_switcher_status'] = $this->request->post['module_zemez_color_switcher_status'];
		} else {
			$data['module_zemez_color_switcher_status'] = $this->config->get('module_zemez_color_switcher_status');
		}

		if (isset($this->request->post['module_zemez_color_switcher_scheme'])) {
			$data['module_zemez_color_switcher_scheme'] = $this->request->post['module_zemez_color_switcher_scheme'];
		} elseif ($this->config->get('module_zemez_color_switcher_scheme')) {
			$data['module_zemez_color_switcher_scheme'] = $this->config->get('module_zemez_color_switcher_scheme');
		} else {
			$data['module_zemez_color_switcher_scheme'] = 'color_scheme_1';
		}

		$color_schemes = glob(DIR_CATALOG . 'view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/stylesheet/color_schemes/*');

		$i = 0; foreach ($color_schemes as $scheme) {
			$data['color_schemes'][$i] = array(
				'scheme' => preg_replace('/\\.[^.\\s]{3,4}$/', '', explode('color_schemes/', $scheme)[1]),
				'name' => 'Color Scheme' . ' ' . ($i + 1)
				);
			$i++;
		}

		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/zemez_color_switcher', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/zemez_color_switcher')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}