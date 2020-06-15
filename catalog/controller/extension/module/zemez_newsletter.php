<?php

class ControllerExtensionModuleZemezNewsletter extends Controller {
	private function install()	{
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "zemez_newsletter` (
			`zemez_newsletter_id` int(11) NOT NULL AUTO_INCREMENT,
			`zemez_newsletter_email` varchar(255) NOT NULL,
			PRIMARY KEY (`zemez_newsletter_id`)
		)");
	}

	public function index($setting) {
		$this->install();
		$this->load->language('extension/module/zemez_newsletter');

		if (isset($setting['zemez_newsletter_description'][$this->config->get('config_language_id')])) {
			$data['description'] = html_entity_decode($setting['zemez_newsletter_description'][$this->config->get('config_language_id')]['description'], ENT_QUOTES, 'UTF-8');
		}

		$data['action'] = $this->url->link('extension/module/zemez_newsletter', '', true);

		return $this->load->view('extension/module/zemez_newsletter', $data);
	}

	public function addNewsletter() {
		$this->load->language('extension/module/zemez_newsletter');

		$this->load->model('account/customer');
		$this->load->model('extension/module/zemez_newsletter');

		$json        = [];
		$email       = $this->request->post['zemez_newsletter_email'];
		$email_valid = utf8_strlen($email) <= 96 && filter_var($email, FILTER_VALIDATE_EMAIL);

		if ($email_valid) {
			if ($this->customer->isLogged() && strcmp($this->customer->getEmail(), $email) == 0) {
				if ($this->customer->getNewsletter() == 0) {
					$this->model_account_customer->editNewsletter(1);
					$json['success'] = $this->language->get('text_success');
				} else {
					$json['error'] = $this->language->get('error_exist_email');
				}
			} else {
				if (count($this->model_extension_module_zemez_newsletter->getNewsletterByEmail($email)) != 0) {
					$json['error'] = $this->language->get('error_exist_email');
				} else if (count($this->model_account_customer->getCustomerByEmail($email)) == 0) {
					$this->model_extension_module_zemez_newsletter->addNewsletter($email);
					$json['success'] = $this->language->get('text_success');
				} else {
					$json['error'] = $this->language->get('error_exist_user');
				}
			}
		} else {
			$json['error'] = $this->language->get('error_invalid_email');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}