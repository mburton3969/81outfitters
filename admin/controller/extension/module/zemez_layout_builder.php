<?php

class ControllerExtensionModuleZemezLayoutBuilder extends Controller {
	private $error = array();
	private $mdata = array();

	public function index() {
		
		$this->load->language('extension/module/zemez_layout_builder');
		$this->load->model('setting/setting');
		$this->load->model('setting/module');
		$this->load->model('tool/image');

		$this->document->setTitle( strip_tags( $this->language->get('heading_title') ) );
		
		$this->document->addScript('view/javascript/layout_builder/sortable.js');
		$this->document->addScript('view/javascript/layout_builder/script.js');
		$this->document->addStyle('view/stylesheet/layout_builder/style.css');

		$this->mdata['HTTP_CATALOG'] = HTTP_CATALOG;

		if( isset($this->request->get['module_id']) && isset($this->request->get['delete']) ){
			$this->model_setting_module->deleteModule( $this->request->get['module_id'] );
			$this->response->redirect($this->url->link('extension/module/zemez_layout_builder', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$action = $this->request->post['layout_builder_module']['action'];
			$store_id = $this->request->post['layout_builder_module']['store_id'];
			$surl = isset($store_id)?'&store_id='.$store_id:'';

			unset( $this->request->post['layout_builder_module']['action'] );
			unset( $this->request->post['layout_builder_module']['store_id']);
			unset( $this->request->post['layout_builder_module']['stores']);
			unset( $this->request->post['layout_builder_module']['banners']);	


			$data = array();
			foreach ($this->request->post['layout_builder_module'] as $key => $value) {
				$data = $value;	
				$data['layout'] = ( htmlspecialchars_decode($value['layout']) );
				break;
			}

			if( empty($data['name']) ){
				$this->error['warning_name'] = $this->language->get('error_name');
				$this->response->redirect($this->url->link('extension/module/zemez_layout_builder', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			}	

			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('zemez_layout_builder', $data );
			} else {
				$this->model_setting_module->editModule( $this->request->get['module_id'], $data );
			}	



			$this->session->data['success'] = $this->language->get('text_success');
			if($action == "save_stay"){
				if( isset($this->request->get['module_id']) ) {
					$this->response->redirect($this->url->link('extension/module/zemez_layout_builder', 'module_id='.$this->request->get['module_id'].'&user_token=' . $this->session->data['user_token'] . '', true));
				}else{
					$this->response->redirect($this->url->link('extension/module/zemez_layout_builder', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
				}
			}else{
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			}

		}

		$this->_getLanguage();
		$this->_breadcrumbs();
		$this->_alert();

		$this->mdata['stores'] = $this->_getStores();
		$store_id = isset($this->request->get['store_id'])?$this->request->get['store_id']:0;
		$this->mdata['store_id'] = $store_id;

		$theme = $this->config->get('theme_' . $this->config->get('config_theme') . '_directory');

		$dir = DIR_CATALOG.'view/theme/'.$theme.'/template/extension/module/zemez_layout_builder';  
		$output = array();
		if( is_dir($dir) ){
			$dir = $dir.'/*.twig';
			$files = glob($dir);
			$output = array();

			foreach( $files as $file ){

				$name =  str_replace( ".twig", "", basename( $file ) );
				$a = file_get_contents( $file );  

				if( preg_match( "#Template\s*:\s*([\w+\s+]+)+(\r\n)?#", $a,$match) ){ 
					$output[$name] = $match[1];
				}else {
					$output[$a] = $name;
				}
			}
		}

		$this->load->model('localisation/language');
		$this->mdata['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('design/banner');	
		$banners = $this->model_design_banner->getBanners();

		foreach( $banners as $key => $banner  ){
			$banners[$key]['total'] = count( $this->model_design_banner->getBannerImages( $banner['banner_id']) );
		}

		$this->mdata['banners'] = $banners; 
		$this->mdata['templates'] = $output;

		if (isset($this->request->post['layout_builder_status'])) {
			$this->mdata['layout_builder_status'] = $this->request->post['layout_builder_status'];
		} else {
			$this->mdata['layout_builder_status'] = $this->config->get('layout_builder_status');
		}

		if (isset($this->request->post['layout_builder_module'])) {
			$modules = $this->request->post['layout_builder_module'];
		} else {
			$setting = $this->model_setting_setting->getSetting("zemez_layout_builder", $store_id);
			$modules = isset($setting['layout_builder_module'])?$setting['layout_builder_module']:array();
		}

		$default = array(
			'name' => '',
			'class' => '',
			'layout' => '',
			'id' => '',
			'status' => 0
		);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
			$this->mdata['selectedid'] = $this->request->get['module_id'];

			
			$this->mdata['subheading'] = 'Edit Module: '. $module_info['name'];
			$this->mdata['action'] = $this->url->link('extension/module/zemez_layout_builder', 'module_id='.$this->request->get['module_id'].'&user_token=' . $this->session->data['user_token'] . '&type=module', true);
		} else {
			$module_info = $default;
			$this->mdata['selectedid'] = 0;
			$this->mdata['subheading'] = 'Create New A Home Page Module';
			$this->mdata['action'] = $this->url->link('extension/module/zemez_layout_builder', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		}

		$moduletabs = $this->model_setting_module->getModulesByCode( 'zemez_layout_builder' );
		$this->mdata['link'] = $this->url->link('extension/module/zemez_layout_builder', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);



		$modules = array( 0=> $module_info );

		$this->mdata['modules'] = $modules; 
		$this->mdata['moduletabs'] = $moduletabs;

		$this->mdata['edit_action'] = $this->url->link('design/banner/edit', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		$this->mdata['add_action'] = $this->url->link('design/banner/add', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		$this->mdata['entry_banner_group'] = $this->language->get( 'entry_banner_group' );
		$this->mdata['entry_banner_template'] = $this->language->get( 'entry_banner_template' );
		
		$this->load->model('tool/image');
		$this->mdata['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$this->mdata['header'] = $this->load->controller('common/header');
		$this->mdata['column_left'] = $this->load->controller('common/column_left');
		$this->mdata['footer'] = $this->load->controller('common/footer');
		$template = 'extension/module/layout_builder/modules.twig';
		$this->mdata['olang'] = $this->language;
		$this->mdata['ourl'] = $this->url;
		$this->mdata['extensions'] = $this->_modulesInstalled(); 
		$this->mdata['ifmocmod'] = $this->url->link('extension/module/zemez_layout_builder/listmodules', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$sfxclss = $this->detectSfxClasses($this->config->get('theme_' . $this->config->get('config_theme') . '_directory'));
		$this->mdata['sfxclss']  = $sfxclss['widget'];

		$this->response->setOutput($this->load->view($template, $this->mdata));
	}

	public  function detectSfxClasses( $template ){
		
		$pagestyle =  DIR_CATALOG.'view/theme/default/stylesheet/homebuilder.css';
		$tcss =  DIR_CATALOG.'view/theme/'.$template.'/stylesheet/homebuilder.css';

		$captions  = array( 'col' => array() , 'row' => array() );

		if( file_exists($tcss) ){
			$content =  file_get_contents( $tcss );
		}else {
			$content   =  file_get_contents( $pagestyle );
		}
		
		$a = preg_match_all( "#\.zemez-col\.(\w+)\s*{\s*#", $content, $matches );
		if( isset($matches[1]) ){
			$captions['col']  = $matches[1];
		}

		$a = preg_match_all( "#\.zemez-row\.(\w+)\s*{\s*#", $content, $matches );
		if( isset($matches[1]) ){
			$captions['row']  = $matches[1];
		}
		$a = preg_match_all( "#\.widget\.(\w+)\s*{\s*#", $content, $matches );
		if( isset($matches[1]) ){
			foreach( $matches[1] as $class ){
				$captions['widget']  = $matches[1];
			}
		}
		return $captions;
	}

	protected function showModules(){
		$data['extensions'] = array();
		$extensions = $this->model_setting_extension->getInstalled('module');
		
		$files = glob(DIR_APPLICATION . "controller/extension/module/*.php");

		if ($files) {
			foreach ($files as $file) {

				$content = file_get_contents( $file );

				if( !preg_match( "#editModule#", $content ) ){
					continue;
				}

				$extension = basename($file, '.php');

				$this->load->language('extension/module/' . $extension);

				$module_data = array();

				$data['extensions'][] = array(
					'name'      => strip_tags($this->language->get('heading_title')),
					'module'    => $module_data,
					'install'   => $this->url->link('extension/extension/module/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
					'uninstall' => $this->url->link('extension/extension/module/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
					'installed' => in_array($extension, $extensions),
					'edit'      => $this->url->link('extension/module/' . $extension, 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
				);
			}
		}
		return $data['extensions'];
	}

	public function _modulesInstalled(){
		$this->load->model('setting/extension');
		$this->load->model('setting/module');
		$data['extensions'] = array();
		
		$extensions = $this->model_setting_extension->getInstalled('module');

		// Get a list of installed modules
		$extensions = $this->model_setting_extension->getInstalled('module');

		// Add all the modules which have multiple settings for each module
		foreach ($extensions as $code) {
			$this->load->language('extension/module/' . $code, 'extension');
			if($code == "zemez_layout_builder"){
				continue;
			}

			$module_data = array();

			$modules = $this->model_setting_module->getModulesByCode($code);

			foreach ($modules as $module) {
				$module_data[] = array(
					'name' => strip_tags($module['name']),
					'code' => $code . '.' . $module['module_id'],
					'id'   => $module['module_id']
				);
			}

			if ($this->config->has('module_' . $code . '_status') || $module_data) {
				$data['extensions'][$code] = array(
					'name'   => strip_tags($this->language->get('extension')->get('heading_title')),
					'code'   => $code,
					'module' => $module_data ? $module_data : [['name' => strip_tags($this->language->get('extension')->get('heading_title')), 'code' => $code]]
				);
			}
		}

		return $data['extensions'];
	}
	public function _alert(){
		if (isset($this->error['warning'])) {
			$this->mdata['error_warning'] = $this->error['warning'];
		} else {
			$this->mdata['error_warning'] = '';
		}
		if (!isset($this->request->post['layout_builder_module']['name']) && isset($this->error['warning_name'])) {
			$this->mdata['error_name'] = $this->error['warning_name'];
		} else {
			$this->mdata['error_name'] = '';
		}
		if (isset($this->session->data['success'])) {
			$this->mdata['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->mdata['success'] = '';
		}
	}

	public function _breadcrumbs(){
		$this->mdata['breadcrumbs'] = array();

		$this->mdata['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
			'separator' => false
		);

		$this->mdata['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
			'separator' => ' :: '
		);

		$this->mdata['breadcrumbs'][] = array(
			'text'      => strip_tags($this->language->get('heading_title')),
			'href'      => $this->url->link('extension/module/tmcustom', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
			'separator' => ' :: '
		);
	}

	public function _getStores(){

		$this->load->model('setting/store');

		$action = array();
		$action[] = array(
			'text' => $this->language->get('text_edit'),
			'href' => $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);
		$store_default = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name') . $this->language->get('text_default'),
			'url'      => HTTP_CATALOG,
		);
		$stores = $this->model_setting_store->getStores();
		array_unshift($stores, $store_default);
		
		foreach ($stores as &$store) {
			$url = '';
			if ($store['store_id'] > 0 ) {
				$url = '&store_id='.$store['store_id'];
			}
		}
		return $stores;
	}

	public function _getLanguage(){
		$this->mdata['objlang'] = $this->language;

		$this->mdata['heading_title'] = $this->language->get('heading_banner_title');
		$this->mdata['tab_module'] = $this->language->get('tab_module');
		// Text
		$this->mdata['prefix_class'] = $this->language->get('prefix_class');
		// Button
		$this->mdata['button_save'] = $this->language->get('button_save');
		$this->mdata['button_save_stay'] = $this->language->get('button_save_stay');
		$this->mdata['button_cancel'] = $this->language->get('button_cancel');
		$this->mdata['button_add_module'] = $this->language->get('button_add_module');
		// Entry
		$this->mdata['entry_layout'] = $this->language->get('entry_layout');
		$this->mdata['entry_status'] = $this->language->get('entry_status');
		$this->mdata['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->mdata['entry_position'] = $this->language->get('entry_position');
		$this->mdata['entry_tabs'] = $this->language->get('entry_tabs');
		$this->mdata['entry_banner_layouts'] = $this->language->get('entry_banner_layouts');
		$this->mdata['entry_caption'] = $this->language->get('entry_caption');
		$this->mdata['text_disabled'] = $this->language->get('text_disabled');
		$this->mdata['text_enabled'] = $this->language->get('text_enabled');

		$this->mdata['no_image'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		// user_Token
		$this->mdata['user_token'] = $this->session->data['user_token'] . '&type=module';

		
		$this->mdata['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/zemez_layout_builder')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		foreach ($this->request->post['layout_builder_module'] as $key => $value) {
			if (isset($value['name']) && empty($value['name'])) {
				$this->error['warning_name'] = $this->language->get('error_name');
			}
		}
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	public function listmodules(){

		$this->load->language('extension/module/zemez_layout_builder');
		$this->load->model('setting/setting');
		$this->load->model('setting/extension');
		$this->load->model('setting/module');
		$this->load->model('tool/image');

		$this->document->addStyle('view/stylesheet/layout_builder/style.css');

		$this->mdata['ocmodules'] = $this->showModules();
		$template = 'extension/module/layout_builder/ocmodules.twig';

		$this->mdata['header'] = $this->load->controller('common/header');
		$this->mdata['column_left'] = $this->load->controller('common/column_left');
		$this->mdata['footer'] = $this->load->controller('common/footer');
		$this->mdata['install'] = $this->url->link('extension/install', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);


		$this->response->setOutput($this->load->view($template, $this->mdata));
	}
}
?>