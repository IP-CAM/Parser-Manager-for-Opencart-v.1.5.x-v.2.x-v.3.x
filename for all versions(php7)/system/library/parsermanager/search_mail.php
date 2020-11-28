<?php
$this->url_market = 'http://torg.mail.ru';
$data['items'] = array();
$content = $this->getContentParser($url);
$content = iconv("windows-1251","utf-8",  $content);
$this->saveFileParsing($content);
$html_parse = str_get_html($content);
if (!$html_parse) {
	return false;
}
$product_parse = $html_parse->find('ul[class=preview-card-list__items] li');
if ($product_parse && !empty($product_parse)) {
	for ($i = 0; $i < 10; $i++) {
		if (isset($product_parse[$i])) {
			$product_title_parse = $product_parse[$i]->find('div[class=preview-card-line__title-text]', 0);
			if ($product_title_parse) {
				$data['items'][$i]['title'] = trim($product_title_parse->plaintext);
			} else {
				$data['items'][$i]['title'] = '';
			}

			$product_image_parse = $product_parse[$i]->find('img[class^=img img-holder__img]', 0);
			if ($product_image_parse) {
				$data['items'][$i]['src'] =  $product_image_parse->attr['src'];
			} else {
				$data['items'][$i]['src'] = '';
			}

			$product_title_link = $product_parse[$i]->find('div[class=preview-card-line__title] a', 0);
			if ($product_title_link) {
				$data['items'][$i]['href'] =  'https:'.$product_title_link->attr['href'];
			} else {
				$data['items'][$i]['href'] = '';
			}
			$product_desc_parse = $product_parse[$i]->find('div[class=preview-card-line__description]', 0);
			if ($product_desc_parse) {
				$data['items'][$i]['desc'] = trim($product_desc_parse->plaintext);
			} else {
				$data['items'][$i]['desc'] = '';
			}
			$product_price_parse = $product_parse[$i]->find('div[class=preview-card-line__price]', 0);
			if ($product_price_parse) {
				$data['items'][$i]['price'] = $product_price_parse->outertext;
			} else {
				$data['items'][$i]['price'] = 'Не найдена.';
			}
		}
	}
}

foreach($data['items'] as $key => $val){
	if (empty($val['title'])OR(preg_match('#\/go\/\?data=#', $val['href'], $m))){
		unset($data['items'][$key]);
	}
}

$html_parse->clear();
unset($html_parse);
return $data['items'];
?>