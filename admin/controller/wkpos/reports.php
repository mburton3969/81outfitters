<?php
class ControllerWkposReports extends Controller {
  private $error = array();
  public function index() {
    if (!$this->config->get('module_wkpos_status')) {
			$this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
		}

    $data = array();
    $data = array_merge($data, $this->load->language('wkpos/reports'));
    $this->document->setTitle($this->language->get('heading_title'));
    $data['breadcrumbs'] = array();
    $data['breadcrumbs'][] = array(
      'text' => $data['text_home'],
      'href' => $this->url->link('common/home', 'user_token='.$this->session->data['user_token'], true)
    );
    $data['breadcrumbs'][] = array(
      'text' => $data['heading_title'],
      'href' => $this->url->link('wkpos/reports', 'user_token='.$this->session->data['user_token'], true)
    );
    $data['payments'] = array();
    $data['payments'][] = array(
      'text' => $this->config->get('wkpos_card_title'.$this->config->get('config_language_id')),
      'code' => 'card'
    );
    $data['payments'][] = array(
      'text' => $this->config->get('wkpos_cash_title'.$this->config->get('config_language_id')),
      'code' => 'cash'
    );

    $data['export_url'] = $this->url->link('wkpos/reports/backupSales', 'user_token='.$this->session->data['user_token'], true);
    $data['payment'] = array();
    $data['outlets'] = array();
    $this->load->model('wkpos/outlet');
    $this->load->model('wkpos/supplier');
    $this->load->model('wkpos/user');
    if (isset($this->session->data['error_data'])) {
      $data['error_data'] = $this->language->get('error_data');
      unset($this->session->data['error_data']);
    } else {
      $data['error_data'] = '';
    }
    $data['outlets'] = $this->model_wkpos_outlet->getOutlets();
    $data['users'] = $this->model_wkpos_user->getUsers();
    $data['suppliers'] = $this->model_wkpos_supplier->getSuppliers();
    $data['user_token'] = $this->session->data['user_token'];
    $data['limit'] = $this->config->get('config_limit_admin');
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('wkpos/reports', $data));
  }
  
  public function productReport() {
    $json = array();
    if ($this->validateAccess()) {
        $get = $this->request->get;
        $page = isset($get['page']) ? $get['page'] : 1;
        $this->load->model('wkpos/reports');
        $filter = array(
          'supplier_id' => isset($get['filter_supplier']) ? $get['filter_supplier'] : '',
          'outlet_id'   => isset($get['filter_outlet']) ? $get['filter_outlet'] : '',
          'start'       => ($page - 1) * $this->config->get('config_limit_admin'),
    			'limit'       => $this->config->get('config_limit_admin')
        );
        $json['total'] = $this->model_wkpos_reports->getTotalProducts($filter);
        $json['products'] = $this->model_wkpos_reports->getProducts($filter);
        foreach ($json['products'] as $key => $product) {
          if ($product['sold']) {
            $json['products'][$key]['remaining'] = ((int)$product['quantity'] - (int)$product['sold']) > 0 ? (int)$product['quantity'] - (int)$product['sold'] : 0;
          } else {
            $json['products'][$key]['remaining'] = $json['products'][$key]['quantity'];
            $json['products'][$key]['sold'] = 0;
          }
        }
        $json['success'] = true;
        $json['error_permission'] = false;
    } else if (isset($this->error['permission'])){
      $json['error_permission'] = $this->error['permission'];
    }
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }
  public function salesReport() {
    $json = array();
    if ($this->validateAccess()) {
      $get = $this->request->get;
      $page = isset($get['page']) ? $get['page'] : 1;
      $this->load->model('wkpos/reports');
      $filter = array(
        'user_id'     => isset($get['filter_user']) ? $get['filter_user'] : '',
        'outlet_id'   => isset($get['filter_outlet']) ? $get['filter_outlet'] : '',
        'payment'     => isset($get['filter_payment']) ? $get['filter_payment'] : '',
        'order_mode'  => isset($get['filter_mode']) ? $get['filter_mode'] : '',
        'date_from'   => isset($get['filter_date_from']) ? $get['filter_date_from'] : '',
        'date_to'     => isset($get['filter_date_to']) ? $get['filter_date_to'] : '',
        'year'        => isset($get['filter_year']) ? $get['filter_year'] : '',
        'month'       => isset($get['filter_month']) ? $get['filter_month'] : '',
        'customer_id' => isset($get['filter_customer']) ? $get['filter_customer'] : '',
        'start'       => ($page - 1) * $this->config->get('config_limit_admin'),
        'limit'       => $this->config->get('config_limit_admin')
      );
    }
    $json['sales'] = $this->model_wkpos_reports->getSales($filter);
    $json['total'] = $this->model_wkpos_reports->getTotalSales($filter);
    foreach ($json['sales'] as $key => $total) {
      $json['sales'][$key]['total'] = $this->currency->format($total['total'], $total['currency_code']);
    }
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }
  public function customerAutocomplete() {
    $json = array();
    if ($this->validateAccess() && isset($this->request->get['filter_customer']) && strlen($this->request->get['filter_customer']) >= 2) {
      $this->load->model('wkpos/reports');
      $filter = array(
        'filter_customer' => $this->request->get['filter_customer'],
      );
      $json= $this->model_wkpos_reports->getOrderCustomers($filter);
    }
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }
  protected function validateAccess() {
    $this->load->language('wkpos/reports');
    if (!$this->user->hasPermission('access', 'wkpos/reports')) {
      $this->error['permission'] = $this->language->get('error_permission');
    }
    return !$this->error;
  }
}
