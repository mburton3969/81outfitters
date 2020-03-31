<?php

class ControllerExtensionModuleZemezMegaMenu extends Controller
{

	public function index($setting)
	{
		$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/js/zemez_megamenu/superfish.min.js');
		$this->document->addScript('catalog/view/theme/' . $this->config->get('theme_' . $this->config->get('config_theme') . '_directory') . '/js/zemez_megamenu/jquery.rd-navbar.min.js');

		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$this->load->model('extension/module/zemez_megamenu');

		$categories = $this->model_catalog_category->getCategories(0);
		foreach ($categories as $categorie_id => $categorie) {
			if (!$categorie['top']) {
				unset($categories[$categorie_id]);
			}
		}

		$categories = array_values($categories);

		$data['menu_items'] = [];
		if (isset($setting['menu_item'])) {
			$top_category_count = 0;

			foreach ($setting['menu_item'] as $menu_item_id => $menu_item) {
				$columns          = [];
				$products_count   = [];
				$categories_count = [];
				if ($menu_item['type']) {

					$name       = $menu_item[$this->config->get('config_language_id')]['title'];
					$href       = $menu_item['link'];
					$multilevel = '';

					if (isset($menu_item['column'])) {
						$column_categories = [];
						foreach ($menu_item['column'] as $column_id => $column) {
							$list   = '';
							$module = '';

							if ($column['category_show']) {
								$category_lv_2      = $this->model_catalog_category->getCategory($column['category_id']);
								$category_lv_2_href = $this->url->link('product/category', 'path=' . $column['category_id'], true);
							} else {
								$category_lv_2      = '';
								$category_lv_2_href = '';
							}

							switch ($column['content']) {
								case 4:
								$filter_data = array(
									'filter_category_id'  => $column['category_id'],
									'filter_sub_category' => true,
									'sort'                => 'p.date_added',
									'order'               => 'DESC',
									'start'               => isset($products_count[$column['category_id']]) ? $products_count[$column['category_id']] + 1 : 0,
									'limit'               => $column['prod_limit']
									);

								$results = $this->model_catalog_product->getProducts($filter_data);
								isset($products_count[$column['category_id']]) ? $products_count[$column['category_id']] += $column['prod_limit'] : $products_count[$column['category_id']] = (int)$column['prod_limit'];

								if ($results) {
									foreach ($results as $product_info) {
										$list .= "<li>\n<a href=\"" . $this->url->link('product/product', '&product_id=' . $product_info['product_id'], true) . "\">" . $product_info['name'] . "</a>\n</li>\n";
									}
								}
								break;
								case 3:
								isset($column_categories[$column['category_id']]) ? $column_categories[$column['category_id']]++ : $column_categories[$column['category_id']] = 0;

								$cats_2 = $this->model_catalog_category->getCategories($column['category_id']);

								if (isset($cats_2[$column_categories[$column['category_id']]])) {
									$list .= "<li class=\"submenu_title\">\n<a href=\"" . $this->url->link('product/category', 'path=' . $cats_2[$column_categories[$column['category_id']]]['category_id'], true) . "\">" . $cats_2[$column_categories[$column['category_id']]]['name'] . "</a>\n</li>\n";

									$cats_3 = $this->model_catalog_category->getCategories($cats_2[$column_categories[$column['category_id']]]['category_id']);

									foreach ($cats_3 as $cats_3_key => $cats_3_value) {
										if ($column['limit'] <= $cats_3_key) {
											break;
										}
										$list .= "<li>\n<a href=\"" . $this->url->link('product/category', 'path=' . $cats_3_value['category_id'], true) . "\">" . $cats_3_value['name'] . "</a>\n</li>\n";
									}
								}
								break;
								case 2:
								isset($column_categories[$column['category_id']]) ? $column_categories[$column['category_id']]++ : $column_categories[$column['category_id']] = 0;

								$cats_2 = $this->model_catalog_category->getCategories($column['category_id']);

								if (isset($cats_2[$column_categories[$column['category_id']]])) {
									$list .= "<li>\n<a href=\"" . $this->url->link('product/category', 'path=' . $cats_2[$column_categories[$column['category_id']]]['category_id'], true) . "\">" . $cats_2[$column_categories[$column['category_id']]]['name'] . "</a>\n</li>\n";

									$filter_data = array(
										'filter_category_id'  => $cats_2[$column_categories[$column['category_id']]]['category_id'],
										'filter_sub_category' => true,
										'sort'                => 'p.date_added',
										'order'               => 'DESC',
										'start'               => 0,
										'limit'               => $column['prod_limit']
										);

									$results = $this->model_catalog_product->getProducts($filter_data);
									if ($results) {
										foreach ($results as $product_info) {
											$list .= "<li>\n<a href=\"" . $this->url->link('product/product', '&product_id=' . $product_info['product_id'], true) . "\">" . $product_info['name'] . "</a>\n</li>\n";
										}
									}
								}
								break;
								case 1:
								isset($categories_count[$column['category_id']]) ? $categories_count[$column['category_id']] += $column['limit'] : $categories_count[$column['category_id']] = 0;
								$cats_2 = array_slice($this->model_catalog_category->getCategories($column['category_id']), $categories_count[$column['category_id']], $column['limit']);

								foreach ($cats_2 as $cat) {
									$list .= "<li>\n<a href=\"" . $this->url->link('product/category', 'path=' . $cat['category_id'], true) . "\">" . $cat['name'] . "</a>\n</li>\n";
								}
								break;
								case 0:
								$code = $this->model_extension_module_zemez_megamenu->getModuleCode($column['module_id']);
								$setting_info = $this->model_setting_module->getModule($column['module_id']);
								$module .= $this->load->controller('extension/module/' . $code, $setting_info);
								break;
							}
							$columns[] = array(
								'width'                => $column['width'],
								'custom_category'      => $category_lv_2,
								'custom_category_href' => $category_lv_2_href,
								'module'               => $module,
								'list'                 => $list
								);
						}
					}
				} elseif (isset($categories[$top_category_count])) {
					$column['category_show'] = 0;
					$name = $categories[$top_category_count]['name'];
					$href = $this->url->link('product/category', 'path=' . $categories[$top_category_count]['category_id'], true);

					$category_id = $categories[$top_category_count]['category_id'];

					$menu_item['submenu_type'] ? $multilevel = '' : $multilevel = $this->getCatTree($categories[$top_category_count]['category_id']) . "\n";

					if (isset($menu_item['column'])) {
						$products_count    = [];
						$column_categories = [];
						foreach ($menu_item['column'] as $column_id => $column) {
							$list   = '';
							$module = '';

							if ($column['category_show']) {
								$category_lv_2      = $this->model_catalog_category->getCategory($column['category_id']);
								$category_lv_2_href = $this->url->link('product/category', 'path=' . $column['category_id'], true);
							} else {
								$category_lv_2      = '';
								$category_lv_2_href = '';
							}

							switch ($column['content']) {
								case 4:
								$filter_data = array(
									'filter_category_id'  => $category_id,
									'filter_sub_category' => true,
									'sort'                => 'p.date_added',
									'order'               => 'DESC',
									'start'               => isset($products_count[$category_id]) ? $products_count[$category_id] + 1 : 0,
									'limit'               => $column['prod_limit']
									);

								$results = $this->model_catalog_product->getProducts($filter_data);
								isset($products_count[$category_id]) ? $products_count[$category_id] += $column['prod_limit'] : $products_count[$category_id] = (int)$column['prod_limit'];

								if ($results) {
									foreach ($results as $product_info) {
										$list .= "<li>\n<a href=\"" . $this->url->link('product/product', '&product_id=' . $product_info['product_id'], true) . "\">" . $product_info['name'] . "</a>\n</li>\n";
									}
								}
								break;
								case 3:
								isset($column_categories[$category_id]) ? $column_categories[$category_id]++ : $column_categories[$category_id] = 0;

								$cats_2 = $this->model_catalog_category->getCategories($category_id);

								if (isset($cats_2[$column_categories[$category_id]])) {
									$list .= "<li class=\"submenu_title\">\n<a href=\"" . $this->url->link('product/category', 'path=' . $cats_2[$column_categories[$category_id]]['category_id'], true) . "\">" . $cats_2[$column_categories[$category_id]]['name'] . "</a></li>\n";

									$cats_3 = $this->model_catalog_category->getCategories($cats_2[$column_categories[$category_id]]['category_id']);


									foreach ($cats_3 as $cats_3_key => $cats_3_value) {
										if ($column['limit'] <= $cats_3_key) {
											break;
										}
										$list .= "<li>\n<a href=\"" . $this->url->link('product/category', 'path=' . $cats_3_value['category_id'], true) . "\">" . $cats_3_value['name'] . "</a>\n</li>\n";
									}

								}
								break;
								case 2:
								isset($column_categories[$category_id]) ? $column_categories[$category_id]++ : $column_categories[$category_id] = 0;
								$cats_2 = $this->model_catalog_category->getCategories($category_id);

								if (isset($cats_2[$column_categories[$category_id]])) {
									$list .= "<li>\n<a href=\"" . $this->url->link('product/category', 'path=' . $cats_2[$column_categories[$category_id]]['category_id'], true) . "\">" . $cats_2[$column_categories[$category_id]]['name'] . "</a>\n</li>\n";

									$filter_data = array(
										'filter_category_id'  => $cats_2[$column_categories[$category_id]]['category_id'],
										'filter_sub_category' => true,
										'sort'                => 'p.date_added',
										'order'               => 'DESC',
										'start'               => 0,
										'limit'               => $column['prod_limit']
										);

									$results = $this->model_catalog_product->getProducts($filter_data);
									if ($results) {
										foreach ($results as $product_info) {
											$list .= "<li>\n<a href=\"" . $this->url->link('product/product', '&product_id=' . $product_info['product_id'], true) . "\">" . $product_info['name'] . "</a>\n</li>\n";
										}
									}
								}
								break;
								case 1:
								isset($categories_count[$category_id]) ? $categories_count[$category_id] += $column['limit'] : $categories_count[$category_id] = 0;
								$cats_2 = array_slice($this->model_catalog_category->getCategories($category_id), $categories_count[$category_id], $column['limit']);

								foreach ($cats_2 as $cat) {
									$list .= "<li>\n<a href=\"" . $this->url->link('product/category', 'path=' . $cat['category_id'], true) . "\">" . $cat['name'] . "</a>\n</li>\n";
								}
								break;
								case 0:
								$code = $this->model_extension_module_zemez_megamenu->getModuleCode($column['module_id']);
								$setting_info = $this->model_setting_module->getModule($column['module_id']);
								$module .= $this->load->controller('extension/module/' . $code, $setting_info);
								break;
							}
							$columns[] = array(
								'width'                => $column['width'],
								'custom_category'      => $category_lv_2,
								'custom_category_href' => $category_lv_2_href,
								'module'               => $module,
								'list'                 => $list
								);
						}
					}

					$top_category_count++;
				} else {
					continue;
				}

				$liClass = ((!$menu_item['type'] && $menu_item['submenu_type']) || ($menu_item['type'] && $menu_item['submenu_show'])) ? 'sf-with-mega' : '';

				$data['menu_items'][] = array(
					'href'    => $href,
					'name'    => $name,
					'mega'    => $liClass,
					'multi'   => $multilevel,
					'per-row' => $menu_item['columns-per-row'],
					'column'  => $columns
					);
			}
		}

		return $this->load->view('extension/module/zemez_megamenu', $data);
	}

	function getCatTree($category_id = 0)
	{
		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		$this->load->model('catalog/category');
		$category_data = "";

		$categories = $this->model_catalog_category->getCategories((int)$category_id);

		foreach ($categories as $category) {
			$name = $category['name'];
			$href = $this->url->link('product/category', 'path=' . $category['category_id']);
			$class = in_array($category['category_id'], $parts) ? " class=\"active\"" : "";
			$parent = $this->getCatTree($category['category_id']);
			if ($parent) {
				$class = $class ? " class=\"active\"" : " class=\"parent\"";
			}
			$category_data .= "<li>\n<a href=\"" . $href . "\"" . $class . ">" . $name . "</a>" . $parent . "\n</li>\n";

		}

		return strlen($category_data) ? "<ul class=\"simple_menu\">\n" . $category_data . "\n</ul>\n" : "";
	}
}