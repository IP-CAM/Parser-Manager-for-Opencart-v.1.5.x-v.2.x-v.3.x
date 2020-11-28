<?php
$data['items'] = array();
$content = $this->getContentParser($url);

$this->saveFileParsing($content);		
if (preg_match('#captcha#U', $content, $captcha_match)	) {
    $this->yandexCaptcha($content);
    exit;
}

$html_parse = new simple_html_dom();
$html_parse->load($content);

if (!$html_parse) {
	return false;
}

$product_parse = $html_parse->find('div[class^=n-snippet-card snippet-card clearfix], div[class=n-product-summary__content], div[class^=n-snippet-cell2 i-bem], div[class^=n-snippet-card2 i-bem b-zone], article');

if (!$product_parse) {
	$html_parse->clear();
	unset($html_parse);	
	preg_match('#window.location.replace\(\"(.*)\"#Uis', $content, $matchUrl);
	if(isset($matchUrl[1])){		
		$url = $this->url_market.$matchUrl[1];		
		$content = $this->getContentParser($url);
		$this->saveFileParsing($content);		
		if (preg_match('#captcha#U', $content, $captcha_match)	) {
			$this->yandexCaptcha($content);
			exit;
		}
		$html_parse = new simple_html_dom();
		$html_parse->load($content);

		if (!$html_parse) {
			return false;
		}		
		
		$product_parse = $html_parse->find('div[class^=n-snippet-card snippet-card clearfix], div[class=n-product-summary__content], div[class^=n-snippet-cell2 i-bem], div[class^=n-snippet-card2 i-bem b-zone], article');
		
	}	
}

if ($product_parse) {
	for ($i = 0; $i < 10; $i++) {
		if (isset($product_parse[$i])) {
			$product_image_parse = $product_parse[$i]->find('img', 0);
			if ($product_image_parse) {
				$data['items'][$i]['src'] = $product_image_parse->attr['src'];
			} elseif ($item_src = $html_parse->find('ul[class=n-gallery__thumbs] img', 0)) {
				$data['items'][$i]['src'] = 'http:' . $item_src->attr['src'];
			} else {
				$data['items'][$i]['src'] = '';
			}
			$data['items'][$i]['title'] = '';
			$product_title_parse = $product_parse[$i]->find('h1[class^=title title_size], span[class=snippet-card__header-text], div[class=n-product-title] h1, div[class=n-snippet-cell2__header], div[class=n-snippet-card2__title] a, h3[class=n-snippet-card2__title] a, h3 a', 0);
			if ($product_title_parse) {
				$data['items'][$i]['title'] = trim($product_title_parse->plaintext);
			} else {
				$product_title_parse = $html_parse->find('h1[class^=title title_size], span[class=snippet-card__header-text], div[class=n-product-title] h1, div[class=n-snippet-cell2__header]', 0);
				if ($product_title_parse) {
					 $data['items'][$i]['title'] = trim($product_title_parse->plaintext);
				}
			}
			$product_title_link = $product_parse[$i]->find('a[class=snippet-card__image link], div[class=n-snippet-card2__title] a, h3[class=n-snippet-card2__title] a, h3[class=n-snippet-cell2__title] a, h3 a', 0);
			if ($product_title_link) {
				$data['items'][$i]['href'] = $this->url_market . $product_title_link->attr['href'];
			} elseif ($item_href = $html_parse->find('a[class=link n-smart-link i-bem]', 0)) {
				$data['items'][$i]['href'] = $this->url_market . $item_href->attr['href'];
			} else {
				$data['items'][$i]['href'] = '';
			}
			$pattern = '#^(.*product.*\/\d{1,}.*)[\/|?]#Ui';					
			preg_match($pattern, $data['items'][$i]['href'], $m_href);
			if($m_href){
				$data['items'][$i]['href'] = $m_href[1].'?track=tabs';											
			}
			
			$product_desc_parse = $product_parse[$i]->find('ul[class=snippet-card__desc-list], ul[class=n-product-spec-list], div[class=n-snippet-card2__content] ul', 0);
			if ($product_desc_parse) {
				$data['items'][$i]['desc'] = $product_desc_parse->plaintext;
			} else {
				$data['items'][$i]['desc'] = '';
			}
			$product_price_parse = $product_parse[$i]->find('span[class=price], div[class=price]', 0);
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
return $data;


?>