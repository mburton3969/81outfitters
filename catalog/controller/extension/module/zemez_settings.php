<?php
class ControllerExtensionModuleZemezSettings extends Controller {
	public function index() {
		$this->load->language('common/header');
		
		$data['text_setting']         = $this->language->get('text_setting');
		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');

		return $this->load->view('extension/module/zemez_settings', $data);
	}
}