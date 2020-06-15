<?php
class ControllerExtensionModuleZemezFooterLinks extends Controller {
	public function index($setting) {
		switch ($setting['type']) {
			case '0':
			return $this->information();
			break;
			case '1':
			return $this->service();
			break;
			case '2':
			return $this->extras();
			break;
			case '3':
			return $this->account();
			break;
			case '4':
			return $this->contact();
			break;
			default:
			return false;
			break;
		}
	}

	public function information() {
		$this->load->language('extension/module/zemez_footer_links');

		$this->load->model('catalog/information');

		$data['text_information'] = $this->language->get('text_information');		
		$data['informations'] = array();

		$data['text_contacts'] = $this->language->get('text_contacts');
		$data['text_return']  = $this->language->get('text_return');

		foreach ($this->model_catalog_information->getInformations() as $result) {
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
					);
			}
		}
		
		$data['contact'] = $this->url->link('information/contact');
		$data['return'] = $this->url->link('account/return/add', '', true);
		
		if(($this->config->has('theme_' . $this->config->get('config_theme') . '_simple_blog_status')) && ($this->config->get('theme_' . $this->config->get('config_theme') . '_simple_blog_status'))) {
			$data['simple_blog_found'] = 1;
			$tmp = $this->config->get('config_simple_blog_footer_heading');
			if (!empty($tmp)) {
				$data['simple_blog_footer_heading'] = $this->config->get('config_simple_blog_footer_heading');
			} else {
				$data['simple_blog_footer_heading'] = $this->language->get('text_simple_blog');
			}
			$data['simple_blog']	= $this->url->link('simple_blog/article');
		}

		return $this->load->view('extension/module/zemez_footer_links_information', $data);
	}
	
	public function service() {
		$this->load->language('extension/module/zemez_footer_links');		
		
			

		if(($this->config->has('theme_' . $this->config->get('config_theme') . '_simple_blog_status')) && ($this->config->get('theme_' . $this->config->get('config_theme') . '_simple_blog_status'))) {
			$data['simple_blog_found'] = 1;
			$tmp = $this->config->get('config_simple_blog_footer_heading');
			if (!empty($tmp)) {
				$data['simple_blog_footer_heading'] = $this->config->get('config_simple_blog_footer_heading');
			} else {
				$data['simple_blog_footer_heading'] = $this->language->get('text_simple_blog');
			}
			$data['simple_blog']	= $this->url->link('simple_blog/article');
		}

		return $this->load->view('extension/module/zemez_footer_links_service', $data);
	}
	
	public function extras() {
		$this->load->language('common/footer');
		$data['home']            = $this->url->link('common/home');
		
		$data['name']            = $this->config->get('config_name');
		$data['powered']         = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));

		return $this->load->view('extension/module/zemez_footer_links_extras', $data);
	}
	
	public function account() {
		$this->load->language('extension/module/zemez_footer_links');
		
		$data['logged'] = $this->customer->isLogged();
		$data['login'] = $this->url->link('account/login', '', true);
		$data['account'] = $this->url->link('account/account', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);
		$data['voucher']      = $this->url->link('account/voucher', '', true);
		$data['affiliate'] = $this->url->link('affiliate/login', '', true);	
		$data['sitemap'] = $this->url->link('information/sitemap');
		
		$data['text_sitemap'] = $this->language->get('text_sitemap');
		$data['text_voucher'] = $this->language->get('text_voucher');
		$data['text_affiliate'] = $this->language->get('text_affiliate');
		$data['text_account'] = $this->language->get('text_account');
		$data['text_order'] = $this->language->get('text_order');
		$data['text_wishlist'] = $this->language->get('text_wishlist');
		$data['text_newsletter'] = $this->language->get('text_newsletter');
		$data['text_login'] = $this->language->get('text_login');

		return $this->load->view('extension/module/zemez_footer_links_account', $data);
	}

	public function contact() {
		$this->load->language('extension/module/zemez_footer_links');

		$data['address']        = nl2br($this->config->get('config_address'));
		$data['telephone']      = $this->config->get('config_telephone');
		$data['fax']            = $this->config->get('config_fax');
		$data['email']          = $this->config->get('config_email');
		$data['geocode']        = $this->config->get('config_geocode');
		$data['open']           = $this->config->get('config_open');
		
		$data['text_contact']   = $this->language->get('text_contact');
		$data['text_telephone'] = $this->language->get('text_telephone');
		$data['text_fax']       = $this->language->get('text_fax');
		$data['text_email']     = $this->language->get('text_email');

		return $this->load->view('extension/module/zemez_footer_links_contact', $data);
	}
}