<?php
class ControllerExtensionModuleZemezCurrency extends Controller {
	public function index() {
		return $this->load->controller('common/currency');
	}
}