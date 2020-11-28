<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Поиск товара</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <?php if(!empty($items)){?>
                <table id='products' >
                <?php for($i=0;$i<10;$i++){
					if(isset($items[$i])){?>
                    <tr>
                        <td style='width:15%;'>
                            <img src='<?php echo $items[$i]['src'];?>'>
                        </td>
                        <td style='width:65%;'>
                            <a href='<?php echo $items[$i]['href'];?>' target='_blank'><?php echo $items[$i]['title'];?></a><br>
                            <?php echo  $items[$i]['desc'];?>
                            <br>Цена:<?php echo $items[$i]['price'];?>
                        </td>
                        <td style='width:10%;'>
                            <button name = "<?php echo $product_id;?>" type="button" class="btn btn-primary pull-center" data-dismiss="modal" onclick="addUrl('<?php echo $product_id;?>', '<?php echo urlencode($items[$i]['href']);?>')">Выбрать</button>
                        </td>
                    </tr>					
                    <?php  }
				}?>
                </table>
                <?php
			} else {?>
				<div class="col-sm-10">
                <h4>Товаров не найдено!!!</h4>
				</div>
			<?php  }?>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    function addUrl(product_id, url_parsing) {
		if(product_id==0){
			
		}
		
        $.ajax({
            type: "POST",
            data: "product_id="+product_id+"&url="+url_parsing,
            url: "index.php?route=module/parsermanager/addUrl&token="+getURLVar('token'),
            dataType: "html",
            success: function(msg){
                if (msg == '')
                    msg = decodeURIComponent(url_parsing);			   
				$('td[name='+product_id+'] div.add-url-product').remove();
				$('td[name='+product_id+']').append("<div  class='delete_url' ><a class='btn btn-default' href='"+msg+"' target='_blank'>"+msg.substring(0,20)+"..."+msg.substring(msg.length-10)+"</a><input type='hidden' name='parse_url["+product_id+"]' value='"+msg+"'><div style='float:right;'><a  class='btn btn-danger' title='Удалить ссылку' onclick='deleteUrl(" + product_id + ");'><i class='fa fa-trash-o'/> </i></a></div></div>")
				//$('#modal-image').remove();
            },
            error: function(){
                alert('error');

            }
        });

    }



</script>