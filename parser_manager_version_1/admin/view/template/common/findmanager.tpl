<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title><?php echo $title; ?></title>
<base href="<?php echo $base; ?>" />
<script type="text/javascript" src="view/javascript/jquery/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="view/javascript/jquery/lazyload/jquery.lazyload.min.js"></script>
<script type="text/javascript" src="view/javascript/jquery/ui/jquery-ui-1.8.16.custom.min.js"></script>
<link rel="stylesheet" type="text/css" href="view/javascript/jquery/ui/themes/ui-lightness/jquery-ui-1.8.16.custom.css" />
<script type="text/javascript" src="view/javascript/jquery/ui/external/jquery.bgiframe-2.1.2.js"></script>
<script type="text/javascript" src="view/javascript/jquery/ajaxupload.js"></script>


<style type="text/css">
body {
	padding: 0;
	margin: 0;
	background: #F7F7F7;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
}
img {
	border: 0;
}
#container {
	padding: 0px 10px 7px 10px;
	height: 340px;
}
#menu {
	clear: both;
	height: 29px;
	margin-bottom: 3px;
}
#column-left {
	background: #FFF;
	border: 1px solid #CCC;
	float: left;
	width:100%;
	height: 320px;
	overflow: auto;
}

#dialog {
	display: none;
}
.button {
	display: block;
	float: left;
	padding: 8px 5px 8px 25px;
	margin-right: 5px;
	background-position: 5px 6px;
	background-repeat: no-repeat;
	cursor: pointer;
}
.button:hover {
	background-color: #EEEEEE;
}
.thumb {
	padding: 5px;
	width: 105px;
	height: 105px;
	background: #F7F7F7;
	border: 1px solid #CCCCCC;
	cursor: pointer;
	cursor: move;
	position: relative;
}
#products{
	background: #aaaaaa; 
		
}
#products tr,td{
	background: #F7F7F7; 
	padding:10px;
	
}


</style>

<script type="text/javascript">		
	 parent.$("#dialog #loading").hide();	
</script>

</head>
<body>
<div id="container">
  <div id="menu">
  </div>
  <div id="column-left"> 
  <?php
	if(!empty($items)){
		?>
		<table id='products' >
		<?php for($i=0;$i<10;$i++){
			if(isset($items[$i])){
			?>
				<tr>
					<td style='width:15%;'>
						<img src='<?php echo $items[$i]['src'];?>'>		
					</td>
					<td style='width:65%;'>
						<a href='<?php echo $items[$i]['href'];?>' target='_blank'><?php echo $items[$i]['title'];?></a><br>
						<?php echo  $items[$i]['desc'];?>
						<br>Цена:<?php echo $items[$i]['price'];?>
					</td>
					<td style='width:20%;'>
						<a href="javascript:void(0)" name = "<?php echo $this->request->get['product_id'];?>"   onclick="addUrl('<?php echo $this->request->get['product_id'];?>', '<?php echo urlencode($items[$i]['href']);?>')" >Выбрать</a>
					</td>
					</tr>
			<?php }
		}?>
		</table>
	<?php 
	} else {?>
		<span>Товаров не найдено!!!</span>
	
	<?php  }  ?>
  
  </div>
</div>
<script type="text/javascript">

function addUrl(product_id, url_parsing) {			
	$.ajax({	
		type: "POST",
		data: "product_id="+product_id+"&url="+url_parsing,
		url: "index.php?route=module/parsermanager/addUrl&token=<?php echo $this->session->data['token']; ?>",
		dataType: "html",
		success: function(msg){	
			if (msg == '')
				msg = decodeURIComponent(url_parsing);
			parent.$('td[name='+product_id+'] div.delete_url').append("<a href="+msg+" target='_blank'>"+msg.substring(0,20)+"..."+msg.substring(msg.length-10)+"</a><div  class='delete_url' style='float:right;margin-right:7px;'><input type='hidden' name='parse_url["+product_id+"]' value='"+msg+"'><img src='view/image/delete.png'  onclick='deleteUrl("+product_id+");' /> </div>");
			parent.frameclosefindmanager();	
	   },	 
		error: function(){
			alert('error');
			parent.frameclosefindmanager();	
		}
	});
	
}


 
</script>
</body>
</html>