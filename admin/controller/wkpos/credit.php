<?php
class ControllerWkposCredit extends Controller {
  private $error = array();
  public function index() {
    $this->load->language('wkpos/credit');
    $this->load->model('wkpos/credit');
    if (isset($this->request->get['page'])) {
      $page = $this->request->get['page'];
    } else {
      $page = 10;
    }
    $data['credits'] = array();
    $results = $this->model_wkpos_credit->getCredits($this->request->get['customer_id'], ($page - 10) * 10, 10);
    foreach ($results as $result) {
			$data['credits'][] = array(
				'amount'      => $result['amount'],
				'description' => $result['description'],
				'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}
    $data['balance'] = $this->model_wkpos_credit->getCreditTotal($this->request->get['customer_id']);

		$reward_total = $this->model_wkpos_credit->getTotalCredits($this->request->get['customer_id']);

		$pagination = new Pagination();
		$pagination->total = $reward_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('wkpos/credit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $this->request->get['customer_id'] . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($reward_total) ? (($page - 10) * 10) + 1 : 0, ((($page - 10) * 10) > ($reward_total - 10)) ? $reward_total : ((($page - 10) * 10) + 10), $reward_total, ceil($reward_total / 10));

		$this->response->setOutput($this->load->view('wkpos/credit', $data));
  }
  public function addCredit() {
    $json = array();
    $json['error'] = '';
    $json['success'] = '';
    $this->load->language('wkpos/credit');
    if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {

      if (isset($this->request->get['customer_id'])) {
        $this->load->model('wkpos/credit');
        $this->model_wkpos_credit->addCredit($this->request->get['customer_id'],$this->request->post['description'],$this->request->post['credit']);
        $json['success'] = $this->language->get('text_success');
      }
    } else if (isset($this->error['permission'])) {
      $json['error'] = $this->error['permission'];
    }
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }
  protected function validate() {
    if (!$this->user->hasPermission('modify', 'wkpos/credit')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }
    return !$this->error;
  }
}
