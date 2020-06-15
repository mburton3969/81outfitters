<?php
class ControllerExtensionModuleZemezAjaxQuickView extends Controller {
	public function ajaxQuickView() {

		$this->load->language('extension/module/zemez_ajax_quick_view');

		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$product_id   = $this->request->post['product_id'];
		$image_width  = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_quickview_width');
		$image_height = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_quickview_height');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		$data['label_sale']        = $this->config->get('config_label_sale');
		$data['label_discount']    = $this->config->get('config_label_discount');
		$data['label_new']         = $this->config->get('config_label_new');

		$data['stock_status']      = $product_info['quantity'];

		if ($product_info) {
			if ($product_info['image']) {
				$image = $this->model_tool_image->resize($product_info['image'], $image_width, $image_height);
				$additional_images = $this->model_catalog_product->getProductImages($product_id);
				if ($additional_images) {
					foreach ($additional_images as $key => $additional_img) {
						$additional_thumbs[$key] = $this->model_tool_image->resize($additional_img['image'], $image_width, $image_height);
					}
				} else {
					$additional_thumbs = [];
				}
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $image_width, $image_height);
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

			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

			if ($this->config->get('config_label_new')) {
				$product_new = $this->model_catalog_product->getLatestProducts($this->config->get('config_label_new_limit'));
				$label_new = false;
				foreach ($product_new as $product_new_id => $product) {
					if ($product_new[$product_new_id]['product_id'] == $product_id) {
						$label_new = true;
						break;
					}
				}
			} else {
				$label_new = false;
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
		}

		$data['product'] = array(
			'product_id'        => $product_id,
			'name'              => $product_info['name'],
			'model'             => $product_info['model'],
			'manufacturer'      => $product_info['manufacturer'],
			'thumb'             => $image,
			'thumb_width'       => $image_width,
			'thumb_height'      => $image_height,
			'additional_thumbs' => $additional_thumbs,
			'price'             => $price,
			'tax'               => $tax,
			'rating'            => $rating,
			'special'           => $special,
			'label_new'         => $label_new,
			'label_discount'    => $label_discount,
			'options'           => $options
			);

		$this->response->setOutput($this->load->view('extension/module/zemez_ajax_quick_view_popup', $data));
	}
}