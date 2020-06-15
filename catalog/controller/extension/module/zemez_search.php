<?php
class ControllerExtensionModuleZemezSearch extends Controller {
	public function index() {
		return $this->load->controller('common/search');
	}
}