<?php
/****** CITA ******
 * CODING: HCK0011 / 2017-01-19
 * Description: Send Invoice to user who general customer
 */
 
 session_start();
 include_once '_config_inc.php';
 include_once BASE_PATH.'_libs/site_class.php';
 include_once BASE_PATH.'_libs/limit_text.php';
 
 $db = new gen_class($configs);
 
  //send mail function
 function send_mail($to, $email, $subject, $messages){
 	$headers  = 'MIME-Version: 1.0' . "\r\n";
 	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
 	$headers .= 'From: Bosdom Order'."<$email>" . "\r\n";
 	$messages = $messages;
 	if(@mail($to, $subject, $messages, $headers)){
 		return true;
 	}
 	return false;
 }
if(isset($_POST['send_invoice'])){
	$invoice_id = $_POST['invoice_id'];
	$name = $_POST['name'];
	$address = $_POST['address'];
	$phone = $_POST['phone'];
	
	// Get Invoice
	$db->where('id',$invoice_id);
	$getInvoice = $db->getOne('tbl_invoice');
	$date = $getInvoice['date'];
	$time = strtotime($date);
	$invoice_date = date("d-M-Y  g:i A", $time);
	// Get Invoice Detail and product Detail

	$selectedFields = 'product_sku_title, P.id as proID, P.code as proCode, P.title as proTitle, P.ordered as proOrdered, I.customer_price as proCustomPrice, quantity, in_stock';

	$db->join('tbl_invoice_detail I','P.id=I.product_id','inner');
	$db->where('I.invoice_id',$invoice_id);
	$getProducts = $db->get('tbl_products P',null,$selectedFields);
	$AllTotal = 0;
	$products_list = '';
	$bgcolor = '';
	$i=0;
	
	$totalInstock = 0;
	$buyQty = 0;
	$totalOrdered = 0;

	foreach($getProducts as $product){
		if($i==0){
			$bgcolor = '#ffffff';
			$i=1;
		}else{
			$bgcolor = '#f9f9f9';
			$i=0;
		}

		$totalInstock = $product['in_stock'];
		$totalOrdered = $product['proOrdered'];
		$buyQty 	  = $product['quantity'];
		$totalInstock = $totalInstock - $buyQty;
		$totalOrdered = $totalOrdered + $buyQty;

		$dataUpdate = array(
			'in_stock' 	=> $totalInstock,
			'ordered'	=> $totalOrdered
		);

		//updat stock
		$db->where('id',$product['proID']);
		$db->update('tbl_products',$dataUpdate);

		$skuInfo = '';
		if($product['product_sku_title'] != ""){
			$skuInfo = '('.$product['product_sku_title'].')';
		}
		
		$products_list= $products_list.'
			<tr style="color:#000000; font-family:Century Gothic; font-size:14px; line-height:30px" bgcolor="'.$bgcolor.'">
				<td align="center">'.$product['proCode'].'</td>
				<td>'.limitText_return($product['proTitle'],30).$skuInfo.'</td>
				<td align="center">'.$product['quantity'].'</td>
				<td align="center">'.$product['proCustomPrice'].'</td>
			</tr>
		';
		
        $AllTotal = $AllTotal + ($product['proCustomPrice'] * $product['quantity']);
	}

    $date = new DateTime();
	$currentDate = $date->format("Y-m-d H:i:s");

	//update invoice status
	$updateInvoice = array(
		'status' => 1,
		'date'	=> $currentDate	
	);
	
	
	$db->where('id',$invoice_id);
	$db->update('tbl_invoice',$updateInvoice);

	//Update partner Wallet
	require(BASE_PATH.'update_wallet_partner_only.php');
	
}elseif(isset($_POST['member_buy'])){
	
	//Take Score from product add to administrator and user
	require(BASE_PATH.'update_score.php');
	//Update Member and Partner Wallet
	require(BASE_PATH.'update_wallet.php');
	
}

/**** IN GOD WE TRUST ****/
?>
