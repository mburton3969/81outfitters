<?php
class ControllerExtensionModuleZemezLookbook extends Controller {
	public function index($setting) {

		static $module = 0;

		if (isset($this->request->get['route']) && $this->request->get['route'] == 'product/lookbook/collection') {
			return $this->collection($setting, $module++);
		} elseif (isset($this->request->get['route']) && $this->request->get['route'] == 'product/lookbook') {
			return $this->banner($setting, $module++);
		} else {
			if ( $setting['display_content'] == 0 ) {
				return $this->collection($setting, $module++);
			} else {
				return $this->banner($setting, $module++);
			}
		}
	}

	public function banner($setting, $module) {

		$this->load->language('extension/module/zemez_lookbook_banner');

		$this->load->model('tool/image');
		$this->load->model('catalog/lookbook');

		$module_id = $this->model_catalog_lookbook->getModuleId($setting['name']);

		$data['collection_id']   = $this->url->link('product/lookbook/collection', 'collection_id=' . $module_id, true);

		if (is_file(DIR_IMAGE . $setting['banner_image'])) {
			$data['thumb'] = $this->model_tool_image->resize($setting['banner_image'], $setting['banner_image_width'], $setting['banner_image_height']);
		} else {
			$data['thumb'] = '';
		}
		$data['banner_wrapper_class'] = $setting['banner_wrapper_class'];
		$data['description'] = html_entity_decode($setting['banner_description'][$this->config->get('config_language_id')]['description']);
		$data['module'] = $module;
		return $this->load->view('extension/module/zemez_lookbook_banner', $data);
	}

	public function collection($setting, $module) {

		$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/js/zemez_lookbook/jquery.map-trifecta.js');

		$this->load->language('extension/module/zemez_lookbook');

		$data['label_sale']     = $this->config->get('theme_' . $this->config->get('config_theme') . '_label_sale');
		$data['label_discount'] = $this->config->get('theme_' . $this->config->get('config_theme') . '_label_discount');
		$data['label_new']      = $this->config->get('theme_' . $this->config->get('config_theme') . '_label_new');

		$this->load->model('tool/image');
		$this->load->model('catalog/product');

		$data['collections']   = $this->url->link('product/lookbook', '', true);

		if (is_file(DIR_IMAGE . $setting['collection_image'])) {
			$data['thumb'] = $this->model_tool_image->resize($setting['collection_image'], $setting['collection_image_width'], $setting['collection_image_height']);
		} else {
			$data['thumb'] = '';
		}

		$data['product_map'] = str_replace(['data-mapid="', 'name="'], ['data-mapid="' . $module . '_', 'name="' . $module . '_'], trim(html_entity_decode($setting['product_map'])));

		$products = $setting['product'];

		if (!empty($products)) {

			if ($this->config->get('theme_' . $this->config->get('config_theme') . '_label_new')) {
				$product_new = $this->model_catalog_product->getLatestProducts($this->config->get('theme_' . $this->config->get('config_theme') . '_label_new_limit'));
			}

			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($this->config->get('theme_' . $this->config->get('config_theme') . '_label_new')) {
					$label_new = 0;
					foreach ($product_new as $product_new_id => $product) {
						if ($product_new[$product_new_id]['product_id'] == $product_id) {
							$label_new = 1;
							break;
						}
					}
				}

				if ($product_info) {
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $setting['product_image_width'], $setting['product_image_height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['product_image_width'], $setting['product_image_height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
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
					foreach ($this->model_catalog_product->getProductOptions($product_id) as $option) {
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

					$data['products'][] = array(
						'product_id'     => $product_info['product_id'],
						'thumb'          => $image,
						'img-width'      => $setting['product_image_width'],
						'img-height'     => $setting['product_image_height'],
						'name'           => $product_info['name'],
						'description'    => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get($this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'          => $price,
						'special'        => $special,
						'label_discount' => $label_discount,
						'label_new'      => $this->config->get('theme_' . $this->config->get('config_theme') . '_label_new') ? $label_new : 0,
						'tax'            => $tax,
						'rating'         => $rating,
						'href'           => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
						'options'        => $options
					);
				}
			}
		}
		$data['module'] = $module;
		return $this->load->view('extension/module/zemez_lookbook_collection', $data);
	}
}