<style>
    body {
        overflow:auto;
    }
	
	.setting ul{
		padding:0;
		list-style-type:none;
	}
	
	.setting input {
		margin-right:15;		
	}
	
	.setting td.name-td + td {
		padding-left:30px;border-top: 1px solid #DDDDDD;
	}
	
	.setting td.name-td {
		vertical-align: text-top;
		list-style-type:none;
	}
	
	.setting .prop_img {
		margin-left:5px;
	}
	
	.setting .subsetting {
		margin-left: 30px;		 
	}
	
	.setting .subsetting b{
		margin-left: 10px;
		margin-right: 10px;
	}
		
	
	.left1{
        color: #222222;
        font-weight: bold;
        text-decoration: none;
        background-color: #EFEFEF;
        text-align:center;
		padding:7px;

    }
	
	.left2{
		text-decoration: none;
        text-align:left;
		padding:7px;

    }
	
    #loading{
        position:absolute;
        padding-left:50%;
        padding-top:20%;
		
    }
	
	#loading_img{
			display:none;
			position: fixed;
			top:50%;
			left:38%;
			z-index:101;
			color: #FFFFFF;
			font-size: 18px;
		}
		
    .my_button{
        background: none repeat scroll 0 0 #003A88;
        border-radius: 10px 10px 10px 10px;
        color: #FFFFFF;
        display: inline-block;
        padding: 5px 15px;
        text-decoration: none;
        }

    #prod_table	{
        width: 100%;
        border: 1px solid #DBDBDB;
        border-collapse: collapse;

    }
	
	#parsing_button{		
		margin-top:20px; ;
	}
   
	
    #prod_table td{
        border: 1px solid #DBDBDB;
        padding-top:5px;
        padding-bottom:5px;
    }



    #prod_table .event{
        background: #fff8dc;
    }

    .popopwindow{
        padding-top:35px;
        display:none;
        position: fixed;
        top:50%;
        left:25%;
        z-index:101;
        width:700px;
        height:100px;
        margin:-131px 0 0 -59px;
        text-align:left;
        background: #DBDBDB;
        border: 1px solid #333333;
		-moz-border-radius: 10px;
		-webkit-border-radius: 10px;
		border-radius: 10px;
    }

    #uploadhref{
        margin:10px;
        display:none;
    }

    .hideWrap{
        text-align:left;
        clear:both;
    }
    .hideBtn{
        display:block;
        padding:1px 14px 2px;
        outline:none;
    }
    .hideCont{
        display:none;
        z-index:auto ;
    }


    #fade {
        display: none;
        position: fixed;
        top: 0%;
        left: 0%;
        width: 100%;
        height: 100%;
        background-color:#000;
        z-index:100;
        -moz-opacity: 0.2;
        opacity:.20;
        filter: alpha(opacity=20);
    }
    	
	.tableForm{
		margin-left:33%;
	}	
	
</style>


<?php echo $header; ?>	
<div id="loading_img"  >
	<img  src="<?php echo $base.'view/image/ajax-loader.gif';?>" style="padding-left:90px;"/>
	<p>Выполняется проверка прокси...</p>
</div>
<div id="fade"> </div>
<div id="light" class="popopwindow">
	<input type="text"  class="input-search" style="margin: 15 20 0 30;text-shadow: 0px 1px 0px #fff;width:90%;">
	<input id="lightfind" type="button" class="my_button" value="Поиск" onclick = "" style="margin:15 20 35 230; width:100px;">
	<input type="button" class="my_button" value="Закрыть" onclick = "closePopUp()" style="width:100px;">
</div>
<div id="addUrl" class="popopwindow">
	<input type="text" class="add-url" style="margin: 15 20 0 30;text-shadow: 0px 1px 0px #fff;width:90%;">
	<input type="button" name="save_url" class="my_button" value="Сохранить ссылку"  style="margin:15 20 35 200; width:150px;">
	<input type="button" class="my_button" value="Закрыть" onclick = "$('#addUrl').hide();$('#fade').hide();" style="width:100px;">
</div>

<div id="upload" class="popopwindow">
		<img id="loading" src="<?php echo $base.'view/image/loading.gif';?>" style="display:none;padding-left:25%;">
		<form name="form" action="" method="POST" enctype="multipart/form-data">
	<table cellpadding="0" cellspacing="0" class="tableForm" >
		<thead>
			<tr>
				<th>Выберите файл и нажмите загрузить</th>
			</tr>
		</thead>
		<tbody>	
			<tr>
				<td>				
				<input id="fileToUpload" type="file" size="45" name="fileToUpload" ></td></tr>
		</tbody>
			<tfoot>
				<tr align="center">
					<td><input  type="submit" id="buttonUpload" onclick="return ajaxFileUpload();" class="my_button" value="Загрузить">
					<input  type="submit" id="buttonclose" onclick="$('#upload').hide();$('#fade').hide();return false;" class="my_button" value="Отмена">
					</td>
					
				</tr>
			</tfoot>
	
	</table>
		</form>    	
	
	</div>   
<div id="content">
  <div class="breadcrumb">   
    <?php  foreach ($this->data['breadcrumbs'] as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>

  </div>
 <?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
  <?php } ?>
	<?php if (isset($error_warning)) { ?>
	<div class="warning"><?php echo $error_warning; ?></div>
	<?php } ?>
  <div class="box">
    <div class="heading">	
      <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?></h1>      
    </div>
    <div class="content">	
	<div id="setting">
		<form id="form_setting" action="<?php echo $base."index.php?route=module/parsermanager/index&token=".$this->session->data['token'];?>" method="POST" >
			<fieldset><legend>Настройки</legend>
					<div class="hideWrap">
						<a class="hideBtn" href="javascript:void(0)" onclick="$('#hideCont1').slideToggle('normal');
						$(this).toggleClass('show');
						return false;">Показать/скрыть</a> <a href="<?php echo $base."index.php?route=module/parsermanager/parselog&token=".$this->session->data['token'];?>"  target="_blank" style="padding-left:10px;padding-top:0px;float:right;">Показать лог</a>	
						<a onclick="ViewContent();"  style="padding-left:50px;padding-top:0px;float:right;">Показать страницу</a>
						<div id="hideCont1" class="hideCont">
							<div class="trdiv" >					
							<table class="setting">
								<tr>
									<td  class="name-td" >
										<b>Ключ активации:</b>
									</td>
									<td>
										<input type="text"  size="100" name="parsermanager_setting[license_key]"   value="<?php if (isset($setting['license_key'])) echo $setting['license_key'];?>" />								
									</td>
								</tr>
								<tr>
									<td class="name-td">
										<b>Источник парсинга:</b>
									</td>
									<td>
										<ul>
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
											
										</ul>
									</td>
								</tr>
								<tr>
									<td  class="name-td" >
										<b>User-agent:</b>
									</td>
									<td>
										<input type="text"  id="user-agent" size="100" name="parsermanager_setting[user_agent]"   value="<?php if (isset($setting['user_agent'])) echo $setting['user_agent'];?>" />									
										<b><a href="javascript:void(0)" onclick="$('#user-agent').val(navigator.userAgent);">Взять из браузера</a></b>
									</td>
									
									</td>
								</tr>
								<tr>
									<td  class="name-td">
										<b>Прокси:</b>
									</td>
									<td>									
										<ul>
											<li style="margin-top:5px;">
												<input type="radio"  class="proxy" name="parsermanager_setting[proxy_check]" value="0" <?php if (isset($setting['proxy_check']) && $setting['proxy_check'] == '0' ) echo "CHECKED";?>/>Без прокси<br>
											</li>
											<li style="margin-top:5px;">
												<input type="radio"  class="proxy" name="parsermanager_setting[proxy_check]" value="1" <?php if (isset($setting['proxy_check']) && $setting['proxy_check'] == '1' ) echo "CHECKED";?>/>Прокси
												<span id="proxy_user" class="subsetting" style="padding-left:15px;">
													<b>Прокси IP:порт</b><input type="text"  size="25" name="parsermanager_setting[proxy_port]"   value="<?php if (isset($setting['proxy_port'])) echo $setting['proxy_port'];?>" />
													<b>Пользователь:пароль</b><input type="text"  size="25" name="parsermanager_setting[user_pass]"  value="<?php if (isset($setting['user_pass'])) echo $setting['user_pass'];?>" />
												</span>
											</li>
											<li style="margin-top:3px;">
												<input type="radio" class="proxy"  name="parsermanager_setting[proxy_check]" value="2" <?php if (isset($setting['proxy_check']) && $setting['proxy_check'] == '2' ) echo "CHECKED";?>  />Прокси - лист											
													<span id="proxy_list" class="subsetting" >
														<b><a href="javascript:void(0)" onclick="$('#fade').show();$('#upload').show();">Загрузить</a></b>
														<b><a href="javascript:void(0)" onclick="proxy_check_ajax();" >Проверить</a></b>													
													</span>										
											</li>
										</ul>									
									</td>
								</tr>
								<tr>
									<td  class="name-td" >
										<b>Cookie:</b>
									</td>
									<td>
										<input type="radio"  name="parsermanager_setting[cookie_check]" value="1" <?php if (isset($setting['cookie_check']) && $setting['cookie_check'] == '1' ) echo "CHECKED";?>/>Включить
										<input type="radio"  name="parsermanager_setting[cookie_check]" value="0" <?php if (isset($setting['cookie_check']) && $setting['cookie_check'] == '0' ) echo "CHECKED";?>/>Выключить
										<span id="cookie_upload" class="subsetting" >
														<b><a href="javascript:void(0)" onclick="$('#fade').show();$('#upload').show();">Загрузить файл с cookie</a></b>
														<b><a href="javascript:void(0)" onclick="delete_cookie();" >Удалить cookie</a></b>													
										</span>	
									
									</td>
								</tr>
								<tr>
									<td  class="name-td">
										<b>Папка для изображений:</b>
									</td>
									<td>
										<div class="prop_img">
												<span id="dir_image" style = "display:none">
													<?php echo DIR_IMAGE.'data/';?></span>
												<span  id="dir"><?php if(isset($setting['dir_to']) ) echo $setting['dir_to'];?></span>
												<span  id="spandir"></span> <a onclick="image_upload();">Выбрать папку</a>
												<input type="text"  size = "50" name="parsermanager_setting[dir_to]"  value="<?php  echo $setting['dir_to'];?>" id="dir_to"  style = "display:none">				
												
										</div>
										<input type="checkbox" style="margin:5 15 0 20;" name="parsermanager_setting[create_dir_image]" value="1" <?php if (isset($setting['create_dir_image'])) echo "CHECKED";?>/>Создавать папку для изображений<br>												
									</td>
								</tr>
								<tr id="tab-yandex">
									<td  class="name-td">
										<b>Даннные для Яндекс.маркет:</b>
									</td>	
									<td>
										<table>
											<tr>
												<td>
													<input type="radio" name="parsermanager_setting[yandex][productdata]" value="1" <?php if (isset($setting['yandex']['productdata']) && $setting['yandex']['productdata'] == '1' ) echo "CHECKED";?>/>Добавлять данные										
													<input type="radio" name="parsermanager_setting[yandex][productdata]" value="0" <?php if (isset($setting['yandex']['productdata']) && $setting['yandex']['productdata'] == '0' ) echo "CHECKED";?>/>Заменять данные<br>
												</td>
											<tr>
											<tr>
												<td >										
													<input type="checkbox" name="parsermanager_setting[yandex][addatribyte]" value="1" <?php if (isset($setting['yandex']['addatribyte'])) echo "CHECKED";?>/>Атрибуты<br>												
													<input type="checkbox" name="parsermanager_setting[yandex][addescription]" value="1" <?php if (isset($setting['yandex']['addescription'])) echo "CHECKED";?> />Описание<br>												
													<input type="checkbox" style="margin-top:15px;" name="parsermanager_setting[yandex][title]" value="1" <?php if (isset($setting['yandex']['title'])) echo "CHECKED";?> />Заменять название товара<br>
													<input type="checkbox"  name="parsermanager_setting[yandex][sku]" value="1" <?php if (isset($setting['yandex']['sku'])) echo "CHECKED";?> />Добавлять артикул<br>
													<input type="checkbox" name="parsermanager_setting[yandex][keyword]" value="1" <?php if (isset($setting['yandex']['keyword'])) echo "CHECKED";?> />Добавлять SEO URL<br>											
													<input type="checkbox" name="parsermanager_setting[yandex][model]" value="1" <?php if (isset($setting['yandex']['model'])) echo "CHECKED";?> />Заменять модель	<br>										
													<input type="checkbox" name="parsermanager_setting[yandex][seo_h1]" value="1" <?php if (isset($setting['yandex']['seo_h1'])) echo "CHECKED";?> />Заменять HTML-тег H1<br>											
												</td>
												<td style="vertical-align: text-top;">												
													<input type="checkbox" name="parsermanager_setting[yandex][meta_description]" value="1" <?php if (isset($setting['yandex']['meta_description'])) echo "CHECKED";?> />Мета-тег "Описание"(с включенным описанием)<br>	
													<input type="checkbox" name="parsermanager_setting[yandex][addallimg]" value="1" <?php if (isset($setting['yandex']['addallimg'])) echo "CHECKED";?>>Дополнительные изображения<br>															
													<input type="checkbox" style="margin-top:15px;" name="parsermanager_setting[yandex][addimage]" value="1" <?php if (isset($setting['yandex']['addimage'])) echo "CHECKED";?>>Изменять главное изображение<br>
													<input type="checkbox"  name="parsermanager_setting[yandex][price]" value="1" <?php if (isset($setting['yandex']['price'])) echo "CHECKED";?>>Заменять цену<br>	
													<input type="checkbox"  name="parsermanager_setting[yandex][manufacturer]" value="1" <?php if (isset($setting['yandex']['manufacturer'])) echo "CHECKED";?>>Добавлять производителя<br>
													<input type="checkbox"  name="parsermanager_setting[yandex][meta_keyword]" value="1" <?php if (isset($setting['yandex']['meta_keyword'])) echo "CHECKED";?>>Заменять Мета-тег Keywords<br>
													<input type="checkbox" name="parsermanager_setting[yandex][seo_title]" value="1" <?php if (isset($setting['yandex']['seo_title'])) echo "CHECKED";?> />Заменять HTML-тег Title	<br>									
													<input type="checkbox" name="parsermanager_setting[yandex][mobile_attribute]" value="1" <?php if (isset($setting['yandex']['mobile_attribute'])) echo "CHECKED";?> />Парсить атрибуты из мобильной версии											
												</td>
											</tr>										
										</table>	
									</td>								
								</tr>
								<tr id="tab-hotline">
									<td  class="name-td">
										<b>Даннные для Hotline.ua:</b>
									</td>	
									<td>
										<table>
											<tr>
												<td>
													<input type="radio" name="parsermanager_setting[hotline][productdata]" value="1" <?php if (isset($setting['hotline']['productdata']) && $setting['hotline']['productdata'] == '1' ) echo "CHECKED";?>/>Добавлять данные										
													<input type="radio" name="parsermanager_setting[hotline][productdata]" value="0" <?php if (isset($setting['hotline']['productdata']) && $setting['hotline']['productdata'] == '0' ) echo "CHECKED";?>/>Заменять данные<br>
												</td>
											<tr>
											<tr>
												<td >										
													<input type="checkbox" name="parsermanager_setting[hotline][addatribyte]" value="1" <?php if (isset($setting['hotline']['addatribyte'])) echo "CHECKED";?>/>Атрибуты<br>												
													<input type="checkbox" name="parsermanager_setting[hotline][addescription]" value="1" <?php if (isset($setting['hotline']['addescription'])) echo "CHECKED";?> />Описание<br>												
													<input type="checkbox" style="margin-top:15px;" name="parsermanager_setting[hotline][title]" value="1" <?php if (isset($setting['hotline']['title'])) echo "CHECKED";?> />Заменять название товара<br>
													<input type="checkbox"  name="parsermanager_setting[hotline][sku]" value="1" <?php if (isset($setting['hotline']['sku'])) echo "CHECKED";?> />Добавлять артикул<br>
													<input type="checkbox" name="parsermanager_setting[hotline][keyword]" value="1" <?php if (isset($setting['hotline']['keyword'])) echo "CHECKED";?> />Добавлять SEO URL<br>	
													<input type="checkbox" name="parsermanager_setting[hotline][model]" value="1" <?php if (isset($setting['hotline']['model'])) echo "CHECKED";?> />Заменять модель	<br>										
													<input type="checkbox" name="parsermanager_setting[hotline][seo_h1]" value="1" <?php if (isset($setting['hotline']['seo_h1'])) echo "CHECKED";?> />Заменять HTML-тег H1										
																				
												</td>
												<td style="vertical-align: text-top;">												
													<input type="checkbox" name="parsermanager_setting[hotline][meta_description]" value="1" <?php if (isset($setting['hotline']['meta_description'])) echo "CHECKED";?> />Мета-тег "Описание"(с включенным описанием)<br>	
													<input type="checkbox" name="parsermanager_setting[hotline][addallimg]" value="1" <?php if (isset($setting['hotline']['addallimg'])) echo "CHECKED";?>>Дополнительные изображения<br>															
													<input type="checkbox" style="margin-top:15px;" name="parsermanager_setting[hotline][addimage]" value="1" <?php if (isset($setting['hotline']['addimage'])) echo "CHECKED";?>>Изменять главное изображение<br>
													<input type="checkbox"  name="parsermanager_setting[hotline][price]" value="1" <?php if (isset($setting['hotline']['price'])) echo "CHECKED";?>>Заменять цену<br>	
													<input type="checkbox"  name="parsermanager_setting[hotline][manufacturer]" value="1" <?php if (isset($setting['hotline']['manufacturer'])) echo "CHECKED";?>>Добавлять производителя<br>
													<input type="checkbox"  name="parsermanager_setting[hotline][meta_keyword]" value="1" <?php if (isset($setting['hotline']['meta_keyword'])) echo "CHECKED";?>>Заменять Мета-тег Keywords<br>
													<input type="checkbox" name="parsermanager_setting[hotline][seo_title]" value="1" <?php if (isset($setting['hotline']['seo_title'])) echo "CHECKED";?> />Заменять HTML-тег Title											
											
												</td>
											</tr>										
										</table>	
									</td>								
								</tr>
								<tr id="tab-onliner">
									<td  class="name-td">
										<b>Даннные для Onliner.by:</b>
									</td>	
									<td>
										<table>
											<tr>
												<td>
													<input type="radio" name="parsermanager_setting[onliner][productdata]" value="1" <?php if (isset($setting['onliner']['productdata']) && $setting['onliner']['productdata'] == '1' ) echo "CHECKED";?>/>Добавлять данные										
													<input type="radio" name="parsermanager_setting[onliner][productdata]" value="0" <?php if (isset($setting['onliner']['productdata']) && $setting['onliner']['productdata'] == '0' ) echo "CHECKED";?>/>Заменять данные<br>
												</td>
											<tr>
											<tr>
												<td >										
													<input type="checkbox" name="parsermanager_setting[onliner][addatribyte]" value="1" <?php if (isset($setting['onliner']['addatribyte'])) echo "CHECKED";?>/>Атрибуты<br>												
													<input type="checkbox" name="parsermanager_setting[onliner][addescription]" value="1" <?php if (isset($setting['onliner']['addescription'])) echo "CHECKED";?> />Описание<br>												
													<input type="checkbox" style="margin-top:15px;" name="parsermanager_setting[onliner][title]" value="1" <?php if (isset($setting['onliner']['title'])) echo "CHECKED";?> />Заменять название товара<br>
													<input type="checkbox"  name="parsermanager_setting[onliner][sku]" value="1" <?php if (isset($setting['onliner']['sku'])) echo "CHECKED";?> />Добавлять артикул<br>
													<input type="checkbox" name="parsermanager_setting[onliner][keyword]" value="1" <?php if (isset($setting['onliner']['keyword'])) echo "CHECKED";?> />Добавлять SEO URL<br>											
													<input type="checkbox" name="parsermanager_setting[onliner][model]" value="1" <?php if (isset($setting['onliner']['model'])) echo "CHECKED";?> />Заменять модель	<br>										
													<input type="checkbox" name="parsermanager_setting[onliner][seo_h1]" value="1" <?php if (isset($setting['onliner']['seo_h1'])) echo "CHECKED";?> />Заменять HTML-тег H1											

													</td>
												<td style="vertical-align: text-top;">												
													<input type="checkbox" name="parsermanager_setting[onliner][meta_description]" value="1" <?php if (isset($setting['onliner']['meta_description'])) echo "CHECKED";?> />Мета-тег "Описание"(с включенным описанием)<br>	
													<input type="checkbox" name="parsermanager_setting[onliner][addallimg]" value="1" <?php if (isset($setting['onliner']['addallimg'])) echo "CHECKED";?>>Дополнительные изображения<br>															
													<input type="checkbox" name="parsermanager_setting[onliner][addallimg_big]" value="1" <?php if (isset($setting['onliner']['addallimg_big'])) echo "CHECKED";?>>Дополнительные изображения c большим разрешением<br>															
													<input type="checkbox" style="margin-top:15px;" name="parsermanager_setting[onliner][addimage]" value="1" <?php if (isset($setting['onliner']['addimage'])) echo "CHECKED";?>>Изменять главное изображение<br>
													<input type="checkbox"  name="parsermanager_setting[onliner][price]" value="1" <?php if (isset($setting['onliner']['price'])) echo "CHECKED";?>>Заменять цену<br>	
													<input type="checkbox"  name="parsermanager_setting[onliner][manufacturer]" value="1" <?php if (isset($setting['onliner']['manufacturer'])) echo "CHECKED";?>>Добавлять производителя<br>
													<input type="checkbox"  name="parsermanager_setting[onliner][meta_keyword]" value="1" <?php if (isset($setting['onliner']['meta_keyword'])) echo "CHECKED";?>>Заменять Мета-тег Keywords<br>
													<input type="checkbox" name="parsermanager_setting[onliner][seo_title]" value="1" <?php if (isset($setting['onliner']['seo_title'])) echo "CHECKED";?> />Заменять HTML-тег Title											
											
												</td>
											</tr>										
										</table>	
									</td>								
								</tr>
								<tr id="tab-mail">
									<td  class="name-td">
										<b>Даннные для Torg.mail.ru:</b>
									</td>	
									<td>
										<table>
											<tr>
												<td>
													<input type="radio" name="parsermanager_setting[mail][productdata]" value="1" <?php if (isset($setting['mail']['productdata']) && $setting['mail']['productdata'] == '1' ) echo "CHECKED";?>/>Добавлять данные										
													<input type="radio" name="parsermanager_setting[mail][productdata]" value="0" <?php if (isset($setting['mail']['productdata']) && $setting['mail']['productdata'] == '0' ) echo "CHECKED";?>/>Заменять данные<br>
												</td>
											<tr>
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
								</tr>
								<tr>
									<td class="name-td">
										<b  >Фильтр:</b>
									</td>
									<td>
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
													  <?php foreach ($categories as $category) { ?>
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
								<tr>
									<td class="name-td">
										<b>Поиск на сайте источнике:</b>
									</td>
									<td>
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
								<tr>
									<td class="name-td">
										<b>Задержка при парсинге<br> нескольких товаров(сек.):</b>
									</td>
									<td>
										<input type="text"  size="3" name="parsermanager_setting[pause]" style="margin-left:7px;" value="<?php if (isset($setting['pause'])) echo $setting['pause']; else echo '0'; ?>" />
									</td>
								</tr>
								<tr>
									<td class="name-td">
										<b>Настройки для новых товаров:</b>
									</td>
								<td>
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
											<td><?php echo $entry_main_category;?></td>
											<td>
												<select name="parsermanager_setting[main_category_id]">
													<option value="0" selected="selected"><?php echo $text_none; ?></option>
													<?php foreach ($categories as $category) { ?>
													<?php if ($category['category_id'] == $main_category_id) { ?>
													<option value="<?php echo $category['category_id']; ?>" selected="selected"><?php echo $category['name']; ?></option>
													<?php } else { ?>
													<option value="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></option>
													<?php } ?>
													<?php } ?>
												  </select>
											 </td>
										</tr>
										<tr>											
											<td><?php echo $entry_category; ?></td>
											<td>
												<div class="scrollbox" style="height: 120px;">
													  <?php $class = 'odd'; ?>
													  <?php foreach ($categories as $category) { ?>
													  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
													  <div class="<?php echo $class; ?>">
														<?php if (in_array($category['category_id'], $product_category)) { ?>
														<input type="checkbox" name="parsermanager_setting[product_category][]" value="<?php echo $category['category_id']; ?>" checked="checked" />
														<?php echo $category['name']; ?>
														<?php } else { ?>
														<input type="checkbox" name="parsermanager_setting[product_category][]" value="<?php echo $category['category_id']; ?>" />
														<?php echo $category['name']; ?>
														<?php } ?>
													  </div>
													  <?php } ?>
												 </div>
												 <a onclick="$(this).parent().find(':checkbox').attr('checked', true);"><?php echo $text_select_all; ?></a> / <a onclick="$(this).parent().find(':checkbox').attr('checked', false);"><?php echo $text_unselect_all; ?></a>
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
								<tr>
									<td  class="name-td">
										<b>Количество  товаров<br> на странице:</b>
									</td>
									<td >
										<input type="text" name="parsermanager_setting[countproduct]" size="3"  value="<?php if (isset($setting['countproduct'])) echo $setting['countproduct'];?>" >
									</td>
								</tr>
								<tr>
									<td  class="name-td">
										<b>Удалять из названия:</b>
									</td>
									<td >										
										<textarea name="parsermanager_setting[delete_title]"  cols="100" rows="2"><?php echo isset($setting['delete_title']) ? $setting['delete_title'] : ''; ?></textarea>
									</td>
								</tr>								
								<tr>
									<td  class="name-td">
										<b>Обновление:</b>
									</td>
									<td style="border-bottom:1px solid #DDDDDD;">
										<a href="<?php echo $base."index.php?route=module/parsermanager/updateTableParser&token=".$this->session->data['token'];?>">Обновить таблицы парсера</a>								
									</td>
								</tr>							
								<tr>
									<td  class="name-td" colspan="2">
										<input type="submit" class="my_button"  name="parsermanager_setting[save_setting]" value="Сохранить изменения">
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
			
			<div id="new_product">	
				<form id="form_add_product" action="">
					<fieldset><legend>Добавление нового товара</legend>	
							<table>						
								<tr>
									<td width="80%;" name="0"   style="text-align:right;" >
									<div style="float:right;"><img src="view/image/add.png"  onclick="addUrl(0); " /></div>
									<div  class="delete_url">										
									</div>
									
									<td  width="100px;" style="text-align:center;">
									<a onclick="showPopUp(0);" class="item0" title="samsung">Найти товар</a></td>
									<td  width="150px;" style="text-align:right;"><a onclick="addProduct();" class="button">Добавить товар</a></td>
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
						<a onclick="searchAllProducts();" class="button"><?php echo 'Искать ссылки' ?></a>
						<a onclick="parsingAllProducts();" class="button"><?php echo 'Парсить все' ?></a>
						<a onclick="deleteProduct();" class="button">Удалить товар</a>
					</div>				
				</div>			
				<div id="product_list">
					<fieldset><legend>Обновление существующих товаров</legend>	
					<table id="prod_table">
						<thead>								
						<tr>	
							<td class="left1"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></td>
							<td class="left1">Изобр.</td>
							<td class="left1"><?php echo $column_name; ?></td>
							<td class="left1">Модель</td>
							<td class="left1">Артикул/SKU</td>	
							<td class="left1">Цена</td>	
							<td class="left1" >Ссылка на товар</td>	
							<td class="left1" colspan="3" align="left">Действие</td>
						</tr>
						</thead>
						<tr class="left1" > 
							<td class="left1"></td>
							<td class="left1"></td>									
							<td class="left1"><input type="text" id="filter_name"  value="<?php if (isset($setting['filter_name'])) echo $setting['filter_name'];?>" size = "40" name="filter_name" class="ui-autocomplete-input" autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true" value="<?php  echo $setting['filter_name'];?>"></td>
							<td class="left1"><input type="text" id="filter_model"  value="<?php if (isset($setting['filter_model'])) echo $setting['filter_model'];?>" size = "30" name="filter_model" class="ui-autocomplete-input" autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true" value="<?php  echo $setting['filter_model'];?>"></td>
							<td class="left1"><input type="text" id="filter_sku_exist"  value="<?php if (isset($setting['filter_sku_exist'])) echo $setting['filter_sku_exist'];?>" size = "10" name="filter_sku_exist"  value="<?php  echo $setting['filter_sku_exist'];?>"></td></td>
							<td  class="left1"></td>
							<td class="left1" ></td>	           
													   
							<td class="left1" colspan="3"><a onclick="filter();" class="button">Фильтр</a></td>
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
								<td class="left2" >												
									<input  class='input-product' type='checkbox' name="selected[<?php echo $product['product_id'];?>][product_id]" value='<?php echo $product['product_id'];?>'>	
								</td>
								<td class="left2"><img src="<?php echo $product['image']; ?>" style="padding: 1px; border: 1px solid #DDDDDD;" /></td>
								<td  class="left2">										
									<a name ="<?php echo $product['product_id'];?>" href="<?php echo $base."index.php?route=catalog/product/update&token=".$this->session->data['token']."&product_id=".$product['product_id'];?>"  target="_blank"><?php echo $product['name'];?></a>
								</td>
								<td class="left2" ><?php echo $product['model'];?></td>
								<!--<td class="left2"><?php echo $product['sku'];?></td>-->
								<td class="left2" ><input type="text" name="selected[<?php echo $product['product_id'];?>][sku]" value="<?php echo $product['sku'];?>" size="10"></td>
								<td class="left2" ><input type="text" name="selected[<?php echo $product['product_id'];?>][price]" value="<?php echo $product['price'];?>" size="8"></td>
								<td  class="left2" name='<?php echo $product['product_id'];?>' width="300:px;">							
									<div style="float:right;"><img src="view/image/add.png"  onclick="addUrl(<?php echo $product['product_id'];?>); " /></div>
									<div  class="delete_url">
										<?php if (!empty($product['url_parsing'])) {?> 							
										<a href="<?php echo $product['url_parsing'];?>" target="_blank"><?php echo substr($product['url_parsing'], 0, 20).'...'.substr($product['url_parsing'], strlen($product['url_parsing'])-10);?></a>
										<div  style="float:right;margin-right:7px;"><img src="view/image/delete.png"  onclick="deleteUrl(<?php echo $product['product_id'];?>); " /></div>
										<?php }?>
									</div>
								</td>					
								<td class="left2">								
								<a  name='<?php echo $product['product_id'];?>' onclick="find_product(<?php echo $product['product_id'];?>, null);">Поиск</a>																						
								</td>
								<td class="left2">
								<a  name = '<?php echo $product['product_id'];?>' onclick="showPopUp(<?php echo $product['product_id'];?>);" >Ввести<br> запрос</a>						
								</td>
								<td  class="left2">
									<a onclick="parsingProduct(<?php echo $product['product_id'];?>);" name = "<?php echo $product['product_id'];?>" >Парсить</a>
								</td>
							</tr>
							<?php 	
							$ev++;
						}						
						?>						
						</table>
					</fieldset>
				</div>
			</form>	
		<div class="pagination"><?php echo $pagination; ?></div>		
   </div>	 
</div>
<script type="text/javascript" src="view/javascript/jquery/ajaxfileuploadparser.js"></script> 
<script type="text/javascript">

$(document).ready(function () {	
	$('#filter_name').autocomplete({
	delay: 0,
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=module/parsermanager/autocompleteparserj&token=<?php echo $this->session->data['token'];?>&filter_name=' +  $('#filter_name').val(),
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
	select: function(event, ui) {
		$('#filter_name').val(ui.item.label);
		$('#filter_name').val(ui.item.label);
		return false;
	},
	focus: function(event, ui) {
      	return false;
   	}
    });

	
	
	$('#filter_model').autocomplete({
	delay: 0,
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=module/parsermanager/autocompleteparserj&token=<?php echo $this->session->data['token'];?>&filter_model=' +  $('#filter_model').val(),
			dataType: 'json',
			success: function(json) {	
				response($.map(json, function(item) {
					return {
						label: item.model,
						value: item.product_id
					}
				}));
			}
		});		
	}, 
	select: function(event, ui) {		
		$('#filter_model').val(ui.item.label);				
		return false;
	},
	focus: function(event, ui) {
      	return false;
   	}
    });	
	
    $('#attributefile').click(function () {
            if(($("#attributefile").prop("checked"))){
                $("#uploadhref").show();
            } else {
                $("#uploadhref").hide();
            }

        }
    );
	
	
	view_tab();

});

function deleteUrl(product_id) {	
	if (!confirm("Удалить ссылку?")) {
		return false;
	}
	
	 $.ajax({		
		url: "index.php?route=module/parsermanager/deleteUrl&token=<?php echo $this->session->data['token'];?>&product_id="+product_id,
		 dataType: "html",
		success: function(msg){
		$('td[name='+product_id+'] div.delete_url').html('');
   },
    error: function(){
        alert('error');
    }
 });
}

function proxy_check_ajax() {
	
	$('#loading_img').show();
	$('#fade').show();
	$.ajax({		
		url: "index.php?route=module/parsermanager/proxycheck&token=<?php echo $this->session->data['token']; ?>",
		dataType: "html",
		success: function(msg){
		$('#loading_img').hide();
		$('#fade').hide();
		alert('Найдено рабочих прокси - ' +  msg );
	
   },
    error: function(){
        alert('error');
		$('#loading_img').hide();
		$('#fade').hide();
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
	
	$('#form_parsing').attr('action', '<?php echo $base."index.php?route=module/parsermanager/parsingAllproducts&token=".$this->session->data['token'];?>'+'&product_id='+filter_value+page);
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
	
	$('#form_parsing').attr('action', '<?php echo $base."index.php?route=module/parsermanager/searchAllproducts&token=".$this->session->data['token'];?>'+'&product_id='+filter_value+page);
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
	
	$('#form_parsing').attr('action', '<?php echo $base."index.php?route=module/parsermanager/parseProduct&token=".$this->session->data['token'];?>'+'&product_id='+product_id+'&price='+price+filter_value+page);
	$('#form_parsing').submit();
	
}

function filter() {
        var url = "index.php?route=module/parsermanager&token=<?php echo $this->session->data['token']; ?>";
        var filter_name = $('#filter_name').attr('value');
        var filter_sku_exist = $('#filter_sku_exist').attr('value');
		var filter_model = $('#filter_model').attr('value');		
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

	var url = "index.php?route=module/parsermanager/delete&token=<?php echo $this->session->data['token']; ?>";
	url += '&selected=' +selected ;
	url += '&page=' + page ;
	window.location.assign(url);
	
};

function addProduct() {
	var url = "index.php?route=module/parsermanager/addproductparser&token=<?php echo $this->session->data['token']; ?>";
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
	
	if(!$(url_product)){
		alert('Нет ссылки на товар!');
		return false;
	} else {	
		url += '&url=' + encodeURIComponent(url_product);	
	}
	
	location = url;
	
};

function find_product(product_id, product_name) {
	$('#dialog').remove();	
	$('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><img id="loading" src="<?php echo $base.'view/image/loading.gif';?>"><iframe  src="index.php?route=module/parsermanager/findmanager&token=<?php echo $this->session->data['token']; ?>&product_id='+product_id+'&product_name='+product_name+'" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no"      scrolling="auto" name="frame"></iframe></div>');	
	$('#dialog').dialog({
		title: '<?php echo "Результаты поиска"; ?>',
		bgiframe: false,
		width: 800,
		height: 400,
		resizable: false,
		modal: false
	});
	$('#fade').hide();   
	$('#light').hide();
}


function image_upload() {
	$('#dialog').remove();	
	$('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe  src="index.php?route=module/parsermanager/foldermanager&token=<?php echo $this->session->data['token']; ?>" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no"      scrolling="auto" name="frame"></iframe></div>');	
	$('#dialog').dialog({
		title: '<?php echo "Менеджер папок"; ?>',
		bgiframe: false,
		width: 800,
		height: 400,
		resizable: false,
		modal: false
	});
}

function ViewContent() { 
	$('#dialog').remove();	
	$('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe  src="<?php echo $base.'download/content.htm' ;?>" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no"      scrolling="auto" name="frame"></iframe></div>');	
	$('#dialog').dialog({
		title: '<?php echo "Последняя страница"; ?>',
		bgiframe: false,
		width: 900,
		height: 400,
		resizable: false,
		modal: false
	});
}



function frameclose() {	
	$('#dialog').remove();
	$('#dir_to').val($('#dir_image').text()+$('#spandir').text());
	$('#dir').text($('#dir_image').text());
}

function frameclosefindmanager() {	
	$('#dialog').remove();
}

function showPopUp(product_id){
	var product_name = $('a[name='+product_id+']').html();	
	if 	(product_name){
		product_name = delsym(product_name);
	}
	$('#light .input-search').attr('value', product_name);
	$('#lightfind').attr('onclick', "product_name = delsym($('#light .input-search').val());find_product('"+product_id+"', product_name)");
	$('#light').show();	
	$('#fade').show();		
}
     
function closePopUp(){
	$('#searchdiv').hide();
	$('#fade').hide();   
	$('#light').hide();
	$('body').css('overflow','auto');	
	//$('body').scrollTop(pos.top-100);
}

function delsym(str) {
	str=str.replace(/\'/g,' ');
	str=str.replace(/\"/g,' ');
	str=str.replace(/\)/g,' ');
	str=str.replace(/\(/g,' ');
	str=str.replace(/&lt;/g,'<');
	str=str.replace(/&gt;/g,'>');	
	str=str.replace(/&nbsp;/g,' ');
	
	return str;
}

function addUrl(product_id) {
	$('.add-url').val('');
	$('#addUrl').show();	
	$('.add-url').attr('name', product_id);
	$('input[name=save_url]').attr('onclick', 'addUrlAjax()');
	$('#fade').show();
}

function addUrlAjax(product_id){	
	var product_id = ($('.add-url').attr('name'));
	var url_parsing = ($('.add-url').val());
	
	$.ajax({	
		type: "POST",
		data: "product_id="+product_id+"&url="+encodeURIComponent(url_parsing),
		url: "index.php?route=module/parsermanager/addurl&token=<?php echo $this->session->data['token']; ?>",
		dataType: "html",
		success: function(msg){		
			$('td.yandex[name='+product_id+'] div.delete-url').html('');
			$('td[name='+product_id+'] div.delete_url').append("<a href="+url_parsing+" target='_blank'>"+url_parsing.substring(0,20)+"..."+url_parsing.substring(url_parsing.length-10)+"</a><div  class='delete_url' style='float:right;margin-right:7px;'><input type='hidden' name='parse_url["+product_id+"]' value='"+url_parsing+"'><img src='view/image/delete.png'  onclick='deleteUrl("+product_id+");' /> </div>")
			$('#addUrl').hide();
			$('#fade').hide();
	   },
		error: function(){
			//alert('error');
			$('#addUrl').hide();
			$('#fade').hide();
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
		url: "index.php?route=module/parsermanager/clearTableUrls&token=<?php echo $this->session->data['token']; ?>",
		dataType: "html",
		success: function(msg){
		$('#error-table').hide();		
		alert(msg);	
   },
    error: function(){
      //  alert('error');
    }
 });
}

function delete_cookie(){	
	$.ajax({		
		url: "index.php?route=module/parsermanager/delete_cookie&token=<?php echo $this->session->data['token']; ?>",
		dataType: "html",
		success: function(msg){
		alert(msg);	
   },
    error: function(){
      //  alert('error');
    }
 });
}

function ajaxFileUpload()
	{
		$.ajaxFileUpload
		(
			{
				url:'index.php?route=module/parsermanager/ajaxUploadFile&token=<?php echo $this->session->data['token']; ?>',
				secureuri:false,
				fileElementId:'fileToUpload',
				dataType: 'json',
				data:{name:'logan', id:'id'},
				success: function (data, status)
				{
					if(typeof(data.error) != 'undefined')
					{
						if(data.error != '')
						{
							alert(data.error);
						} else {
							alert(data.msg);
							$('#upload').hide();
							$('#fade').hide();
						}
					}
				},
				error: function (data, status, e)
				{
					alert(e);
				}
			}
		)
		
		return false;

}

</script>

<?php echo $footer; ?>
