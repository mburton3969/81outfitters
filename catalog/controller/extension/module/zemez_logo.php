<?php
class ControllerExtensionModuleZemezLogo extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/zemez_logo');

		$this->load->model('tool/image');

		$data['home'] = $this->url->link('common/home');
		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), $setting['width'], $setting['height']);
		} else {
			$data['logo'] = '';
		}

		return $this->load->view('extension/module/zemez_logo', $data);
	}
}