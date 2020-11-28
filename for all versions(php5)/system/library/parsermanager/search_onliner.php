<?php
$data['items'] = array();
$content = $this->getContentParser($url);
$content = preg_replace_callback("#HTTP.*{\"products\":#Uism", function ($matches) {
	return '{"products":';
}, $content);
$json_content = json_decode($content, 1);
if (isset($json_content['products'])) {
	$cn = count($json_content['products']);
	for ($i = 0; $i < $cn; $i++) {
		if (isset($json_content['products'][$i]['html_url']) && isset($json_content['products'][$i]['full_name'])) {
			$data['items'][$i]['href'] = $json_content['products'][$i]['html_url'];
			$data['items'][$i]['title'] = $json_content['products'][$i]['full_name'];
		} else {
			$data['items'][$i]['href'] = '';
			$data['items'][$i]['title'] = '';
		}
		if (isset($json_content['products'][$i]['images']['header'])) {
			$data['items'][$i]['src'] = $json_content['products'][$i]['images']['header'];
		} else {
			$data['items'][$i]['src'] = '';
		}
		if (isset($json_content['products'][$i]['description'])) {
			$data['items'][$i]['desc'] = $json_content['products'][$i]['description'];
		} else {
			$data['items'][$i]['desc'] = '';
		}
		if (isset($json_content['products'][$i]['prices']['price_min']['amount'])) {
			$data['items'][$i]['price'] = $json_content['products'][$i]['prices']['price_min']['amount'];
		} else {
			$data['items'][$i]['price'] = 'Не найдена.';
		}
	}
}
for ($i = 1; $i < count($data['items']); $i++) {
	for ($j = $i; $j < count($data['items']); $j++) {
		if (isset($data['items'][$i]) AND isset($data['items'][$j]) AND $data['items'][$i]['title'] == $data['items'][$j]['title']) {
			unset($data['items'][$j]);
		}
	}
}
return $data['items'];
?>
