<?php
class ControllerWkposSupplier extends Controller {
  public function index() {
    if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$ajax = true;
		} else {
			$ajax = false;
		}

		if (!$ajax) {
			$this->response->redirect($this->url->link('error/not_found', '', true));
		}
  }

	public function addRequest() {
		$json = array();
    if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$ajax = true;
		} else {
			$ajax = false;
		}

		if (!$ajax) {
			$this->response->redirect($this->url->link('error/not_found', '', true));
		}
    
		$this->load->model('wkpos/wkpos');
		$this->load->model('wkpos/supplier');
		$this->load->language('wkpos/wkpos');

		if (isset($this->request->post['request_data']) && $this->request->post['request_data'] && $this->load->controller('wkpos/wkpos/checkUserLogin')) {
			$this->model_wkpos_supplier->addSupplyRequest($this->request->post['request_data'], $this->request->post['comment']);

			$json['success'] = $this->language->get('text_supply_success');
		} else {
			$json['error'] = $this->language->get('error_supply');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getRequestHistory() {
		$json = array();

		$this->load->model('wkpos/supplier');

		$requests = $this->model_wkpos_supplier->getRequestHistory();

		$json['requests'] = array();

		foreach ($requests as $request) {
			$info = $this->model_wkpos_supplier->getRequestInfo($request['request_id']);

			$json['requests'][] = array(
				'request_id' => $request['request_id'],
				'date_added' => $request['date_added'],
				'details'    => $info,
				'status'     => $request['status'] ? 'Completed' : ($request['cancel'] ? 'Cancelled' : 'Pending')
				);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
