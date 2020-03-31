<?php

class ControllerExtensionModuleZemezLayoutBuilder extends Controller {

	private $mdata = array();
	private $strFind = "";
	private $url = '';

	public function index($setting) { 
		$this->load->model('tool/image');
		$this->load->model('design/banner');
		$this->load->model('setting/module');
		$d = array("banner_layout" => 1, "prefix" => '');
		$strFind = htmlspecialchars_decode("&quot;fullwidth&quot;:&quot;1&quot;");
		$setting = array_merge($d, $setting);

		$this->mdata['objimg'] = $this->model_tool_image;
		$layouts = ( trim($setting['layout']) );
		$this->mdata['layouts'] = $layouts;

		$tpl = 'zemez_layout_builder.twig';

		if (file_exists('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/stylesheet/homebuilder.css')) {
			$this->document->addStyle('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/stylesheet/homebuilder.css');
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/homebuilder.css');
		}
		$this->url = $this->config->get('config_secure') ? $this->config->get('config_ssl') : $this->config->get('config_url');

		$layout = json_decode( $layouts );
		$layouts = $this->buildLayoutData( $layout,  1 );

		$this->mdata['layouts'] = $layouts;
		$this->mdata['url'] =  $this->config->get('config_secure') ? $this->config->get('config_ssl') : $this->config->get('config_url');
		
		$this->mdata['class'] = isset($setting['class'])?$setting['class']:'';
		$this->mdata['id'] = isset($setting['id'])?$setting['id']:'';
		$this->mdata['heading'] = isset($setting['heading'])?$setting['heading']:'';

		$template = $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/template/extension/module/'.$tpl;
		
		$this->mdata['template'] = $template;

		return $this->load->view('extension/module/zemez_layout_builder', $this->mdata);
	}
	
	protected function buildStyles( $element ){
		$styles = array();
		if( isset($element->padding) && $element->padding ){
			$styles[]= 'padding:'.$element->padding;
		}
		if( isset($element->margin) && $element->margin ){
			$styles[]= 'margin:'.$element->margin;
		}
		if( isset($element->bgcolor) && $element->bgcolor ){
			$styles[] = 'background-color:'.$element->bgcolor;
		}
		if( isset($element->bgimage) && $element->bgimage ){
			$styles[] = 'background-image:url(\''.$this->url .'image/'.$element->bgimage.'\')';
			if( isset($element->iposition) && $element->iposition ){
				$styles[] ='background-position:'.$element->iposition;
			}
			if( isset($element->iattachment) && $element->iattachment ){
				$styles[] = 'background-attachment:'.$element->iattachment;
			}
		}
		if( !empty($styles) ){
			$element->attrs = $element->attrs . ' style="'.implode(";", $styles).'"';
		}

		return $element; 
	}

	public function buildLayoutData( $rows , $rl=1 ){ 
		$layout = array();
		$this->templatepath = DIR_TEMPLATE . 'extension/module/zemez_layout_builder/';
		foreach( $rows as $rkey =>  $row ){
			$row->level=$rl;

			$row = $this->mergeRowData( $row );
			foreach( $row->cols as $ckey => $col ){
				$col = $this->mergeColData( $col );
				foreach( $col->widgets as  $wkey => $w ){
					if( isset($w->module) ){
						$w->content = $this->renderModule( array('code'=>$w->module) );
					}
				}
				if( isset($col->rows) ){
					$col->rows = $this->buildLayoutData( $col->rows, $rl+1 );
				}
				$row->cols[$ckey] = $col;
			}

			$layout[$rkey] = $row;
		}

		return $layout;
	}

	protected function renderModule( $module  ){
		$part = explode('.', $module['code']);

		if (isset($part[0]) && $this->config->get('module_' . $part[0] . '_status')) {
			return $this->load->controller('extension/module/' . $part[0]);
		}

		if (isset($part[1])) {
			$setting_info = $this->model_setting_module->getModule($part[1]);

			if ($setting_info && $setting_info['status']) {
				return $this->load->controller('extension/module/' . $part[0], $setting_info);
			}
		}
		return ;
	}

	protected function mergeColData( $col ){
		$col->attrs = '';
		$col = $this->buildStyles( $col );
		if( isset($col->sfxcls) && $col->sfxcls ){
			$col->sfxcls = trim( $col->sfxcls );
		}else {
			$col->sfxcls = '';
		}
		return $col;
	}

	public function mergeRowData( $row ){
		$row->attrs = '';
		$styles = array();
		$row = $this->buildStyles( $row );  

		if( isset($row->sfxcls) && $row->sfxcls ){
			$row->sfxcls =  trim( $row->sfxcls );
		}else {
			$row->sfxcls = '';
		}

		return $row;
	}
}
?>