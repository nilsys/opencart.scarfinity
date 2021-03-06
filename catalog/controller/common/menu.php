<?php
class ControllerCommonMenu extends Controller {
	public function index() {
		$this->load->language('common/menu');

		$data['isMobile'] = constant('isMobile');
		$data['isTablet'] = constant('isTablet');

		if(!isset($this->request->get['route']) || $this->request->get['route'] == 'common/home') {
			$data['isHome'] = true;
		}

		// Menu
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			if ($category['top']) {
				// Level 2
				$children_data = array();

				$children = $this->model_catalog_category->getCategories($category['category_id']);

				foreach ($children as $child) {
					// Level 3
					$level3_data = array();
					$level3 = $this->model_catalog_category->getCategories($child['category_id']);

					foreach ($level3 as $child3) {
						$filter3_data = array(
							'filter_category_id'  => $child['category_id'],
							'filter_sub_category' => true
						);

						$level3_data[] = array(
							'name' => $child3['name'],
							'href' => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'] . '_' . $child3['category_id'])
						);
					}


					$filter_data = array(
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => true
					);

					$children_data[] = array(
						'name'  => $child['name'],
						'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
						'children' => $level3_data
					);
				}

				// Level 1
				$data['categories'][] = array(
					'name'     => $category['name'],
					'children' => $children_data,
					'column'   => $category['column'] ? $category['column'] : 1,
					'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}
		}

		$data['telephone'] = $this->config->get('config_telephone');
		$data['catalog'] = $this->url->link('product/catalog');
		$data['special'] = $this->url->link('product/special');
		$data['contact'] = $this->url->link('information/contact');
		$data['about_us'] = $this->url->link('information/information', 'information_id=4');

		return array(
			'mobile' 	=> $this->load->view('common/mobile_menu', $data),
			'desktop' 	=> $this->load->view('common/desktop_menu', $data),
		);
	}
}
