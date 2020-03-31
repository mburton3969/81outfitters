<?php
class ControllerExtensionModuleZemezLanguage extends Controller {
	public function index() {
		return $this->load->controller('common/language');
	}
}