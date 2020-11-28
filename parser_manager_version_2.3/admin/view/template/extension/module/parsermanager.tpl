<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
<div class="page-header">
    <div class="container-fluid">
        <h1><?php echo $heading_title; ?></h1>
        <ul class="breadcrumb">
            <?php foreach ($breadcrumbs as $breadcrumb) { ?>
            <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
            <?php } ?>
        </ul>
    </div>
</div>
<div class="container-fluid">
<!-- Сообщения-->
<?php if (isset($this->session->data['license'])) { ?>
<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $this->session->data['license']; ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
<?php } ?>
<?php if ($error_warning) { ?>
<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
<?php } ?>
<?php if ($success) { ?>
<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
</div>
<?php } ?>
<!-- Сообщения-->

<div class="panel panel-default">
<div class="panel-heading">
<div id="setting">
<form id="form_setting" action="<?php echo $base."index.php?route=extension/module/parsermanager/index&token=".$token;?>" method="POST" >
<fieldset>
<legend><h3 class="panel-title"> <?php echo $text_list; ?> </h3></legend>
<div class="hideWrap">
<a class="hideBtn" href="javascript:void(0)" onclick="$('#hideCont1').slideToggle('normal');
						$(this).toggleClass('show');
						return false;">Показать/скрыть</a> 
<a href="<?php echo $base."index.php?route=extension/module/parsermanager/parselog&token=".$token;?>"  target="_blank" style="padding-left:10px;padding-top:0px;float:right;">Показать лог</a>
<a href="<?php echo $base."index.php?route=extension/module/parsermanager/viewPage&token=".$token;?>"  target="_blank" style="padding-left:10px;padding-top:0px;float:right;">Показать страницу</a>
<div id="hideCont1" class="hideCont" style="display: none;">
<div class="trdiv">
<table class="setting">
<tr>
				<td  class="name-td">
					<b>Добавить ссылки:</b>
				</td>
				<td style="border-bottom:1px solid #DDDDDD;">
					<a href="<?php echo $base."index.php?route=extension/module/parsermanager/getUrsByFile&token=".$token;?>">Добавить ссылки</a>								
				</td>
			</tr>
<tr>
	<td  class="name-td" >
		<b>Ключ активации:</b>
	</td>
	<td>
		<input type="text" style="margin:5px 0 5px  0;" size="100" name="parsermanager_setting[license_key]"   value="<?php if (isset($setting['license_key'])) echo $setting['license_key'];?>" />								
	</td>
</tr>
<tr  style="border-top: 1px solid #DDDDDD;">
    <td class="name-td" >
        <b>Источник парсинга:</b>
    </td>
    <td style="padding-left:5px;">
        <ul style="padding:0;list-style-type:none;">
            <li>
                <input type="radio" name="parsermanager_setting[source]" id="yandex" value="1" onclick="view_tab();" <?php if (isset($setting['source']) && $setting['source'] == '1' ) echo "CHECKED";?> />Яндекс.маркет
                <span style = "margin-left:20px;">Выберите регион:</span>
                <select  name="parsermanager_setting[region]" style="max-width: 250px;"  >
                    <?php foreach ($regions as $key => $region) { ?>
                    <?php if ($region == $region_set) { ?>
                    <option value="<?php echo $region; ?>" selected="selected"><?php echo $key; ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $region; ?>"><?php echo $key; ?></option>
                    <?php } ?>
                    <?php } ?>
                </select>
            </li>
            <li>
                <input type="radio" name="parsermanager_setting[source]" id="hotline"  value="2"  onclick="view_tab();" <?php if (isset($setting['source']) && $setting['source'] == '2' ) echo "CHECKED";?>  />Hotline.ua
            </li>
            <li>
                <input type="radio" name="parsermanager_setting[source]" id="onliner"  value="3"  onclick="view_tab();" <?php if (isset($setting['source']) && $setting['source'] == '3' ) echo "CHECKED";?>  />Onliner.by
            </li>
		<!--	<li>
				<input type="radio" name="parsermanager_setting[source]" id="mail"  value="4"  onclick="view_tab();" <?php if (isset($setting['source']) && $setting['source'] == '4' ) echo "CHECKED";?>  />Torg.mail.ru
			</li>-->
        </ul>
    </td>
</tr>
<tr>
	<td  class="name-td" >
		<b>User-agent:</b>
	</td>
	<td>
		<input type="text" style = "margin:5px 0 5px  0;" id="user-agent" size="70" name="parsermanager_setting[user_agent]"   value="<?php if (isset($setting['user_agent'])) echo $setting['user_agent'];?>" />									
		<b><a href="javascript:void(0)" onclick="$('#user-agent').val(navigator.userAgent);">Взять из браузера</a></b>
	</td>
	
	</td>
</tr>
<tr  style="border-top: 1px solid #DDDDDD;">
    <td  class="name-td">
        <b>Прокси:</b>
    </td>
    <td style="padding-left:5px;">
        <ul style="padding:0;list-style-type:none;">
            <li style="margin-top:5px;">
                <input type="radio"  class="proxy" name="parsermanager_setting[proxy_check]" value="0" <?php if (isset($setting['proxy_check']) && $setting['proxy_check'] == '0' ) echo "CHECKED";?>/>Без прокси<br>
            </li>
            <li style="margin-top:5px;">
                <input type="radio"  class="proxy" name="parsermanager_setting[proxy_check]" value="1" <?php if (isset($setting['proxy_check']) && $setting['proxy_check'] == '1' ) echo "CHECKED";?>/>Прокси
				<span id="proxy_user" class="subsetting" style="padding-left:35px;">
					<b>Прокси IP:порт</b><input type="text"  size="25" name="parsermanager_setting[proxy_port]"   value="<?php if (isset($setting['proxy_port'])) echo $setting['proxy_port'];?>" />
					<b>Пользователь:пароль</b><input type="text"  size="25" name="parsermanager_setting[user_pass]"  value="<?php if (isset($setting['user_pass'])) echo $setting['user_pass'];?>" />
				</span>
            </li>
            <li style="margin-top:3px;">
                <input type="radio" class="proxy"  name="parsermanager_setting[proxy_check]" value="2" <?php if (isset($setting['proxy_check']) && $setting['proxy_check'] == '2' ) echo "CHECKED";?>  />Прокси - лист
				<span id="proxy_list" class="subsetting" >
					<a class="btn btn-primary btn-xs" href="javascript:void(0)"  onclick="$('#upload-cookie').modal('show');">Загрузить файл с прокси</a>
					<a id="proxy_check_button" class="btn btn-primary btn-xs" href="javascript:void(0)" onclick="proxy_check_ajax();" >Проверить</a>			
				</span>
            </li>
        </ul>
    </td>
</tr>
<tr  style="border-top: 1px solid #DDDDDD;">
    <td  class="name-td" >
        <b>Cookie:</b>
    </td>
    <td style="padding-left:5px;">
        <input type="radio"  name="parsermanager_setting[cookie_check]" value="1" <?php if (isset($setting['cookie_check']) && $setting['cookie_check'] == '1' ) echo "CHECKED";?>/>Включить
        <input type="radio"  name="parsermanager_setting[cookie_check]" value="0" <?php if (isset($setting['cookie_check']) && $setting['cookie_check'] == '0' ) echo "CHECKED";?>/>Выключить
		<span id="cookie_upload" class="subsetting" >
			<a class="btn btn-primary btn-xs" href="javascript:void(0)"  onclick="$('#upload-cookie').modal('show');">Загрузить файл с cookie</a>
			<a class="btn btn-primary btn-xs" href="javascript:void(0)"  onclick="delete_cookie();" >Удалить cookie</a>													
		</span>

    </td>
</tr>
<tr  style="border-top: 1px solid #DDDDDD;">
    <td  class="name-td">
        <b>Папка для изображений:</b>
    </td>
    <td style="padding-left:5px;">
        <div class="prop_img">
			<span id="dir_image" >
				<?php echo DIR_IMAGE_PARSER;?>
			</span>
            <span  id="dir"><?php if(isset($setting['dir_to']) ) echo $setting['dir_to'];?></span>
         	<a class="btn btn-primary btn-xs" id="image-path" >Выбрать папку </a>

            <input type="text"  size = "50" name="parsermanager_setting[dir_to]"  value="<?php  echo $setting['dir_to'];?>" id="dir_to"  style = "display:none">

        </div>
        <input type="checkbox" style="margin:5 15 0 20;" name="parsermanager_setting[create_dir_image]" value="1" <?php if (isset($setting['create_dir_image'])) echo "CHECKED";?>/>Создавать папку для изображений<br>
    </td>
</tr>
<tr id="tab-yandex"  style="border-top: 1px solid #DDDDDD;">
    <td  class="name-td">
        <b>Даннные для Яндекс.маркет:</b>
    </td>
    <td style="padding-left:5px;">
        <table>
            <tr>
                <td>
                    <input type="radio" name="parsermanager_setting[yandex][productdata]" value="1" <?php if (isset($setting['yandex']['productdata']) && $setting['yandex']['productdata'] == '1' ) echo "CHECKED";?>/>Добавлять данные
                    <input type="radio" name="parsermanager_setting[yandex][productdata]" value="0" <?php if (isset($setting['yandex']['productdata']) && $setting['yandex']['productdata'] == '0' ) echo "CHECKED";?>/>Заменять данные<br>
                </td>
            </tr>
            <tr>
                <td >
                    <input type="checkbox" name="parsermanager_setting[yandex][addatribyte]" value="1" <?php if (isset($setting['yandex']['addatribyte'])) echo "CHECKED";?>/>Атрибуты<br>
                    <input type="checkbox" name="parsermanager_setting[yandex][addescription]" value="1" <?php if (isset($setting['yandex']['addescription'])) echo "CHECKED";?> />Описание<br>
                    <input type="checkbox" style="margin-top:15px;" name="parsermanager_setting[yandex][title]" value="1" <?php if (isset($setting['yandex']['title'])) echo "CHECKED";?> />Заменять название товара<br>
                    <input type="checkbox"  name="parsermanager_setting[yandex][sku]" value="1" <?php if (isset($setting['yandex']['sku'])) echo "CHECKED";?> />Добавлять артикул<br>
                    <input type="checkbox" name="parsermanager_setting[yandex][keyword]" value="1" <?php if (isset($setting['yandex']['keyword'])) echo "CHECKED";?> />Добавлять SEO URL<br>
					<input type="checkbox"  name="parsermanager_setting[yandex][minprice]" value="1" <?php if (isset($setting['yandex']['minprice'])) echo "CHECKED";?>>Искать минимальную цену
				</td>
                <td style="vertical-align: text-top;">
                    <input type="checkbox" name="parsermanager_setting[yandex][meta_description]" value="1" <?php if (isset($setting['yandex']['meta_description'])) echo "CHECKED";?> />Мета-тег "Описание"(с включенным описанием)<br>
                    <input type="checkbox" name="parsermanager_setting[yandex][addallimg]" value="1" <?php if (isset($setting['yandex']['addallimg'])) echo "CHECKED";?>>Дополнительные изображения<br>
                    <input type="checkbox" style="margin-top:15px;" name="parsermanager_setting[yandex][addimage]" value="1" <?php if (isset($setting['yandex']['addimage'])) echo "CHECKED";?>>Изменять главное изображение<br>
                    <input type="checkbox"  name="parsermanager_setting[yandex][price]" value="1" <?php if (isset($setting['yandex']['price'])) echo "CHECKED";?>>Заменять цену<br>
                    <input type="checkbox"  name="parsermanager_setting[yandex][manufacturer]" value="1" <?php if (isset($setting['yandex']['manufacturer'])) echo "CHECKED";?>>Добавлять производителя<br>
					<input type="checkbox"  name="parsermanager_setting[yandex][addvideo]" value="1" <?php if (isset($setting['yandex']['addvideo'])) echo "CHECKED";?>>Добавлять видео(вместе с характеристиками)

				</td>
            </tr>
        </table>
    </td>
</tr>
<tr id="tab-hotline"  style="border-top: 1px solid #DDDDDD;">
    <td  class="name-td">
        <b>Даннные для Hotline.ua:</b>
    </td>
    <td style="padding-left:5px;">
        <table>
            <tr>
                <td>
                    <input type="radio" name="parsermanager_setting[hotline][productdata]" value="1" <?php if (isset($setting['hotline']['productdata']) && $setting['hotline']['productdata'] == '1' ) echo "CHECKED";?>/>Добавлять данные
                    <input type="radio" name="parsermanager_setting[hotline][productdata]" value="0" <?php if (isset($setting['hotline']['productdata']) && $setting['hotline']['productdata'] == '0' ) echo "CHECKED";?>/>Заменять данные<br>
                </td>
            <tr>
            </tr>
                <td >
                    <input type="checkbox" name="parsermanager_setting[hotline][addatribyte]" value="1" <?php if (isset($setting['hotline']['addatribyte'])) echo "CHECKED";?>/>Атрибуты<br>
                    <input type="checkbox" name="parsermanager_setting[hotline][addescription]" value="1" <?php if (isset($setting['hotline']['addescription'])) echo "CHECKED";?> />Описание<br>
                    <input type="checkbox" style="margin-top:15px;" name="parsermanager_setting[hotline][title]" value="1" <?php if (isset($setting['hotline']['title'])) echo "CHECKED";?> />Заменять название товара<br>
                    <input type="checkbox"  name="parsermanager_setting[hotline][sku]" value="1" <?php if (isset($setting['hotline']['sku'])) echo "CHECKED";?> />Добавлять артикул<br>
                    <input type="checkbox" name="parsermanager_setting[hotline][keyword]" value="1" <?php if (isset($setting['hotline']['keyword'])) echo "CHECKED";?> />Добавлять SEO URL<br>
					<input type="checkbox"  name="parsermanager_setting[hotline][minprice]" value="1" <?php if (isset($setting['hotline']['minprice'])) echo "CHECKED";?>>Искать минимальную цену

				</td>
                <td style="vertical-align: text-top;">
                    <input type="checkbox" name="parsermanager_setting[hotline][meta_description]" value="1" <?php if (isset($setting['hotline']['meta_description'])) echo "CHECKED";?> />Мета-тег "Описание"(с включенным описанием)<br>
                    <input type="checkbox" name="parsermanager_setting[hotline][addallimg]" value="1" <?php if (isset($setting['hotline']['addallimg'])) echo "CHECKED";?>>Дополнительные изображения<br>
                    <input type="checkbox" style="margin-top:15px;" name="parsermanager_setting[hotline][addimage]" value="1" <?php if (isset($setting['hotline']['addimage'])) echo "CHECKED";?>>Изменять главное изображение<br>
                    <input type="checkbox"  name="parsermanager_setting[hotline][price]" value="1" <?php if (isset($setting['hotline']['price'])) echo "CHECKED";?>>Заменять цену<br>
                    <input type="checkbox"  name="parsermanager_setting[hotline][manufacturer]" value="1" <?php if (isset($setting['hotline']['manufacturer'])) echo "CHECKED";?>>Добавлять производителя<br>
					<input type="checkbox"  name="parsermanager_setting[hotline][addvideo]" value="1" <?php if (isset($setting['hotline']['addvideo'])) echo "CHECKED";?>>Добавлять видео

				</td>
            </tr>
        </table>
    </td>
</tr>
<tr id="tab-onliner"  style="border-top: 1px solid #DDDDDD;">
    <td  class="name-td">
        <b>Даннные для Onliner.by:</b>
    </td>
    <td style="padding-left:5px;">
        <table>
            <tr>
                <td>
                    <input type="radio" name="parsermanager_setting[onliner][productdata]" value="1" <?php if (isset($setting['onliner']['productdata']) && $setting['onliner']['productdata'] == '1' ) echo "CHECKED";?>/>Добавлять данные
                    <input type="radio" name="parsermanager_setting[onliner][productdata]" value="0" <?php if (isset($setting['onliner']['productdata']) && $setting['onliner']['productdata'] == '0' ) echo "CHECKED";?>/>Заменять данные<br>
                </td>
            </tr>
            <tr>
                <td >
                    <input type="checkbox" name="parsermanager_setting[onliner][addatribyte]" value="1" <?php if (isset($setting['onliner']['addatribyte'])) echo "CHECKED";?>/>Атрибуты<br>
                    <input type="checkbox" name="parsermanager_setting[onliner][addescription]" value="1" <?php if (isset($setting['onliner']['addescription'])) echo "CHECKED";?> />Описание<br>
                    <input type="checkbox" style="margin-top:15px;" name="parsermanager_setting[onliner][title]" value="1" <?php if (isset($setting['onliner']['title'])) echo "CHECKED";?> />Заменять название товара<br>
                    <input type="checkbox"  name="parsermanager_setting[onliner][sku]" value="1" <?php if (isset($setting['onliner']['sku'])) echo "CHECKED";?> />Добавлять артикул<br>
                    <input type="checkbox" name="parsermanager_setting[onliner][keyword]" value="1" <?php if (isset($setting['onliner']['keyword'])) echo "CHECKED";?> />Добавлять SEO URL<br>
					<input type="checkbox" name="parsermanager_setting[onliner][minprice]" value="1" <?php if (isset($setting['onliner']['minprice'])) echo "CHECKED";?>>Искать минимальную цену

			   </td>
                <td style="vertical-align: text-top;">
                    <input type="checkbox" name="parsermanager_setting[onliner][meta_description]" value="1" <?php if (isset($setting['onliner']['meta_description'])) echo "CHECKED";?> />Мета-тег "Описание"(с включенным описанием)<br>
                    <input type="checkbox" name="parsermanager_setting[onliner][addallimg]" value="1" <?php if (isset($setting['onliner']['addallimg'])) echo "CHECKED";?>>Дополнительные изображения<br>
                    <input type="checkbox" style="margin-top:15px;" name="parsermanager_setting[onliner][addimage]" value="1" <?php if (isset($setting['onliner']['addimage'])) echo "CHECKED";?>>Изменять главное изображение<br>
                    <input type="checkbox"  name="parsermanager_setting[onliner][price]" value="1" <?php if (isset($setting['onliner']['price'])) echo "CHECKED";?>>Заменять цену<br>
                    <input type="checkbox"  name="parsermanager_setting[onliner][manufacturer]" value="1" <?php if (isset($setting['onliner']['manufacturer'])) echo "CHECKED";?>>Добавлять производителя<br>
                    <input type="checkbox"  name="parsermanager_setting[onliner][addvideo]" value="1" <?php if (isset($setting['onliner']['addvideo'])) echo "CHECKED";?>>Добавлять видео
                </td>
            </tr>
			
        </table>
    </td>
</tr>
<!--<tr id="tab-mail" style="border-top: 1px solid #DDDDDD;">
	<td  class="name-td">
		<b>Даннные для Torg.mail.ru:</b>
	</td>	
	<td style="padding-left:5px;">
		<table>
					<tr>
						<td>
							<input type="radio" name="parsermanager_setting[mail][productdata]" value="1" <?php if (isset($setting['mail']['productdata']) && $setting['mail']['productdata'] == '1' ) echo "CHECKED";?>/>Добавлять данные										
							<input type="radio" name="parsermanager_setting[mail][productdata]" value="0" <?php if (isset($setting['mail']['productdata']) && $setting['mail']['productdata'] == '0' ) echo "CHECKED";?>/>Заменять данные<br>
						</td>
					</tr>
					<tr>
						<td >										
							<input type="checkbox" name="parsermanager_setting[mail][addatribyte]" value="1" <?php if (isset($setting['mail']['addatribyte'])) echo "CHECKED";?>/>Атрибуты<br>												
							<input type="checkbox" name="parsermanager_setting[mail][addescription]" value="1" <?php if (isset($setting['mail']['addescription'])) echo "CHECKED";?> />Описание<br>												
							<input type="checkbox" style="margin-top:15px;" name="parsermanager_setting[mail][title]" value="1" <?php if (isset($setting['mail']['title'])) echo "CHECKED";?> />Заменять название товара<br>
							<input type="checkbox"  name="parsermanager_setting[mail][sku]" value="1" <?php if (isset($setting['mail']['sku'])) echo "CHECKED";?> />Добавлять артикул<br>
							<input type="checkbox" name="parsermanager_setting[mail][keyword]" value="1" <?php if (isset($setting['mail']['keyword'])) echo "CHECKED";?> />Добавлять SEO URL<br>											
							<input type="checkbox" name="parsermanager_setting[mail][model]" value="1" <?php if (isset($setting['mail']['model'])) echo "CHECKED";?> />Заменять модель	<br>										
							<input type="checkbox" name="parsermanager_setting[mail][seo_h1]" value="1" <?php if (isset($setting['mail']['seo_h1'])) echo "CHECKED";?> />Заменять HTML-тег H1											

						</td>
						<td style="vertical-align: text-top;">												
							<input type="checkbox" name="parsermanager_setting[mail][meta_description]" value="1" <?php if (isset($setting['mail']['meta_description'])) echo "CHECKED";?> />Мета-тег "Описание"(с включенным описанием)<br>	
							<input type="checkbox" name="parsermanager_setting[mail][addallimg]" value="1" <?php if (isset($setting['mail']['addallimg'])) echo "CHECKED";?>>Дополнительные изображения<br>															
							<input type="checkbox" style="margin-top:15px;" name="parsermanager_setting[mail][addimage]" value="1" <?php if (isset($setting['mail']['addimage'])) echo "CHECKED";?>>Изменять главное изображение<br>
							<input type="checkbox"  name="parsermanager_setting[mail][price]" value="1" <?php if (isset($setting['mail']['price'])) echo "CHECKED";?>>Заменять цену<br>	
							<input type="checkbox"  name="parsermanager_setting[mail][manufacturer]" value="1" <?php if (isset($setting['mail']['manufacturer'])) echo "CHECKED";?>>Добавлять производителя<br>
							<input type="checkbox"  name="parsermanager_setting[mail][meta_keyword]" value="1" <?php if (isset($setting['mail']['meta_keyword'])) echo "CHECKED";?>>Заменять Мета-тег Keywords<br>
							<input type="checkbox" name="parsermanager_setting[mail][seo_title]" value="1" <?php if (isset($setting['mail']['seo_title'])) echo "CHECKED";?> />Заменять HTML-тег Title											
					
						</td>
					</tr>
		</table>
	</td>	
</tr>	-->
<tr  style="border-top: 1px solid #DDDDDD;">
    <td class="name-td">
        <b>Процент к цене:</b>
    </td>
     <td style="padding-left:5px;">
        <input type="text"  size="3" name="parsermanager_setting[marga]" style="margin-left:7px;" value="<?php if (isset($setting['marga'])) { echo $setting['marga']; } else {echo '0';} ?>" />
    </td>
</tr>									
<tr  style="border-top: 1px solid #DDDDDD;">
    <td class="name-td">
        <b>Фильтр:</b>
    </td>
    <td style="padding-left:5px;">
        <table>
            <tr>
                <td>
                    <input type="checkbox" name="parsermanager_setting[filter_attribute]"  value="1" <?php if (isset($setting['filter_attribute'])) echo "CHECKED";?>>Отсутствуют аттрибуты  <br>
                    <input type="checkbox" name="parsermanager_setting[filter_description]" value="1" <?php if (isset($setting['filter_description'])) echo "CHECKED";?>>Отсутствует описание<br>
                    <input type="checkbox" name="parsermanager_setting[filter_sku]" value="1" <?php if (isset($setting['filter_sku'])) echo "CHECKED";?>>Отсутствует артикул<br>
                    <input type="checkbox" name="parsermanager_setting[filter_url]" value="1" <?php if (isset($setting['filter_url'])) echo "CHECKED";?>>Есть ссылка на товар<br>
                    <input type="checkbox" name="parsermanager_setting[filter_url_empty]" value="1" <?php if (isset($setting['filter_url_empty'])) echo "CHECKED";?>>Нет ссылки на товар
                </td>
                <td style="vertical-align: text-top;">
                    <input type="checkbox" name="parsermanager_setting[filter_image_main]" value="1" <?php if (isset($setting['filter_image_main'])) echo "CHECKED";?>>Отсутствует главное изображение<br>
                    <input type="checkbox" name="parsermanager_setting[filter_image_all]"  value="1" <?php if (isset($setting['filter_image_all'])) echo "CHECKED";?>>Отсутствуют дополнительные изображения<br>
                    <input type="checkbox" name="parsermanager_setting[filter_price]" value="1" <?php if (isset($setting['filter_price'])) echo "CHECKED";?>>Отсутствует цена<br>
                    <input type="checkbox" name="parsermanager_setting[filter_onproduct]" value="1" <?php if (isset($setting['filter_onproduct'])) echo "CHECKED";?>>Товар включен
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <br>Не показывать обновленные товары в течении(мин.):
                    <input type="text"  size="5" name="parsermanager_setting[time]" style="margin-left:7px;" value="<?php if (isset($setting['time'])) echo $setting['time'];?>" />
                </td>
            </tr>
            <tr>
                <td> <br>Категория:
                    <select name="parsermanager_setting[filter_category]" style="max-width: 250px;"  >
                        <option value=""></option>
                        <?php foreach ($allCategories as $category) { ?>
                        <?php if (isset($setting['filter_category']) AND $category['category_id'] == $setting['filter_category']) { ?>
                        <option value="<?php echo $category['category_id']; ?>" selected="selected"><?php echo $category['name']; ?></option>
                        <?php } else { ?>
                        <option value="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></option>
                        <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr  style="border-top: 1px solid #DDDDDD;">
    <td class="name-td">
        <b>Поиск на сайте источнике:</b>
    </td>
    <td style="padding-left:5px;">
        <table>
            <tr>
                <td>
                    <input type="radio" name="parsermanager_setting[productsearch]" value="1"  <?php if(isset($setting['productsearch']) && $setting['productsearch'] == '1') echo " checked='checked'"?> />По названию товара	<br>
                    <input type="radio" name="parsermanager_setting[productsearch]" value="0"  <?php if(isset($setting['productsearch']) && $setting['productsearch'] == '0') echo " checked='checked'"?> />По модели
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr  style="border-top: 1px solid #DDDDDD;">
    <td class="name-td">
        <b>Задержка при парсинге<br> нескольких товаров(сек.):</b>
    </td>
    <td style="padding-left:5px;">
        <input type="text"  size="3" name="parsermanager_setting[pause]" style="margin-left:7px;" value="<?php if (isset($setting['pause'])) echo $setting['pause']; else echo '0'; ?>" />
    </td>
</tr>
<tr  style="border-top: 1px solid #DDDDDD;">
    <td class="name-td">
        <b>Настройки для новых товаров:</b>
    </td>
    <td style="padding-left:5px;">
        <table>
            <tr>
                <td><?php echo $entry_manufacturer; ?></td>
                <td>
                    <select name="parsermanager_setting[manufacturer_id]">
                        <option value="0" selected="selected"><?php echo $text_none; ?></option>
                        <?php foreach ($manufacturers as $manufacturer) { ?>
                        <?php if ($manufacturer['manufacturer_id'] == $manufacturer_id) { ?>
                        <option value="<?php echo $manufacturer['manufacturer_id']; ?>" selected="selected"><?php echo $manufacturer['name']; ?></option>
                        <?php } else { ?>
                        <option value="<?php echo $manufacturer['manufacturer_id']; ?>"><?php echo $manufacturer['name']; ?></option>
                        <?php } ?>
                        <?php } ?>
                    </select>
                </td>
            </tr>
			<tr>				
				<td colspan="2">
					<input type="text" name="category" value="" placeholder="<?php echo $entry_category; ?>" id="input-category" class="form-control" />
					<div id="product-category" class="well well-sm" style="height: 150px; overflow: auto;">
						<?php foreach ($product_categories as $product_category) { ?>
						<div id="product-category<?php echo $product_category['category_id']; ?>"><i class="fa fa-minus-circle"></i> <?php echo $product_category['name']; ?>
						  <input type="hidden" name="parsermanager_setting[product_category][]" value="<?php echo $product_category['category_id']; ?>" />
						</div>
						<?php } ?>						 
					</div>
				  </td>
			</tr>
			
            <tr>
                <td><?php echo $entry_store; ?></td>
                <td>
                    <div class="scrollbox" style="height: 60px; margin-top: 10px;">
                        <?php $class = 'even'; ?>
                        <div class="<?php echo $class; ?>">
                            <?php if (in_array(0, $product_store)) { ?>
                            <input type="checkbox" name="parsermanager_setting[product_store][]" value="0" checked="checked" />
                            <?php echo $text_default; ?>
                            <?php } else { ?>
                            <input type="checkbox" name="parsermanager_setting[product_store]product_store[]" value="0" />
                            <?php echo $text_default; ?>
                            <?php } ?>
                        </div>
                        <?php foreach ($stores as $store) { ?>
                        <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                        <div class="<?php echo $class; ?>">
                            <?php if (in_array($store['store_id'], $product_store)) { ?>
                            <input type="checkbox" name="parsermanager_setting[product_store]paproduct_store[]" value="<?php echo $store['store_id']; ?>" checked="checked" />
                            <?php echo $store['name']; ?>
                            <?php } else { ?>
                            <input type="checkbox" name="parsermanager_setting[product_store]product_store[]" value="<?php echo $store['store_id']; ?>" />
                            <?php echo $store['name']; ?>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr  style="border-top: 1px solid #DDDDDD;">
    <td  class="name-td">
        <b>Количество  товаров<br> на странице:</b>
    </td>
    <td  style="padding-left:5px;">
        <input type="text" name="parsermanager_setting[countproduct]" size="3"  value="<?php if (isset($setting['countproduct'])) echo $setting['countproduct'];?>" >
    </td>
</tr>
<tr style="border-top: 1px solid #DDDDDD;">
	<td  class="name-td">
		<b>Обновление:</b>
	</td>
	<td style="padding-left:5px;">
		<a href="<?php echo HTTP_SERVER."index.php?route=extension/module/parsermanager/updateTableParser&token=".$token;?>">Обновить таблицы парсера</a>								
	</td>
</tr>
<tr style="border-top: 1px solid #DDDDDD;">
    <td  class="name-td" colspan="2" style="padding-top:10px;">
        <button  name="save_setting" type="submit" form="form_setting" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"> </i> Сохранить </button>
    </td>
    <td>
    </td>
</tr>
</table>
</div>
</div>
</div>
</fieldset>
</form>
</div>
<!-- Конец настроек -->
</div>
<div class="panel-heading">
    <div id="new_product">
        <form id="form_add_product" action="">
            <fieldset><legend><h3>Добавление нового товара</h3></legend>
                <table>
                    <tr>
                        <td width="85%;" name="0"   style="text-align:right;" >
							<div class="add-url-product" style="float:right;">
								<a  class="btn btn-primary" title="Добавить ссылку" onclick="addNewUrl(0);"><i class='fa fa-plus-circle' /></i></a>
							</div> 
						</td>		
                        <td  width="50px;" style="text-align:center;">
                            <a  class="btn btn-primary" title="Найти товар"  name="0" onclick="$('#search-product-modal').modal('show');"><i class="fa fa-search"></i> </a>
						</td>	
                        <td  width="150px;" style="text-align:right;">
							<a class="btn btn-primary" title="Добавить товар" onclick="addProduct();" class="button">Добавить товар</a>
						</td>
                    </tr>
                    <tr>
                        <td ></td>
                        <td ></td>
                        <td >SKU:&nbsp;&nbsp;<input id="addproduct_sku" type="text" name="['addproduct_sku']" value="" size="15"><br>Price:&nbsp;<input type="text" id="addproduct_price" name="addproduct_price" value="" size="15"></td>

                    </tr>
                </table>
            </fieldset>
        </form>
    </div>
</div>

<div class="panel-body">	
    <form id="form_parsing" action="" method="POST">
        <?php if (isset($error_table) AND $error_table  == 1){ ?>
        <table id="error-table" style="width:100%;">
            <tr >
                <td >
                    <div class="warning" style="text-align: right ;">
                        <span style="margin-right:20px;">Прошлый парсинг закончился неудачей! Список товаров для парсинга не пустой!</span>
                        <input type='hidden' name='reparsingall' value='1' >
                        <a onclick="parsingAllProducts();" class="button"><?php echo 'Парсить дальше' ?></a>
                        <a onclick="clearTableUrls();" class="button"><?php echo 'Очистить список' ?></a>
                    </div>
                </td>
            </tr>
        </table>
        <?php }?>


        <div id="parsing_button"  >
            <div style="text-align:right;padding-right:5px;">
                <a class="btn btn-primary btn-xs" onclick="searchAllProducts();" class="button"><?php echo 'Искать ссылки' ?></a>
                <a class="btn btn-primary btn-xs" onclick="parsingAllProducts();" class="button"><?php echo 'Парсить все' ?></a>
                <a class="btn btn-primary btn-xs" onclick="deleteProduct();" class="button">Удалить товар</a>
            </div>
        </div>
        <div id="product_list">
            <fieldset><legend>Обновление существующих товаров</legend>
			
                <table id="prod_table" class="table table-bordered table-hover" >
                    <thead>
                    <tr>
                        <th class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></td>
                        <th class="text-center">Изобр.</td>
                        <th class="text-center"><?php echo $column_name; ?></td>
                        <th class="text-center">Модель</td>
                        <td class="text-center">Арт./SKU</td>
                        <th class="text-center">Цена</td>
                        <th class="text-center">Ссылка на товар</td>
                        <th colspan="3"  class="text-center">Действие</td>
                    </tr>
                    </thead>
                    <tr class="left1" >
                        <td></td>
                        <td></td>
                        <td><input type="text" id="filter_name"  value="<?php if (isset($setting['filter_name'])) echo $setting['filter_name'];?>" size = "25" name="filter_name" class="ui-autocomplete-input" autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true" value="<?php  echo $setting['filter_name'];?>"></td>
                        <td><input type="text" id="filter_model"  value="<?php if (isset($setting['filter_model'])) echo $setting['filter_model'];?>" size = "15" name="filter_model" class="ui-autocomplete-input" autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true" value="<?php  echo $setting['filter_model'];?>"></td>
                        <td><input type="text" id="filter_sku_exist"  value="<?php if (isset($setting['filter_sku_exist'])) echo $setting['filter_sku_exist'];?>" size = "10" name="filter_sku_exist"  value="<?php  echo $setting['filter_sku_exist'];?>"></td></td>
                        <td></td>
                        <td></td>
                        <td colspan="3" style="text-align:center;"><a onclick="filter();" class="btn btn-primary btn-xs">Фильтр</a></td>
                    </tr>
                    <?php
					$ev = 1;
					foreach ($products as $product){
						if(fmod ($ev,2)!=0) {
							$str_ev = "class='event'";	
						} else {
							$str_ev = '';
						}	
					?>
                    <tr <?php echo $str_ev;?>>
                    <td class="left2">
                        <input  class='input-product' type='checkbox' name="selected[<?php echo $product['product_id'];?>][product_id]" value='<?php echo $product['product_id'];?>'>
                    </td>
                    <td class="left2"><img src="<?php echo $product['image']; ?>" style="padding: 1px; border: 1px solid #DDDDDD;" /></td>
                    <td  class="left2">
                        <a name ="<?php echo $product['product_id'];?>" href="<?php echo $base."index.php?route=catalog/product/edit&token=".$token."&product_id=".$product['product_id'];?>"  target="_blank"><?php echo $product['name'];?></a>
                    </td>
                    <td class="left2" ><?php echo $product['model'];?></td>
                    <!--<td class="left2"><?php echo $product['sku'];?></td>-->
                    <td class="left2" ><input type="text" name="selected[<?php echo $product['product_id'];?>][sku]" value="<?php echo $product['sku'];?>" size="10"></td>
                    <td class="left2" ><input type="text" name="selected[<?php echo $product['product_id'];?>][price]" value="<?php echo $product['price'];?>" size="8"></td>
                    <td  class="left2" name='<?php echo $product['product_id'];?>'>	
                        <?php if (!empty($product['url_parsing'])) {?>
							<div  class="delete_url" style='width:260px;'>							
								<a class="btn btn-default" href="<?php echo $product['url_parsing'];?>" target="_blank"><?php echo substr($product['url_parsing'], 0, 20).'...'.substr($product['url_parsing'], strlen($product['url_parsing'])-10);?></a>
								<a  style="float:right;" class="btn btn-danger" title="Удалить ссылку" onclick="deleteUrl(<?php echo $product['product_id'];?>);"><i class='fa fa-trash-o'/> </i></a>
								
							</div>
                        <?php } else {?>
						<div class="add-url-product" style="float:right;">
							<a  class="btn btn-primary" title="Добавить ссылку" onclick="addNewUrl(<?php echo $product['product_id'];?>);"><i class='fa fa-plus-circle'  /></i></a>
						</div>
						<?php }?>					
                    </td>
                    <td class="left2">
						<a  class="btn btn-primary" title="Поиск по названию"  name="<?php echo $product['product_id'];?>" onclick="find_product(<?php echo $product['product_id'];?>, null);"><i class="fa fa-search"  /></i></a>
                    </td>
                    <td class="left2">
                        <a class="btn btn-primary btn-xs" onclick='searchByContent(<?php echo $product['product_id'];?>)'>Ввести<br> запрос</a>
                    </td>
                    <td  class="left2">
                        <a class="btn btn-primary btn-xs"" onclick="parsingProduct(<?php echo $product['product_id'];?>);" name = "<?php echo $product['product_id'];?>" >Парсить</a>
                    </td>
                    </tr>
                    <?php	$ev++;}?>					
                </table>
            </fieldset>
        </div>
    </form>

</div>
</div>

<div class="row">
    <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
    <div class="col-sm-6 text-right"><?php echo $results; ?></div>
</div>
</div>
</div>

<div id="url-modal" class="modal fade" tabindex="-1" >
    <div class="modal-dialog modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">?</button>
                <h4 class="modal-title">Ввод ссылки</h4>
            </div>
            <div class="modal-body">
                <input id="add-url" type="text" size="120" class="add-url" data-product="" style="margin: 15 20 0 30;text-shadow: 0px 1px 0px #fff;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="addUrlAjax($('#add-url').attr('data-product'), $('#add-url').val());" data-dismiss="modal" >Сохранить</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<div id="upload-cookie" class="modal fade" tabindex="-1" >
    <div class="modal-dialog modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">?</button>
                <h4 class="modal-title">Выберите файл и нажмите загрузить</h4>
            </div>
								
				<div class="modal-body">				
					<input  id="fileToUpload" type="file" size="45" name="fileToUpload" >				
				</div>
				<div class="modal-footer">			
					<button type="button" class="btn btn-primary" onclick="ajaxFileUpload();" data-dismiss="modal" >Загрузить</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
				</div>
				
        </div>
    </div>
</div>

<div id="search-product-modal" class="modal fade" tabindex="-1" >
    <div class="modal-dialog modal-lg ">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">?</button>
                <h4 class="modal-title">Ввод ввод названия</h4>
            </div>
            <div class="modal-body">
                <input id="input-search-product" type="text" size="120" class="add-url" data-product="" style="margin: 15 20 0 30;text-shadow: 0px 1px 0px #fff;" placeholder="Введи название товара">
            </div>
            <div class="modal-footer">
                <button type="button" id="search-product" class="btn btn-primary" onclick="find_product(0, $('#input-search-product').val());" data-dismiss="modal" >Поиск</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript" src="view/javascript/jquery/ajaxfileuploadparser.js"></script>
<script type="text/javascript">

$(document).ready(function () {

   $(document).delegate('a[id=image-path]', 'click', function() {        
        $(this).parents('.note-editor').find('.note-editable').focus();
        $.ajax({
            url: 'index.php?route=extension/module/parsermanager/foldermanager&token=<?php echo $token; ?>',
            dataType: 'html',
            beforeSend: function() {
                $('#modal-search i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
                $('#modal-search').prop('disabled', true);
            },
            complete: function() {
                $('#modal-search i').replaceWith('<i class="fa fa-upload"></i>');
                $('#modal-search').prop('disabled', false);
            },
            success: function(html) {
                $('body').append('<div id="modal-image" class="modal">' + html + '</div>');

                $('#modal-image').modal('show');
            }
        });
    });

    
    $('#filter_name').autocomplete({
        delay: 0,
        source: function(request, response) {
            $.ajax({
                url: 'index.php?route=extension/module/parsermanager/autocompleteparserj&token=<?php echo $token; ?>&filter_name=' +  $('#filter_name').val(),
                dataType: 'json',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item.name,
                            value: item.product_id
                        }
                    }));
                }
            });
        },
        'select': function(item) {
			$('input[name=\'filter_name\']').val(item['label']);
		},
        focus: function(event, ui) {
            return false;
        }
    });

  	$('input[name=\'filter_model\']').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: 'index.php?route=extension/module/parsermanager/autocompleteparserj&token=<?php echo $token; ?>&filter_model=' +  encodeURIComponent(request),
				dataType: 'json',
				success: function(json) {
					response($.map(json, function(item) {
						return {
							label: item['model'],
							value: item['product_id']
						}
					}));
				}
			});
		},
		'select': function(item) {
			$('input[name=\'filter_model\']').val(item['label']);
		}
		});
		
	
    $('#attributefile').click(function () {
		if(($("#attributefile").prop("checked"))){
			$("#uploadhref").show();
		} else {
			$("#uploadhref").hide();
		}
	});
		view_tab();
	});

	
function searchByContent(product_id) {
	var product = $('#prod_table a[name='+product_id+']');	
	$('#input-search-product').val(product.html());
	$('#search-product').attr('onclick', 'find_product('+ product_id +', $("#input-search-product").val())');
	$("#search-product-modal").modal("show");	
}
	
function deleteUrl(product_id) {
    if (!confirm("Удалить ссылку?")) {
        return false;
    }

    $.ajax({
        url: "index.php?route=extension/module/parsermanager/deleteUrl&token=<?php echo $token; ?>&product_id="+product_id,
        dataType: "html",
        success: function(msg){
            $('td[name='+product_id+'] div.delete_url').remove();
			$('td[name='+product_id+']').append('<div class="add-url-product" style="float:right;"><a  class="btn btn-primary" title="Добавить ссылку" onclick="addNewUrl(' + product_id + ');"><i class="fa fa-plus-circle"/></i></a></div>');			
        },
        error: function(){
            alert('error');
        }
    });
}

function proxy_check_ajax() {
    $.ajax({
        url: "index.php?route=extension/module/parsermanager/proxycheck&token=<?php echo $token; ?>",
        dataType: "html",
		beforeSend: function() {
            $('#proxy_check_button').html('<i class="fa fa-circle-o-notch fa-spin"></i>');
            
        },
        success: function(msg){
			$('#proxy_check_button').html('Проверить');
            alert('Найдено рабочих прокси - ' +  msg );

        },
        error: function(){
            alert('error');
            
        }
    });
}

function parsingAllProducts() {
    var filter_name = $('#filter_name').attr('value');
    var filter_sku_exist = $('#filter_sku_exist').attr('value');
    var filter_model = $('#filter_model').attr('value');
    var filter_value = '';
    var page = $('.links b').html();

    if (filter_name) {
        filter_value += '&filter_name=' + encodeURIComponent(filter_name);
    }

    if (filter_sku_exist) {
        filter_value += '&filter_sku_exist=' + encodeURIComponent(filter_sku_exist);
    }

    if (filter_model) {
        filter_value += '&filter_model=' + encodeURIComponent(filter_model);
    }

    if (page) {
        page = '&page=' + page;
    } else {
        page = '';
    }

    $('#form_parsing').attr('action', '<?php echo $base."index.php?route=extension/module/parsermanager/parsingAllproducts&token=".$token;?>'+'&product_id='+filter_value+page);
    $('#form_parsing').submit();

}

function searchAllProducts() {
    var filter_name = $('#filter_name').attr('value');
    var filter_sku_exist = $('#filter_sku_exist').attr('value');
    var filter_model = $('#filter_model').attr('value');
    var filter_value = '';
    var page = $('.links b').html();

    if (filter_name) {
        filter_value += '&filter_name=' + encodeURIComponent(filter_name);
    }

    if (filter_sku_exist) {
        filter_value += '&filter_sku_exist=' + encodeURIComponent(filter_sku_exist);
    }

    if (filter_model) {
        filter_value += '&filter_model=' + encodeURIComponent(filter_model);
    }

    if (page) {
        page = '&page=' + page;
    } else {
        page = '';
    }

    $('#form_parsing').attr('action', '<?php echo $base."index.php?route=extension/module/parsermanager/searchAllproducts&token=".$token;?>'+'&product_id='+filter_value+page);
    $('#form_parsing').submit();

}

function parsingProduct(product_id) {
    var filter_name = $('#filter_name').attr('value');
    var filter_sku_exist = $('#filter_sku_exist').attr('value');
    var filter_model = $('#filter_model').attr('value');
    var price = $('input[name*=\'selected['+product_id+'][price]\']').val();
    var filter_value = '';
    var page = $('.links b').html();
    //$('input[name*=\'selected\']')
    if (filter_name) {
        filter_value += '&filter_name=' + encodeURIComponent(filter_name);
    }

    if (filter_sku_exist) {
        filter_value += '&filter_sku_exist=' + encodeURIComponent(filter_sku_exist);
    }

    if (filter_model) {
        filter_value += '&filter_model=' + encodeURIComponent(filter_model);
    }

    if (page) {
        page = '&page=' + page;
    } else {
        page = '';
    }

    $('#form_parsing').attr('action', '<?php echo $base."index.php?route=extension/module/parsermanager/parseProduct&token=";?>'+getURLVar('token')+'&product_id='+product_id+'&price='+price+filter_value+page);
    $('#form_parsing').submit();

}

function filter() {
    var url = "index.php?route=extension/module/parsermanager&token=<?php echo $token; ?>";
    var filter_name = $('#filter_name').val();
    var filter_sku_exist = $('#filter_sku_exist').val();
    var filter_model = $('#filter_model').val();
    if (filter_name) {
        url += '&filter_name=' + encodeURIComponent(filter_name);
    }
    if (filter_sku_exist) {
        url += '&filter_sku_exist=' + encodeURIComponent(filter_sku_exist);
    }
    if (filter_model) {
        url += '&filter_model=' + encodeURIComponent(filter_model);
    }
	
    location = url;
}

function deleteProduct() {
    var selected =[];
    var i=0;
    var page = $('.links b').html();
    if (!confirm('Удаление невозможно отменить! Вы уверены, что хотите это сделать?')) {
        return false;
    }
    $('input[class=\'input-product\']:checked').each(function () {
                selected[i] = this.value;
                i++;
            }
    );
    var url = "index.php?route=extension/module/parsermanager/delete&token=<?php echo $token; ?>";
    url += '&selected=' +selected ;
    url += '&page=' + page ;
    window.location.assign(url);

};

function addProduct() {
    var url = "index.php?route=extension/module/parsermanager/addproductparser&token=<?php echo $token; ?>";
    var filter_name = $('#filter_name').attr('value');
    var filter_sku_exist = $('#filter_sku_exist').attr('value');
    var filter_model = $('#filter_model').attr('value');
    var url_product = ($('td[name=0] div.delete_url a').attr('href'));
    var sku = $('#addproduct_sku').attr('value');
    var price = $('#addproduct_price').attr('value');
	
    if (filter_name) {
        url += '&filter_name=' + encodeURIComponent(filter_name);
    }

    if (filter_sku_exist) {
        url += '&filter_sku_exist=' + encodeURIComponent(filter_sku_exist);
    }

    if (sku) {
        url += '&sku=' + encodeURIComponent(sku);
    }

    if (price) {
        url += '&price=' + encodeURIComponent(price);
    }

    if(!url_product){
        alert('Нет ссылки на товар!');
        return false;
    } else {
        url += '&url=' + encodeURIComponent(url_product);
    }
	
    location = url;

};

function find_product(product_id, product_name) {
    $('#modal-image').remove();
    $.ajax({
        url: 'index.php?route=extension/module/parsermanager/findmanager&token=<?php echo $token; ?>&product_id=' + product_id + '&product_name=' + product_name ,
        dataType: 'html',
        beforeSend: function() {
            $('a[name='+product_id+']  i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
            $('#modal-search').prop('disabled', true);
        },
        complete: function() {
            $('a[name='+product_id+'] i').replaceWith('<i class="fa fa-search"></i>');
            $('#modal-search').prop('disabled', false);
        },
        success: function(html) {
            $('body').append('<div id="modal-image" class="modal">' + html + '</div>');
            $('#modal-image').modal('show');
        }
    });
    return false;   
}

function addNewUrl(product_id) {
	$('#url-modal input').val('');	
	$('#url-modal input').attr('data-product', product_id);	
	$('#url-modal').modal('show');		
}

function addUrlAjax(product_id, url_parsing){   
    $.ajax({
        type: "POST",
        data: "product_id="+product_id+"&url="+encodeURIComponent(url_parsing),
        url: "index.php?route=extension/module/parsermanager/addurl&token=<?php echo $token; ?>",
        dataType: "html",
        success: function(msg){			
			$('td[name='+product_id+'] div.add-url-product').remove();
            $('td[name='+product_id+']').append("<div  class='delete_url' ><a class='btn btn-default' href='"+url_parsing+"' target='_blank'>"+url_parsing.substring(0,20)+"..."+url_parsing.substring(url_parsing.length-10)+"</a><input type='hidden' name='parse_url["+product_id+"]' value='"+url_parsing+"'><div style='float:right;'><a  class='btn btn-danger' title='Удалить ссылку' onclick='deleteUrl(" + product_id + ");'><i class='fa fa-trash-o'/> </i></a></div></div>")
		},
        error: function(){          
        }
    });
}

function  view_tab(){	
	if(($("#yandex").prop("checked"))){
		$('#tab-yandex').show();
		$('#tab-hotline').hide();
		$('#tab-onliner').hide();		
		$('#tab-mail').hide();		
	}
	
	if(($("#hotline").prop("checked"))){
		$('#tab-hotline').show();
		$('#tab-yandex').hide();		
		$('#tab-onliner').hide();		
		$('#tab-mail').hide();		
	}
	
	if(($("#onliner").prop("checked"))){
		$('#tab-onliner').show();
		$('#tab-yandex').hide();
		$('#tab-hotline').hide();				
		$('#tab-mail').hide();		
	}
	
	if(($("#mail").prop("checked"))){
		$('#tab-mail').show();
		$('#tab-yandex').hide();
		$('#tab-hotline').hide();
		$('#tab-onliner').hide();				
	}	
}
function clearTableUrls(){
    $.ajax({
        url: "index.php?route=extension/module/parsermanager/clearTableUrls&token=<?php echo $token; ?>",
        dataType: "html",
        success: function(msg){
            $('#error-table').hide();
            alert(msg);
        },
        error: function(){
            alert('error');
        }
    });
}

function delete_cookie(){
    $.ajax({
        url: "index.php?route=extension/module/parsermanager/delete_cookie&token=<?php echo $token; ?>",
        dataType: "html",
        success: function(msg){
            alert(msg);
        },
        error: function(){
            alert('error');
        }
    });
}

function ajaxFileUpload()
{ 
	$.ajaxFileUpload
		({
			url:'index.php?route=extension/module/parsermanager/ajaxUploadFile&token=<?php echo $token; ?>',
			secureuri:false,
			fileElementId:'fileToUpload',
			dataType: 'json',
			success: function (data, status)
			{
				if(typeof(data.error) != 'undefined')
				{
					if(data.error != '')
					{
						alert(data.error);
					} else {
						alert(data.msg);						
					}
				}
			},
			error: function (data, status, e)
			{
				alert(e);
			}
		});
		
		return false;
}
///opencart 2.0
$('#modal-search').on('click', function() {
    $('#modal-image').remove();

    $.ajax({
        url: 'index.php?route=common/finmanager&token=<?php echo $token; ?>&target=' + $(element).parent().find('input').attr('id') + '&thumb=' + $(element).attr('id'),
        dataType: 'html',
        beforeSend: function() {
            $('#modal-search i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
            $('#modal-search').prop('disabled', true);
        },
        complete: function() {
            $('#modal-search i').replaceWith('<i class="fa fa-upload"></i>');
            $('#modal-search').prop('disabled', false);
        },
        success: function(html) {
            $('body').append('<div id="modal-image" class="modal">' + html + '</div>');
            $('#modal-image').modal('show');
        }
    });
    $(element).popover('hide');
});

// Category
$('input[name=\'category\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/category/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request),
			dataType: 'json',			
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['name'],
						value: item['category_id']
					}
				}));
			}
		});
	},
	'select': function(item) {
		$('input[name=\'category\']').val('');
		
		$('#product-category' + item['value']).remove();
		
		$('#product-category').append('<div id="product-category' + item['value'] + '"><i class="fa fa-minus-circle"></i> ' + item['label'] + '<input type="hidden" name="parsermanager_setting[product_category][]" value="' + item['value'] + '" /></div>');	
	}
});

$('#product-category').delegate('.fa-minus-circle', 'click', function() {
	$(this).parent().remove();
});
</script>

<?php echo $footer; ?>
