<?php
class ControllerExtensionModuleSpecial extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/special');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		// Innamed data
		// page.[].layout_type
		$innamed = explode('.', $setting['name']);
		if(isset($innamed[0])) $data['page'] = $innamed[0];
		if(isset($innamed[1])) $data['heading_title'] = $innamed[1];
		if(isset($innamed[2])) $data['layout'] = $innamed[2];

		$data['products'] = array();

		$filter_data = array(
			'sort'  => 'pd.name',
			'order' => 'ASC',
			'start' => 0,
			'limit' => $setting['limit']
		);

		$results = $this->model_catalog_product->getProductSpecials($filter_data);

		if ($results) {
			foreach ($results as $result) {

				$images = array();

				if($result['images']) {
					foreach ($result['images'] as $image) {
						$images[] = array(
							'popup' => $this->model_tool_image->resize($image['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
							'thumb' => $this->model_tool_image->resize($image['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')),
							'lazy'	=> $this->model_tool_image->resize($image['image'], 256, 256)
						);
					}
				}

				$colors = array();

				if($result['colors']) {
					foreach ($result['colors'] as $color) {
						$colors[] = array(
							'image' => array(
								'thumb' => $this->model_tool_image->resize($color['image'], 32, 32),
								'popup' => $this->model_tool_image->resize($color['image'], 1024, 1024),
							)
						);
					}
				}

				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else if(isset($images[0])) {
					$image = $images[0]['thumb'];
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}

				$benefit = 0;

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price_origin'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$benefit = $result['price_origin'];
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$benefit = $benefit - $result['special'];
				} else {
					$special = false;
				}

				$benefit = $this->currency->format($benefit, $this->session->data['currency']);

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}

				$props = array();

				foreach(explode(',', $result['location']) as $prop) {
					$values = explode(':', $prop);
					if(isset($values[1]) && isset($values[0])) {
						$props[$values[0]] = $values[1]; 
					}
				}

				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'isbn'		  => $result['isbn'],
					'image'       => $image,
					'images'      => $images,
					'colors'	  => $colors,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'benefit'	  => $benefit,
					'tax'         => $tax,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id']),
					'props'		  => $props,
					'date'		  => array(
						'added'	  	=> $result['date_added'],
						'modified'	=> $result['date_modified'],
						'available'	=> $result['date_available'],
					)
				);
			}

			return $this->load->view('extension/module/special', $data);
		}
	}
}