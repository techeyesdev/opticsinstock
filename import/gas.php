<?php

ini_set('max_execution_time', 3000);

error_reporting(E_ALL);
ini_set('display_errors', '1');


//echo '<br/> gas = '.$_SERVER["SCRIPT_FILENAME"];



# include parseCSV class.
require_once('parsecsv.lib.php');

require_once("/home/techey5/public_html/opticsinstock.com/app/Mage.php");
umask(0);
Mage::app();



$fileName = 'gas.csv';
 


//mysql_select_db($db2,$databaseConnection);


	



$csv = new parseCSV();
					
$csv->auto($fileName);

/*
?><pre><?
	print_r($csv->data); 
?></pre><?
*/

//die();

$html = '2<br/>';

foreach($csv->data AS $key=>$value)
{
	
	$sku = '';	
	$product='';
	$stockItem = '';
	
	$is_inStock = 0;
	/*
	$sku =  str_replace("\"","",trim($value['"item_id"']));
	$sku = trim(substr($sku,3));
	*/
	
	$sku =  str_replace("\"","",trim($value['"item_id"']));
	
	//$sku = str_replace('-','',$sku);
	$qty = (int)$value['qty_available'];
	

	
	/*
	if($sku<>'BUC0135SSSC')
		continue;
	
	echo '<br/> sku = '.$sku.' qty = '.$qty;*/
	
	$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
	
	if($product)
	{
		
					$prdId = $product->getId();
					
					
					echo '<br/> id='. $product->getId().' sku = '.$sku.' stoc = '.$qty;
					$html.='<br/> id='. $product->getId().' sku = '.$sku.' stoc = '.$qty;
					 
					 if($qty>'0')
							$is_inStock = 1;
					 else
							echo '<br/> stoc 0 for sku '.$sku;
							
					
					
					$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
					
					
					$updatedFromHicks = $product->getUpdatedFromScript();
					
					
					//echo 'id = '.$product->getId().' updated = '.$updatedFromHicks;
					
					try { 
						
						if( $updatedFromHicks=='1' )
						{
							$origQty = (int)$stockItem->getQty();
							
							
				
							
							if($qty>$origQty)
							{
								$stockItem->setData('qty', $qty);
								$stockItem->setData('is_in_stock', 1);
								$stockItem->save();
								
								
								//echo '<br/> qty = '.$qty.' orig qty = '.$origQty;
								
							}
							else
							{
							
							$html.= '<br/> product '.$product->getId().' was not updated because billhicks script updated with a larger value of stoc='.$origQty.' from the curent desired value of '.$qty;	
						//	echo '<br/> product '.$product->getId().' was not updated because billhicks script updated with a larger value of stoc='.$origQty.' from the curent desired value of '.$qty;	
							}
						}
						else
						{
							$stockItem->setData('qty', $qty);
							$stockItem->setData('is_in_stock', 1);
							$stockItem->save();
						}
					} 
					catch (Exception $ex) { 
						//echo '<br/> error for product '.$product->getId().' with stock value = '.$qty.' and sku='.$sku.'  '.$ex->getMessage();
						
						$html.= '<br/> error for product '.$product->getId().' with stock value = '.$qty.' and sku='.$sku.'  '.$ex->getMessage();
					}
					
					
	}
	else
	{
		//echo '<br/>'.$sku.' not found!';
		$html.= '<br/>'.$sku.' not found!';
	}
	
}


require_once('class.phpmailer.php');

$mailer = new PHPMailer();



$mailer->SetFrom('support@techeye.com', 'Optinstock');


$mailer->AddAddress('cs@techeyes.com', 'CS Techeye');	
$mailer->Subject    = "Optinstock cron update for GAS ".date('Y-m-d H:i:s');
	 
$mailer->MsgHTML($html);
	
	
//echo '<br/>'.$html;
	
	
if(!$mailer->Send()) {
			echo "<br/>Mailer Error: " . $mailer->ErrorInfo;
			}
else
{
		echo '<br/> Email Sent';
}

