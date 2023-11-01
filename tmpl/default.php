<?php 
// No direct access
defined('_JEXEC') or die('Restricted access');
?>
<?php
$action_form = JUri::root() .'index.php/obchod/';
?>
<form method="GET" action= <?=$action_form?> id="search_form">
        <input type="text" name="search_box" class="form-control form-control-lg" placeholder='Hledat produkt...'
              id="search_box" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" autocomplete="off"
              value="<?=isset($_GET['search_box'])?htmlentities($_GET['search_box']):''?>"
            onkeyup="javascript:load_data(this.value)"  />
        <span id="search_result"></span>

        <input type="submit" class="btn btn-primary"
	        id="send" name="send" value="Vyhledat"><br />

<p class="lead">
<?php 
    if(isset($docs[count((array) ($docs))]['total_found'])):
        if ($docs[count((array) ($docs))]['total'] == 1) {
            //	    $product_link = (JURI::root().'?option=com_virtuemart&view=productdetails&virtuemart_product_id='.($docs[0] ["virtuemart_product_id"]).'&virtuemart_category_id='.($docs[0] ["virtuemart_category_id"]));
            $app = JFactory::getApplication();
            $product_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.($docs[0] ["virtuemart_product_id"]).'&virtuemart_category_id='.($docs[0] ["virtuemart_category_id"]));
            $app->redirect( JRoute::_($product_link) );
            
    
            //echo "<script>location.href='$product_link';</script>";
            //        header('Location: '.$product_link);
            //        header('Connection: close');
        }

        $last = count ($docs);
        $query_znacky = $docs[$last]['query'];
        $znacky = (ModZSearchSphinxHelper::getManufacturers($query_znacky));
//        echo '<form method="POST" action="index.php?module=zsearchsphinx" id="search_znacky_form">';
        echo 'Filtr značek : <br />';        
        foreach($znacky as $znacka){
        //echo '<a href='.$znacka.'>'.$znacka.'   --   </a>';
//        echo'<input type="hidden"  name="znacka_search_"'.$znacka.' value="'.$znacka.'">';
//        echo'<input type="hidden"  name="query_search_znacka" value="'.$docs.'">';
        echo'<input type="submit" name="znacky_search" class="btn btn-primary" value="'.$znacka.'">';

        }
        
        echo '<br />Nalezeno položek : '.$docs[count((array) ($docs))]['total'].'<br />';
//        echo '</form>';
    endif;

?>
</form>
</p>     
<div class="row"><div class="span" style="display: none;"></div>
    <?php if (count((array)($docs)) > 0): ?>
 	<div class="span9"><?php require_once dirname(__FILE__) . '/paginator.php';?></div>
<?php
    
     $i = 0; foreach ($docs as $doc):

            $product_id = $doc["virtuemart_product_id"] ?? null;
            $product_name = $doc['product_name'] ?? null;
	        $category_id = $doc['virtuemart_category_id'] ?? null;
	?>
            <div class="span9">
		<div class="container">
                <?php if (!$product_id) continue;?>
            <form method="post" class="product js-recalculate" action="#">
            <div class="addtocart-bar">
                
                <div class="main-image">
                    <div class="product-details-imege-handler">
                <?php $image_link = JUri::root() .'images/virtuemart/product/resized/'.$doc['file_url'];?>
            <img src=<?=$image_link;?>>
                    </div>
                    <div class="clear"></div>
                </div>

                
      <?php			
              $product_link = (JURI::root().'?option=com_virtuemart&view=productdetails&virtuemart_product_id='.($doc['virtuemart_product_id']).'&virtuemart_category_id='.($doc['virtuemart_category_id'])); 
       ?>    
            <a href="<?=$product_link; ?>"><?= $doc['product_name']?></a>
            <br />
            <?='<strong>dostupnost : '.$doc['product_availability'].'</strong>' ?>
            <br />
            <br />
            <?= "<i>Vaše cena : ".$doc['product_price']." Kč bez DPH/ks </i>"?>    

            <span class="quantity-box">
            <?= "<input class='input-mini' type='number' name='quantity[]' value=".$doc['min_order_level']." step=".$doc['step_order_level'].">"?> 

            </span>
                <span class="quantity-controls js-recalculate">
                <span class="quantity-controls quantity-plus"></span>
                <span class="quantity-controls quantity-minus"></span>
            </span>
            <input type="submit" name="addtocart" class="btn btn-primary" value="Do košíku" title="Do košíku">
            <input type="hidden" name="virtuemart_product_id[]" value=<?=$product_id?>>
            <noscript><input type="hidden" name="task" value="add"/></noscript>  
            <br/>
            <hr>
            </div>	
            <input type="hidden" name="option" value="com_virtuemart">
            <input type="hidden" name="view" value="cart">
            <input type="hidden" name="virtuemart_product_id[]" value=<?=$product_id?>>
            <input type="hidden" name="pname" value=<?=$product_name?>>
            <input type="hidden" name="pid" value=<?=$product_id?>>
            <input type="hidden" name="Itemid" value=<?=$category_id?>>
            </form>
                    </div>
            </div>

                
                
	<?php $i++; endforeach; ?>
        <div class="span9"><?php require dirname(__FILE__) . '/paginator.php';?></div>
    	<?php elseif (isset($_GET['query']) && $_GET['query'] != ''): ?>
            <p>Nenalezeno !</p>
	<?php endif; ?>
</div>
