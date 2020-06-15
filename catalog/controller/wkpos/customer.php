<?php
class ControllerWkposCustomer extends Controller {
	public function index() {
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$ajax = true;
		} else {
			$ajax = false;
		}

		if (!$ajax) {
			$this->response->redirect($this->url->link('error/not_found', '', true));
		}

		$json['customers'] = array();
     if(isset($this->request->get['start'])) {
			 $start = $this->request->get['start'];
		 } else {
			 $start = 0;
		 }
		$this->load->model('wkpos/customer');
		$json['customers'] = array();
		$customers = $this->model_wkpos_customer->getCustomers( 0 ,$start);
		foreach ($customers as $customer)  {
			if (is_null($customer['credit_amount'])) {
				$credit_uf = 0;
				$credit_f = $this->currency->format($credit_uf, $this->session->data['currency']);
			} else {
				$credit_uf = $this->currency->convert($customer['credit_amount'], $this->config->get('config_currency'), $this->session->data['currency']);
				$credit_f = $this->currency->format($credit_uf, $this->session->data['currency']);
			}
			$json['total_customer'] = $this->model_wkpos_customer->getTotalCustomers();
			$json['customers'][] = array(
				'customer_id'			=> $customer['customer_id'],
				'customer_group_id'		=> $customer['customer_group_id'],
				'name'						=> $customer['name'],
				'email'						=> $customer['email'],
				'telephone'				=> $customer['telephone'],
				'credit_uf'				=> $credit_uf,
				'credit_f'				=> $credit_f,
			);
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function addCustomer() {
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$ajax = true;
		} else {
			$ajax = false;
		}

		if (!$ajax) {
			$this->response->redirect($this->url->link('error/not_found', '', true));
		}

		$json = array();
		$this->load->language('account/register');
		$this->load->language('account/address');
		$this->load->model('account/customer');

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$json['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$json['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen(trim($this->request->post['email'])) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$json['email'] = $this->language->get('error_email');
		}

		if ($this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
			$json['email'] = $this->language->get('error_exists');
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$json['telephone'] = $this->language->get('error_telephone');
		}

		if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
			$json['address_1'] = $this->language->get('error_address_1');
		}

		if ((utf8_strlen(trim($this->request->post['city'])) < 2) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
			$json['city'] = $this->language->get('error_city');
		}

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

		if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
			$json['postcode'] = $this->language->get('error_postcode');
		}

		if ($this->request->post['country_id'] == '') {
			$json['country'] = $this->language->get('error_country');
		}

		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
			$json['zone'] = $this->language->get('error_zone');
		}

		// Customer Group
		if (isset($this->request->post['customer_group_id']) && $this->request->post['customer_group_id']) {
			$customer_group_id = $this->request->post['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		if ($json) {
			$json['error'] = 1;
		}

		if (!$json) {
			$this->load->model('account/customer');
			$this->load->model('account/address');
			$data = array();
			$data = $this->request->post;
			$data['fax'] = '';
			$data['address_2'] = '';
			$data['company'] = '';
			$data['customer_group_id'] = $customer_group_id;
			if ($this->config->get('wkpos_customer_password')) {
				$data['password'] = $this->config->get('wkpos_customer_password');
			} else {
				$data['password'] = '1234';
			}
			if ($this->config->get('wkpos_newsletter')) {
				$data['newsletter'] = $this->config->get('wkpos_newsletter');
			} else {
				$data['newsletter'] = 0;
			}
			$json['customer_id'] = $this->model_account_customer->addCustomer($data);
			$data['default'] = true;
			$this->model_account_address->addAddress($json['customer_id'], $data);
			$json['success'] = 'Customer added successfully';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
