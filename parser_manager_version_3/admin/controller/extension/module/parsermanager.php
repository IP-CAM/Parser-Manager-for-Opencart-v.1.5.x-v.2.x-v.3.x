<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
header('Content-Type: text/html; charset=UTF-8');
define('PARSER_LOG', DIR_LOGS . 'parser.log');
define('PROXY_LIST', DIR_DOWNLOAD . 'proxy_list.txt');
define('DIR_IMAGE_PARSER', DIR_IMAGE);
require_once(DIR_SYSTEM . 'library/parsermanager/simple_html_dom.php');
require_once(DIR_SYSTEM . 'library/parsermanager/MCurl.php');

class Controllerextensionmoduleparsermanager extends Controller
{
    private $error = array();
    private $page;
    private $dir;
    protected static $_instance;
    private $manufacture_array = array('name' => '', 'manufacturer_store' => array('0' => '0'), 'keyword' => '', 'image' => '', 'sort_order' => '');

    private function replace_sym($string)
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

    private function replace_yandex($string)
    {
        $converter = array(
            '%20' => '+',
            '%C2%A0' => '+',
            '%26lt' => '',
            '%26gt' => '',
        );
        return strtr($string, $converter);
    }

    private function clearshar($str)
    {
        $search = array("'<script[^>]*?>.*?</script>'si",
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
            "'novinka'i");
        return preg_replace_callback($search, function ($matches) {
            return '';
        }, $str);
    }

    private function hex2rgb($color)
    {
        if ($color[0] == '#')
            $color = substr($color, 1);
        if (strlen($color) == 6)
            list($r, $g, $b) = array($color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5]);
        elseif (strlen($color) == 3)
            list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
        else
            return false;
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return array($r, $g, $b);
    }

    private function rus2translit($string)
    {
        $converter = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v',
            'г' => 'g', 'д' => 'd', 'е' => 'e',
            'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
            'и' => 'i', 'й' => 'y', 'к' => 'k',
            'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r',
            'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
            'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            'А' => 'A', 'Б' => 'B', 'В' => 'V',
            'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
            'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
            'И' => 'I', 'Й' => 'Y', 'К' => 'K',
            'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R',
            'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
            'Ь' => '\'', 'Ы' => 'Y', 'Ъ' => '\'',
            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
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

    public function install()
    {
        $this->load->model('extension/parser');
        $this->model_extension_parser->createParserUrls();
        $this->model_extension_parser->createTableUrls();
        $this->session->data['success'] = 'Модуль Парсер-менеджер установлен!';
    }

    public function index()
    {
		$this->document->addStyle('view/stylesheet/parsermanager.css');
		$this->document->addScript('view/javascript/jquery/ajaxfileuploadparser.js');
		
        $this->load->language('extension/module/parsermanager');
        $this->document->setTitle($this->language->get('heading_title'));
        $data['heading_title'] = $this->language->get('heading_title');
        $data['button_addfield'] = $this->language->get('button_addfield');
        $data['button_deletefield'] = $this->language->get('button_deletefield');
        $data['column_image'] = $this->language->get('column_image');
        $data['column_name'] = $this->language->get('column_name');
        $data['entry_manufacturer'] = $this->language->get('entry_manufacturer');
        $data['entry_main_category'] = $this->language->get('entry_main_category');
        $data['entry_category'] = $this->language->get('entry_category');
        $data['entry_store'] = $this->language->get('entry_store');
        $data['text_select_all'] = $this->language->get('text_select_all');
        $data['text_unselect_all'] = $this->language->get('text_unselect_all');
        $data['text_none'] = $this->language->get('text_none');
        $data['text_default'] = $this->language->get('text_default');
        $data['text_list'] = $this->language->get('text_list');
        $data['button_save'] = $this->language->get('button_save');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_model'] = $this->language->get('entry_model');
        $data['entry_price'] = $this->language->get('entry_price');
        $data['entry_quantity'] = $this->language->get('entry_quantity');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['license_key'] = $this->language->get('license_key');
		
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => false
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/parsermanager', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => ' :: '
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => ' :: '
        );
		
		if (isset($this->request->get['store_id'])) {
			$store_id = $this->request->get['store_id'];
		} else {
			$store_id = 0;
		}
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		$data['add_urls'] = $this->url->link('extension/module/parsermanager/getUrsByFile', 'user_token=' . $this->session->data['user_token'] , true);
        $data['action'] = $this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store_id, true);
        $data['continue'] = $this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'] . '&continue=1&store_id=' . $store_id, true);
        $data['view_log'] = $this->url->link('extension/module/parsermanager/parselog', 'user_token=' . $this->session->data['user_token'] . '&continue=1&store_id=' . $store_id, true);
        $data['view_page'] = $this->url->link('extension/module/parsermanager/viewPage', 'user_token=' . $this->session->data['user_token'] . '&continue=1&store_id=' . $store_id, true);


        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $data['base'] = HTTPS_SERVER;
        } else {
            $data['base'] = HTTP_SERVER;
        }

        $url = '';

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_price'])) {
            $url .= '&filter_price=' . $this->request->get['filter_price'];
        }

        if (isset($this->request->get['filter_quantity'])) {
            $url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->session->data['error'])) {
            $data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        }
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()
            && isset($this->request->post['parsermanager_setting'])
        ) {
            $tmp_setting = $this->request->post;
            unset($this->session->data['license']);
            if (isset($this->request->post['parsermanager_setting']['create_dir_image'])) {
                $this->load->model('catalog/category');
                $tmp_categories = $this->model_catalog_category->getCategories(0);
                if (isset($this->request->post['parsermanager_setting']['product_category'])) {
                    $tmp_product_category = $this->request->post['parsermanager_setting']['product_category'];
                } else {
                    $tmp_product_category = array();
                }
                foreach ($tmp_categories as $category) {
                    if (isset($tmp_product_category[0]) AND ($category['category_id'] == $tmp_product_category[0])) {
                        $path = explode("_gt_", $this->replace_sym($this->rus2translit($category['name'])));
                        $new_dir_image = DIR_IMAGE_PARSER . 'catalog/';
                        $j = 0;
                        foreach ($path as $d) {
                            $d = preg_replace_callback('#nbsp_#U', function ($matches) {
                                return '_';
                            }, $d);
                            $d = preg_replace_callback('#nbsp#U', function ($matches) {
                                return '_';
                            }, $d);
                            $new_dir_image .= $d . '/';
                            if (!is_dir($new_dir_image) AND $j < 5) {
                                mkdir($new_dir_image, 0777);
                            }
                            $j++;
                        }
                        $tmp_setting['parsermanager_setting']['dir_to'] = $new_dir_image;
                    }
                }
            }
            $this->model_setting_setting->editSetting('parsermanager', $tmp_setting);
            $this->session->data['success'] = $this->language->get('savesatting');
            $this->response->redirect($this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'], 'SSL'));
        }
		
        $data['setting'] = $this->config->get('parsermanager_setting');
		// Categories
        $this->load->model('catalog/category');
        $data['allCategories'] = $this->model_catalog_category->getCategories(0);
        if (isset($data['setting']['product_category'])) {
            $categories = $data['setting']['product_category'];
        } elseif (isset($this->request->get['product_id'])) {
            $categories = $this->model_catalog_product->getProductCategories($this->request->get['product_id']);
        } else {
            $categories = array();
        }
        $data['product_categories'] = array();
        foreach ($categories as $category_id) {
            $category_info = $this->model_catalog_category->getCategory($category_id);
            if ($category_info) {
                $data['product_categories'][] = array(
                    'category_id' => $category_info['category_id'],
                    'name' => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
                );
            }
        }
        /*if (empty($data['setting'])){
            $data['setting'] = unserialize('a:13:{s:6:"source";s:1:"1";s:11:"proxy_check";s:1:"0";s:10:"proxy_port";s:0:"";s:9:"user_pass";s:0:"";s:6:"dir_to";s:43:"C:\apache\localhost\www\oc15511/image/data/";s:6:"yandex";a:1:{s:11:"productdata";s:1:"1";}s:3:"hotline";a:1:{s:11:"productdata";s:1:"1";}s:7:"onliner";a:1:{s:11:"productdata";s:1:"1";}s:15:"filter_category";s:0:"";s:13:"productsearch";s:1:"1";s:15:"manufacturer_id";s:1:"0";s:16:"main_category_id";s:1:"0";s:12:"countproduct";s:2:"10";}');
        }*/
        if (isset($data['setting']['main_category_id'])) {
            $data['main_category_id'] = $data['setting']['main_category_id'];
        } else {
            $data['main_category_id'] = 0;
        }
        if (isset($data['setting']['product_category'])) {
            $data['product_category'] = $data['setting']['product_category'];
        } else {
            $data['product_category'] = array();
        }
        //производители		
        $this->load->model('catalog/manufacturer');
        $data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();
        if (isset($data['setting']['manufacturer_id'])) {
            $data['manufacturer_id'] = $data['setting']['manufacturer_id'];
        } else {
            $data['manufacturer_id'] = 0;
        }
        //магазины		
        $this->load->model('setting/store');
        $data['stores'] = $this->model_setting_store->getStores();
        if (isset($this->request->post['product_store'])) {
            $data['product_store'] = $this->request->post['product_store'];
        } elseif (isset($this->request->get['product_id'])) {
            $data['product_store'] = $this->model_catalog_product->getProductStores($this->request->get['product_id']);
        } else {
            $data['product_store'] = array(0);
        }
        if (isset($data['setting']['dir_to']) &&
            (!empty($data['setting']['dir_to']))
        ) {
            $data['setting']['dir_to'] = trim($data['setting']['dir_to']);
            $this->dir = trim(str_replace(DIR_IMAGE_PARSER, ' ', rtrim($data['setting']['dir_to'], '/'))) . '/';
        } else {
            $data['setting']['dir_to'] = DIR_IMAGE_PARSER . 'catalog/';
            $this->dir = trim(str_replace(DIR_IMAGE_PARSER, ' ', rtrim(DIR_IMAGE_PARSER . 'catalog/', '/'))) . '/';
        }
        $this->load->model('extension/parser');
        //установка региона для яндекс маркета
        $data['regions'] = array('Москва' => '213', 'Минск' => '157', 'Киев' => '143', 'Алматы' => '162');
        $data['region_set'] = '';
        if (isset($data['setting']['region'])) {
            $data['region_set'] = $data['setting']['region'];
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'] - 1;
        } else {
            $page = 0;
        }

        $filter2url = '';
        if (isset($this->request->get['filter_name']) AND !empty($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
            $data['setting']['filter_name'] = $filter_name;
            $filter2url .= '&filter_name=' . $filter_name;
        } else {
            $filter_name = '';
            $data['setting']['filter_name'] = $filter_name;
        }
        if (isset($this->request->get['filter_sku_exist']) AND !empty($this->request->get['filter_sku_exist'])) {
            $filter_sku_exist = $this->request->get['filter_sku_exist'];
            $data['setting']['filter_sku_exist'] = $filter_sku_exist;
            $filter2url .= '&filter_sku_exist=' . $filter_sku_exist;
        } else {
            $filter_sku_exist = '';
            $data['setting']['filter_sku_exist'] = $filter_sku_exist;
        }
        if (isset($this->request->get['filter_model']) AND !empty($this->request->get['filter_model'])) {
            $filter_model = $this->request->get['filter_model'];
            $data['setting']['filter_model'] = $filter_model;
            $filter2url .= '&filter_model=' . $filter_model;
        } else {
            $filter_model = '';
            $data['setting']['filter_model'] = $filter_model;
        }
        //Добавление таблицы ссылок, с проверкой
        //$this->model_extension_parser->createParserUrls();
        //Создание таблицы для хранения ссылок при парсинге нескольких продуктов
      //  $this->model_extension_parser->createTableUrls();
      
		if ($this->model_extension_parser->getTableUrlRows() > 0) {
            $data['error_table'] = 1;
        }
		
        $data = array_merge($data, array(
            'filter_image_main' => isset($data['setting']['filter_image_main']) ? $data['setting']['filter_image_main'] : '',
            'filter_image_all' => isset($data['setting']['filter_image_all']) ? $data['setting']['filter_image_all'] : '',
            'filter_attribute' => isset($data['setting']['filter_attribute']) ? $data['setting']['filter_attribute'] : '',
            'filter_description' => isset($data['setting']['filter_description']) ? $data['setting']['filter_description'] : '',
            'filter_sku' => isset($data['setting']['filter_sku']) ? $data['setting']['filter_sku'] : '',
            'filter_url' => isset($data['setting']['filter_url']) ? $data['setting']['filter_url'] : '',
            'filter_url_empty' => isset($data['setting']['filter_url_empty']) ? $data['setting']['filter_url_empty'] : '',
            'filter_model' => isset($data['setting']['filter_model']) ? $data['setting']['filter_model'] : '',
            'filter_onproduct' => isset($data['setting']['filter_onproduct']) ? $data['setting']['filter_onproduct'] : '',
            'start' => isset($data['setting']['countproduct']) ? $page * (int)$data['setting']['countproduct'] : 0,
            'limit' => isset($data['setting']['countproduct']) ? $data['setting']['countproduct'] : 10,
            'filter_category' => isset($data['setting']['filter_category']) ? $data['setting']['filter_category'] : array(),
            'filter_price' => isset($data['setting']['filter_price']) ? $data['setting']['filter_price'] : '',
            'filter_time' => isset($data['setting']['time']) ? date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) - (int)$data['setting']['time'] * 60) : date("Y-m-d H:i:s"),
            'filter_name' => $filter_name,
            'filter_sku_exist' => $filter_sku_exist,
            'source' => isset($data['setting']['source']) ? $data['setting']['source'] : '',
        ));
        $results = $this->model_extension_parser->SearchProducts($data);
        $product_total = $this->model_extension_parser->getTotalProducts($data);
        $this->load->model('tool/image');
        $data['products'] = array();
        foreach ($results as $result) {
            $url_parsing = '';
            if ($result['image'] && file_exists(DIR_IMAGE_PARSER . $result['image'])) {
                $image = $this->model_tool_image->resize($result['image'], 40, 40);
            } else {
                $image = $this->model_tool_image->resize('no_image.jpg', 40, 40);
            }
            //Добавление ссылок в таблицу товаров
            $url_parsing = $this->getUrlProduct($result['product_id']);
            $data['products'][] = array(
                'product_id' => $result['product_id'],
                'name' => $result['name'],
                'model' => $result['model'],
                'price' => $result['price'],
                'sku' => $result['sku'],
                'image' => $image,
                'url_parsing' => $url_parsing,
                'url_parsing_text' => substr($url_parsing, 0, 20).'...'.substr($url_parsing, strlen($url_parsing)-10),
                'edit'       => $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . $url, true)
            );
        }

        $this->load->model('catalog/product');
        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = (string)($page + 1);
        $pagination->limit = isset($data['setting']['countproduct']) ? $data['setting']['countproduct'] : 10;
        $pagination->text = $this->language->get('text_pagination');
        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page) * $pagination->limit) + 1 : 1, ((($page) * $pagination->limit) > ($product_total - $pagination->limit)) ? $product_total : ((($page) * $pagination->limit) + $pagination->limit), $product_total, ceil($product_total / $pagination->limit));
        $pagination->url = $this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'] . '&page={page}' . $filter2url, 'SSL');
        $data['pagination'] = $pagination->render();
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/module/parsermanager', $data));
    }

    public function deleteUrl()
    {
        if (isset($this->request->get['product_id']) AND !empty($this->request->get['product_id'])) {
            $product_id = $this->request->get['product_id'];
            $this->load->model('extension/parser');
            $data['setting'] = $this->config->get('parsermanager_setting');
            if ($data['setting']['source'] == '1')
                $this->model_extension_parser->deleteUrlYandex($product_id);
            if ($data['setting']['source'] == '2')
                $this->model_extension_parser->deleteUrlHotline($product_id);
            if ($data['setting']['source'] == '3')
                $this->model_extension_parser->deleteUrlOnliner($product_id);
            if ($data['setting']['source'] == '4')
                $this->model_extension_parser->deleteUrlMail($product_id);
        }
    }

    public function addUrl()
    {
        if (isset($this->request->post['product_id']) AND !empty($this->request->post['product_id'])
            AND isset($this->request->post['url']) AND !empty($this->request->post['url'])
        ) {
            $product_id = $this->request->post['product_id'];
            $url = trim(html_entity_decode(html_entity_decode(($this->request->post['url']))));
            $this->load->model('extension/parser');
            $data['setting'] = $this->config->get('parsermanager_setting');
            $product_total = $this->model_extension_parser->getProductParseUrls($product_id);
            if (empty($product_total)) {
                $this->model_extension_parser->addProductParseUrls($product_id);
            }
            if ($data['setting']['source'] == '1')
                $this->model_extension_parser->insertUrlYandex($product_id, $url);
            if ($data['setting']['source'] == '2')
                $this->model_extension_parser->insertUrlHotline($product_id, $url);
            if ($data['setting']['source'] == '3')
                $this->model_extension_parser->insertUrlOnliner($product_id, $url);
            if ($data['setting']['source'] == '4')
                $this->model_extension_parser->insertUrlMail($product_id, $url);
            echo $url;
        }
    }

    public function delete()
    {
        $selected = array();
        $this->load->language('catalog/product');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('catalog/product');
        
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
            $this->response->redirect($this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'));
        }
    }

    public function clearTableUrls()
    {
        $this->load->model('extension/parser');
        $this->model_extension_parser->deleteTableUrlALL();
        echo 'Список очищен!';
    }

    //Парсинг одного продукта по ID
    public function parseProduct()
    {
        $this->load->language('extension/module/parsermanager');
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
                $this->response->redirect($this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'] . $filter . $page, 'SSL'));
                exit;
            }
            if ($setting['source_setting']['productdata'] == '1') {
                $this->parsingProductForAdd($product_id, $url_parsing);
            }
            if ($setting['source_setting']['productdata'] == '0') {
                $this->parsingProductForReplace($product_id, $url_parsing);
            }
        }
        $this->response->redirect($this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'] . $filter . $page, 'SSL'));
        exit;
    }
    /*------------------------------------------------------------------*/
    /*---------------------------Замена---------------------------------*/
    /*------------------------------------------------------------------*/
    public function parsingProductForReplace($product_id, $url_parsing)
    {
        $this->load->language('extension/module/parsermanager');
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
    /*------------------------------------------------------------------*/
    /*---------------------------Добавление-----------------------------*/
    /*------------------------------------------------------------------*/
    public function parsingProductForAdd($product_id, $url_parsing)
    {
        $this->load->language('extension/module/parsermanager');
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
        $this->load->model('extension/parser');
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
                    $products = $this->model_extension_parser->getProductParseUrls($product['product_id']);
                    if (empty($products)) {
                        $this->model_extension_parser->addProductParseUrls($product['product_id']);
                    }
                    //Поиск товаров на яндексе		
                    if ($setting['source'] == '1') {
                        $url = $this->url_market . '/search?cvredirect=1&text=' . rawurlencode($search);
                        $data_find = $this->FindManagerByURlYandex($url);
                        if (isset($data_find['captcha'])) {
                            if (isset($data_find['captcha']['key']) AND isset($data_find['captcha']['retpath']) AND isset($data_find['captcha']['img'])) {
                                $this->viewYandexCaptcha($data_find['captcha']['key'], $data_find['captcha']['retpath'], $data_find['captcha']['img']);
                            }
                        }						
						
                        if (isset($data_find['items'][0]['href'])) {
							$pattern = '#^(.*product\/\d{1,}.*)[\/|?]#Ui';
							preg_match($pattern, $data_find['items'][0]['href'], $m_href);
							if ($m_href) {
								$data_find['items'][0]['href'] = $m_href[1] . '?track=tabs';
							}
                            $this->model_extension_parser->insertUrlYandex($product['product_id'], $data_find['items'][0]['href']);
                        }
                    }
                    //Поиск товаров на хотлайне			
                    if ($setting['source'] == '2') {
                        $url = 'https://hotline.ua/sr/?q=' . $this->replace_yandex(rawurlencode($search));
                        $data_find = $this->FindManagerByURlHotline($url);
                        if (isset($data_find[0]['href'])) {
                            $this->model_extension_parser->insertUrlHotline($product['product_id'], $data_find[0]['href']);
                        } elseif (isset($data_find[0]['href'])) {
                            $this->model_extension_parser->insertUrlHotline($product['product_id'], $data_find[0]['href']);
                        }
                    }
                    //Поиск товаров на онлайнере		
                    if ($setting['source'] == '3') {
                        $url = 'https://catalog.api.onliner.by/search/products?query=' . $this->replace_yandex($search);
                        $data_find = $this->FindManagerByURlOnliner($url);
                        if (isset($data_find[0]['href'])) {
                            $this->model_extension_parser->insertUrlOnliner($product['product_id'], $data_find[0]['href']);
                        } elseif (isset($data_find[1]['href'])) {
                            $this->model_extension_parser->insertUrlOnliner($product['product_id'], $data_find[1]['href']);
                        }
                    }
                    if ($setting['source'] == '4') {
                        $url = 'http://torg.mail.ru/search/?q=' . $this->replace_yandex($search);
                        $data_find = $this->FindManagerByURlMail($url);
                        if (isset($data_find[0]['href'])) {
                            $this->model_extension_parser->insertUrlMail($product['product_id'], $data_find[0]['href']);
                        } elseif (isset($data_find[1]['href'])) {
                            $this->model_extension_parser->insertUrlMail($product['product_id'], $data_find[1]['href']);
                        }
                    }
                }
            }
        }
        $this->response->redirect($this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'] . $filter . $page, 'SSL'));
        exit;
    }

    public function parsingAllProducts()
    {
        $setting = $this->getSettingParser();
        $this->load->model('extension/parser');
        //$this->model_extension_parser->deleteTableUrlALL();		
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
                        $this->model_extension_parser->insertTableUrl($val['product_id'], $url);
                }
            }
        }
        $product_parsing = $this->model_extension_parser->getTableUrls();
        foreach ($product_parsing as $product) {
            if ($setting['source_setting']['productdata'] == '1') {
                if ($this->parsingProductForAdd($product['product_id'], $product['url'])) {
                    $this->model_extension_parser->deleteProductTableUrl($product['product_id']);
                }
            }
            if ($setting['source_setting']['productdata'] == '0') {
                if ($this->parsingProductForReplace($product['product_id'], $product['url'])) {
                    $this->model_extension_parser->deleteProductTableUrl($product['product_id']);
                }
            }
            MCurl::addMessage("Задержка : " . (int)$setting['pause'] . ' секунд.');
            sleep((int)$setting['pause']);
        }
        $this->response->redirect($this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'] . $filter . $page, 'SSL'));
        exit;
    }

    public function AddProductParser()
    {
        $this->load->model('extension/parser');
        $this->load->language('extension/module/parsermanager');
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
                $pattern = '#^(.*product\/\d{1,}.*)[\/|?]#Ui';
                preg_match($pattern, $url, $m_href);
                if ($m_href) {
                    $url = $m_href[1] . '?track=tabs';
                }
                if ($data = $this->parserYandex($url)) {
                    //проверка на дубликаты при добавленнии товара
                    $el = array_keys($data['product_description']);
                    $result = $this->model_extension_parser->getProductName($data['product_description'][$el[0]]['name']);
                    if (!empty($result)) {
                        MCurl::addMessage("Товар: " . $data['product_description'][$el[0]]['name'] . ' не добавлен, такой товар уже есть.');
                        $this->session->data['error'] = "Товар не добавлен, такой товар уже существует!";
                        $this->response->redirect($this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'] . $filter, 'SSL'));
                        exit;
                    }
                    $data['url_yandex'] = $url;
                    if (!isset($setting['source_setting']['manufacturer'])) {
                        if (isset($setting['manufacturer_id'])) {
                            $data = array_merge($data, array('manufacturer_id' => $setting['manufacturer_id']));
                        }
                    }
                    if (isset($setting['main_category_id'])) {
                        $data = array_merge($data, array('main_category_id' => $setting['main_category_id']));
                    }
                    if (isset($setting['product_category'])) {
                        $data = array_merge($data, array('product_category' => $setting['product_category']));
                    }
                    if (isset($this->request->get['sku'])) {
                        $data['sku'] = $this->request->get['sku'];
                    }
                    if (isset($this->request->get['price'])) {
                        $data['price'] = (int)$this->request->get['price'];
                    }
                    $this->model_extension_parser->addProduct($data);
                    $this->session->data['success'] = $this->language->get('text_success');
                }
            }
            if ($setting['source'] == '2') {
                if ($data = $this->parserHotline($url)) {
                    //проверка на дубликаты при добавленнии товара
                    $el = array_keys($data['product_description']);
                    $result = $this->model_extension_parser->getProductName($data['product_description'][$el[0]]['name']);
                    if (!empty($result)) {
                        MCurl::addMessage("Товар: " . $data['product_description'][$el[0]]['name'] . ' не добавлен, такой товар уже есть.');
                        $this->session->data['error'] = "Товар не добавлен, такой товар уже существует!";
                        $this->response->redirect($this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'] . $filter, 'SSL'));
                        exit;
                    }
                    $data['url_hotline'] = $url;
                    if (!isset($setting['source_setting']['manufacturer'])) {
                        if (isset($setting['manufacturer_id'])) {
                            $data = array_merge($data, array('manufacturer_id' => $setting['manufacturer_id']));
                        }
                    }
                    if (isset($setting['main_category_id'])) {
                        $data = array_merge($data, array('main_category_id' => $setting['main_category_id']));
                    }
                    if (isset($setting['product_category'])) {
                        $data = array_merge($data, array('product_category' => $setting['product_category']));
                    }
                    if (isset($this->request->get['sku'])) {
                        $data['sku'] = $this->request->get['sku'];
                    }
                    if (isset($this->request->get['price'])) {
                        $data['price'] = (int)$this->request->get['price'];
                    }
                    $this->model_extension_parser->addProduct($data);
                    $this->session->data['success'] = $this->language->get('text_success');
                }
            }
            if ($setting['source'] == '3') {
                if ($data = $this->parserOnliner($url)) {
                    //проверка на дубликаты при добавленнии товара
                    $el = array_keys($data['product_description']);
                    $result = $this->model_extension_parser->getProductName($data['product_description'][$el[0]]['name']);
                    if (!empty($result)) {
                        MCurl::addMessage("Товар: " . $data['product_description'][$el[0]]['name'] . ' не добавлен, такой товар уже есть.');
                        $this->session->data['error'] = "Товар не добавлен, такой товар уже существует!";
                        $this->response->redirect($this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'] . $filter, 'SSL'));
                        exit;
                    }
                    $data['url_onliner'] = $url;
                    if (!isset($setting['source_setting']['manufacturer'])) {
                        if (isset($setting['manufacturer_id'])) {
                            $data = array_merge($data, array('manufacturer_id' => $setting['manufacturer_id']));
                        }
                    }
                    if (isset($setting['main_category_id'])) {
                        $data = array_merge($data, array('main_category_id' => $setting['main_category_id']));
                    }
                    if (isset($setting['product_category'])) {
                        $data = array_merge($data, array('product_category' => $setting['product_category']));
                    }
                    if (isset($this->request->get['sku'])) {
                        $data['sku'] = $this->request->get['sku'];
                    }
                    if (isset($this->request->get['price'])) {
                        $data['price'] = (int)$this->request->get['price'];
                    }
                    $this->model_extension_parser->addProduct($data);
                    $this->session->data['success'] = $this->language->get('text_success');
                }
            }
            if ($setting['source'] == '4') {
                if ($data = $this->parserMail($url)) {
                    //проверка на дубликаты при добавленнии товара
                    $el = array_keys($data['product_description']);
                    $result = $this->model_extension_parser->getProductName($data['product_description'][$el[0]]['name']);
                    if (!empty($result)) {
                        MCurl::addMessage("Товар: " . $data['product_description'][$el[0]]['name'] . ' не добавлен, такой товар уже есть.');
                        $this->session->data['error'] = "Товар не добавлен, такой товар уже существует!";
                        $this->response->redirect($this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'] . $filter, 'SSL'));
                        exit;
                    }
                    $data['url_mail'] = $url;
                    if (!isset($setting['source_setting']['manufacturer'])) {
                        if (isset($setting['manufacturer_id'])) {
                            $data = array_merge($data, array('manufacturer_id' => $setting['manufacturer_id']));
                        }
                    }
                    if (isset($setting['main_category_id'])) {
                        $data = array_merge($data, array('main_category_id' => $setting['main_category_id']));
                    }
                    if (isset($setting['product_category'])) {
                        $data = array_merge($data, array('product_category' => $setting['product_category']));
                    }
                    if (isset($this->request->get['sku'])) {
                        $data['sku'] = $this->request->get['sku'];
                    }
                    if (isset($this->request->get['price'])) {
                        $data['price'] = (int)$this->request->get['price'];
                    }
                    $this->model_extension_parser->addProduct($data);
                    $this->session->data['success'] = $this->language->get('text_success');
                }
            }
        }
        $this->response->redirect($this->url->link('extension/module/parsermanager', 'user_token=' . $this->session->data['user_token'] . $filter, 'SSL'));
        exit;
    }

    private function updateProductAndAdd($product_id, $data1 = array())
    {
        $this->load->model('localisation/language');
        $this->load->model('catalog/product');
        $setting = $this->getSettingParser();
        $artributes = $this->model_catalog_product->getProductAttributes($product_id);
        $data = $this->model_catalog_product->getProduct($product_id);
        $data['languages'] = $this->model_localisation_language->getLanguages();
        $data = array_merge($data, array(
            'product_description' => $this->model_catalog_product->getProductDescriptions($product_id)
        ));
        if (isset($setting['source_setting']['title']) && $setting['source_setting']['title'] == '1') {
            foreach ($data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['name'] = $data1['product_description'][$language['language_id']]['name'];
                $data['product_description'][$language['language_id']]['seo_h1'] = $data1['product_description'][$language['language_id']]['seo_h1'];
                $data['product_description'][$language['language_id']]['seo_title'] = $data1['product_description'][$language['language_id']]['seo_title'];
                $data['product_description'][$language['language_id']]['meta_keyword'] = $data1['product_description'][$language['language_id']]['meta_keyword'];
            }
            $data['model'] = $data1['model'];
        }
        if (isset($setting['source_setting']['addescription']) && $setting['source_setting']['addescription'] == '1') {
            foreach ($data['languages'] as $language) {
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
            foreach ($data['languages'] as $language) {
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

    private function updateProductAndReplace($product_id, $data1 = array())
    {
        $this->load->model('localisation/language');
        $this->load->model('catalog/product');
        $setting = $this->getSettingParser();
        $artributes = $this->model_catalog_product->getProductAttributes($product_id);
        $data = $this->model_catalog_product->getProduct($product_id);
        $data['languages'] = $this->model_localisation_language->getLanguages();
        $data = array_merge($data, array(
            'product_description' => $this->model_catalog_product->getProductDescriptions($product_id)
        ));
        if (isset($setting['source_setting']['title']) && $setting['source_setting']['title'] == '1') {
            foreach ($data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['name'] = $data1['product_description'][$language['language_id']]['name'];
            }
        }
        if (isset($setting['source_setting']['model']) && $setting['source_setting']['model'] == '1') {
            $data['model'] = $data1['model'];
        }
        if (isset($setting['source_setting']['meta_description']) && $setting['source_setting']['meta_description'] == '1') {
            foreach ($data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['meta_description'] .= $data1['product_description'][$language['language_id']]['meta_description'];
            }
        }
        if (isset($setting['source_setting']['seo_h1']) && $setting['source_setting']['seo_h1'] == '1') {
            foreach ($data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['seo_h1'] = $data1['product_description'][$language['language_id']]['seo_h1'];
            }
        }
        if (isset($setting['source_setting']['meta_keyword']) && $setting['source_setting']['meta_keyword'] == '1') {
            foreach ($data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['meta_keyword'] = $data1['product_description'][$language['language_id']]['meta_keyword'];
            }
        }
        if (isset($setting['source_setting']['seo_title']) && $setting['source_setting']['seo_title'] == '1') {
            foreach ($data['languages'] as $language) {
                $data['product_description'][$language['language_id']]['seo_title'] = $data1['product_description'][$language['language_id']]['seo_title'];
            }
        }
        if (isset($setting['source_setting']['addescription']) && $setting['source_setting']['addescription'] == '1') {
            foreach ($data['languages'] as $language) {
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
        $this->load->model('extension/parser');
        $product_total = $this->model_extension_parser->getProductParseUrls($product_id);
        $setting_parser = $this->config->get('parsermanager_setting');
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

    //Настройки для текущего ресурса
    private function getSettingParser()
    {
        $setting = array();
        $setting_parser = $this->config->get('parsermanager_setting');
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

    private function addAtributes($attribyte_groupe, $attributes)
    {
        $arr_to_prod1 = array();
        $array_attribute = $attributes;
        $this->load->model('extension/parser');
        $search_attribyte_groupe = $this->model_extension_parser->SearchAttribyteGroupe($attribyte_groupe);
        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();
        if (empty($search_attribyte_groupe)) {
            foreach ($data['languages'] as $language) {
                $data['attribute_group_description'][$language['language_id']]['name'] = $attribyte_groupe;
            }
            $data['sort_order'] = 10;
            $this->load->model('catalog/attribute_group');
            $this->model_catalog_attribute_group->addAttributeGroup($data);
        }
        $search_attribyte_groupe = $this->model_extension_parser->SearchAttribyteGroupe($attribyte_groupe);
        $i = 100;
        foreach ($attributes as $key => $attribute) {
            $searchAttByGrup = array();
            $searchAttByGrup = $this->model_extension_parser->SearchAttByGrup($key, $search_attribyte_groupe['attribute_group_id']);
            if (empty($searchAttByGrup)) {
                foreach ($data['languages'] as $language) {
                    $d['attribute_description'][$language['language_id']]['name'] = $key;
                }
                $d['attribute_group_id'] = $search_attribyte_groupe['attribute_group_id'];
                $d['sort_order'] = $i;
                $this->load->model('catalog/attribute');
                $this->model_catalog_attribute->addAttribute($d);
                $i++;
            }
            $searchAttByGrup = $this->model_extension_parser->SearchAttByGrup($key, $search_attribyte_groupe['attribute_group_id']);
            $arr_to_prod['name'] = $key;
            $arr_to_prod['attribute_id'] = $searchAttByGrup['attribute_id'];
            foreach ($data['languages'] as $language) {
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
        $this->load->model('extension/parser');
        $searchManufacture = $this->model_extension_parser->getManufactureName($manufacture);
        if (empty($searchManufacture)) {
            $this->load->model('localisation/language');
            $data['languages'] = $this->model_localisation_language->getLanguages();
            $this->load->model('catalog/manufacturer');
            $manufacture_array = $this->manufacture_array;
            foreach ($data['languages'] as $language) {
                $manufacture_array['manufacturer_description'][$language['language_id']] = array(
                    'seo_h1' => $manufacture,
                    'name' => $manufacture,
                    'meta_title' => $manufacture,
                    'meta_h1' => $manufacture,
                    'seo_title' => $manufacture,
                    'meta_keyword' => $manufacture,
                    'meta_description' => '',
                    'description' => ''
                );
            }
            $manufacture_array['name'] = $manufacture;
            $this->model_catalog_manufacturer->addManufacturer($manufacture_array);
            $searchManufacture = $this->model_extension_parser->getManufactureName($manufacture);
        }
        $manufacture_id = $searchManufacture['manufacturer_id'];
        return $manufacture_id;
    }

    public function getDataParser($items = array())
    {
        $this->load->model('localisation/language');
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
        $data['languages'] = $this->model_localisation_language->getLanguages();
        foreach ($data['languages'] as $language) {
            $data['product_description'][$language['language_id']] = array(
                'name' => $items['title'],
                'seo_h1' => $items['title'],
                'seo_title' => $items['title'],
                'meta_keyword' => $items['title'],
                'meta_title' => $items['title'],
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
            $nImages = array();
            foreach ($items['img_arr'] as $dataImage) {
                $nImages[] = array_merge($dataImage, array('video' => ''));
            }
            $data = array_merge($data,
                array('product_image' => $nImages));
        }
        if (!empty($items['attribute'])) {
            $i = 0;
            foreach ($items['attribute'] as $attribute) {
                foreach ($attribute['product_attribute_description'] as $product_attribute_description) {
                    foreach ($data['languages'] as $language) {
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
        $data['setting'] = $this->config->get('parsermanager_setting');
        $data['title'] = $this->language->get('heading_title');
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $data['base'] = HTTPS_SERVER;
        } else {
            $data['base'] = HTTP_SERVER;
        }
        $data['entry_folder'] = $this->language->get('entry_folder');
        $data['entry_move'] = $this->language->get('entry_move');
        $data['entry_copy'] = $this->language->get('entry_copy');
        $data['entry_rename'] = $this->language->get('entry_rename');
        $data['button_folder'] = $this->language->get('button_folder');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_rename'] = $this->language->get('button_rename');
        $data['button_upload'] = $this->language->get('button_upload');
        $data['button_refresh'] = $this->language->get('button_refresh');
        $data['button_submit'] = $this->language->get('button_submit');
        $data['error_select'] = $this->language->get('error_select');
        $data['error_directory'] = $this->language->get('error_directory');
        $data['user_token'] = $this->session->data['user_token'];
        if (isset($this->request->get['product_id']) AND $this->request->get['product_id'] != 'null') {
            $product_id = $this->request->get['product_id'];
            $data['product_id'] = $this->request->get['product_id'];
        } else {
            $product_id = 0;
            $data['product_id'] = 0;
        }
        if (isset($this->request->get['product_name']) AND $this->request->get['product_name'] != 'null') {
            $search = $this->request->get['product_name'];
        } else {
            $this->load->model('catalog/product');
            $product_total = $this->model_catalog_product->getProduct($product_id);
            $product_description = $this->model_catalog_product->getProductDescriptions($product_id);
            if ($data['setting']['productsearch'] == '0') {
                $search = $product_total['model'];
            } else {
                $search = $product_description[1]['name'];
            }
        }
        if (isset($data['setting']['region'])) {
            if ($data['setting']['region'] == '213') {
                $this->url_market = 'https://market.yandex.ru';
            }
            if ($data['setting']['region'] == '143') {
                $this->url_market = 'https://market.yandex.ua';
            }
            if ($data['setting']['region'] == '157') {
                $this->url_market = 'https://market.yandex.by';
            }
            if ($data['setting']['region'] == '162') {
                $this->url_market = 'https://market.yandex.kz';
            }
        }
        $data['items'] = array();
        //Поиск товаров на яндексе		
        if ($data['setting']['source'] == '1') {
            $url = $this->url_market . '/search?cvredirect=1&text=' . rawurlencode($search);
            $data_find = $this->FindManagerByURlYandex($url);
            $data['items'] = $data_find['items'];
            if (isset($data_find['captcha'])) {
                if (isset($data_find['captcha']['key']) AND isset($data_find['captcha']['retpath']) AND isset($data_find['captcha']['img'])) {
                    $this->viewYandexCaptcha($data_find['captcha']['key'], $data_find['captcha']['retpath'], $data_find['captcha']['img']);
                }
            }
			
			
        }
        //Поиск товаров на хотлайне			
        if ($data['setting']['source'] == '2') {
            $url = 'https://hotline.ua/sr/?q=' . $this->replace_yandex(rawurlencode($search));
            $data['items'] = $this->FindManagerByURlHotline($url);
        }
        //Поиск товаров на онлайнере		
        if ($data['setting']['source'] == '3') {
            $url = 'https://catalog.api.onliner.by/search/products?query=' . $this->replace_yandex($search);
            $data['items'] = $this->FindManagerByURlOnliner($url);
        }
        if ($data['setting']['source'] == '4') {
            $url = 'http://torg.mail.ru/search/?q=' . $this->replace_yandex($search);
            $data['items'] = $this->FindManagerByURlMail($url);
        }
        $this->response->setOutput($this->load->view('common/findmanager', $data));
    }

    private function getContentParser($url)
    {
        $data['setting'] = $this->config->get('parsermanager_setting');
        $page = Mcurl::getInstance();
		$page->setUserAgent($data['setting']['user_agent']);
        if ($data['setting']['cookie_check'] == 1) {
            $region = $data['setting']['region'];
            $page->setCookie($region);
        }
        if ($data['setting']['proxy_check'] == 0) {
            $content = $page->getContent($url);
            return $content;
        }
        if ($data['setting']['proxy_check'] == 1) {
            $page->setProxy($data['setting']['proxy_port'], $data['setting']['user_pass']);
            $content = $page->getContent($url);
            return $content;
        }
        if ($data['setting']['proxy_check'] == 2) {
            $content = $page->getContentProxyList($url, PROXY_LIST);
            return $content;
        }
    }

    private function getImageParser($url, $dir, $file)
    {
        $data['setting'] = $this->config->get('parsermanager_setting');
        $page = Mcurl::getInstance();
		//$page->setUserAgent($data['setting']['user_agent']);
        if ($data['setting']['proxy_check'] == 0) {
            $content = $page->getImage($url, $dir, $file);
            return $content;
        }
        if ($data['setting']['proxy_check'] == 1) {
            $page->setProxy($data['setting']['proxy_port'], $data['setting']['user_pass']);
            $content = $page->getImage($url, $dir, $file);
            return $content;
        }
        if ($data['setting']['proxy_check'] == 2) {
            $content = $page->getImageProxyList($url, $dir, $file, PROXY_LIST);
            return $content;
        }
    }

    public function autocompleteparserj()
    {
        $json = array();
        if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
            $this->load->model('catalog/product');
            $this->load->model('extension/parser');
            $data['setting'] = $this->config->get('parsermanager_setting');
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
                'filter_image_main' => isset($data['setting']['filter_image_main']) ? $data['setting']['filter_image_main'] : '',
                'filter_image_all' => isset($data['setting']['filter_image_all']) ? $data['setting']['filter_image_all'] : '',
                'filter_attribute' => isset($data['setting']['filter_attribute']) ? $data['setting']['filter_attribute'] : '',
                'filter_description' => isset($data['setting']['filter_description']) ? $data['setting']['filter_description'] : '',
                'filter_sku' => isset($data['setting']['filter_sku']) ? $data['setting']['filter_sku'] : '',
                'filter_model' => $filter_model,
                'start' => 0,
                'limit' => 20,
                'filter_category' => isset($data['setting']['filter_category']) ? $data['setting']['filter_category'] : array(),
                'filter_price' => isset($data['setting']['filter_price']) ? $data['setting']['filter_price'] : '',
                'filter_name' => $filter_name
            );
            $results = $this->model_extension_parser->SearchProducts($data);
            foreach ($results as $result) {
                $json[] = array(
                    'product_id' => $result['product_id'],
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'model' => $result['model'],
                    'price' => $result['price'],
                    'sku' => $result['sku'],
                );
            }
        }
        $this->response->setOutput(json_encode($json));
    }

    //folder manager
    public function FolderManager()
    {
        $this->load->language('extension/module/parsermanager');
        if (isset($this->request->get['filter_name'])) {
            $filter_name = rtrim(str_replace(array('../', '..\\', '..', '*'), '', $this->request->get['filter_name']), '/');
        } else {
            $filter_name = null;
        }
        // Make sure we have the correct directory
        if (isset($this->request->get['directory'])) {
            $directory = rtrim(DIR_IMAGE . 'catalog/' . str_replace(array('../', '..\\', '..'), '', $this->request->get['directory']), '/');
        } else {
            $directory = DIR_IMAGE . 'catalog';
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        $data['images'] = array();
        $this->load->model('tool/image');
        // Get directories
        $directories = glob($directory . '/' . $filter_name . '*', GLOB_ONLYDIR);
        if (!$directories) {
            $directories = array();
        }
        // Get files
        $files = glob($directory . '/' . $filter_name . '*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}', GLOB_BRACE);
        if (!$files) {
            $files = array();
        }
        // Merge directories and files
        $images = array_merge($directories, $files);
        // Get total number of files and directories
        $image_total = count($images);
        // Split the array based on current page number and max number of items per page of 10
        $images = array_splice($images, ($page - 1) * 16, 16);
        foreach ($images as $image) {
            $name = str_split(basename($image), 14);
            if (is_dir($image)) {
                $url = '';
                if (isset($this->request->get['target'])) {
                    $url .= '&target=' . $this->request->get['target'];
                }
                if (isset($this->request->get['thumb'])) {
                    $url .= '&thumb=' . $this->request->get['thumb'];
                }
                $data['images'][] = array(
                    'thumb' => '',
                    'name' => implode(' ', $name),
                    'type' => 'directory',
                    'path' => utf8_substr($image, utf8_strlen(DIR_IMAGE)),
                    'href' => $this->url->link('extension/module/parsermanager/foldermanager', 'user_token=' . $this->session->data['user_token'] . '&directory=' . urlencode(utf8_substr($image, utf8_strlen(DIR_IMAGE . 'catalog/'))) . $url, 'SSL')
                );
            }
        }
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['entry_search'] = $this->language->get('entry_search');
        $data['entry_folder'] = $this->language->get('entry_folder');
        $data['button_parent'] = $this->language->get('button_parent');
        $data['button_refresh'] = $this->language->get('button_refresh');
        $data['button_upload'] = $this->language->get('button_upload');
        $data['button_folder'] = $this->language->get('button_folder');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_search'] = $this->language->get('button_search');
        $data['folder_manager'] = $this->language->get('folder_manager');
        $data['user_token'] = $this->session->data['user_token'];
        if (isset($this->request->get['directory'])) {
            $data['directory'] = urlencode($this->request->get['directory']);
        } else {
            $data['directory'] = '';
        }
        if (isset($this->request->get['filter_name'])) {
            $data['filter_name'] = $this->request->get['filter_name'];
        } else {
            $data['filter_name'] = '';
        }
        // Return the target ID for the file manager to set the value
        if (isset($this->request->get['target'])) {
            $data['target'] = $this->request->get['target'];
        } else {
            $data['target'] = '';
        }
        // Return the thumbnail for the file manager to show a thumbnail
        if (isset($this->request->get['thumb'])) {
            $data['thumb'] = $this->request->get['thumb'];
        } else {
            $data['thumb'] = '';
        }
        // Parent
        $url = '';
        if (isset($this->request->get['directory'])) {
            $pos = strrpos($this->request->get['directory'], '/');
            if ($pos) {
                $url .= '&directory=' . urlencode(substr($this->request->get['directory'], 0, $pos));
            }
        }
        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }
        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }
        $data['parent'] = $this->url->link('extension/module/parsermanager/foldermanager', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
        // Refresh
        $url = '';
        if (isset($this->request->get['directory'])) {
            $url .= '&directory=' . urlencode($this->request->get['directory']);
        }
        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }
        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }
        $data['refresh'] = $this->url->link('extension/module/parsermanager/foldermanager', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
        $url = '';
        if (isset($this->request->get['directory'])) {
            $url .= '&directory=' . urlencode(html_entity_decode($this->request->get['directory'], ENT_QUOTES, 'UTF-8'));
        }
        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }
        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }
        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        $pagination = new Pagination();
        $pagination->total = $image_total;
        $pagination->page = $page;
        $pagination->limit = 16;
        $pagination->url = $this->url->link('extension/module/parsermanager/foldermanager', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', 'SSL');
        $data['pagination'] = $pagination->render();
        $this->response->setOutput($this->load->view('common/foldermanager', $data));
    }

    public function proxycheck()
    {
        $page = MCurl::getInstance();
        echo $page->proxyCheckMulti('https://market.yandex.ru', PROXY_LIST, 'title>Яндекс.Маркет');
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
                $msg .= DIR_DOWNLOAD;
                //for security reason, we force to remove all uploaded file
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
        $this->load->language('tool/log');
        $this->document->setTitle($this->language->get('heading_title'));
        $data['heading_title'] = $this->language->get('heading_title');
        $data['button_clear'] = $this->language->get('button_clear');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['error_warning'] = '';
        $data['text_list'] = $this->language->get('text_list');
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => false
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('tool/log', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => ' :: '
        );
        $data['clear'] = $this->url->link('tool/log/clear', 'user_token=' . $this->session->data['user_token'], 'SSL');
        $file = PARSER_LOG;
        if (file_exists($file)) {
            $data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
        } else {
            $data['log'] = '';
        }
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        $this->response->setOutput($this->load->view('tool/log', $data));
        $this->template = 'tool/log';
    }

    public function viewPage()
    {
        if (file_exists(DIR_DOWNLOAD . 'content.htm')) {
            $content = file_get_contents(DIR_DOWNLOAD . 'content.htm');
            echo $content;
        } else {
            echo "Ничего нет!";
        }
    }

    public function saveFileParsing($content = '')
    {
        /*$content = preg_replace_callback('#<script.*>#Uims', function ($matches) {
            return '';
        }, $content);
        */
        file_put_contents(DIR_DOWNLOAD . 'content.htm', $content);
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/parsermanager')) {
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

    public function delete_cookie()
    {
        if (file_exists(DIR_DOWNLOAD . 'cookie.txt')) {
            file_put_contents(DIR_DOWNLOAD . 'cookie.txt', '');
            echo "Cookie очищены!";
        } else {
            echo "Ошибка очистки cookie!";
        }
    }

    public function setYandexCaptcha()
    {
        $content = '';
        if (isset($this->request->post['key'])
            AND isset($this->request->post['retpath'])
            AND isset($this->request->post['rep'])
        ) {
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
        $this->response->redirect($this->url->link('extension/module/parsermanager/', 'user_token=' . $this->session->data['user_token'], 'SSL'));
    }

    public function updateTableParser()
    {
        $this->load->model('extension/parser');
        $result = $this->model_extension_parser->updateParserUrls();
        $this->session->data['success'] = $result;
        $this->response->redirect($this->url->link('extension/module/parsermanager/', 'user_token=' . $this->session->data['user_token'], 'SSL'));
    }

    private function yandexCaptcha($content)
    {
        if (preg_match('#name=\"key\" value=\"(.*)\"#Uism', $content, $key_match)) {
            $key = urlencode(html_entity_decode($key_match[1]));
        }
        if (preg_match('#name=\"retpath\" value=\"(.*)\"#Uism', $content, $retpath_match)) {
            $retpath = urlencode(html_entity_decode($retpath_match[1]));
        }
        if (preg_match('#b-captcha__image.*src=\"(.*)\"#Uism', $content, $img_match) OR preg_match('#image form__captcha.*src=\"(.*)\"#Uism', $content, $img_match)) {
            $img = $img_match[1];
        }
        if (isset($key) AND isset($retpath) AND isset($img)) {
            $this->viewYandexCaptcha($key, $retpath, $img);
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
            <form method="POST"
                  action="<?php echo HTTP_SERVER . "index.php?route=extension/module/parsermanager/setyandexcaptcha&user_token=" . $this->session->data['user_token']; ?>">
                <input type="hidden" value="<?php echo $key; ?>" name="key">
                <input type="hidden" value="<?php echo $retpath; ?>" name="retpath">
                <img class="b-captcha__image" src="<?php echo $img; ?>">
                <input value="" name="rep" size=10>
                <input class="b-captcha__submit" type="submit" value="Отправить">
            </form>
        </div>
        </body>
        </html>
        <?php
    }
}
//end class-*+/
