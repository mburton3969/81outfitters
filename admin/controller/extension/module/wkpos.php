<?php
class ControllerExtensionModuleWkpos extends Controller {
	private $error = array();

	public function install() {
		$this->load->model('wkpos/wkpos');
		$this->model_wkpos_wkpos->createTables();
	}

	public function uninstall() {
		$this->load->model('wkpos/wkpos');
		$this->model_wkpos_wkpos->deleteTables();
	}

	public function index() {
		$data = $this->load->language('extension/module/wkpos');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_wkpos', $this->request->post);
			$this->model_setting_setting->editSetting('wkpos', $this->request->post);

		if(isset($this->request->post["wkpos_head_color"]) && isset($this->request->post["wkpos_splash_bg_color"]) && isset($this->request->post["wkpos_app_icon"])) {
				$myfile = fopen(DIR_APPLICATION."../manifest.json", "w");
				$this->load->model('localisation/language');
				$data['languages'] = $this->model_localisation_language->getLanguages();
				foreach($data['languages'] as $language_data) {
					if($this->config->get('config_language') && isset($this->request->post["wkpos_app_name"][$language_data['language_id']]['value']) && $this->request->post["wkpos_short_name"][$language_data['language_id']]['value'] && $this->config->get('config_language') == $language_data['code'] ) {
						$txt = '{
							"name": "'.$this->request->post["wkpos_app_name"][$language_data['language_id']]['value'].'",
							"short_name": "'.$this->request->post["wkpos_short_name"][$language_data['language_id']]['value'].'",
							"start_url": "./wkpos",
							"display": "standalone",
							"theme_color": "'.$this->request->post["wkpos_head_color"].'",
							"gcm_sender_id": "103953800507",
							"background_color": "'.$this->request->post["wkpos_splash_bg_color"].'",
							"icons": [{
								"src": "image/'.$this->request->post["wkpos_app_icon"].'",
								"sizes": "192x192",
								"type": "image/png"
							}]
						}';
						fwrite($myfile, $txt);
						fclose($myfile);
					}
				}
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'type=module&user_token=' . $this->session->data['user_token'], true));
		}

		$error_array = array(
			'warning',
			'firstname',
			'lastname',
			'email',
			'telephone',
			'address_1',
			'city',
			'postcode',
			'country',
			'zone',
			'store_country',
			'store_zone',
			'low_stock',
			'heading',
			'sub_heading',
			'slot',
			'barcode_size'
			);

		foreach ($error_array as $error) {
			if (isset($this->error[$error])) {
				$data['error_' . $error] = $this->error[$error];
			} else {
				$data['error_' . $error] = '';
			}
		}
		$this->load->model('tool/image');
$this->document->addStyle('view/javascript/color-picker/css/bootstrap-colorpicker.min.css');
$this->document->addScript('view/javascript/color-picker/js/bootstrap-colorpicker.min.js');

if (isset($this->request->post['wkpos_app_icon']) && is_file(DIR_IMAGE . $this->request->post['wkpos_app_icon'])) {
	$data['thumb'] = $this->model_tool_image->resize($this->request->post['wkpos_app_icon'], 100, 100);
} elseif ($this->config->get('wkpos_app_icon')) {
	$data['thumb'] = $this->model_tool_image->resize($this->config->get('wkpos_app_icon'), 100, 100);
} else {
	$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
}

$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'type=module&user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/wkpos', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/wkpos', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'type=module&user_token=' . $this->session->data['user_token'], true);

		$data['front_end'] = HTTPS_CATALOG . 'wkpos';

		$data['user_token'] = $this->session->data['user_token'];

		$config_array = array(
			'show_note',
			'populars',
			'low_stock',
			'show_whole',
			'show_lowstock_prod',
			'email_agent',
			'new_customer_group_id',
			'newsletter',
			'customer_password',
			'customer_group_id',
			'firstname',
			'lastname',
			'email',
			'telephone',
			'fax',
			'company',
			'address_1',
			'address_2',
			'city',
			'postcode',
			'country_id',
			'zone_id',
			'store_country_id',
			'store_zone_id',
			'cash_status',
			'cash_order_status_id',
			'card_status',
			'card_order_status_id',
			'credit_status',
			'credit_order_status',
			'home_delivery_status',
			'home_delivery_max',
			'discount_status',
			'coupon_status',
			'tax_status',
			'store_logo',
			'store_name',
			'store_address',
			'order_date',
			'order_time',
			'order_id',
			'cashier_name',
			'customer_name',
			'shipping_mode',
			'payment_mode',
			'store_detail',
			'barcode_width',
			'barcode_name',
			'barcode_type',
			'barcode_slot',
			'generate_barcode',
			'print_size',
			'print_font_weight',
			'default_outlet',
			'splash_bg_color',
			'head_color',
			'app_icon',
			'app_name',
			'short_name',
			'pos_left_bg',
			'pos_left_color',
			'pos_top_bg',
		);

		$this->load->model('wkpos/outlet');

		$data['outlets'] = $this->model_wkpos_outlet->getOutlets();

		$data['error_heading'] = array();
		$data['error_sub_heading'] = array();
		$data['error_logcontent'] = array();
		$data['error_cash_title'] = array();
		$data['error_card_title'] = array();
		$data['error_credit_title'] = array();

		$this->load->model('localisation/language');
		$languages = $data['languages'] = $this->model_localisation_language->getLanguages();
		$this->load->model('localisation/currency');
		$currency = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));

		if ($currency['symbol_left']) {
			$data['currency'] = $currency['symbol_left'];
		} else {
			$data['currency'] = $currency['symbol_right'];
		}

		foreach ($languages as $language) {
			if (isset($this->request->post['wkpos_heading' . $language['language_id']])) {
				$data['wkpos_heading'][$language['language_id']] = $this->request->post['wkpos_heading' . $language['language_id']];
			} else {
				$data['wkpos_heading'][$language['language_id']] = $this->config->get('wkpos_heading' . $language['language_id']);
			}

			if (isset($this->request->post['wkpos_sub_heading' . $language['language_id']])) {
				$data['wkpos_sub_heading'][$language['language_id']] = $this->request->post['wkpos_sub_heading' . $language['language_id']];
			} else {
				$data['wkpos_sub_heading'][$language['language_id']] = $this->config->get('wkpos_sub_heading' . $language['language_id']);
			}

			if (isset($this->request->post['wkpos_logcontent' . $language['language_id']])) {
				$data['wkpos_logcontent'][$language['language_id']] = $this->request->post['wkpos_logcontent' . $language['language_id']];
			} else {
				$data['wkpos_logcontent'][$language['language_id']] = $this->config->get('wkpos_logcontent' . $language['language_id']);
			}

			if (isset($this->request->post['wkpos_cash_title' . $language['language_id']])) {
				$data['wkpos_cash_title'][$language['language_id']] = $this->request->post['wkpos_cash_title' . $language['language_id']];
			} else {
				$data['wkpos_cash_title'][$language['language_id']] = $this->config->get('wkpos_cash_title' . $language['language_id']);
			}
			if (isset($this->request->post['wkpos_card_title' . $language['language_id']])) {
				$data['wkpos_card_title'][$language['language_id']] = $this->request->post['wkpos_card_title' . $language['language_id']];
			} else {
				$data['wkpos_card_title'][$language['language_id']] = $this->config->get('wkpos_card_title' . $language['language_id']);
			}
			if (isset($this->request->post['wkpos_home_delivery_title' . $language['language_id']])) {
				$data['wkpos_home_delivery_title'][$language['language_id']] = $this->request->post['wkpos_home_delivery_title' . $language['language_id']];
			} else {
				$data['wkpos_home_delivery_title'][$language['language_id']] = $this->config->get('wkpos_home_delivery_title' . $language['language_id']);
			}

			if (isset($this->request->post['wkpos_credit_title' . $language['language_id']])) {
				$data['wkpos_credit_title'][$language['language_id']] = $this->request->post['wkpos_credit_title' . $language['language_id']];
			} else {
				$data['wkpos_credit_title'][$language['language_id']] = $this->config->get('wkpos_credit_title' . $language['language_id']);
			}

			if (isset($this->error['wkpos_heading'][$language['language_id']])) {
				$data['error_heading'][$language['language_id']] = $this->error['wkpos_heading'][$language['language_id']];
			} else {
				$data['error_heading'][$language['language_id']] = '';
			}

			if (isset($this->error['wkpos_sub_heading'][$language['language_id']])) {
				$data['error_sub_heading'][$language['language_id']] = $this->error['wkpos_sub_heading'][$language['language_id']];
			} else {
				$data['error_sub_heading'][$language['language_id']] = '';
			}

			if (isset($this->error['logcontent'][$language['language_id']])) {
				$data['error_logcontent'][$language['language_id']] = $this->error['logcontent'][$language['language_id']];
			} else {
				$data['error_logcontent'][$language['language_id']] = '';
			}

			if (isset($this->error['cash_title'][$language['language_id']])) {
				$data['error_cash_title'][$language['language_id']] = $this->error['cash_title'][$language['language_id']];
			} else {
				$data['error_cash_title'][$language['language_id']] = '';
			}
			if (isset($this->error['card_title'][$language['language_id']])) {
				$data['error_card_title'][$language['language_id']] = $this->error['card_title'][$language['language_id']];
			} else {
				$data['error_card_title'][$language['language_id']] = '';
			}
			if (isset($this->error['delivery_title'][$language['language_id']])) {
				$data['error_delivery_title'][$language['language_id']] = $this->error['delivery_title'][$language['language_id']];
			} else {
				$data['error_delivery_title'][$language['language_id']] = '';
			}
			if (isset($this->error['credit_title'][$language['language_id']])) {
				$data['error_credit_title'][$language['language_id']] = $this->error['credit_title'][$language['language_id']];
			} else {
				$data['error_credit_title'][$language['language_id']] = '';
			}
		}

		foreach ($config_array as $config_index) {
		if (isset($this->request->post['wkpos_' . $config_index]) && !is_array($this->request->post['wkpos_' . $config_index])) {
			$data['wkpos_' . $config_index] = is_array($this->request->post['wkpos_' . $config_index]) ? $this->request->post['wkpos_' . $config_index] : trim($this->request->post['wkpos_' . $config_index]);
		} else {
				$data['wkpos_' . $config_index] = $this->config->get('wkpos_' . $config_index);
		}
	}


		$data['pricelist_status'] = $this->config->get('module_oc_pricelist_status');
		if ($this->config->get('module_oc_pricelist_status')) {
			if (isset($this->request->post['wkpos_price_list_status'])) {
				$data['wkpos_price_list_status'] = $this->request->post['wkpos_price_list_status'];
			} else {
				$data['wkpos_price_list_status'] = $this->config->get('wkpos_price_list_status');
			}
		}

		if (isset($this->request->post['module_wkpos_status'])) {
			$data['module_wkpos_status'] = $this->request->post['module_wkpos_status'];
		} else {
			$data['module_wkpos_status'] = $this->config->get('module_wkpos_status');
		}

		$this->load->model('customer/customer_group');

		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/wkpos', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/wkpos')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->registry->set('Htmlfilter', new Htmlfilter($this->registry));
		$this->request->post['wkpos_logcontent1'] = isset($this->request->post['wkpos_logcontent1']) ?  htmlentities($this->Htmlfilter->HTMLFilter(html_entity_decode($this->request->post['wkpos_logcontent1']),'',true)) : '';

		if ((utf8_strlen(trim($this->request->post['wkpos_firstname'])) < 1) || (utf8_strlen(trim($this->request->post['wkpos_firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['wkpos_lastname'])) < 1) || (utf8_strlen(trim($this->request->post['wkpos_lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen($this->request->post['wkpos_email']) > 96) || !filter_var($this->request->post['wkpos_email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen(trim($this->request->post['wkpos_telephone'])) < 3) || (utf8_strlen($this->request->post['wkpos_telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if ((utf8_strlen(trim($this->request->post['wkpos_address_1'])) < 3) || (utf8_strlen(trim($this->request->post['wkpos_address_1'])) > 128)) {
			$this->error['address_1'] = $this->language->get('error_address_1');
		}

		if ((utf8_strlen(trim($this->request->post['wkpos_city'])) < 2) || (utf8_strlen(trim($this->request->post['wkpos_city'])) > 128)) {
			$this->error['city'] = $this->language->get('error_city');
		}

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->post['wkpos_country_id']);

		if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['wkpos_postcode'])) < 2 || utf8_strlen(trim($this->request->post['wkpos_postcode'])) > 10)) {
			$this->error['postcode'] = $this->language->get('error_postcode');
		}

		if ($this->request->post['wkpos_country_id'] == '') {
			$this->error['country'] = $this->language->get('error_country');
		}

		if (!isset($this->request->post['wkpos_zone_id']) || $this->request->post['wkpos_zone_id'] == '' || !is_numeric($this->request->post['wkpos_zone_id'])) {
			$this->error['zone'] = $this->language->get('error_zone');
		}

		if ($this->request->post['wkpos_store_country_id'] == '') {
			$this->error['store_country'] = $this->language->get('error_store_country');
		}

		if (!isset($this->request->post['wkpos_store_zone_id']) || $this->request->post['wkpos_store_zone_id'] == '' || !is_numeric($this->request->post['wkpos_store_zone_id'])) {
			$this->error['store_zone'] = $this->language->get('error_store_zone');
		}

		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();

		foreach ($languages as $language) {
			if ((utf8_strlen(trim($this->request->post['wkpos_cash_title' . $language['language_id']])) < 3) || (utf8_strlen(trim($this->request->post['wkpos_cash_title' . $language['language_id']])) > 64)) {
	      $this->error['cash_title'][$language['language_id']] = $this->language->get('error_cash_title');
			}

			if ((utf8_strlen(trim($this->request->post['wkpos_card_title' . $language['language_id']])) < 3) || (utf8_strlen(trim($this->request->post['wkpos_card_title' . $language['language_id']])) > 64)) {
	      $this->error['card_title'][$language['language_id']] = $this->language->get('error_card_title');
			}

			if ((utf8_strlen(trim($this->request->post['wkpos_home_delivery_title' . $language['language_id']])) < 3) || (utf8_strlen(trim($this->request->post['wkpos_home_delivery_title' . $language['language_id']])) > 64)) {
	      $this->error['delivery_title'][$language['language_id']] = $this->language->get('error_home_delivery_title');
			}

			if ((utf8_strlen(trim($this->request->post['wkpos_credit_title' . $language['language_id']])) < 3) || (utf8_strlen(trim($this->request->post['wkpos_credit_title' . $language['language_id']])) > 64)) {
	      $this->error['credit_title'][$language['language_id']] = $this->language->get('error_credit_title');
			}

			if ((utf8_strlen(trim($this->request->post['wkpos_heading' . $language['language_id']])) < 1) || (utf8_strlen(trim($this->request->post['wkpos_heading' . $language['language_id']])) > 64)) {
	      $this->error['wkpos_heading'][$language['language_id']] = $this->language->get('error_heading');
			}

			if (utf8_strlen(trim($this->request->post['wkpos_sub_heading' . $language['language_id']])) > 200) {
	      $this->error['wkpos_sub_heading'][$language['language_id']] = $this->language->get('error_sub_heading');
			}

			if (utf8_strlen(trim(strip_tags(html_entity_decode($this->request->post['wkpos_logcontent' . $language['language_id']], ENT_QUOTES, 'UTF-8')))) > 5000) {
				$this->error['logcontent'][$language['language_id']] = $this->language->get('error_logcontent');
			}

			if ((int)$this->request->post['wkpos_barcode_width'] > 200) {
				$this->error['barcode_size'] = $this->language->get('error_barcode_size');
			}

			$this->request->post['wkpos_cash_title' . $language['language_id']] = trim($this->request->post['wkpos_cash_title' . $language['language_id']]);
			$this->request->post['wkpos_card_title' . $language['language_id']] = trim($this->request->post['wkpos_card_title' . $language['language_id']]);
			$this->request->post['wkpos_home_delivery_title' . $language['language_id']] = trim($this->request->post['wkpos_home_delivery_title' . $language['language_id']]);
			$this->request->post['wkpos_credit_title' . $language['language_id']] = trim($this->request->post['wkpos_credit_title' . $language['language_id']]);
		}

		if ((int)$this->request->post['wkpos_populars'] < 0) {
			$this->error['warning'] = $this->language->get('populer_product_error');
		}

		if ($this->request->post['wkpos_low_stock'] < 0) {
		  $this->error['low_stock'] = $this->language->get('error_low_stock');
		}

		if (isset($this->request->post['wkpos_barcode_slot']) && (((int)$this->request->post['wkpos_barcode_slot'] < 5) || (int)$this->request->post['wkpos_barcode_slot'] > 100)) {
			$this->error['slot'] = $this->language->get('error_slot');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}
