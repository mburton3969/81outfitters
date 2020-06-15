<?php

class ControllerExtensionModuleZemezGoogleMap extends Controller
{
	public function index($setting)
	{
		$this->load->language('extension/module/zemez_google_map');

		$key = '';
		if ($setting['zemez_google_map_key']) {
			$key .= 'key=';
			$key .= trim($setting['zemez_google_map_key'] );
			$key .= '&amp;';
		}
		$this->document->addStyle('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/js/zemez_google_map/zemez_google_map.css');
		$this->document->addScript('//maps.googleapis.com/maps/api/js?' . $key . 'sensor=' . $setting['zemez_google_map_sensor'] . '');
		$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/js/zemez_google_map/jquery.rd-google-map.js');

		$data['main_geocode'] = $this->config->get('config_geocode');
		$data['main_address'] = $this->config->get('config_address');

		if (isset($setting['zemez_google_map_address'])) {
			$data['address'] = $setting['zemez_google_map_address'];
		} else {
			$data['address'] = '';
		}
		if (isset($setting['zemez_google_map_geocode'])) {
			$data['geocode'] = $setting['zemez_google_map_geocode'];
		} else {
			$data['geocode'] = '';
		}

		$data['zoom'] = $setting['zemez_google_map_zoom'];
		$data['type'] = $setting['zemez_google_map_type'];
		$data['width'] = $setting['zemez_google_map_width'];
		$data['height'] = $setting['zemez_google_map_height'];

		if (strlen(trim($setting['zemez_google_map_styles'])) > 0) {
			$data['styles'] = trim($setting['zemez_google_map_styles']);
		} else {
			$data['styles'] = "[]";
		}

		$data['disable_ui'] = $setting['zemez_google_map_disable_ui'];
		$data['scrollwheel'] = $setting['zemez_google_map_scrollwheel'];
		$data['draggable'] = $setting['zemez_google_map_draggable'];


		$markerWidth = $setting['zemez_google_map_marker_width'];
		$markerHeight = $setting['zemez_google_map_marker_height'];
		if (is_file(DIR_IMAGE . $setting['zemez_google_map_marker'])) {
			$data['marker_image'] = $this->model_tool_image->resize($setting['zemez_google_map_marker'], $markerWidth, $markerHeight);
		} else {
			$data['marker_image'] = '';
		}

		if (is_file(DIR_IMAGE . $setting['zemez_google_map_marker_active'])) {
			$data['marker_active_image'] = $this->model_tool_image->resize($setting['zemez_google_map_marker_active'], $markerWidth, $markerHeight);
		} else {
			$data['marker_active_image'] = '';
		}

		return $this->load->view('extension/module/zemez_google_map', $data);

	}

}