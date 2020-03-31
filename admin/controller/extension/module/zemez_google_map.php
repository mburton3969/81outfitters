<?php

class ControllerExtensionModuleZemezGoogleMap extends Controller
{
	private $error = array();

	public function index()
	{
		$this->load->language('extension/module/zemez_google_map');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');
		$this->load->model('tool/image');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('zemez_google_map', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['zemez_google_map_marker_width'])) {
			$data['error_marker_width'] = $this->error['zemez_google_map_marker_width'];
		} else {
			$data['error_marker_width'] = '';
		}
		if (isset($this->error['zemez_google_map_marker_height'])) {
			$data['error_marker_height'] = $this->error['zemez_google_map_marker_height'];
		} else {
			$data['error_marker_height'] = '';
		}
		if (isset($this->error['zemez_google_map_marker_height'])) {
			$data['error_marker_height'] = $this->error['zemez_google_map_marker_height'];
		} else {
			$data['error_marker_height'] = '';
		}
		if (isset($this->error['zemez_google_map_zoom'])) {
			$data['error_zoom'] = $this->error['zemez_google_map_zoom'];
		} else {
			$data['error_zoom'] = '';
		}

		if (isset($this->error['zemez_google_map_marker_active'])) {
			$data['error_marker_active'] = $this->error['zemez_google_map_marker_active'];
		} else {
			$data['error_marker_active'] = '';
		}

		if (isset($this->error['zemez_google_map_marker'])) {
			$data['error_marker'] = $this->error['zemez_google_map_marker'];
		} else {
			$data['error_marker'] = '';
		}

		if (isset($this->error['zemez_google_map_styles'])) {
			$data['error_styles'] = $this->error['zemez_google_map_styles'];
		} else {
			$data['error_styles'] = '';
		}


		if (isset($this->error['zemez_google_map_geocode'])) {
			$data['error_geocode'] = $this->error['zemez_google_map_geocode'];
		} else {
			$data['error_geocode'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
			);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/zemez_google_map', 'user_token=' . $this->session->data['user_token'], true)
			);

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/zemez_google_map', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/zemez_google_map', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}


		if (isset($this->request->post['zemez_google_map_status'])) {
			$data['status'] = $this->request->post['zemez_google_map_status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['zemez_google_map_key'])) {
			$data['zemez_google_map_key'] = $this->request->post['zemez_google_map_key'];
		} elseif (!empty($module_info)) {
			$data['zemez_google_map_key'] = $module_info['zemez_google_map_key'];
		} else {
			$data['zemez_google_map_key'] = '';
		}

		if (isset($this->request->post['zemez_google_map_address'])) {
			$data['zemez_google_map_address'] = $this->request->post['zemez_google_map_address'];
		} elseif (!empty($module_info) && isset($module_info['zemez_google_map_address'])) {
			$data['zemez_google_map_address'] = $module_info['zemez_google_map_address'];
		} else {
			$data['zemez_google_map_address'] = '';
		}

		if (isset($this->request->post['zemez_google_map_geocode'])) {
			$data['zemez_google_map_geocode'] = $this->request->post['zemez_google_map_geocode'];
		} elseif (!empty($module_info) && isset($module_info['zemez_google_map_geocode'])) {
			$data['zemez_google_map_geocode'] = $module_info['zemez_google_map_geocode'];
		} else {
			$data['zemez_google_map_geocode'] = '';
		}

		if (isset($this->request->post['zemez_google_map_zoom'])) {
			$data['zemez_google_map_zoom'] = $this->request->post['zemez_google_map_zoom'];
		} elseif (!empty($module_info)) {
			$data['zemez_google_map_zoom'] = $module_info['zemez_google_map_zoom'];
		} else {
			$data['zemez_google_map_zoom'] = '';
		}
		if (isset($this->request->post['zemez_google_map_type'])) {
			$data['zemez_google_map_type'] = $this->request->post['zemez_google_map_type'];
		} elseif (!empty($module_info)) {
			$data['zemez_google_map_type'] = $module_info['zemez_google_map_type'];
		} else {
			$data['zemez_google_map_type'] = '';
		}

		if (isset($this->request->post['zemez_google_map_marker'])) {
			$data['zemez_google_map_marker'] = $this->request->post['zemez_google_map_marker'];
		} elseif (!empty($module_info)) {
			$data['zemez_google_map_marker'] = $module_info['zemez_google_map_marker'];
		} else {
			$data['zemez_google_map_marker'] = '';
		}

		if (isset($this->request->post['zemez_google_map_marker']) && is_file(DIR_IMAGE . $this->request->post['zemez_google_map_marker'])) {
			$data['map_marker'] = $this->model_tool_image->resize($this->request->post['zemez_google_map_marker'], 100, 100);
		} elseif (!empty($module_info) && isset($module_info['zemez_google_map_marker']) && is_file(DIR_IMAGE . $module_info['zemez_google_map_marker'])) {
			$data['map_marker'] = $this->model_tool_image->resize($module_info['zemez_google_map_marker'], 100, 100);
		} else {
			$data['map_marker'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}


		if (isset($this->request->post['zemez_google_map_marker_active'])) {
			$data['zemez_google_map_marker_active'] = $this->request->post['zemez_google_map_marker_active'];
		} elseif (!empty($module_info)) {
			$data['zemez_google_map_marker_active'] = $module_info['zemez_google_map_marker_active'];
		} else {
			$data['zemez_google_map_marker_active'] = '';
		}

		if (isset($this->request->post['zemez_google_map_marker_active']) && is_file(DIR_IMAGE . $this->request->post['zemez_google_map_marker_active'])) {
			$data['map_marker_active'] = $this->model_tool_image->resize($this->request->post['zemez_google_map_marker_active'], 100, 100);
		} elseif (!empty($module_info) && isset($module_info['zemez_google_map_marker_active']) && is_file(DIR_IMAGE . $module_info['zemez_google_map_marker_active'])) {
			$data['map_marker_active'] = $this->model_tool_image->resize($module_info['zemez_google_map_marker_active'], 100, 100);
		} else {
			$data['map_marker_active'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}


		if (isset($this->request->post['zemez_google_map_marker_width'])) {
			$data['zemez_google_map_marker_width'] = $this->request->post['zemez_google_map_marker_width'];
		} elseif (!empty($module_info)) {
			$data['zemez_google_map_marker_width'] = $module_info['zemez_google_map_marker_width'];
		} else {
			$data['zemez_google_map_marker_width'] = '';
		}

		if (isset($this->request->post['zemez_google_map_marker_height'])) {
			$data['zemez_google_map_marker_height'] = $this->request->post['zemez_google_map_marker_height'];
		} elseif (!empty($module_info)) {
			$data['zemez_google_map_marker_height'] = $module_info['zemez_google_map_marker_height'];
		} else {
			$data['zemez_google_map_marker_height'] = '';
		}

		if (isset($this->request->post['zemez_google_map_sensor'])) {
			$data['zemez_google_map_sensor'] = $this->request->post['zemez_google_map_sensor'];
		} elseif (!empty($module_info)) {
			$data['zemez_google_map_sensor'] = $module_info['zemez_google_map_sensor'];
		} else {
			$data['zemez_google_map_sensor'] = '';
		}

		if (isset($this->request->post['zemez_google_map_width'])) {
			$data['zemez_google_map_width'] = $this->request->post['zemez_google_map_width'];
		} elseif (!empty($module_info)) {
			$data['zemez_google_map_width'] = $module_info['zemez_google_map_width'];
		} else {
			$data['zemez_google_map_width'] = '';
		}

		if (isset($this->request->post['zemez_google_map_height'])) {
			$data['zemez_google_map_height'] = $this->request->post['zemez_google_map_height'];
		} elseif (!empty($module_info)) {
			$data['zemez_google_map_height'] = $module_info['zemez_google_map_height'];
		} else {
			$data['zemez_google_map_height'] = '';
		}

		if (isset($this->request->post['zemez_google_map_styles'])) {
			$data['zemez_google_map_styles'] = $this->request->post['zemez_google_map_styles'];
		} elseif (!empty($module_info)) {
			$data['zemez_google_map_styles'] = $module_info['zemez_google_map_styles'];
		} else {
			$data['zemez_google_map_styles'] = '';
		}

		if (isset($this->request->post['zemez_google_map_disable_ui'])) {
			$data['zemez_google_map_disable_ui'] = $this->request->post['zemez_google_map_disable_ui'];
		} elseif (!empty($module_info)) {
			$data['zemez_google_map_disable_ui'] = $module_info['zemez_google_map_disable_ui'];
		} else {
			$data['zemez_google_map_disable_ui'] = '';
		}

		if (isset($this->request->post['zemez_google_map_scrollwheel'])) {
			$data['zemez_google_map_scrollwheel'] = $this->request->post['zemez_google_map_scrollwheel'];
		} elseif (!empty($module_info)) {
			$data['zemez_google_map_scrollwheel'] = $module_info['zemez_google_map_scrollwheel'];
		} else {
			$data['zemez_google_map_scrollwheel'] = '';
		}

		if (isset($this->request->post['zemez_google_map_draggable'])) {
			$data['zemez_google_map_draggable'] = $this->request->post['zemez_google_map_draggable'];
		} elseif (!empty($module_info)) {
			$data['zemez_google_map_draggable'] = $module_info['zemez_google_map_draggable'];
		} else {
			$data['zemez_google_map_draggable'] = '';
		}


		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/zemez_google_map', $data));
	}

	protected function validate()
	{
		if (!$this->user->hasPermission('modify', 'extension/module/zemez_google_map')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!is_numeric($this->request->post['zemez_google_map_zoom'])) {
			$this->error['zemez_google_map_zoom'] = $this->language->get('error_zoom');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!empty($this->request->post['zemez_google_map_marker']) || !empty($this->request->post['zemez_google_map_marker_active'])) {
			if (!is_numeric($this->request->post['zemez_google_map_marker_width'])) {
				$this->error['zemez_google_map_marker_width'] = $this->language->get('error_marker_width');
			}
			if (!is_numeric($this->request->post['zemez_google_map_marker_height'])) {
				$this->error['zemez_google_map_marker_height'] = $this->language->get('error_marker_height');
			}
		}
		if (!empty($this->request->post['zemez_google_map_marker']) && empty($this->request->post['zemez_google_map_marker_active'])) {
			$this->error['zemez_google_map_marker_active'] = $this->language->get('error_marker_active');
		}

		if (empty($this->request->post['zemez_google_map_marker']) && !empty($this->request->post['zemez_google_map_marker_active'])) {
			$this->error['zemez_google_map_marker'] = $this->language->get('error_marker');
		}

		if (isset($this->request->post['zemez_google_map_styles'])) {
			$str = html_entity_decode(trim($this->request->post['zemez_google_map_styles']));

			if (!empty($str) && !$this->isJSON($str)) {
				$this->error['zemez_google_map_styles'] = $this->language->get('error_styles');
			}
		}

		if (isset($this->request->post['zemez_google_map_geocode'])){
			foreach ($this->request->post['zemez_google_map_geocode'] as $code){
				if (!preg_match("/^[+-]?\d+\.\d+, ?[+-]?\d+\.\d+$/",$code)){
					$this->error['zemez_google_map_geocode'] = $this->language->get('error_geocode');
					break;
				}
			}
		}

		return !$this->error;
	}

	private function isJSON($string){
		return is_array(json_decode($string)) ? true : false;
	}
}