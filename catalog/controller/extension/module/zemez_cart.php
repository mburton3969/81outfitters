<?php
class ControllerExtensionModuleZemezCart extends Controller {
	public function index() {
		return $this->load->controller('common/cart');
	}
}