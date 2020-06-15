<?php
class ControllerWkposReturns extends Controller {
  private $error = array();
  public function index() {
    if (!$this->config->get('module_wkpos_status')) {
			$this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
		}

    $this->load->language('wkpos/returns');
    $this->load->language('sale/return');
    $this->document->setTitle($this->language->get('return_heading_title'));

    $data['breadcrumbs'] = array();
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/home', 'user_token='.$this->session->data['user_token'], true)
    );

    $this->load->model('localisation/return_status');

    $data['return_statuses'] = $this->model_localisation_return_status->getReturnStatuses();

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('return_heading_title'),
      'href' => $this->url->link('wkpos/returns', 'user_token='.$this->session->data['user_token'], true)
    );
    
    $data['limit'] = $this->config->get('config_limit_admin');
    $data['user_token'] = $this->session->data['user_token'];
    $data['header'] = $this->load->controller('common/header');
    $data['footer'] = $this->load->controller('common/footer');
    $data['column_left'] = $this->load->controller('common/column_left');
    $this->response->setOutput($this->load->view('wkpos/returns', $data));
  }
  public function loadReturns() {
    $this->load->language('wkpos/return');
    $this->load->model('wkpos/returns');
    $json = array();
    if (isset($this->request->get['filter_return_id'])) {
      $filter_return_id = $this->request->get['filter_return_id'];
    } else {
      $filter_return_id = '';
    }
    if (isset($this->request->get['filter_order_id'])) {
      $filter_order_id = $this->request->get['filter_order_id'];
    } else {
      $filter_order_id = '';
    }
    if (isset($this->request->get['filter_product'])) {
      $filter_product = $this->request->get['filter_product'];
    } else {
      $filter_product = '';
    }
    if (isset($this->request->get['filter_model'])) {
      $filter_model = $this->request->get['filter_model'];
    } else {
      $filter_model = '';
    }
    if (isset($this->request->get['filter_customer'])) {
      $filter_customer = $this->request->get['filter_customer'];
    } else {
      $filter_customer = '';
    }
    if (isset($this->request->get['filter_date_added'])) {
      $filter_date_added = $this->request->get['filter_date_added'];
    } else {
      $filter_date_added = '';
    }
    if (isset($this->request->get['filter_date_modified'])) {
      $filter_date_modified = $this->request->get['filter_date_modified'];
    } else {
      $filter_date_modified = '';
    }
    if (isset($this->request->get['filter_status'])) {
      $filter_status = $this->request->get['filter_status'];
    } else {
      $filter_status = '';
    }
    if (isset($this->request->get['sort'])) {
      $sort = $this->request->get['sort'];
    } else {
      $sort = 'r.return_id';
    }
    if (isset($this->request->get['order'])) {
      $order = $this->request->get['order'];
    } else {
      $order = 'DESC';
    }

    if (isset($this->request->post['filter_start'])) {
      $filter_start = $this->request->post['filter_start'];
    } else {
      $filter_start = '';
    }
    if (isset($this->request->post['limit'])) {
      $limit = $this->request->post['limit'];
    } else {
      $limit = $this->config->get('config_limit_admin');
    }
    $filter_data = array(
      'filter_return_id'    => $filter_return_id,
      'filter_order_id'     => $filter_order_id,
      'filter_customer'     => $filter_customer,
      'filter_product'      => $filter_product,
      'filter_model'        => $filter_model,
      'filter_date_added'   => $filter_date_added,
      'filter_date_modified'=> $filter_date_modified,
      'filter_status'       => $filter_status,
      'sort'                => $sort,
      'order'               => $order,
      'start'               => $filter_start,
      'limit'               => $limit
    );
    $results = $this->model_wkpos_returns->getPOSReturns($filter_data);
    $json['total'] = $this->model_wkpos_returns->getTotalPOSReturns($filter_data);
    $json['returns'] = array();
    $json['count'] = count($results);
    if ($results) {
      foreach ($results as $result) {
        $json['returns'][] = array(
  				'return_id'     => $result['return_id'],
  				'order_id'      => $result['order_id'],
  				'customer'      => $result['customer'],
  				'product'       => $result['product'],
  				'model'         => $result['model'],
  				'return_status' => $result['status'],
  				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
  				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
  				'edit'          => $this->url->link('sale/return/edit', 'user_token=' . $this->session->data['user_token'] . '&return_id=' . $result['return_id'], true)
  			);
      }
    }
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }
  protected function validate() {
    if (!$this->user->hasPermission('access', 'wkpos/returns')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }
    return !$this->error;
  }
}
