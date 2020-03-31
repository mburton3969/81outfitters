<?php
class ControllerExtensionModuleZemezColorSwitcher extends Controller {

	public function index() {
		$this->load->language('extension/module/zemez_color_switcher');

		$data['heading_title'] = $this->language->get('heading_title');

		$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/js/zemez_colorswitcher/jquery.cookies.js');

		$this->user = new Cart\User($this->registry);

		if ($this->user->hasPermission('modify', 'extension/module/zemez_color_switcher')) {
			$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/js/zemez_colorswitcher/style_switcher.js');
			$data['active_color_scheme'] = $this->config->get('module_zemez_color_switcher_scheme');
		} else {
			$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/js/zemez_colorswitcher/style_switcher_demo.js');
			if (isset($_COOKIE['module_zemez_color_switcher_scheme'])) {
				$data['active_color_scheme'] = $_COOKIE['module_zemez_color_switcher_scheme'];
			} else {
				$data['active_color_scheme'] = '';
			}
		}

		$color_schemes = glob('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/stylesheet/color_schemes/*');

		$i = 0; foreach ($color_schemes as $scheme) {
			$data['color_schemes'][$i] = array(
				'scheme' => preg_replace('/\\.[^.\\s]{3,4}$/', '', explode('color_schemes/', $scheme)[1]),
				'name'   => 'Color Scheme' . ' ' . ($i + 1)
				);
			$i++;
		}

		$data['title'] = $this->language->get('title');

		return $this->load->view('extension/module/zemez_color_switcher', $data);
	}
	public function updateSchame() {

		$this->load->model('extension/module/zemez_color_switcher');

		$json = array();
		$this->user = new Cart\User($this->registry);
		if ($this->user->hasPermission('modify', 'extension/module/zemez_color_switcher')) {
			$permission = true;
		} else {
			$permission = false;
		}

		$json['post'] = $this->request->post['module_zemez_color_switcher_scheme'];

		if ($permission) {
			$this->model_extension_module_zemez_color_switcher->editSettingValue('module_zemez_color_switcher', 'module_zemez_color_switcher_scheme', $json['post']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}