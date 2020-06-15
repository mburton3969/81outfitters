<?php

class ControllerExtensionModuleZemezSingleCategoryProduct extends Controller
{
	public function index($setting)
	{
		$this->load->language('extension/module/zemez_single_category_product');
		$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/js/zemez_single_category/bootstrap-tabcollapse.js');
		static $module = 0;
		
		$data['text_save'] = $this->language->get('text_save');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');
		$this->load->model('catalog/manufacturer');
		$this->load->language('product/product');
		$this->load->language('product/category');
		$this->load->model('catalog/review');
		$this->load->model('catalog/category');

		if ($setting['bestseller'] == "1" || $setting['special'] == "1") {
			$filter_data = array(
				'filter_category_id' => $setting['category'],
				);
				
			$results = $this->model_catalog_product->getProducts($filter_data);
		}
		$category_name = explode('&gt;', $setting['path']);
		$category_name = array_pop($category_name);
		$category_name = urlencode($category_name);
		$category_name = explode('%C2%A0%C2%A0', $category_name);	
		$category_name = urldecode(array_pop($category_name));

		$data['tabs']           = $setting['tabs'];
		$data['layout_type']    = $setting['layout_type'];
		$data['category_name']  = $category_name;
		$data['category_link']  = $this->url->link('product/category', 'path=' . $setting['category']);
		$data['label_sale']     = $this->config->get('theme_' . $this->config->get('config_theme') . '_label_sale');
		$data['label_discount'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_label_discount');
		$data['label_new']      = $this->config->get('theme_' . $this->config->get('config_theme') . '_label_new');

		if ($this->config->get('theme_' . $this->config->get('config_theme') . '_label_new')) {
			$products_new = $this->model_catalog_product->getLatestProducts($this->config->get('theme_' . $this->config->get('config_theme') . '_label_new_limit'));
		}

		//Specials
		$data['special_products'] = array();
		if (($setting['special'] == "1" && $setting['tabs'] == "1") || ($setting['type'] == "0" && $setting['tabs'] == "0")) {
			$filter_data = array(
				'sort'  => 'pd.name',
				'order' => 'ASC',
				'start' => 0,
				'limit' => 0
				);

			$specials = $this->model_catalog_product->getProductSpecials($filter_data);	
			
			$res = $results;

			foreach ($res as $key => $cat){
				$flag = false;
				foreach ($specials as $bestseller){
					if ($cat['product_id'] == $bestseller['product_id']){
						$flag = true;
					}
				}
				if (!$flag){
					unset($res[$key]);
				}
			}
			//$data['special_products'] = $this->createProducts($res, $setting, $products_new);
			$data['special_products'] = array_slice($this->createProducts($res, $setting, $products_new), 0, $setting['limit']);
		}

		//Bestsellers
		$data['bestseller_products'] = array();
		if (($setting['bestseller'] == "1" && $setting['tabs'] == "1") || ($setting['type'] == "1" && $setting['tabs'] == "0")) {

			$bestsellers = $this->model_catalog_product->getBestSellerProducts($setting['limit']);

			$res = $results;

			foreach ($res as  $key => $cat){
				$flag = false;
				foreach ($bestsellers as $bestseller){
					if ($cat['product_id'] == $bestseller['product_id']){
						$flag = true;
					}
				}
				if (!$flag){
					unset($res[$key]);
				}
			}

			//$data['bestseller_products'] = $this->createProducts($res, $setting, $products_new);
			$data['bestseller_products'] = array_slice($this->createProducts($res, $setting, $products_new), 0, $setting['limit']);
		}

		//Latest
		$data['latest_products'] = array();
		if (($setting['latest'] == "1" && $setting['tabs'] == "1") || ($setting['type'] == "2" && $setting['tabs'] == "0")) {
			$filter_data = array(
				'filter_category_id' => $setting['category'],
				'sort'               => 'p.date_added',
				'order'              => 'DESC',
				'start'              => 0,
				'limit'              => $setting['limit']
				);

			$results = $this->model_catalog_product->getProducts($filter_data);
			$data['latest_products'] = $this->createProducts($results, $setting, $products_new);
		}

		//Featured
		$data['featured_products'] = array();
		if (($setting['featured'] == "1" && $setting['tabs'] == "1") || ($setting['type'] == "3" && $setting['tabs'] == "0")) {
			if (!$setting['limit']) {
				$setting['limit'] = 4;
			}
			$products = array_slice($setting['product'], 0, (int)$setting['limit']);
			$data['featured_products'] = $this->createProducts($products, $setting, $products_new);
		}

		$data['module'] = $module++;
		return $this->load->view('extension/module/zemez_single_category_product', $data);
	}

	private function getQuickDesc($product)
	{
		$desc = html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8');
		$quick_descr_start = strpos($desc, '</iframe>') + 9;
		if ($quick_descr_start > 9) {
			return substr($desc, $quick_descr_start);
		} else {
			return $desc;
		}
	}

	private function createProducts($products, $setting, $products_new)
	{
		$productArray = array();
		if ($products) {
			if (count($products) > $setting['limit']){
				array_slice($products,0,$setting['limit']);
			}
			foreach ($products as $product_info) {
				if (!is_array($product_info)) {
					$product_info = $this->model_catalog_product->getProduct($product_info);
				}
				$review_total = $this->model_catalog_review->getTotalReviewsByProductId($product_info['product_id']);

				if ($product_info) {

					/* $label_new = 0;
					if ($this->config->get('config_label_new')) {
						foreach ($products_new as $products_new_id) {
							if ($product_info['product_id'] == $products_new_id['product_id']) {
								$label_new = 1;
								break;
							}
						}
					} */
					$label_new = 0;
					if ($this->config->get('theme_' . $this->config->get('config_theme') . '_label_new')) {
						$label_new = 0;
						foreach ($products_new as $products_new_id) {
							if ($product_info['product_id'] == $products_new_id['product_id']) {
								$label_new = 1;
								break;
							}
						}
					}

					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					$additional_image = $this->model_catalog_product->getProductImages($product_info['product_id']);
					if ($additional_image) {
						$additional_image = $this->model_tool_image->resize($additional_image[0]['image'], $setting['width'], $setting['height']);
					} else {
						$additional_image = false;
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$product_info['special']) {
						$sale = round((1-$this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'))/$this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')))*100);
					} else {
						$sale = false;
					}

					if ((float)$product_info['special']) {
						$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						$label_discount = '-' . (int)(100 - ($product_info['special'] * 100 / $product_info['price'])) . '%';
					} else {
						$special = false;
						$label_discount = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $product_info['rating'];
					} else {
						$rating = false;
					}

					$options = array();
					foreach ($this->model_catalog_product->getProductOptions($product_info['product_id']) as $option) {
						$product_option_value_data = array();
						foreach ($option['product_option_value'] as $option_value) {
							if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
								if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
									$price_option = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
								} else {
									$price_option = false;
								}
								$product_option_value_data[] = array(
									'product_option_value_id' => $option_value['product_option_value_id'],
									'option_value_id'         => $option_value['option_value_id'],
									'name'                    => $option_value['name'],
									'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
									'price'                   => $price_option,
									'price_prefix'            => $option_value['price_prefix']
									);
							}
						}
						$options[] = array(
							'product_option_id'    => $option['product_option_id'],
							'product_option_value' => $product_option_value_data,
							'option_id'            => $option['option_id'],
							'name'                 => $option['name'],
							'type'                 => $option['type'],
							'value'                => $option['value'],
							'required'             => $option['required']
							);
					}

					$quick_descr = $this->getQuickDesc($product_info);

					$productArray[] = array(
						'product_id'        => $product_info['product_id'],
						'thumb'             => $image,
						'additional_thumb'  => $additional_image,
						'img-width'         => $setting['width'],
						'img-height'        => $setting['height'],
						'name'              => $product_info['name'],
						'description'       => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'             => $price,
						'special'           => $special,
						'sale' 	            => $sale,
						'tax'               => $tax,
						'rating'            => $rating,
						'reviews'           => $review_total,
						'author'            => $product_info['manufacturer'],
						'description1'      => $quick_descr,
						'manufacturers'     => $data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']),
						'model'             => $product_info['model'],
						'text_availability' => $product_info['quantity'],
						'allow'             => $product_info['minimum'],
						'href'              => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
						'options'           => $options,
						'label_discount'    => $label_discount,
						'label_new'         => $label_new
						);
				}
			}
		}
		return $productArray;
	}
}