<?php
	class ControllerWkposReturn extends Controller {

		private $error = array();

		public function index() {
			if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				$ajax = true;
			} else {
				$ajax = false;
			}

			if (!$ajax) {
				$this->response->redirect($this->url->link('error/not_found', '', true));
			}

			$json = array();

			$this->load->model('wkpos/return');
			$this->load->model('localisation/return_reason');

			$json['return_actions'] = $this->model_wkpos_return->getReturnActions();
			$json['return_reasons'] = $this->model_localisation_return_reason->getReturnReasons();
			$json['returns'] = array();

			$results = $this->model_wkpos_return->getReturns($this->request->post['user_id']);

			foreach ($results as $return) {
				$datetime = explode(' ', ($return['date_added']));

				$json['returns'][] = array(
					'return_id' 				=> $return['return_id'],
					'product_id' 				=> $return['product_id'],
					'order_id' 					=> $return['order_id'],
					'customer_id' 			=> $return['customer_id'],
					'name' 							=> $return['firstname'] . ' ' . $return['lastname'],
					'email'			 				=> $return['email'],
					'telephone' 				=> $return['telephone'],
					'product' 					=> $return['product'],
					'model' 						=> $return['model'],
					'quantity' 					=> $return['quantity'],
					'opened' 						=> $return['opened'],
					'product_opened' 		=> $return['opened'] ? 'Yes' : 'No',
					'return_reason_id'	=> $return['return_reason_id'],
					'return_action_id'	=> $return['return_action_id'],
					'return_status_id'	=> $return['return_status_id'],
					'return_reason'			=> $return['return_reason'],
					'return_action'			=> $return['return_action'],
					'return_status'			=> $return['return_status'],
					'comment' 					=> $return['comment'],
					'date_ordered' 			=> $return['date_ordered'],
					'date_added' 				=> $datetime[0],
				);
			}
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}

		public function addReturn() {
			if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				$ajax = true;
			} else {
				$ajax = false;
			}

			if (!$ajax) {
				$this->response->redirect($this->url->link('error/not_found', '', true));
			}
			
			$json = array();
			$post = $this->request->post;
			$credit = 0;
			if (isset($post['return_order_id']) && $post['return_order_id']) {
				$this->load->language('wkpos/wkpos');
				$this->load->model('wkpos/return');
				$this->load->model('wkpos/order');
				$this->load->model('account/order');
				$order_info = $this->model_wkpos_order->getOrder($post['return_order_id']);
				$order_products = $this->model_account_order->getOrderProducts($post['return_order_id']);
				$return_data = array();
				$json['error'] = array();
				if (isset($post['product']) && $post['product']) {

					foreach ($post['return_product_id'] as $i => $value) {
						if (!isset($post['quantity'][$i]) || !$post['quantity'][$i]) {
							$json['error'][$value]['quantity'] = sprintf($this->language->get('error_return_quantity'), $post['product'][$i]);
						} else {
							foreach ($order_products as $key => $val) {
								if ($value == $val['order_product_id'] && $post['quantity'][$i] > $val['quantity']) {
									$json['error'][$value]['quantity'] = sprintf($this->language->get('error_quantity_exceed'),$post['product'][$i]);
								}
								if ($value == $val['order_product_id']) {
									$credit = (float)$val['price']*(float)$post['quantity'][$i];
								}
							}

						}
						if (!isset($post['return_reason_id'][$i]) || !$post['return_reason_id'][$i]) {
							$json['error'][$value]['reason'] = sprintf($this->language->get('error_return_reason'), $post['product'][$i]);
						}

						if (!isset($post['return_action'][$i]) || !$post['return_action'][$i]) {
							$json['error'][$value]['action'] = sprintf($this->language->get('error_return_action'), $post['product'][$i]);
						}

						if (!isset($post['opened'][$i])) {
							$json['error'][$value]['opened'] = sprintf($this->language->get('error_return_opened'), $post['product'][$i]);
						}
					}
					if (!$json['error']) {
						foreach ($post['product'] as $key => $product) {
							$return_data = array(
								'order_id' 						=> $this->request->post['return_order_id'],
								'customer_id' 				=> $order_info['customer_id'],
								'firstname'						=> $post['firstname'] ? $post['firstname'] : $order_info['firstname'],
								'lastname' 						=> $post['lastname'] ? $post['lastname'] : $order_info['lastname'],
								'email' 							=> $post['email'] ? $post['email'] : $order_info['email'],
								'telephone' 					=> isset($post['telephone']) ? $post['telephone'] : $order_info['telephone'],
								'date_ordered' 				=> $order_info['date_added'],
								'product' 						=> $product,
								'model' 							=> $post['model'][$key],
								'quantity' 						=> $post['quantity'][$key],
								'opened' 							=> $post['opened'][$key],
								'return_reason_id' 		=> $post['return_reason_id'][$key],
								'product_id'					=> $post['return_product_id'][$key],
								'comment'							=> $post['comment'],
								'return_action_id'		=> $post['return_action'][$key],
								'credit_amount'				=> $credit,
								'pos_user_id' 				=> $post['pos_user_id']
							);
							$this->model_wkpos_return->addReturn($return_data);
						}
						$json['success'] = $this->language->get('text_return_success');
					}
				}
			} else {
				$json['error']['product'] = $this->language->get('error_return_product');
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}
 ?>
