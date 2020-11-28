<?php
$data['items'] = array();
$content = $this->getContentParser($url);	
$html_parse = str_get_html($content);
if (!$html_parse) {
	return false;
}
$this->saveFileParsing($content);
$product_parse = $html_parse->find('li[class=product-item]');
if ($product_parse) {
	for ($i = 0; $i < 10; $i++) {
		if (isset($product_parse[$i])) {					
			$product_title_parse = $product_parse[$i]->find('div[class=item-info] a', 0);
			if ($product_title_parse) {
				$data['items'][$i]['href'] = 'https://hotline.ua' . $product_title_parse->attr['href'];
				$data['items'][$i]['title'] = trim($product_title_parse->plaintext);
			} else {
				continue;						
			}
			
			$product_image_parse = $product_parse[$i]->find('img[class=img-product busy]', 0);
			if ($product_image_parse) {
				$data['items'][$i]['src'] = 'https://hotline.ua' .$product_image_parse->attr['src'];
			} else {
				$data['items'][$i]['src'] = '';
			}                
			
			$product_desc_parse = $product_parse[$i]->find('div[class=item-info] div[class=text]', 0);
			if ($product_desc_parse) {
				$data['items'][$i]['desc'] = trim($product_desc_parse->plaintext);						
			} else {
				$data['items'][$i]['desc'] = '';						
			}
			
			$product_price_parse = $product_parse[$i]->find('div[class=item-info] div [class=price-lg]', 0);
			if ($product_price_parse) {
				$data['items'][$i]['price'] = $product_price_parse->outertext;
			} else {
				$data['items'][$i]['price'] = 'Не найдена.';
			}
			
		}
	}
}

$html_parse->clear();
unset($html_parse);
return $data['items'];
?>