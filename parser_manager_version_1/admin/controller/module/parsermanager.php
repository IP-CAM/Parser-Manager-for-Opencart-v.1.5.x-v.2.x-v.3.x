<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
header('Content-Type: text/html; charset=UTF-8');
define('PARSER_LOG', DIR_LOGS . 'parser.log');
define('PROXY_LIST', DIR_DOWNLOAD . 'proxy_list.txt');
define('DIR_IMAGE_PARSER', DIR_IMAGE);
require_once(DIR_SYSTEM . 'library/parsermanager/simple_html_dom.php');
require_once(DIR_SYSTEM . 'library/parsermanager/MCurl.php');

class ControllerModuleparsermanager extends Controller
{
    private $error = array();
    private $page;
    private $dir;
    private $url_market = '';
    protected static $_instance;
    private $manufacture_array = array('name' => '', 'manufacturer_store' => array('0' => '0'), 'keyword' => '', 'image' => '', 'sort_order' => '');

    public function install() {
        $this->load->model('module/parser');
        $this->model_module_parser->createParserUrls();
        $this->model_module_parser->createTableUrls();
        $this->session->data['success'] = 'Модуль Парсер-менеджер установлен!';
    }

    public function uninstall() {
        $this->load->model('module/parser');
        $this->model_module_parser->deleteParserUrlsTable();
        $this->model_module_parser->deleteParserUrlsTable();
        $this->session->data['success'] = 'Модуль Парсер-менеджер удален!';
    }

    public function replace_sym($string)
    {
        $data = preg_replace_callback('/[^A-Za-z0-9_]/', function ($matches) {
            return '_';
        }, htmlentities($this->rus2translit($this->clearshar($string))));
        $data = preg_replace_callback('/_{2,}/', function ($matches) {
            return '_';
        }, $data);
        if (strlen($string) > 100) {
            $data = substr($data, 0, 100);
        }
        return $data;
    }

    public function replace_yandex($string)
    {
        $converter = array(
            '%20' => '+',
			'%2F' => '+',
            '%C2%A0' => '+',
            '%26lt' => '',
            '%26gt' => '',
            ' ' => '+'
        );
        return strtr($string, $converter);
    }

    public function delete_title($title, $pattern)
    {
        $title = str_replace($pattern, '', $title);
        return $title;
    }

    public function clearshar($str)
    {
        $search = array(
            "'<script[^>]*?>.*?</script>'si",
            "'<[\/\!]*?[^<>]*?>'si",
            "'([\r\n])[\s]+'",
            "'&(quot|#34);'i",
            "'&(amp|#38);'i",
            "'&(lt|#60);'i",
            "'&(gt|#62);'i",
            "'&(nbsp|#160);'i",
            "'&(iexcl|#161);'i",
            "'&(cent|#162);'i",
            "'&(pound|#163);'i",
            "'&(copy|#169);'i",
            "'&#(\d+);'i",
            "'novinka'i"
        );
        return preg_replace_callback($search, function ($matches) {
            return '';
        }, $str);
    }

    private function hex2rgb($color)
    {
        if ($color[0] == '#')
            $color = substr($color, 1);
        if (strlen($color) == 6)
            list($r, $g, $b) = array(
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5]
            );
        elseif (strlen($color) == 3)
            list($r, $g, $b) = array(
                $color[0] . $color[0],
                $color[1] . $color[1],
                $color[2] . $color[2]
            );
        else
            return false;
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return array(
            $r,
            $g,
            $b
        );
    }

    private function rus2translit($string)
    {
        $converter = array(
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sch',
            'ь' => '',
            'ы' => 'y',
            'ъ' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Ё' => 'E',
            'Ж' => 'Zh',
            'З' => 'Z',
            'И' => 'I',
            'Й' => 'Y',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ф' => 'F',
            'Х' => 'H',
            'Ц' => 'C',
            'Ч' => 'Ch',
            'Ш' => 'Sh',
            'Щ' => 'Sch',
            'Ь' => '\'',
            'Ы' => 'Y',
            'Ъ' => '\'',
            'Э' => 'E',
            'Ю' => 'Yu',
            'Я' => 'Ya'
        );
        return strtr($string, $converter);
    }

    private function getAllCategories($categories, $parent_id = 0, $parent_name = '')
    {
        $output = array();
        if (array_key_exists($parent_id, $categories)) {
            if ($parent_name != '') {
                $parent_name .= $this->language->get('text_separator');
            }
            foreach ($categories[$parent_id] as $category) {
                $output[$category['category_id']] = array(
                    'category_id' => $category['category_id'],
                    'name' => $parent_name . $category['name']
                );
                $output += $this->getAllCategories($categories, $category['category_id'], $parent_name . $category['name']);
            }
        }
        return $output;
    }

    public function index()
    {
        $this->load->language('module/parsermanager');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['button_addfield'] = $this->language->get('button_addfield');
        $this->data['button_deletefield'] = $this->language->get('button_deletefield');
        $this->data['column_image'] = $this->language->get('column_image');
        $this->data['column_name'] = $this->language->get('column_name');
        $this->data['entry_manufacturer'] = $this->language->get('entry_manufacturer');
        $this->data['entry_main_category'] = $this->language->get('entry_main_category');
        $this->data['entry_category'] = $this->language->get('entry_category');
        $this->data['entry_store'] = $this->language->get('entry_store');
        $this->data['text_select_all'] = $this->language->get('text_select_all');
        $this->data['text_unselect_all'] = $this->language->get('text_unselect_all');
        $this->data['text_none'] = $this->language->get('text_none');
        $this->data['text_default'] = $this->language->get('text_default');
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['base'] = HTTPS_SERVER;
        } else {
            $this->data['base'] = HTTP_SERVER;
        }
		
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('module/parsermanager', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/parsermanager', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
        if (isset($this->session->data['error'])) {
            $this->data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        }
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }
        $this->load->model('setting/setting');	
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()
				&& isset($this->request->post['parsermanager_setting']['save_setting'])) {	
				
			$tmp_setting = $this->request->post;
			$setting = $this->model_setting_setting->getSetting('parsermanager');
			
			if(isset($this->request->post['parsermanager_setting']['create_dir_image'])){
			
				$this->load->model('catalog/category');
				if((VERSION == '1.5.1.3') OR (VERSION == '1.5.3.1') OR(VERSION == '1.5.4.1')){
					$categories = $this->model_catalog_category->getAllCategories();
					$tmp_categories = $this->getAllCategories($categories);	
				}else{
					$tmp_categories = $this->model_catalog_category->getCategories(0);
				}	
			
				if (isset($this->request->post['parsermanager_setting']['product_category'])) {
					$tmp_product_category = $this->request->post['parsermanager_setting']['product_category'];		
				} else {
					$tmp_product_category = array();
				}					
					
				foreach ($tmp_categories as $category){
					if (isset($tmp_product_category[0]) AND ($category['category_id'] == $tmp_product_category[0])){						
						$path = explode("_gt_", $this->replace_sym($this->rus2translit($category['name'])));
						$new_dir_image = DIR_IMAGE_PARSER.'data/';
						$j = 0;
						foreach ($path as $d){					
							$new_dir_image .= $d . '/' ;					
							if (!is_dir($new_dir_image) AND $j < 5){
								mkdir($new_dir_image, 0777);						
							}	
							$j++;									
						}
						$tmp_setting['parsermanager_setting']['dir_to'] = $new_dir_image;
					}
				}			 
			}	
			
			$setting[$this->user->getUserName().'_parsermanager_setting'] =  $tmp_setting['parsermanager_setting'];
			$this->model_setting_setting->editSetting('parsermanager', $setting);  
			$this->session->data['success'] = $this->language->get('savesatting');
            $this->redirect($this->url->link('module/parsermanager', 'token=' . $this->session->data['token'], 'SSL'));
		}	
		
		$this->data['setting'] = $this->config->get($this->user->getUserName().'_parsermanager_setting');
		
        $this->load->model('catalog/category');
        if ((VERSION == '1.5.1.3') OR (VERSION == '1.5.3.1') OR (VERSION == '1.5.4.1')) {
            $categories = $this->model_catalog_category->getAllCategories();
            $this->data['categories'] = $this->getAllCategories($categories);
        } else {
            $this->data['categories'] = $this->model_catalog_category->getCategories(0);
        }
        if (isset($this->data['setting']['main_category_id'])) {
            $this->data['main_category_id'] = $this->data['setting']['main_category_id'];
        } else {
            $this->data['main_category_id'] = 0;
        }
        if (isset($this->data['setting']['product_category'])) {
            $this->data['product_category'] = $this->data['setting']['product_category'];
        } else {
            $this->data['product_category'] = array();
        }
        $this->load->model('catalog/manufacturer');
        $this->data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();
        if (isset($this->data['setting']['manufacturer_id'])) {
            $this->data['manufacturer_id'] = $this->data['setting']['manufacturer_id'];
        } else {
            $this->data['manufacturer_id'] = 0;
        }
        $this->load->model('setting/store');
        $this->data['stores'] = $this->model_setting_store->getStores();
        if (isset($this->request->post['product_store'])) {
            $this->data['product_store'] = $this->request->post['product_store'];
        } elseif (isset($this->request->get['product_id'])) {
            $this->data['product_store'] = $this->model_catalog_product->getProductStores($this->request->get['product_id']);
        } else {
            $this->data['product_store'] = array(
                0
            );
        }
        if (isset($this->data['setting']['dir_to']) && (!empty($this->data['setting']['dir_to']))) {
            $this->data['setting']['dir_to'] = trim($this->data['setting']['dir_to']);
            $this->dir = trim(str_replace(DIR_IMAGE_PARSER, ' ', rtrim($this->data['setting']['dir_to'], '/'))) . '/';
        } else {
            $this->data['setting']['dir_to'] = DIR_IMAGE_PARSER . 'data/';
            $this->dir = trim(str_replace(DIR_IMAGE_PARSER, ' ', rtrim(DIR_IMAGE_PARSER . 'data/', '/'))) . '/';
        }
        $this->load->model('module/parser');
        $this->data['regions'] = array(
            'Москва' => '213',
            'Минск' => '157',
            'Киев' => '143',
            'Алматы' => '162'
        );
        $this->data['region_set'] = '';
        if (isset($this->data['setting']['region'])) {
            $this->data['region_set'] = $this->data['setting']['region'];
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'] - 1;
        } else {
            $page = 0;
        }
        $filter2url = '';
        if (isset($this->request->get['filter_name']) AND !empty($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
            $this->data['setting']['filter_name'] = $filter_name;
            $filter2url .= '&filter_name=' . $filter_name;
        } else {
            $filter_name = '';
            $this->data['setting']['filter_name'] = $filter_name;
        }
        if (isset($this->request->get['filter_sku_exist']) AND !empty($this->request->get['filter_sku_exist'])) {
            $filter_sku_exist = $this->request->get['filter_sku_exist'];
            $this->data['setting']['filter_sku_exist'] = $filter_sku_exist;
            $filter2url .= '&filter_sku_exist=' . $filter_sku_exist;
        } else {
            $filter_sku_exist = '';
            $this->data['setting']['filter_sku_exist'] = $filter_sku_exist;
        }
        if (isset($this->request->get['filter_model']) AND !empty($this->request->get['filter_model'])) {
            $filter_model = $this->request->get['filter_model'];
            $this->data['setting']['filter_model'] = $filter_model;
            $filter2url .= '&filter_model=' . $filter_model;
        } else {
            $filter_model = '';
            $this->data['setting']['filter_model'] = $filter_model;
        }

        if ($this->model_module_parser->getTableUrlRows() > 0) {
            $this->data['error_table'] = 1;
        }
        $data = array(
            'filter_image_main' => isset($this->data['setting']['filter_image_main']) ? $this->data['setting']['filter_image_main'] : '',
            'filter_image_all' => isset($this->data['setting']['filter_image_all']) ? $this->data['setting']['filter_image_all'] : '',
            'filter_attribute' => isset($this->data['setting']['filter_attribute']) ? $this->data['setting']['filter_attribute'] : '',
            'filter_description' => isset($this->data['setting']['filter_description']) ? $this->data['setting']['filter_description'] : '',
            'filter_sku' => isset($this->data['setting']['filter_sku']) ? $this->data['setting']['filter_sku'] : '',
            'filter_url' => isset($this->data['setting']['filter_url']) ? $this->data['setting']['filter_url'] : '',
            'filter_url_empty' => isset($this->data['setting']['filter_url_empty']) ? $this->data['setting']['filter_url_empty'] : '',
            'filter_model' => isset($this->data['setting']['filter_model']) ? $this->data['setting']['filter_model'] : '',
            'filter_onproduct' => isset($this->data['setting']['filter_onproduct']) ? $this->data['setting']['filter_onproduct'] : '',
            'start' => isset($this->data['setting']['countproduct']) ? $page * (int)$this->data['setting']['countproduct'] : 0,
            'limit' => isset($this->data['setting']['countproduct']) ? $this->data['setting']['countproduct'] : 10,
            'filter_category' => isset($this->data['setting']['filter_category']) ? $this->data['setting']['filter_category'] : array(),
            'filter_price' => isset($this->data['setting']['filter_price']) ? $this->data['setting']['filter_price'] : '',
            'filter_time' => isset($this->data['setting']['time']) ? date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) - (int)$this->data['setting']['time'] * 60) : date("Y-m-d H:i:s"),
            'filter_name' => $filter_name,
            'filter_sku_exist' => $filter_sku_exist,
            'source' => isset($this->data['setting']['source']) ? $this->data['setting']['source'] : ''
        );
        $results = $this->model_module_parser->SearchEmpty($data);
        $product_total = $this->model_module_parser->getTotalProducts($data);
        $this->load->model('tool/image');
        $this->data['products'] = array();
        foreach ($results as $result) {
            $url_parsing = '';
            if ($result['image'] && file_exists(DIR_IMAGE_PARSER . $result['image'])) {
                $image = $this->model_tool_image->resize($result['image'], 40, 40);
            } else {
                $image = $this->model_tool_image->resize('no_image.jpg', 40, 40);
            }
            $url_parsing = $this->getUrlProduct($result['product_id']);
            $this->data['products'][] = array(
                'product_id' => $result['product_id'],
                'name' => $result['name'],
                'model' => $result['model'],
                'price' => $result['price'],
                'sku' => $result['sku'],
                'image' => $image,
                'url_parsing' => $url_parsing
            );
        }
        $this->load->model('catalog/product');
        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = (string)($page + 1);
        $pagination->limit = isset($this->data['setting']['countproduct']) ? $this->data['setting']['countproduct'] : 10;
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('module/parsermanager', 'token=' . $this->session->data['token'] . '&page={page}' . $filter2url, 'SSL');
        $this->data['pagination'] = $pagination->render();
        $this->template = 'module/parsermanager.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    public function deleteUrl()
    {
        if (isset($this->request->get['product_id']) AND !empty($this->request->get['product_id'])) {
            $product_id = $this->request->get['product_id'];
            $this->load->model('module/parser');
            $this->data['setting'] = $this->config->get($this->user->getUserName().'_parsermanager_setting');
            if ($this->data['setting']['source'] == '1')
                $this->model_module_parser->deleteUrlYandex($product_id);
            if ($this->data['setting']['source'] == '2')
                $this->model_module_parser->deleteUrlHotline($product_id);
            if ($this->data['setting']['source'] == '3')
                $this->model_module_parser->deleteUrlOnliner($product_id);
            if ($this->data['setting']['source'] == '4')
                $this->model_module_parser->deleteUrlMail($product_id);
        }
    }

    public function addUrl()
    {
        if (isset($this->request->post['product_id']) AND !empty($this->request->post['product_id']) AND isset($this->request->post['url']) AND !empty($this->request->post['url'])) {
            $product_id = $this->request->post['product_id'];
            $url = trim(html_entity_decode(html_entity_decode(($this->request->post['url']))));
            $this->load->model('module/parser');
            $this->data['setting'] = $this->config->get($this->user->getUserName().'_parsermanager_setting');
            $product_total = $this->model_module_parser->getProductParseUrls($product_id);
            if (empty($product_total)) {
                $this->model_module_parser->addProductParseUrls($product_id);
            }
            if ($this->data['setting']['source'] == '1')
                $this->model_module_parser->insertUrlYandex($product_id, $url);
            if ($this->data['setting']['source'] == '2')
                $this->model_module_parser->insertUrlHotline($product_id, $url);
            if ($this->data['setting']['source'] == '3')
                $this->model_module_parser->insertUrlOnliner($product_id, $url);
            if ($this->data['setting']['source'] == '4')
                $this->model_module_parser->insertUrlMail($product_id, $url);
            echo $url;
        }
    }

    public function delete()
    {
        $selected = array();
        $this->load->language('catalog/product');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('catalog/product');
        $selected = explode(",", $this->request->get['selected']);
        if (isset($this->request->get['selected']) && $this->validateDelete()) {
            $selected = explode(",", $this->request->get['selected']);
            foreach ($selected as $product_id) {
                $this->model_catalog_product->deleteProduct($product_id);
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $url = '';
            if ((isset($this->request->get['page']) AND ($this->request->get['page']) != 'null')) {
                $url .= '&page=' . $this->request->get['page'];
            }
            $this->redirect($this->url->link('module/parsermanager', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }
    }

    public function clearTableUrls()
    {
        $this->load->model('module/parser');
        $this->model_module_parser->deleteTableUrlALL();
        echo 'Список очищен!';
    }

    public function parseProduct()
    {
		
        $this->load->language('module/parsermanager');
        $setting = $this->getSettingParser();        
        $this->dir = trim(str_replace(DIR_IMAGE_PARSER, ' ', rtrim($setting['dir_to'], '/'))) . '/';
        $filter = '';
        $url_parsing = '';
        if (isset($this->request->get['filter_name']) AND !empty($this->request->get['filter_name'])) {
            $filter .= '&filter_name=' . $this->request->get['filter_name'];
        }
        if (isset($this->request->get['filter_sku_exist']) AND !empty($this->request->get['filter_sku_exist'])) {
            $filter .= '&filter_sku_exist=' . $this->request->get['filter_sku_exist'];
        }
        if (isset($this->request->get['filter_model']) AND !empty($this->request->get['filter_model'])) {
            $filter .= '&filter_model=' . $this->request->get['filter_model'];
        }
        if (isset($this->request->get['page']) AND !empty($this->request->get['page'])) {
            $page = '&page=' . $this->request->get['page'];
        } else {
            $page = '';
        }
		
        if (isset($this->request->get['product_id']) AND !empty($this->request->get['product_id'])) {
            $product_id = trim($this->request->get['product_id']);
            $url_parsing = $this->getUrlProduct($product_id);
            if (empty($url_parsing)) {
                $this->session->data['error'] = 'Нет ссылки на товар!';
                $this->redirect($this->url->link('module/parsermanager', 'token=' . $this->session->data['token'] . $filter . $page, 'SSL'));
                exit;
            }
		
            if ($setting['source_setting']['productdata'] == '1') {
                $this->parsingProductForAdd($product_id, $url_parsing);
            }
            if ($setting['source_setting']['productdata'] == '0') {
                $this->parsingProductForReplace($product_id, $url_parsing);
            }
        }
        $this->redirect($this->url->link('module/parsermanager', 'token=' . $this->session->data['token'] . $filter . $page, 'SSL'));
        exit;
    }


    public function parserYandex($url)
    {
        require(DIR_SYSTEM . 'library/parsermanager/parser_yandex.php');		
		return $data;
    }	
	

    public function parserOnliner($url)
    {
        require(DIR_SYSTEM . 'library/parsermanager/parser_onliner.php');
		return $data;
    }
	
	 public function parserHotline($url)
    {
        require(DIR_SYSTEM . 'library/parsermanager/parser_hotline.php');
		return $data;
    }

    public function parserMail($url)
    {
        require(DIR_SYSTEM . 'library/parsermanager/parser_mail.php');
		return $data;
    }
	
	public function FindManagerByURlOnliner($url)
    {
       require(DIR_SYSTEM . 'library/parsermanager/search_onliner.php');
	   return $data['items'];
	}
	
	public function FindManagerByURlYandex($url)
    {       
	   require(DIR_SYSTEM . 'library/parsermanager/search_yandex.php');
	   return $data;
    }	
	
	public function FindManagerByURlMail($url)
    {  
		require(DIR_SYSTEM . 'library/parsermanager/search_mail.php');
		return $data['items'];
    }

	
    public function FindManagerByURlHotline($url)
    {
		require(DIR_SYSTEM . 'library/parsermanager/search_hotline.php');
		return $data['items'];
    }   

   
   
    public function parsingProductForReplace($product_id, $url_parsing)
    {
        $this->load->language('module/parsermanager');
        $setting = $this->getSettingParser();
        if ($setting['source'] == '1') {
            if (!empty($product_id) AND !empty($url_parsing)) {
                $data = $this->parserYandex($url_parsing);				
                if ($data) {
                    if (!isset($setting['source_setting']['price']) OR (int)$data['price'] == 0) {
                        if (isset($this->request->get['price'])) {
                            $data['price'] = (int)$this->request->get['price'];
                        } elseif (isset($this->request->post['selected'][$product_id]['price'])) {
                            $data['price'] = (int)$this->request->post['selected'][$product_id]['price'];
                        }
                    }
                    if (!isset($setting['source_setting']['sku'])) {
                        if (isset($this->request->get['sku'])) {
                            $data['sku'] = $this->request->get['sku'];
                        } elseif (isset($this->request->post['selected'][$product_id]['sku'])) {
                            $data['sku'] = $this->request->post['selected'][$product_id]['sku'];
                        }
                    }
                    $this->updateProductAndReplace($product_id, $data);
                    $this->session->data['success'] = $this->language->get('text_success');
                } else {
                    $this->session->data['error'] = $this->language->get('error_find');
                    return false;
                }
            }
        }
        if ($setting['source'] == '2') {
            if (!empty($product_id) AND !empty($url_parsing)) {
                $data = $this->parserHotline($url_parsing);
                if ($data) {
                    if (!isset($setting['source_setting']['price']) OR (int)$data['price'] == 0) {
                        if (isset($this->request->get['price'])) {
                            $data['price'] = (int)$this->request->get['price'];
                        } elseif (isset($this->request->post['selected'][$product_id]['price'])) {
                            $data['price'] = (int)$this->request->post['selected'][$product_id]['price'];
                        }
                    }
                    if (!isset($setting['source_setting']['sku']) OR $data['sku'] == '') {
                        if (isset($this->request->get['sku'])) {
                            $data['sku'] = $this->request->get['sku'];
                        } elseif (isset($this->request->post['selected'][$product_id]['sku'])) {
                            $data['sku'] = $this->request->post['selected'][$product_id]['sku'];
                        }
                    }
                    $this->updateProductAndReplace($product_id, $data);
                    $this->session->data['success'] = $this->language->get('text_success');
                } else {
                    $this->session->data['error'] = $this->language->get('error_find');
                    return false;
                }
            }
        }
        if ($setting['source'] == '3') {
            if (!empty($product_id) AND !empty($url_parsing)) {
                $data = $this->parserOnliner($url_parsing);
                if ($data) {
                    if (!isset($setting['source_setting']['price']) OR (int)$data['price'] == 0) {
                        if (isset($this->request->get['price'])) {
                            $data['price'] = $this->request->get['price'];
                        } elseif (isset($this->request->post['selected'][$product_id]['price'])) {
                            $data['price'] = $this->request->post['selected'][$product_id]['price'];
                        }
                    }
                    if (!isset($setting['source_setting']['sku']) OR $data['sku'] == '') {
                        if (isset($this->request->get['sku'])) {
                            $data['sku'] = $this->request->get['sku'];
                        } elseif (isset($this->request->post['selected'][$product_id]['sku'])) {
                            $data['sku'] = $this->request->post['selected'][$product_id]['sku'];
                        }
                    }
                    $this->updateProductAndReplace($product_id, $data);
                    $this->session->data['success'] = $this->language->get('text_success');
                } else {
                    $this->session->data['error'] = $this->language->get('error_find');
                    return false;
                }
            }
        }
        if ($setting['source'] == '4') {
            if (!empty($product_id) AND !empty($url_parsing)) {
                $data = $this->parserMail($url_parsing);
				
                if ($data) {
                    if (!isset($setting['source_setting']['price']) OR (int)$data['price'] == 0) {
                        if (isset($this->request->get['price'])) {
                            $data['price'] = (int)$this->request->get['price'];
                        } elseif (isset($this->request->post['selected'][$product_id]['price'])) {
                            $data['price'] = (int)$this->request->post['selected'][$product_id]['price'];
                        }
                    }
                    if (!isset($setting['source_setting']['sku']) OR $data['sku'] == '') {
                        if (isset($this->request->get['sku'])) {
                            $data['sku'] = $this->request->get['sku'];
                        } elseif (isset($this->request->post['selected'][$product_id]['sku'])) {
                            $data['sku'] = $this->request->post['selected'][$product_id]['sku'];
                        }
                    }
                    $this->updateProductAndReplace($product_id, $data);
                    $this->session->data['success'] = $this->language->get('text_success');
                } else {
                    $this->session->data['error'] = $this->language->get('error_find');
                    return false;
                }
            }
        }
        return true;
    }

    public function parsingProductForAdd($product_id, $url_parsing)
    {
        $this->load->language('module/parsermanager');
        $setting = $this->getSettingParser();
        if ($setting['source'] == '1') {
            if (!empty($product_id) AND !empty($url_parsing)) {
				
                $data = $this->parserYandex($url_parsing);
				
                if ($data) {
                    if (!isset($setting['source_setting']['price']) OR (int)$data['price'] == 0) {
                        if (isset($this->request->get['price'])) {
                            $data['price'] = (int)$this->request->get['price'];
                        } elseif (isset($this->request->post['selected'][$product_id]['price'])) {
                            $data['price'] = (int)$this->request->post['selected'][$product_id]['price'];
                        }
                    }
                    if (!isset($setting['source_setting']['sku'])) {
                        if (isset($this->request->get['sku'])) {
                            $data['sku'] = $this->request->get['sku'];
                        } elseif (isset($this->request->post['selected'][$product_id]['sku'])) {
                            $data['sku'] = $this->request->post['selected'][$product_id]['sku'];
                        }
                    }
                    $this->updateProductAndAdd($product_id, $data);
                    $this->session->data['success'] = $this->language->get('text_success');
                } else {
                    $this->session->data['error'] = $this->language->get('error_find');
                    return false;
                }
            }
        }
        if ($setting['source'] == '2') {
            if (!empty($product_id) AND !empty($url_parsing)) {
                $data = $this->parserHotline($url_parsing);
                if ($data) {
                    if (!isset($setting['source_setting']['price']) OR (int)$data['price'] == 0) {
                        if (isset($this->request->get['price'])) {
                            $data['price'] = (int)$this->request->get['price'];
                        } elseif (isset($this->request->post['selected'][$product_id]['price'])) {
                            $data['price'] = (int)$this->request->post['selected'][$product_id]['price'];
                        }
                    }
                    if (!isset($setting['source_setting']['sku'])) {
                        if (isset($this->request->get['sku'])) {
                            $data['sku'] = $this->request->get['sku'];
                        } elseif (isset($this->request->post['selected'][$product_id]['sku'])) {
                            $data['sku'] = $this->request->post['selected'][$product_id]['sku'];
                        }
                    }
                    $this->updateProductAndAdd($product_id, $data);
                    $this->session->data['success'] = $this->language->get('text_success');
                } else {
                    $this->session->data['error'] = $this->language->get('error_find');
                    return false;
                }
            }
        }
        if ($setting['source'] == '3') {
            if (!empty($product_id) AND !empty($url_parsing)) {
                $data = $this->parserOnliner($url_parsing);
                if ($data) {
                    if (!isset($setting['source_setting']['price']) OR (int)$data['price'] == 0) {
                        if (isset($this->request->get['price'])) {
                            $data['price'] = $this->request->get['price'];
                        } elseif (isset($this->request->post['selected'][$product_id]['price'])) {
                            $data['price'] = $this->request->post['selected'][$product_id]['price'];
                        }
                    }
                    if (!isset($setting['source_setting']['sku'])) {
                        if (isset($this->request->get['sku'])) {
                            $data['sku'] = $this->request->get['sku'];
                        } elseif (isset($this->request->post['selected'][$product_id]['sku'])) {
                            $data['sku'] = $this->request->post['selected'][$product_id]['sku'];
                        }
                    }
                    $this->updateProductAndAdd($product_id, $data);
                    $this->session->data['success'] = $this->language->get('text_success');
                } else {
                    $this->session->data['error'] = $this->language->get('error_find');
                    return false;
                }
            }
        }
        if ($setting['source'] == '4') {
            if (!empty($product_id) AND !empty($url_parsing)) {
                $data = $this->parserMail($url_parsing);
				if ($data) {
                    if (!isset($setting['source_setting']['price']) OR (int)$data['price'] == 0) {
                        if (isset($this->request->get['price'])) {
                            $data['price'] = (int)$this->request->get['price'];
                        } elseif (isset($this->request->post['selected'][$product_id]['price'])) {
                            $data['price'] = (int)$this->request->post['selected'][$product_id]['price'];
                        }
                    }
                    if (!isset($setting['source_setting']['sku'])) {
                        if (isset($this->request->get['sku'])) {
                            $data['sku'] = $this->request->get['sku'];
                        } elseif (isset($this->request->post['selected'][$product_id]['sku'])) {
                            $data['sku'] = $this->request->post['selected'][$product_id]['sku'];
                        }
                    }
                    $this->updateProductAndAdd($product_id, $data);
                    $this->session->data['success'] = $this->language->get('text_success');
                } else {
                    $this->session->data['error'] = $this->language->get('error_find');
                    return false;
                }
            }
        }
        return true;
    }


    public function searchAllProducts()
    {
        $setting = $this->getSettingParser();
        $this->load->model('module/parser');
        $this->load->model('catalog/product');
        if (isset($setting['region'])) {
            if ($setting['region'] == '213') {
                $this->url_market = 'https://market.yandex.ru';
            }
            if ($setting['region'] == '143') {
                $this->url_market = 'https://market.yandex.ua';
            }
            if ($setting['region'] == '157') {
                $this->url_market = 'https://market.yandex.by';
            }
            if ($setting['region'] == '162') {
                $this->url_market = 'https://market.yandex.kz';
            }
        }
        $filter = '';
        $page = '';
        if (isset($this->request->get['filter_name']) AND !empty($this->request->get['filter_name'])) {
            $filter .= '&filter_name=' . $this->request->get['filter_name'];
        }
        if (isset($this->request->get['filter_sku_exist']) AND !empty($this->request->get['filter_sku_exist'])) {
            $filter .= '&filter_sku_exist=' . $this->request->get['filter_sku_exist'];
        }
        if (isset($this->request->get['filter_model']) AND !empty($this->request->get['filter_model'])) {
            $filter .= '&filter_model=' . $this->request->get['filter_model'];
        }
        if (isset($this->request->get['page']) AND !empty($this->request->get['page'])) {
            $page = '&page=' . $this->request->get['page'];
        }
        if (isset($this->request->post['selected'])) {
            $selected = $this->request->post['selected'];
            foreach ($selected as $product) {
                if (isset($product['product_id'])) {
                    if ($setting['productsearch'] == '0') {
                        $product_total = $this->model_catalog_product->getProduct($product['product_id']);
                        $search = $product_total['model'];
                    } else {
                        $product_description = $this->model_catalog_product->getProductDescriptions($product['product_id']);
                        $search = $product_description[1]['name'];
                    }
                    $products = $this->model_module_parser->getProductParseUrls($product['product_id']);
                    if (empty($products)) {
                        $this->model_module_parser->addProductParseUrls($product['product_id']);
                    }
                    if ($setting['source'] == '1') {
                        //$url = $this->url_market . '/search.xml?text=' . $this->replace_yandex(rawurlencode($search));
						$url = $this->url_market . '/search?cvredirect=2&text=' . $this->replace_yandex(rawurlencode($search));
						$data_find = $this->FindManagerByURlYandex($url);
                        if (isset($data_find['captcha'])) {
                            if (isset($data_find['captcha']['key']) AND isset($data_find['captcha']['retpath']) AND isset($data_find['captcha']['img'])) {
                                $this->viewYandexCaptcha($data_find['captcha']['key'], $data_find['captcha']['retpath'], $data_find['captcha']['img']);
                            }
                        }
                        if (isset($data_find['items'][0]['href'])) {
                            $this->model_module_parser->insertUrlYandex($product['product_id'], $data_find['items'][0]['href']);
                        }
                    }
                    if ($setting['source'] == '2') {
						$url = 'http://hotline.ua/sr/?q=' . $this->replace_yandex($search) ;            
                        $data_find = $this->FindManagerByURlHotline($url);
                        if (isset($data_find[0]['href'])) {
                            $this->model_module_parser->insertUrlHotline($product['product_id'], $data_find[0]['href']);
                        } elseif (isset($data_find[0]['href'])) {
                            $this->model_module_parser->insertUrlHotline($product['product_id'], $data_find[0]['href']);
                        }
                    }
                    if ($setting['source'] == '3') {
                        $url = 'https://catalog.api.onliner.by/search/products?query=' . $this->replace_yandex(rawurlencode($search));
                        $data_find = $this->FindManagerByURlOnliner($url);
                        if (isset($data_find[0]['href'])) {
                            $this->model_module_parser->insertUrlOnliner($product['product_id'], $data_find[0]['href']);
                        } elseif (isset($data_find[1]['href'])) {
                            $this->model_module_parser->insertUrlOnliner($product['product_id'], $data_find[1]['href']);
                        }
                    }

                    if ($setting['source'] == '4') {
						$url = 'http://torg.mail.ru/search/?q=' . $this->replace_yandex($search);                        $data_find = $this->FindManagerByURlMail($url);
                        if (isset($data_find[0]['href'])) {
                            $this->model_module_parser->insertUrlMail($product['product_id'], $data_find[0]['href']);
                        } elseif (isset($data_find[1]['href'])) {
                            $this->model_module_parser->insertUrlMail($product['product_id'], $data_find[1]['href']);
                        }
                    }
                    MCurl::addMessage("Задержка : " . (int)$setting['pause'] . ' секунд.');
                    sleep((int)$setting['pause']);
                }
            }
        }
        $this->redirect($this->url->link('module/parsermanager', 'token=' . $this->session->data['token'] . $filter . $page, 'SSL'));
        exit;
    }

    public function parsingAllProducts()
    {
        $setting = $this->getSettingParser();
        $this->load->model('module/parser');
        $this->dir = trim(str_replace(DIR_IMAGE_PARSER, ' ', rtrim($setting['dir_to'], '/'))) . '/';
        $filter = '';
        $page = '';
        if (isset($this->request->get['filter_name']) AND !empty($this->request->get['filter_name'])) {
            $filter .= '&filter_name=' . $this->request->get['filter_name'];
        }
        if (isset($this->request->get['filter_sku_exist']) AND !empty($this->request->get['filter_sku_exist'])) {
            $filter .= '&filter_sku_exist=' . $this->request->get['filter_sku_exist'];
        }
        if (isset($this->request->get['filter_model']) AND !empty($this->request->get['filter_model'])) {
            $filter .= '&filter_model=' . $this->request->get['filter_model'];
        }
        if (isset($this->request->get['page']) AND !empty($this->request->get['page'])) {
            $page = '&page=' . $this->request->get['page'];
        }
        if (isset($this->request->post['selected'])) {
            $selected = $this->request->post['selected'];
            foreach ($selected as $val) {
                if (isset($val['product_id'])) {
                    $url = $this->getUrlProduct($val['product_id']);
                    if (!empty($url))
                        $this->model_module_parser->insertTableUrl($val['product_id'], $url);
                }
            }
        }
        $product_parsing = $this->model_module_parser->getTableUrls();
        foreach ($product_parsing as $product) {
            if ($setting['source_setting']['productdata'] == '1') {
                if ($this->parsingProductForAdd($product['product_id'], $product['url'])) {
                    $this->model_module_parser->deleteProductTableUrl($product['product_id']);
                }
            }
            if ($setting['source_setting']['productdata'] == '0') {
                if ($this->parsingProductForReplace($product['product_id'], $product['url'])) {
                    $this->model_module_parser->deleteProductTableUrl($product['product_id']);
                }
            }
            MCurl::addMessage("Задержка : " . (int)$setting['pause'] . ' секунд.');
            sleep((int)$setting['pause']);
        }
        $this->redirect($this->url->link('module/parsermanager', 'token=' . $this->session->data['token'] . $filter . $page, 'SSL'));
        exit;
    }

    public function AddProductParser()
    {
        $this->load->model('module/parser');
        $this->load->language('module/parsermanager');
        $setting = $this->getsettingParser();
        $this->dir = trim(str_replace(DIR_IMAGE_PARSER, ' ', rtrim($setting['dir_to'], '/'))) . '/';
        $data = array();
        $filter = '';
        if (isset($this->request->get['filter_name']) AND !empty($this->request->get['filter_name'])) {
            $filter .= '&filter_name=' . $this->request->get['filter_name'];
        }
        if (isset($this->request->get['filter_sku_exist']) AND !empty($this->request->get['filter_sku_exist'])) {
            $filter .= '&filter_sku_exist=' . $this->request->get['filter_sku_exist'];
        }
        if (isset($this->request->get['filter_model']) AND !empty($this->request->get['filter_model'])) {
            $filter .= '&filter_model=' . $this->request->get['filter_model'];
        }
        if (!empty($this->request->get['url'])) {
            $url = $this->request->get['url'];
            $url = trim(html_entity_decode(rawurldecode($url)));
            if ($setting['source'] == '1') {
                if ($data = $this->parserYandex($url)) {
                    $el = array_keys($data['product_description']);	
						$result = $this->model_module_parser->getProductName( $data['product_description'][$el[0]]['name']);
						if (!empty($result)){
							MCurl::addMessage("Товар: ". $data['product_description'][$el[0]]['name'].' не добавлен, такой товар уже есть.');	
							$this->session->data['error'] = "Товар не добавлен, такой товар уже существует!";		
							$this->response->redirect($this->url->link('module/parsermanager', 'token=' . $this->session->data['token'].$filter, 'SSL'));
							exit;
					}
                    $data['url_yandex'] = $url;
                    if (!isset($setting['source_setting']['manufacturer'])) {
                        if (isset($setting['manufacturer_id'])) {
                            $data = array_merge($data, array(
                                'manufacturer_id' => $setting['manufacturer_id']
                            ));
                        }
                    }
                    if (isset($setting['main_category_id'])) {
                        $data = array_merge($data, array(
                            'main_category_id' => $setting['main_category_id']
                        ));
                    }
                    if (isset($setting['product_category'])) {
                        $data = array_merge($data, array(
                            'product_category' => $setting['product_category']
                        ));
                    }
                    if (isset($this->request->get['sku'])) {
                        $data['sku'] = $this->request->get['sku'];
                    }
                    if (isset($this->request->get['price'])) {
                        $data['price'] = (int)$this->request->get['price'];
                    }
                    $this->model_module_parser->addProduct($data);
                    $this->session->data['success'] = $this->language->get('text_success');
                }
            }
            if ($setting['source'] == '2') {
                if ($data = $this->parserHotline($url)) {
                    $el = array_keys($data['product_description']);	
					$result = $this->model_module_parser->getProductName( $data['product_description'][$el[0]]['name']);
					$el = array_keys($data['product_description']);	
					$result = $this->model_module_parser->getProductName( $data['product_description'][$el[0]]['name']);
					if (!empty($result)){
						MCurl::addMessage("Товар: ". $data['product_description'][$el[0]]['name'].' не добавлен, такой товар уже есть.');	
						$this->session->data['error'] = "Товар не добавлен, такой товар уже существует!";		
						$this->response->redirect($this->url->link('module/parsermanager', 'token=' . $this->session->data['token'].$filter, 'SSL'));
						exit;
					}
                    $data['url_hotline'] = $url;
                    if (!isset($setting['source_setting']['manufacturer'])) {
                        if (isset($setting['manufacturer_id'])) {
                            $data = array_merge($data, array(
                                'manufacturer_id' => $setting['manufacturer_id']
                            ));
                        }
                    }
                    if (isset($setting['main_category_id'])) {
                        $data = array_merge($data, array(
                            'main_category_id' => $setting['main_category_id']
                        ));
                    }
                    if (isset($setting['product_category'])) {
                        $data = array_merge($data, array(
                            'product_category' => $setting['product_category']
                        ));
                    }
                    if (isset($this->request->get['sku'])) {
                        $data['sku'] = $this->request->get['sku'];
                    }
                    if (isset($this->request->get['price'])) {
                        $data['price'] = (int)$this->request->get['price'];
                    }
                    $this->model_module_parser->addProduct($data);
                    $this->session->data['success'] = $this->language->get('text_success');
                }
            }
            if ($setting['source'] == '3') {
                if ($data = $this->parserOnliner($url)) {
					$el = array_keys($data['product_description']);	
					$result = $this->model_module_parser->getProductName( $data['product_description'][$el[0]]['name']);
					if (!empty($result)){
						MCurl::addMessage("Товар: ". $data['product_description'][$el[0]]['name'].' не добавлен, такой товар уже есть.');	
						$this->session->data['error'] = "Товар не добавлен, такой товар уже существует!";		
						$this->response->redirect($this->url->link('module/parsermanager', 'token=' . $this->session->data['token'].$filter, 'SSL'));
						exit;
					}
                    $data['url_onliner'] = $url;
                    if (!isset($setting['source_setting']['manufacturer'])) {
                        if (isset($setting['manufacturer_id'])) {
                            $data = array_merge($data, array(
                                'manufacturer_id' => $setting['manufacturer_id']
                            ));
                        }
                    }
                    if (isset($setting['main_category_id'])) {
                        $data = array_merge($data, array(
                            'main_category_id' => $setting['main_category_id']
                        ));
                    }
                    if (isset($setting['product_category'])) {
                        $data = array_merge($data, array(
                            'product_category' => $setting['product_category']
                        ));
                    }
                    if (isset($this->request->get['sku'])) {
                        $data['sku'] = $this->request->get['sku'];
                    }
                    if (isset($this->request->get['price'])) {
                        $data['price'] = (int)$this->request->get['price'];
                    }
                    $this->model_module_parser->addProduct($data);
                    $this->session->data['success'] = $this->language->get('text_success');
                }
            }
            if ($setting['source'] == '4') {
                if ($data = $this->parserMail($url)) {
                    $el = array_keys($data['product_description']);	
					$result = $this->model_module_parser->getProductName( $data['product_description'][$el[0]]['name']);
					if (!empty($result)){
						MCurl::addMessage("Товар: ". $data['product_description'][$el[0]]['name'].' не добавлен, такой товар уже есть.');	
						$this->session->data['error'] = "Товар не добавлен, такой товар уже существует!";		
						$this->response->redirect($this->url->link('module/parsermanager', 'token=' . $this->session->data['token'].$filter, 'SSL'));
						exit;
					}
                    $data['url_mail'] = $url;
                    if (!isset($setting['source_setting']['manufacturer'])) {
                        if (isset($setting['manufacturer_id'])) {
                            $data = array_merge($data, array(
                                'manufacturer_id' => $setting['manufacturer_id']
                            ));
                        }
                    }
                    if (isset($setting['main_category_id'])) {
                        $data = array_merge($data, array(
                            'main_category_id' => $setting['main_category_id']
                        ));
                    }
                    if (isset($setting['product_category'])) {
                        $data = array_merge($data, array(
                            'product_category' => $setting['product_category']
                        ));
                    }
                    if (isset($this->request->get['sku'])) {
                        $data['sku'] = $this->request->get['sku'];
                    }
                    if (isset($this->request->get['price'])) {
                        $data['price'] = (int)$this->request->get['price'];
                    }
                    $this->model_module_parser->addProduct($data);
                    $this->session->data['success'] = $this->language->get('text_success');
                }
            }
        }
        $this->redirect($this->url->link('module/parsermanager', 'token=' . $this->session->data['token'] . $filter, 'SSL'));
        exit;
    }

    private function updateProductAndAdd($product_id, $data1 = array())
    {
        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        $this->load->model('catalog/product');
        $setting = $this->getSettingParser();
        $artributes = $this->model_catalog_product->getProductAttributes($product_id);
        $data = $this->model_catalog_product->getProduct($product_id);
        if (function_exists($this->model_catalog_product->getProductTags)) {
            $data['product_tag'] = $this->model_catalog_product->getProductTags($product_id);
        } else {
            $data['product_tag'] = array();
        }
        $data = array_merge($data, array(
            'product_description' => $this->model_catalog_product->getProductDescriptions($product_id)
        ));
        if (isset($setting['source_setting']['title']) && $setting['source_setting']['title'] == '1') {
            foreach ($this->data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['name'] = $data1['product_description'][$language['language_id']]['name'];
                $data['product_description'][$language['language_id']]['seo_h1'] = $data1['product_description'][$language['language_id']]['seo_h1'];
                $data['product_description'][$language['language_id']]['seo_title'] = $data1['product_description'][$language['language_id']]['seo_title'];
                $data['product_description'][$language['language_id']]['meta_keyword'] = $data1['product_description'][$language['language_id']]['meta_keyword'];
            }
            $data['model'] = $data1['model'];
        }
        if (isset($setting['source_setting']['addescription']) && $setting['source_setting']['addescription'] == '1') {
            foreach ($this->data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['description'] .= $data1['product_description'][$language['language_id']]['description'];
            }
        }
        $data = array_merge($data, array(
            'sku' => $data1['sku']
        ));
        if (isset($setting['source_setting']['manufacturer']) && $setting['source_setting']['manufacturer'] == '1') {
            $data = array_merge($data, array(
                'manufacturer_id' => $data1['manufacturer_id']
            ));
        }
        if (isset($setting['source_setting']['meta_description']) && $setting['source_setting']['meta_description'] == '1') {
            foreach ($this->data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['meta_description'] .= $data1['product_description'][$language['language_id']]['meta_description'];
            }
        }
        if (isset($setting['source_setting']['addatribyte']) && $setting['source_setting']['addatribyte'] == '1' && isset($data1['product_attribute'])) {
            $artributes = array_merge($data1['product_attribute'], $this->model_catalog_product->getProductAttributes($product_id));
            $data = array_merge($data, array(
                'product_attribute' => $artributes
            ));
        } else {
            $data = array_merge($data, array(
                'product_attribute' => $artributes
            ));
        }
        $data = array_merge($data, array(
            'price' => $data1['price']
        ));
        if (isset($setting['source_setting']['keyword']) && $setting['source_setting']['keyword'] == '1') {
            $data = array_merge($data, array(
                'keyword' => $data1['keyword']
            ));
        }
        if (isset($setting['source_setting']['addimage']) && $setting['source_setting']['addimage'] == '1') {
            $data = array_merge($data, array(
                'image' => $data1['image']
            ));
        }
		
		$data = array_merge($data, array('product_image' => $this->model_catalog_product->getProductImages($product_id)));
        if (isset($setting['source_setting']['addallimg']) && $setting['source_setting']['addallimg'] == '1') {
            if (isset($data1['product_image'])) {
                $image_all = array_merge($data1['product_image'], $this->model_catalog_product->getProductImages($product_id));
                $data = array_merge($data, array(
                    'product_image' => $image_all
                ));
            }
        } 

        $category = $this->model_catalog_product->getProductCategories($product_id);
        if (!empty($category)) {
            $data = array_merge($data, array(
                'product_category' => $this->model_catalog_product->getProductCategories($product_id)
            ));
        }
        $data = array_merge($data, array(
            'product_discount' => $this->model_catalog_product->getProductDiscounts($product_id)
        ));
        $data = array_merge($data, array(
            'product_option' => $this->model_catalog_product->getProductOptions($product_id)
        ));
        $data = array_merge($data, array(
            'product_related' => $this->model_catalog_product->getProductRelated($product_id)
        ));
        $data = array_merge($data, array(
            'product_reward' => $this->model_catalog_product->getProductRewards($product_id)
        ));
        $data = array_merge($data, array(
            'product_special' => $this->model_catalog_product->getProductSpecials($product_id)
        ));
        $data = array_merge($data, array(
            'product_download' => $this->model_catalog_product->getProductDownloads($product_id)
        ));
        $data = array_merge($data, array(
            'product_layout' => $this->model_catalog_product->getProductLayouts($product_id)
        ));
        $data = array_merge($data, array(
            'product_store' => $this->model_catalog_product->getProductStores($product_id)
        ));
        $this->model_catalog_product->editProduct($product_id, $data);
    }

    private function updateProductAndReplace($product_id, $data1 = array())
    {
        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        $this->load->model('catalog/product');
        $setting = $this->getSettingParser();
        $artributes = $this->model_catalog_product->getProductAttributes($product_id);
        $data = $this->model_catalog_product->getProduct($product_id);
        if (function_exists($this->model_catalog_product->getProductTags)) {
            $data['product_tag'] = $this->model_catalog_product->getProductTags($product_id);
        } else {
            $data['product_tag'] = array();
        }
        $data = array_merge($data, array(
            'product_description' => $this->model_catalog_product->getProductDescriptions($product_id)
        ));
        if (isset($setting['source_setting']['title']) && $setting['source_setting']['title'] == '1') {
            foreach ($this->data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['name'] = $data1['product_description'][$language['language_id']]['name'];
            }
        }
        if (isset($setting['source_setting']['model']) && $setting['source_setting']['model'] == '1') {
            $data['model'] = $data1['model'];
        }
        if (isset($setting['source_setting']['meta_description']) && $setting['source_setting']['meta_description'] == '1') {
            foreach ($this->data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['meta_description'] .= $data1['product_description'][$language['language_id']]['meta_description'];
            }
        }
        if (isset($setting['source_setting']['seo_h1']) && $setting['source_setting']['seo_h1'] == '1') {
            foreach ($this->data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['seo_h1'] = $data1['product_description'][$language['language_id']]['seo_h1'];
            }
        }
        if (isset($setting['source_setting']['meta_keyword']) && $setting['source_setting']['meta_keyword'] == '1') {
            foreach ($this->data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['meta_keyword'] = $data1['product_description'][$language['language_id']]['meta_keyword'];
            }
        }
        if (isset($setting['source_setting']['seo_title']) && $setting['source_setting']['seo_title'] == '1') {
            foreach ($this->data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['seo_title'] = $data1['product_description'][$language['language_id']]['seo_title'];
            }
        }
        if (isset($setting['source_setting']['addescription']) && $setting['source_setting']['addescription'] == '1') {
            foreach ($this->data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['description'] = $data1['product_description'][$language['language_id']]['description'];
            }
        }
        if (isset($setting['source_setting']['sku']) && $setting['source_setting']['sku'] == '1') {
            $data['sku'] = $data1['sku'];
        }
        if (isset($setting['source_setting']['manufacturer']) && $setting['source_setting']['manufacturer'] == '1') {
            $data = array_merge($data, array(
                'manufacturer_id' => $data1['manufacturer_id']
            ));
        }
        if (isset($setting['source_setting']['addatribyte']) && $setting['source_setting']['addatribyte'] == '1' && isset($data1['product_attribute'])) {
            $data = array_merge($data, array(
                'product_attribute' => $data1['product_attribute']
            ));
        } else {
            $data = array_merge($data, array(
                'product_attribute' => $artributes
            ));
        }
        $data = array_merge($data, array(
            'price' => $data1['price']
        ));
        if (isset($setting['source_setting']['keyword']) && $setting['source_setting']['keyword'] == '1') {
            $data = array_merge($data, array(
                'keyword' => $data1['keyword']
            ));
        }
        if (isset($setting['source_setting']['addimage']) && $setting['source_setting']['addimage'] == '1') {
            $data = array_merge($data, array(
                'image' => $data1['image']
            ));
        }
        if (isset($setting['source_setting']['addallimg']) && $setting['source_setting']['addallimg'] == '1') {
            if (isset($data1['product_image'])) {
                $data = array_merge($data, array(
                    'product_image' => $data1['product_image']
                ));
            }
        } else {
            $data = array_merge($data, $this->model_catalog_product->getProductImages($product_id));
        }
        $category = $this->model_catalog_product->getProductCategories($product_id);
        if (!empty($category)) {
            $data = array_merge($data, array(
                'product_category' => $this->model_catalog_product->getProductCategories($product_id)
            ));
        }
        $data = array_merge($data, array(
            'product_discount' => $this->model_catalog_product->getProductDiscounts($product_id)
        ));
        $data = array_merge($data, array(
            'product_option' => $this->model_catalog_product->getProductOptions($product_id)
        ));
        $data = array_merge($data, array(
            'product_related' => $this->model_catalog_product->getProductRelated($product_id)
        ));
        $data = array_merge($data, array(
            'product_reward' => $this->model_catalog_product->getProductRewards($product_id)
        ));
        $data = array_merge($data, array(
            'product_special' => $this->model_catalog_product->getProductSpecials($product_id)
        ));
        $data = array_merge($data, array(
            'product_download' => $this->model_catalog_product->getProductDownloads($product_id)
        ));
        $data = array_merge($data, array(
            'product_layout' => $this->model_catalog_product->getProductLayouts($product_id)
        ));
        $data = array_merge($data, array(
            'product_store' => $this->model_catalog_product->getProductStores($product_id)
        ));
        $this->model_catalog_product->editProduct($product_id, $data);
    }

    private function getUrlProduct($product_id = '')
    {
        $url_parsing = '';
        $this->load->model('module/parser');
        $product_total = $this->model_module_parser->getProductParseUrls($product_id);
        $setting_parser = $this->config->get($this->user->getUserName().'_parsermanager_setting');
        if ($setting_parser['source'] == '1') {
            if (isset($product_total['url_yandex']))
                $url_parsing = $product_total['url_yandex'];
        }
        if ($setting_parser['source'] == '2') {
            if (isset($product_total['url_hotline']))
                $url_parsing = $product_total['url_hotline'];
        }
        if ($setting_parser['source'] == '3') {
            if (isset($product_total['url_onliner']))
                $url_parsing = $product_total['url_onliner'];
        }
        if ($setting_parser['source'] == '4') {
            if (isset($product_total['url_mail']))
                $url_parsing = $product_total['url_mail'];
        }
        return $url_parsing;
    }

    public function getSettingParser()
    {
        $setting = array();
        $setting_parser = $this->config->get($this->user->getUserName().'_parsermanager_setting');
		
        $setting = $setting_parser;
        $setting['source'] = $setting_parser['source'];
        if ($setting_parser['source'] == '1') {
            $setting['source_setting'] = $setting_parser['yandex'];
        }
        if ($setting_parser['source'] == '2') {
            $setting['source_setting'] = $setting_parser['hotline'];
        }
        if ($setting_parser['source'] == '3') {
            $setting['source_setting'] = $setting_parser['onliner'];
        }
        if ($setting_parser['source'] == '4') {
            $setting['source_setting'] = $setting_parser['mail'];
        }

        return $setting;
    }     

    public function images($title, $image_main, $images)
    {
        $setting = $this->getSettingParser();
        $images1 = array();
        if (!empty($image_main)) {
            $link = pathinfo($image_main);
            $ext = isset($link['extension']) && preg_match('#(jpeg)|(jpg)|(png)|(bmp)#Ui', $link['extension'], $matches) ? $matches[0] : 'jpg';
            if ($curl_image = $this->getImageParser($image_main, '../image/' . $this->dir, $this->replace_sym($title) . '_0.' . $ext)) {
                $images1[0] = $this->dir . $curl_image;
            }
        }
        if (!empty($images)) {
            $k = 1;
            foreach ($images as $val) {
                $link = pathinfo($val);
                $ext = isset($link['extension']) && preg_match('#(jpeg)|(jpg)|(png)|(bmp)#Ui', $link['extension'], $matches) ? $matches[0] : 'jpg';
                if ($curl_image = $this->getImageParser($val, '../image/' . $this->dir, $this->replace_sym($title) . '_' . $k . '.' . $ext)) {
                    $images1[$k]['image'] = $this->dir . $curl_image;
                    $images1[$k]['sort_order'] = '';
                    $k++;
                }
            }
        }
        return $images1;
    }

    public function addAtributes($attribyte_groupe, $attributes)
    {
        $arr_to_prod1 = array();
        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        $array_attribute = $attributes;
        $this->load->model('module/parser');
        $search_attribyte_groupe = $this->model_module_parser->SearchAttribyteGroupe($attribyte_groupe);
        if (empty($search_attribyte_groupe)) {
            foreach ($this->data['languages'] as $language) {
                $data['attribute_group_description'][$language['language_id']]['name'] = $attribyte_groupe;
            }
            $data['sort_order'] = 10;
            $this->load->model('catalog/attribute_group');
            $this->model_catalog_attribute_group->addAttributeGroup($data);
        }
        $search_attribyte_groupe = $this->model_module_parser->SearchAttribyteGroupe($attribyte_groupe);
        $i = 100;
        foreach ($attributes as $key => $attribute) {
            $searchAttByGrup = array();
            $searchAttByGrup = $this->model_module_parser->SearchAttByGrup($key, $search_attribyte_groupe['attribute_group_id']);
            if (empty($searchAttByGrup)) {
                foreach ($this->data['languages'] as $language) {
                    $d['attribute_description'][$language['language_id']]['name'] = $key;
                }
                $d['attribute_group_id'] = $search_attribyte_groupe['attribute_group_id'];
                $d['sort_order'] = $i;
                $this->load->model('catalog/attribute');
                $this->model_catalog_attribute->addAttribute($d);
                $i++;
            }
            $searchAttByGrup = $this->model_module_parser->SearchAttByGrup($key, $search_attribyte_groupe['attribute_group_id']);
            $arr_to_prod['name'] = $key;
            $arr_to_prod['attribute_id'] = $searchAttByGrup['attribute_id'];
            foreach ($this->data['languages'] as $language) {
                $arr_to_prod['product_attribute_description'][$language['language_id']] = array(
                    'text' => $attribute
                );
            }
            $arr_to_prod1[] = $arr_to_prod;
        }
        return $arr_to_prod1;
    }

    public function setManufacture($manufacture)
    {
        $this->load->model('module/parser');
        $searchManufacture = $this->model_module_parser->getManufactureName($manufacture);
        if (empty($searchManufacture)) {
            $this->load->model('localisation/language');
            $this->data['languages'] = $this->model_localisation_language->getLanguages();
            $this->load->model('catalog/manufacturer');
            $manufacture_array = $this->manufacture_array;
            foreach ($this->data['languages'] as $language) {
                $manufacture_array['manufacturer_description'][$language['language_id']] = array(
                    'seo_h1' => '',
                    'seo_title' => '',
                    'meta_keyword' => '',
                    'meta_description' => '',
                    'description' => ''
                );
            }
            $manufacture_array['name'] = $manufacture;
            $this->model_catalog_manufacturer->addManufacturer($manufacture_array);
            $searchManufacture = $this->model_module_parser->getManufactureName($manufacture);
        }
        $manufacture_id = $searchManufacture['manufacturer_id'];
        return $manufacture_id;
    }

    public function getDataParser($items = array())
    {
        $this->load->model('localisation/language');
        $this->data['languages'] = $this->model_localisation_language->getLanguages();
        $data = array(
            'model' => $items['title'],
            'price' => $items['price'],
            'tax_class_id' => 1,
            'quantity' => 1,
            'minimum' => 1,
            'subtract' => 1,
            'stock_status_id' => 5,
            'shipping' => 1,
            'length_class_id' => 0,
            'weight_class_id' => 0,
            'status' => 1,
            'sort_order' => 1,
            'sku' => $items['sku'],
            'upc' => '',
            'ean' => '',
            'jan' => '',
            'isbn' => '',
            'mpn' => '',
            'location' => '',
            'points' => 0,
            'weight' => 0,
            'length' => 0,
            'width' => 0,
            'height' => 0,
            'product_tag' => array(
                '',
                ''
            ),
            'keyword' => $items['keyword'],
            'product_store' => array(
                0 => '0'
            ),
            'date_available' => date('Y-m-d')
        );
        foreach ($this->data['languages'] as $language) {
            $data['product_description'][$language['language_id']] = array(
                'name' => $items['title'],
                'seo_h1' => $items['title'],
                'seo_title' => $items['title'],
                'meta_keyword' => $items['title'],
                'model' => $items['title'],
                'description' => $items['description'],
                'meta_description' => $items['meta_description'],
                'tag' => ''
            );
        }
        if (isset($items['manufacturer_id'])) {
            $data = array_merge($data, array(
                'manufacturer_id' => $items['manufacturer_id']
            ));
        }
        if (!empty($items['img_arr'])) {
            $data = array_merge($data, array(
                'product_image' => $items['img_arr']
            ));
        }
        if (!empty($items['attribute'])) {
            $i = 0;
            foreach ($items['attribute'] as $attribute) {
                foreach ($attribute['product_attribute_description'] as $product_attribute_description) {
                    foreach ($this->data['languages'] as $language) {
                        $items['attribute'][$i]['product_attribute_description'][$language['language_id']]['text'] = htmlentities(html_entity_decode($product_attribute_description['text']), ENT_QUOTES, 'UTF-8');
                    }
                }
                $i++;
            }
            $data = array_merge($data, array(
                'product_attribute' => $items['attribute']
            ));
        }
        if (isset($items['image'])) {
            $data = array_merge($data, array(
                'image' => $items['image']
            ));
        }
        return $data;
    }

    public function FindManager()
    {
        $this->load->language('common/findmanager');
        $this->data['setting'] = $this->config->get($this->user->getUserName().'_parsermanager_setting');
        $this->data['title'] = $this->language->get('heading_title');
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['base'] = HTTPS_SERVER;
        } else {
            $this->data['base'] = HTTP_SERVER;
        }
		
        $this->data['entry_folder'] = $this->language->get('entry_folder');
        $this->data['entry_move'] = $this->language->get('entry_move');
        $this->data['entry_copy'] = $this->language->get('entry_copy');
        $this->data['entry_rename'] = $this->language->get('entry_rename');
        $this->data['button_folder'] = $this->language->get('button_folder');
        $this->data['button_delete'] = $this->language->get('button_delete');
        $this->data['button_rename'] = $this->language->get('button_rename');
        $this->data['button_upload'] = $this->language->get('button_upload');
        $this->data['button_refresh'] = $this->language->get('button_refresh');
        $this->data['button_submit'] = $this->language->get('button_submit');
        $this->data['error_select'] = $this->language->get('error_select');
        $this->data['error_directory'] = $this->language->get('error_directory');
        $this->data['token'] = $this->session->data['token'];
        if (isset($this->request->get['product_name']) AND $this->request->get['product_name'] != 'null') {
            $search = $this->request->get['product_name'];
        } else {
            $product_id = $this->request->get['product_id'];
            $this->load->model('catalog/product');
            $product_total = $this->model_catalog_product->getProduct($product_id);
            $product_description = $this->model_catalog_product->getProductDescriptions($product_id);
            if ($this->data['setting']['productsearch'] == '0') {
                $search = $product_total['model'];
            } else {
                $search = $product_description[1]['name'];
            }
        }
        if (isset($this->data['setting']['region'])) {
            if ($this->data['setting']['region'] == '213') {
                $this->url_market = 'https://market.yandex.ru';
            }
            if ($this->data['setting']['region'] == '143') {
                $this->url_market = 'https://market.yandex.ua';
            }
            if ($this->data['setting']['region'] == '157') {
                $this->url_market = 'https://market.yandex.by';
            }
            if ($this->data['setting']['region'] == '162') {
                $this->url_market = 'https://market.yandex.kz';
            }
        }
        $this->data['items'] = array();
        if ($this->data['setting']['source'] == '1') {
            $url = $this->url_market . '/search?cvredirect=1&text=' . $this->replace_yandex(rawurlencode($search));
			$data_find = $this->FindManagerByURlYandex($url);
            $this->data['items'] = $data_find['items'];
            if (isset($data_find['captcha'])) {
                if (isset($data_find['captcha']['key']) AND isset($data_find['captcha']['retpath']) AND isset($data_find['captcha']['img'])) {
                    $this->viewYandexCaptcha($data_find['captcha']['key'], $data_find['captcha']['retpath'], $data_find['captcha']['img']);
                }
            }
        }
		
        if ($this->data['setting']['source'] == '2') {
            $url = 'http://hotline.ua/sr/?q=' . $this->replace_yandex($search) ;            
            $this->data['items'] = $this->FindManagerByURlHotline($url);
        }
        if ($this->data['setting']['source'] == '3') {
            $url = 'https://catalog.api.onliner.by/search/products?query=' . $this->replace_yandex(rawurlencode($search));
            $this->data['items'] = $this->FindManagerByURlOnliner($url);
        }
        if ($this->data['setting']['source'] == '4') {
            $url = 'http://torg.mail.ru/search/?q=' . $this->replace_yandex($search);
            $this->data['items'] = $this->FindManagerByURlMail($url);
        }
        $this->template = 'common/findmanager.tpl';
        $this->response->setOutput($this->render());
    }

    public function getContentParser($url)
    {
        $this->data['setting'] = $this->config->get($this->user->getUserName().'_parsermanager_setting');
        $page = Mcurl::getInstance();
        $page->setUserAgent($this->data['setting']['user_agent']);
        if ($this->data['setting']['cookie_check'] == 1) {
            $region = $this->data['setting']['region'];
            $page->setCookie($region);
        }
        if ($this->data['setting']['proxy_check'] == 0) {
            $content = $page->getContent($url);
            return $content;
        }
        if ($this->data['setting']['proxy_check'] == 1) {
            $page->setProxy($this->data['setting']['proxy_port'], $this->data['setting']['user_pass']);
            $content = $page->getContent($url);
            return $content;
        }
        if ($this->data['setting']['proxy_check'] == 2) {
            $content = $page->getContentProxyList($url, PROXY_LIST);
            return $content;
        }
    }

    private function getImageParser($url, $dir, $file)
    {
        $this->data['setting'] = $this->config->get($this->user->getUserName().'_parsermanager_setting');
        $page = Mcurl::getInstance();
        if ($this->data['setting']['proxy_check'] == 0) {
            $content = $page->getImage($url, $dir, $file);
            return $content;
        }
        if ($this->data['setting']['proxy_check'] == 1) {
            $page->setProxy($this->data['setting']['proxy_port'], $this->data['setting']['user_pass']);
            $content = $page->getImage($url, $dir, $file);
            return $content;
        }
        if ($this->data['setting']['proxy_check'] == 2) {
            $content = $page->getImageProxyList($url, $dir, $file, PROXY_LIST);
            return $content;
        }
    }

   
    public function autocompleteparserj()
    {
        $json = array();
        if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
            $this->load->model('catalog/product');
            $this->load->model('module/parser');
            $this->data['setting'] = $this->config->get($this->user->getUserName().'_parsermanager_setting');
            if (isset($this->request->get['filter_name'])) {
                $filter_name = trim($this->request->get['filter_name']);
            } else {
                $filter_name = '';
            }
            if (isset($this->request->get['filter_model'])) {
                $filter_model = trim($this->request->get['filter_model']);
            } else {
                $filter_model = '';
            }
            $data = array(
                'filter_image_main' => isset($this->data['setting']['filter_image_main']) ? $this->data['setting']['filter_image_main'] : '',
                'filter_image_all' => isset($this->data['setting']['filter_image_all']) ? $this->data['setting']['filter_image_all'] : '',
                'filter_attribute' => isset($this->data['setting']['filter_attribute']) ? $this->data['setting']['filter_attribute'] : '',
                'filter_description' => isset($this->data['setting']['filter_description']) ? $this->data['setting']['filter_description'] : '',
                'filter_sku' => isset($this->data['setting']['filter_sku']) ? $this->data['setting']['filter_sku'] : '',
                'filter_model' => $filter_model,
                'start' => 0,
                'limit' => 20,
                'filter_category' => isset($this->data['setting']['filter_category']) ? $this->data['setting']['filter_category'] : array(),
                'filter_price' => isset($this->data['setting']['filter_price']) ? $this->data['setting']['filter_price'] : '',
                'filter_name' => $filter_name
            );
            $results = $this->model_module_parser->SearchEmpty($data);
            foreach ($results as $result) {
                $json[] = array(
                    'product_id' => $result['product_id'],
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'model' => $result['model'],
                    'price' => $result['price'],
                    'sku' => $result['sku']
                );
            }
        }
        $this->response->setOutput(json_encode($json));
    }

    public function FolderManager()
    {
        $this->load->language('common/foldermanager');
        $this->data['title'] = $this->language->get('heading_title');
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['base'] = HTTPS_SERVER;
        } else {
            $this->data['base'] = HTTP_SERVER;
        }
        $this->data['entry_folder'] = $this->language->get('entry_folder');
        $this->data['entry_move'] = $this->language->get('entry_move');
        $this->data['entry_copy'] = $this->language->get('entry_copy');
        $this->data['entry_rename'] = $this->language->get('entry_rename');
        $this->data['button_folder'] = $this->language->get('button_folder');
        $this->data['button_delete'] = $this->language->get('button_delete');
        $this->data['button_rename'] = $this->language->get('button_rename');
        $this->data['button_upload'] = $this->language->get('button_upload');
        $this->data['button_refresh'] = $this->language->get('button_refresh');
        $this->data['button_submit'] = $this->language->get('button_submit');
        $this->data['error_select'] = $this->language->get('error_select');
        $this->data['error_directory'] = $this->language->get('error_directory');
        $this->data['token'] = $this->session->data['token'];
        $this->data['directory'] = DIR_IMAGE_PARSER . 'data/';
        $this->template = 'common/foldermanager.tpl';
        $this->response->setOutput($this->render());
    }

    public function proxycheck()
    {
        $page = MCurl::getInstance();
        $this->data['setting'] = $this->config->get($this->user->getUserName().'_parsermanager_setting');
        $page->setUserAgent($this->data['setting']['user_agent']);
        if ($this->data['setting']['source'] == '1') {
            echo $page->proxyCheckMulti('https://market.yandex.ru', PROXY_LIST, 'title>Яндекс.Маркет');
        }
        if ($this->data['setting']['source'] == '2') {
            echo $page->proxyCheckMulti('http://hotline.ua', PROXY_LIST, 'title>Hotline');
        }
        if ($this->data['setting']['source'] == '3') {
            echo $page->proxyCheckMulti('http://catalog.onliner.by', PROXY_LIST, 'title>Каталог Onliner.by');
        }
        unset($page);
    }

    public function ajaxUploadFile()
    {
        $error = "";
        $msg = "";
        $fileElementName = 'fileToUpload';
        if (!empty($_FILES[$fileElementName]['error'])) {
            switch ($_FILES[$fileElementName]['error']) {
                case '1':
                    $error = 'Размер файла больше разрешенного директивой upload_max_filesize в php.ini';
                    break;
                case '2':
                    $error = 'Размер файла превышает указанное значение в MAX_FILE_SIZE';
                    break;
                case '3':
                    $error = 'Файл был загружен только частично';
                    break;
                case '4':
                    $error = 'Не был выбран файл для загрузки';
                    break;
                case '6':
                    $error = 'Отсутствует временный каталог';
                    break;
                case '7':
                    $error = 'Не могу записать на диск';
                    break;
                default:
                    $error = 'Случилось что-то непонятное';
            }
        } elseif (empty($_FILES['fileToUpload']['tmp_name']) || $_FILES['fileToUpload']['tmp_name'] == 'none') {
            $error = 'No file was uploaded..';
        } else {
            if ($_FILES["fileToUpload"]["name"] == 'attribute.xml' OR $_FILES["fileToUpload"]["name"] == 'proxy_list.txt' OR $_FILES["fileToUpload"]["name"] == 'cookie.txt') {
                $msg .= " Файл загружен!";
                move_uploaded_file($_FILES['fileToUpload']['tmp_name'], DIR_DOWNLOAD . $_FILES["fileToUpload"]["name"]);
            } else {
                $error .= "Не тот файл!";
            }
        }
        echo "{";
        echo "error: '" . $error . "',\n";
        echo "msg: '" . $msg . "'\n";
        echo "}";
    }

    public function parselog()
    {
        $this->load->language('tool/error_log');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['button_clear'] = $this->language->get('button_clear');
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('tool/error_log', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
        $this->data['clear'] = $this->url->link('tool/error_log/clear', 'token=' . $this->session->data['token'], 'SSL');
        $file = PARSER_LOG;
        if (file_exists($file)) {
            $this->data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
        } else {
            $this->data['log'] = '';
        }
        $this->template = 'tool/error_log.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'module/parsermanager')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    private function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    
    public function updateTableParser()
    {
        $this->load->model('module/parser');
        $result = $this->model_module_parser->updateParserUrls();
        $this->session->data['success'] = $result;
        $this->redirect($this->url->link('module/parsermanager/', 'token=' . $this->session->data['token'], 'SSL'));
    }

    public function delete_cookie()
    {
        if (file_exists(DIR_DOWNLOAD . 'cookie.txt')) {
            file_put_contents(DIR_DOWNLOAD . 'cookie.txt', '');
            echo "Cookie очищены!";
        } else {
            echo "Ошибка очистки cookie!";
        }
    }

    public function saveFileParsing($content = '')
    {
        file_put_contents(DIR_DOWNLOAD . 'content.htm', $content);
    }

    public function setYandexCaptcha()
    {
        $content = '';
        if (isset($this->request->post['key']) AND isset($this->request->post['retpath']) AND isset($this->request->post['rep'])) {
            $key = $this->request->post['key'];
            $retpath = $this->request->post['retpath'];
            $rep = iconv("utf-8", "windows-1251", urlencode($this->request->post['rep']));
            $url_market = parse_url(urldecode($retpath), PHP_URL_HOST);
            $url_captcha = 'http://' . $url_market . '/checkcaptcha?key=' . $key . '&retpath=' . $retpath . '&rep=' . $rep;
            $content = $this->getContentParser($url_captcha);
            if (preg_match('#b-captcha__image#U', $content, $captcha_match)) {
                $this->yandexCaptcha($content);
            }
        }
        $this->session->data['success'] = 'Пробуте парсить снова!';
        $this->redirect($this->url->link('module/parsermanager/', 'token=' . $this->session->data['token'], 'SSL'));
    }

    private function yandexCaptcha($content)
    {
        if (preg_match('#name=\"key\" value=\"(.*)\"#Uism', $content, $key_match)) {
            $key = urlencode(html_entity_decode($key_match[1]));
        }
        if (preg_match('#name=\"retpath\" value=\"(.*)\"#Uism', $content, $retpath_match)) {
            $retpath = urlencode(html_entity_decode($retpath_match[1]));
        }
        if (preg_match('#captcha__image.*src=\"(.*)\"#Uism', $content, $img_match) OR preg_match('#image form__captcha.*src=\"(.*)\"#Uism', $content, $img_match)) {
            $img = $img_match[1];
        }
		
        if (isset($key) AND isset($retpath) AND isset($img)) {
            $this->viewYandexCaptcha($key, $retpath, $img);
        } else {
			echo "Не распознал капчу!";
		}
    }

    private function viewYandexCaptcha($key, $retpath, $img)
    {
        ?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

        <html xmlns="http://www.w3.org/1999/xhtml" lang="ru" xml:lang="ru">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title>Ввод капчи</title>
        </head>
        <body>
        <div>
            <form method="POST" action="<?php
            echo HTTP_SERVER . "index.php?route=module/parsermanager/setyandexcaptcha&token=" . $_SESSION['token'];
            ?>">
                <input type="hidden" value="<?php
                echo $key;
                ?>" name="key">
                <input type="hidden" value="<?php
                echo $retpath;
                ?>" name="retpath">
                <img class="b-captcha__image" src="<?php
                echo $img;
                ?>">
                <input value="" name="rep" size=10>
                <input class="b-captcha__submit" type="submit" value="Отправить">
            </form>
        </div>
        </body>
        </html>

        <?php
    }
}
