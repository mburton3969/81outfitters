<?php
class ControllerExtensionModuleZemezSocialList extends Controller {
	public function index($setting) {

		$data = [
		'title' => $setting['title'][$this->config->get('config_language_id')],
		'description' => html_entity_decode($setting['description'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8'),
		'socials' => isset($setting['socials'][$this->config->get('config_language_id')]) ? $setting['socials'][$this->config->get('config_language_id')] : ''
		];

		return $this->load->view('extension/module/zemez_social_list', $data);
	}
}