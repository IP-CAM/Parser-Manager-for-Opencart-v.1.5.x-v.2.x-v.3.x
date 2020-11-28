<?php
class ModelModuleParser extends Model {

	public function addProduct($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . $this->db->escape($data['tax_class_id']) . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW()");
		
		$product_id = $this->db->getLastId();
		
		$this->addProductParseUrls($product_id);
		
		if (isset($data['url_yandex'])) {		
			$this->db->query("UPDATE " . DB_PREFIX . "parser_urls SET url_yandex='" . $this->db->escape($data['url_yandex']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}
		
		if (isset($data['url_hotline'])){
			$this->db->query("UPDATE " . DB_PREFIX . "parser_urls SET url_hotline='" . $this->db->escape($data['url_hotline']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}
		
		if (isset($data['url_onliner'])){
			$this->db->query("UPDATE " . DB_PREFIX . "parser_urls SET url_onliner='" . $this->db->escape($data['url_onliner']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}
		
		if (isset($data['url_mail'])){
			$this->db->query("UPDATE " . DB_PREFIX . "parser_urls SET url_mail='" . $this->db->escape($data['url_mail']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}
		
		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE product_id = '" . (int)$product_id . "'");
		}
		
		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']). "'");
		}
		
		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");
					
					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {				
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}
	
		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");
				
					$product_option_id = $this->db->getLastId();
				
					if (isset($product_option['product_option_value']) && count($product_option['product_option_value']) > 0 ) {
						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
						} 
					}else{
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_option_id = '".$product_option_id."'");
					}
				} else { 
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value = '" . $this->db->escape($product_option['option_value']) . "', required = '" . (int)$product_option['required'] . "'");
				}
			}
		}
		
		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}
		
		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape(html_entity_decode($product_image['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}
		
		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
			}
		}
		
		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}
		
		if (isset($data['main_category_id']) && $data['main_category_id'] > 0) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$data['main_category_id'] . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$data['main_category_id'] . "', main_category = 1");
		} elseif (isset($data['product_category'][0])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET main_category = 1 WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$data['product_category'][0] . "'");
		}
		
		if (isset($data['product_filter'])) {
			foreach ($data['product_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}
		
		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
			}
		}

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward['points'] . "'");
			}
		}

		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}
						
		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}
						
		$this->cache->delete('product');
	}


	public function SearchAttribyteGroupe($data) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_group_description WHERE name = '" . $this->db->escape($data) . "'");
			return $query->row;
		}	

	public function SearchAttribyte($data) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_description WHERE name = '" . $this->db->escape($data) . "'");
			return $query->row;
		}

	public function SearchAttribytByAttribytGroupe($data) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute WHERE  attribute_id  = '" . $this->db->escape($data) . "'");
			return $query->row;
		}
		
		
	public function SearchOption($data) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_description WHERE name = '" . $data . "'");
			return $query->row;
		}
		
	public function SearchOptionDescription($data, $name) {
			
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value WHERE 
			option_id  = '" . $data . "'");
			return $query->row;
		}	
		
	public function SearchProductBySku($data) {		
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE 
			sku  = '" . $data . "'");
			return $query->row;
		}
		
	public function SearchAttByGrup($attr_name, $attr_gr) {		
		$query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "attribute_description p LEFT JOIN " . DB_PREFIX . 
			"attribute pd ON (p.attribute_id = pd.attribute_id) WHERE p.name = '" . $this->db->escape($attr_name) .  "' AND pd.attribute_group_id = ".$attr_gr);
		return $query->row;	
	}

	public function GetOptionsAndDescription($opt_name, $opt_gr) {		
		$query = $this->db->query("SELECT  * FROM " . DB_PREFIX . "option_value ov LEFT JOIN " . DB_PREFIX ." option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE ov.option_id = '" . $opt_gr .  "' AND ovd.name = '".$opt_name."'");
		return $query->row;	
	}

	public function SearchEmpty($data = array()){
			
			$query = $this->db->query("SHOW GLOBAL VARIABLES WHERE variable_name = 'SQL_MAX_JOIN_SIZE'");			
			if (!empty($query->rows)){			
				$sql = "SET SQL_MAX_JOIN_SIZE = 3000000000000000";
				$query = $this->db->query($sql);			
			}
		
			$sql = "SELECT p.product_id, pd.name, p.sku, p.model, p.image, p.price FROM " . DB_PREFIX . "product p  LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
			
			if ($data['filter_image_all'] == '1') {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_image pi ON (p.product_id=pi.product_id)";		
			}
			
			if($data['filter_attribute'] == '1') {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_attribute pa ON (p.product_id = pa.product_id)" ;
			}
			
			if (!empty($data['filter_category'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)" ;
			}		
			
			if (!empty($data['filter_url']) OR !empty($data['filter_url_empty'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "parser_urls pu ON (p.product_id = pu.product_id)" ;
			}	
			
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'"; 		
			
			if ($data['filter_attribute'] == '1') {
				$sql .= " AND pa.product_id IS NULL "; 
			}
			
			if (isset ($data['filter_url']) AND $data['filter_url'] == '1') {
				if($data['source'] == '1')
					$sql .= " AND pu.url_yandex != '' "; 
				if($data['source'] == '2')
					$sql .= " AND pu.url_hotline != '' ";
				if($data['source'] == '3')
					$sql .= " AND pu.url_onliner != '' "; 
				if($data['source'] == '4')
					$sql .= " AND pu.url_mail != '' ";
			}
			
			if (isset ($data['filter_url_empty']) AND $data['filter_url_empty'] == '1') {
				if($data['source'] == '1')
					$sql .= " AND pu.url_yandex = '' "; 
				if($data['source'] == '2')
					$sql .= " AND pu.url_hotline = '' ";
				if($data['source'] == '3')
					$sql .= " AND pu.url_onliner = '' "; 	
				if($data['source'] == '4')
					$sql .= " AND pu.url_mail = '' ";
			}
			
			
			if (isset ($data['filter_image_all']) AND $data['filter_image_all'] == '1') {
				$sql .= " AND pi.product_id IS NULL ";
			}
			
			if (isset ($data['filter_image_main']) AND $data['filter_image_main'] == '1') {
				$sql .= " AND p.image='' ";	
			}
			
			if (isset ($data['filter_onproduct']) AND $data['filter_onproduct'] == '1') {
				$sql .= " AND p.status='1' ";	
			}
			
			if (isset ($data['filter_sku']) AND $data['filter_sku'] == '1') {
				$sql .= " AND p.sku ='' "; 
			}
			
			if (isset ($data['filter_price']) AND $data['filter_price'] == '1') {
				$sql .= " AND p.price = 0 "; 
			}
			
			if (isset ($data['filter_description']) AND $data['filter_description'] == '1') {
				$sql .= " AND pd.description ='' "; 
			}
			
			if (!empty($data['filter_category'] )) {
				$sql .= " AND p2c.category_id = " . $this->db->escape(utf8_strtolower($data['filter_category'])); 				
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " AND LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
				 
			}
			
			if (!empty($data['filter_sku_exist'])) {
				$sql .= " AND LCASE(p.sku) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_sku_exist'])) . "%'";				 
			}
			
			if (!empty($data['filter_model'])) {
				$sql .= " AND LCASE(p.model) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_model'])) . "%'";				 
			}		
				
			if (!empty($data['filter_time']) AND $data['filter_time'] != date("Y-m-d H:i:s")) {
				$sql .= " AND p.date_modified < '".$data['filter_time']."'" ;
			}	
		
			
			$sql .= " GROUP BY product_id ";
			
			$sql .= " ORDER  BY pd.name ASC ";
			
			if (isset($data['start']) || isset($data['limit'])) {
					if ($data['start'] < 0) {
						$data['start'] = 0;
					}				

					if ($data['limit'] < 1) {
						$data['limit'] = 20;
					}	
				
					$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}	
				
			$query = $this->db->query($sql);
			return $query->rows;
				
	}
	
	public function getTotalProducts($data = array()){
			$query = $this->db->query("SHOW GLOBAL VARIABLES WHERE variable_name = 'SQL_MAX_JOIN_SIZE'");			
			if (!empty($query->rows)){			
				$sql = "SET SQL_MAX_JOIN_SIZE = 3000000000000000";
				$query = $this->db->query($sql);			
			}
	
			$sql = "SELECT p.product_id, pd.name, p.sku, p.model, p.image, p.price FROM " . DB_PREFIX . "product p  LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
			
			if ($data['filter_image_all'] == '1') {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_image pi ON (p.product_id=pi.product_id)";		
			}
			
			if($data['filter_attribute'] == '1') {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_attribute pa ON (p.product_id = pa.product_id)" ;
			}
			
			if (!empty($data['filter_category'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)" ;
			}
			
			if (!empty($data['filter_url']) OR !empty($data['filter_url_empty'])) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "parser_urls pu ON (p.product_id = pu.product_id)" ;
			}	
			
						
			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'"; 		
			
			if ($data['filter_attribute'] == '1') {
				$sql .= " AND pa.product_id IS NULL "; 
			}
				
			if ($data['filter_url'] == '1') {
				if($data['source'] == '1')
					$sql .= " AND pu.url_yandex != '' "; 
				if($data['source'] == '2')
					$sql .= " AND pu.url_hotline != '' ";
				if($data['source'] == '3')
					$sql .= " AND pu.url_onliner != '' "; 	
				if($data['source'] == '4')
					$sql .= " AND pu.url_mail != '' ";
			}	
			
			if (isset ($data['filter_url_empty']) AND $data['filter_url_empty'] == '1') {
				if($data['source'] == '1')
					$sql .= " AND pu.url_yandex = '' "; 
				if($data['source'] == '2')
					$sql .= " AND pu.url_hotline = '' ";
				if($data['source'] == '3')
					$sql .= " AND pu.url_onliner = '' "; 
				if($data['source'] == '4')
					$sql .= " AND pu.url_mail = '' ";
			}			
			
			if (isset ($data['filter_image_all']) AND $data['filter_image_all'] == '1') {
				$sql .= " AND pi.product_id IS NULL ";
			}
			
			if (isset ($data['filter_image_main']) AND $data['filter_image_main'] == '1') {
				$sql .= " AND p.image='' ";	
			}
			
			if (isset ($data['filter_onproduct']) AND $data['filter_onproduct'] == '1') {
				$sql .= " AND p.status='1' ";	
			}
			
			if (isset ($data['filter_sku']) AND $data['filter_sku'] == '1') {
				$sql .= " AND p.sku ='' "; 
			}
			
			if (isset ($data['filter_price']) AND $data['filter_price'] == '1') {
				$sql .= " AND p.price = 0 "; 
			}
			
			if (isset ($data['filter_description']) AND $data['filter_description'] == '1') {
				$sql .= " AND pd.description ='' "; 
			}
			
			if (!empty($data['filter_category'])) {
				$sql .= " AND p2c.category_id = " . $this->db->escape(utf8_strtolower($data['filter_category'])); 				
			}
			
			if (!empty($data['filter_name'])) {
				$sql .= " AND LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
				 
			}		
			
			if (!empty($data['filter_model'])) {
				$sql .= " AND LCASE(p.model) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_model'])) . "%'";				 
			}	
			
			if (!empty($data['filter_sku_exist'])) {
				$sql .= " AND LCASE(p.sku) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_sku_exist'])) . "%'";				 
			}
			
							
			if (!empty($data['filter_time']) AND $data['filter_time'] != date("Y-m-d H:i:s")) {
				$sql .= " AND p.date_modified < '".$data['filter_time']."'" ;
			}			
			
			
			$sql .= " GROUP BY product_id";	
			$query = $this->db->query($sql);
			return $query->num_rows;
				
	}

	public function updateParserUrls(){		
		$result = '';	
		$query = $this->db->query('SELECT * FROM `' . DB_PREFIX .'parser_urls`  LIMIT 1');
		if(!isset($query->row['url_mail'])){
			$sql = 'ALTER TABLE `' . DB_PREFIX . 'parser_urls` ADD COLUMN `url_mail` text NOT NULL';
			if($this->db->query($sql)){
				$result .= "Поле 'url_mail' добавлено<br>";
			} else {
				$result .= "Поле 'url_mail' не добавлено<br>";
			}
		}
		
		$query_parse_url = $this->db->query('SELECT product_id FROM `' . DB_PREFIX . 'parser_urls`');
		$count_delete = 0;
		foreach($query_parse_url->rows as $row){			
			$q = $this->db->query('SELECT product_id FROM `' . DB_PREFIX .'product` WHERE product_id='.(int) $row['product_id']);
			if($q->num_rows==0){
				$this->db->query("DELETE FROM " . DB_PREFIX . "parser_urls WHERE product_id = '" . (int) $row['product_id'] . "'");
				$count_delete++;		
			}					
		}
		$result .= "Удалено несуществующих товаров из таблицы ссылок: " .$count_delete. "<br>";
		$count_add = 0;
		$query = $this->db->query('SELECT product_id FROM `' . DB_PREFIX .'product`');
			
		foreach($query->rows as $row){			
			$q = $this->db->query('SELECT product_id FROM `' . DB_PREFIX .'parser_urls` WHERE product_id='.(int) $row['product_id']);
			if($q->num_rows==0){
				$this->db->query("INSERT INTO " . DB_PREFIX . "parser_urls SET product_id=" . (int) $row['product_id']);
				$count_add++;		
			}					
		}		
		$result .= "Добавлено  товаров в таблицу ссылок: " .$count_add. "<br>";
		
  		return $result;
	}
	
	public function getProductParseUrls($product_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "parser_urls WHERE product_id=" . (int)$product_id);
		return $query->row;
	}	
	
	public function addProductParseUrls($product_id){
		$query = $this->db->query("INSERT INTO " . DB_PREFIX . "parser_urls SET product_id=" . (int)$product_id);
	}
	
	public function insertUrlYandex($product_id, $url_parsing){		
		$this->db->query("UPDATE " . DB_PREFIX . "parser_urls SET url_yandex='" . $this->db->escape($url_parsing) . "' WHERE product_id = '" . (int)$product_id . "'");
	}
	
	public function insertUrlHotline($product_id, $url_parsing){		
		$this->db->query("UPDATE " . DB_PREFIX . "parser_urls SET url_hotline='" . $this->db->escape($url_parsing) . "' WHERE product_id = '" . (int)$product_id . "'");
	}
	
	public function insertUrlOnliner($product_id, $url_parsing){		
		$this->db->query("UPDATE " . DB_PREFIX . "parser_urls SET url_onliner='" . $this->db->escape($url_parsing) . "' WHERE product_id = '" . (int)$product_id . "'");
	}
	
	public function insertUrlMail($product_id, $url_parsing){
		$this->db->query("UPDATE " . DB_PREFIX . "parser_urls SET url_mail='" . $this->db->escape($url_parsing) . "' WHERE product_id = '" . (int)$product_id . "'");
	}
	
	public function deleteUrlYandex($product_id){	
		$this->db->query("UPDATE " . DB_PREFIX . "parser_urls SET url_yandex='' WHERE product_id = '" . (int)$product_id . "'");
	}
	
	public function deleteUrlHotline($product_id){	
		$this->db->query("UPDATE " . DB_PREFIX . "parser_urls SET url_hotline='' WHERE product_id = '" . (int)$product_id . "'");
	}
	
	public function deleteUrlOnliner($product_id){	
		$this->db->query("UPDATE " . DB_PREFIX . "parser_urls SET url_onliner='' WHERE product_id = '" . (int)$product_id . "'");
	}
	
	public function deleteUrlMail($product_id){
		$this->db->query("UPDATE " . DB_PREFIX . "parser_urls SET url_mail='' WHERE product_id = '" . (int)$product_id . "'");
	}

	public function createParserUrls(){
		$sql = 'CREATE TABLE  IF NOT EXISTS `' . DB_PREFIX .'parser_urls` (
			  `product_id` int(11) NOT NULL,
			  `url_yandex` text NOT NULL,
			  `url_hotline` text NOT NULL,
			  `url_onliner` text NOT NULL,
			  `url_mail` text NOT NULL,
			  PRIMARY KEY (`product_id`)
			  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
		$this->db->query($sql);
	}

	public function createTableUrls(){
		$sql = 'CREATE TABLE  IF NOT EXISTS `' . DB_PREFIX .'table_urls` (
			  `product_id` int(11) NOT NULL,
			  `url` text NOT NULL
			  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';			  
		$this->db->query($sql);	  		
	}

	public function deleteParserUrlsTable(){
		$sql = "DROP TABLE IF EXISTS `" . DB_PREFIX . "parser_urls`";
		$this->db->query($sql);
	}

	public function deleteTableUrls(){
		$sql = "DROP TABLE IF EXISTS `" . DB_PREFIX ."table_urls`";
		$this->db->query($sql);
	}

	public function insertTableUrl($product_id, $url){	
		$this->db->query("INSERT INTO " . DB_PREFIX . "table_urls SET product_id = '" . (int) $product_id . "', url = '" . $this->db->escape($url). "'");
	}
	
	public function getTableUrls(){	
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "table_urls");
		return $query->rows;
	}
	
	public function getTableUrlRows(){	
		$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "table_urls");
		return $query->num_rows;
	}
	
	public function deleteProductTableUrl($product_id){	
		$query = $this->db->query("DELETE FROM " . DB_PREFIX . "table_urls WHERE product_id = ". (int) $product_id);
	}
	
	public function deleteTableUrlALL(){	
		$query = $this->db->query("TRUNCATE TABLE " . DB_PREFIX . "table_urls");
	}
///////////////Таблица хранения ссылок на продукты при парсинге всех продуктов////////////////////Конец


	public function getCategoryName($name, $parent_id = 0){
		$query = $this->db->query("SELECT c.category_id, c.parent_id, cd.name, c.image FROM " . DB_PREFIX . "category c  LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE cd.name = '" . $this->db->escape($name) . "' AND c.parent_id = ". (int) $parent_id);
		return $query->row;
	}
	
	public function getManufactureName($name){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer WHERE name = '" . $this->db->escape($name) . "'");
		return $query->row;
	}
	
	public function getProductName($name){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE name = '" . $this->db->escape($name) . "'");
		return $query->row;
	}
	
}
?>