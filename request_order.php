<?php
/****** CITA ******
 * CODING: HCK0011 / 2017-01-06
 * Description: Send order request to admin
 */
session_start();
include_once '_config_inc.php';
include_once BASE_PATH.'_libs/site_class.php';
include_once BASE_PATH.'_libs/limit_text.php';
$db = new gen_class($configs);
$login_status = 'false';
$hasError = false;
if(!empty($_SESSION['user_id'])){
	$login_status = 'true';
}else{
	die('Please login to your account');
}



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

	/* Private Function for this page */
	function getidRequest($db){
		$result = $db->getOne("tbl_request_order","MAX(`id`) AS id");
		if(empty($result['id'])){
			$result['id'] = 0;
		}
		return ((int)$result['id'] + 1);
	}

	/* Private Function for this page */
	function getPrimaryKey($db){
		$result = $db->getOne("tbl_invoice","MAX(`id`) AS id");
		if(empty($result['id'])){
			$result['id'] = 0;
		}
		return ((int)$result['id'] + 1);
	}

	/* Private Function for this page */
	function getPrimaryKey1($db){
		$result = $db->getOne("tbl_invoice_detail","MAX(`id`) AS id");
		if(empty($result['id'])){
			$result['id'] = 0;
		}
		return ((int)$result['id'] + 1);
	}

	$user_id = $_SESSION['user_id'];

	$request_by = '0';
	if(isset($_POST['request_by'])){
		$request_by = $_POST['request_by'];
	}
	
	$token = isset($_SESSION['session_token']) ? $_SESSION['session_token'] : md5(mt_rand(99,9999));
	
	//product score
	$getFirstUser=null;
	$getSecondUser=null;
	$getThirdUser=null;
		
	// Find Referal User
	$db->where('register_id',$$user_id);
	$getFirstUser = $db->getOne('tbl_referal');

	if($getFirstUser!=null){
		$db->where('register_id',$getFirstUser['refer_by_id']);
		$getSecondUser = $db->getOne('tbl_referal');
	}

	if($getSecondUser!=null){
		$db->where('register_id',$getSecondUser['refer_by_id']);
		$getThirdUser = $db->getOne('tbl_referal');
	}

	$productCount = 0;
	
	$multiScore=0;
	$firstScore=0;
	$secondScore=0;
	$thirdScore=0;
	$forthScore=0;
	$manual_score_list = '';
	
	$score ='1,1,1,1,1';
	$manual_score ='';
	$totalScore = 0;
	$totalQty = 0;
	$totalWholesale = 0;
	$totalProfit = 0;
	$totalPrice = 0;
	$totalSale = 0;
	
	$products_list = '';
	
	$memberType = 'General Customer';
	$buyerName = '';
	$buyerPhone = '';
	$buyerAddress = '';
	$buyerEmail = '';

if(isset($_POST['shippingMethod']) && isset($_POST['token']) && $_POST['token'] == $token && !empty($_POST['return-url'])){
    $return_url = urldecode($_POST['return-url']);
	$shippingMethod = $_POST['shippingMethod'];
	
	$paymentMethod = 0;
	if(isset($_POST['online_payment'])) $paymentMethod = 1;

	$db->startTransaction();

	if(isset($_SESSION['buy_now_product_id']) && isset($_SESSION['buy_now_quantity'])){
		
	   //User request single product from buy now
		$product_id = $_SESSION['buy_now_product_id'];
		$quantity = $_SESSION['buy_now_quantity'];
		$product_sku_id = $_SESSION['buy_now_sku'];
		$retail_price = !empty($_SESSION['buy_now_retail_price']) ? $_SESSION['buy_now_retail_price'] : 0;
	
		$currentDate = date("Y-m-d H:i:s");
		
		// Get Product Info
		$db->where('id',$product_id);
		$db->where('status',1);
		$getProduct = $db->getOne('tbl_products');
		if($getProduct['is_manual_score']==1){
			$score = $getProduct['manual_score'];
		}
		$unit_price 	= $getProduct['price'];
		$member_price 	= $getProduct['member_price'];
		$customer_price = $getProduct['customer_price'];
        $owner 			= $getProduct['owner'];
		$product_name 	= $getProduct['title'];
		$product_code 	= $getProduct['code'];
		
		$salePrice = $retail_price != 0 ? $retail_price : $getProduct['member_price'];
		$wholesale_price = $getProduct['member_price'];

		//create product score
		$multiScore = $getProduct['multi_score'];
		$manual_score = $getProduct['manual_score'];
		
		if($getProduct['manual_score'] == '') $manual_score = '1,1,1,1,1';

		if(empty($retail_price)){
			$retail_price = $member_price;
		}

		$requestId = getidRequest($db);
		
		$displayPrice = 0;

		$memberType = 'Member';
		$buyerEmail = $_SESSION['user_email'];
		
		$request = array(
			"id"		=> $requestId,
			"userID"   	=> $user_id,
			"name" 		=> $_SESSION['user_name'],
			"sex" 		=> $_SESSION['user_sex'],
			"email" 	=> $_SESSION['user_email'],
			"address"	=>$_SESSION['user_address'],
			"phone"		=>$_SESSION['user_phone'],
			"date"		=> $currentDate,
			"request_by" 		=> $request_by,
			"shippingMethod" 	=> $shippingMethod,
			"paymentMethod" 	=> $paymentMethod,
			"status" 			=> 0
		);
		
		if($shippingMethod == 2){
			$request['senderPhone']		= $db->filter(@$_POST['senderPhone']);
			$request['receivePhone']	= $db->filter($_POST['receivePhone']);
			$request['sellPrice']		= $db->filter(@$_POST['sellPrice']);
			$request['shippingAddress']	= $db->filter($_POST['shippingAddress']);
			$request['otherNote']		= $db->filter($_POST['otherNote']);
		}
		

		$manual_score_list = explode(',',$manual_score);
		$firstScore		= ($manual_score_list[1]);
		$secondScore	= ($manual_score_list[2]);
		$thirdScore		= ($manual_score_list[3]);
		$forthScore		= ($manual_score_list[4]);
		
		if(empty($getFirstUser)){
			$score = ($productCount * 4) + $firstScore;
		}elseif(!empty($getFirstUser) && empty($getSecondUser)){
			$score = ($productCount*3) + $secondScore;
		}elseif(!empty($getSecondUser) && empty($getThirdUser)){
			$score = ($productCount * 2) + $thirdScore;
		}else{
			$score = ($productCount * 1 ) + $forthScore;
		}
		

		if(!$db->insert('tbl_request_order',$request)){
			$hasError = true;
		}else{

			$totalPrice = ($salePrice * $quantity);
			$totalScore = ($score * $quantity);
			$totalWholesale = ($wholesale_price * $quantity);
			
			//invoice for member
			$products_list ='
			<tr style="color:#000000; font-family:Century Gothic; font-size:14px; line-height:30px">
				<td align="center">'.$getProduct['code'].'</td>
				<td>'.limitText_return($getProduct['title'],30).'</td>
				<td align="center">'.$quantity.'</td>
				<td align="center">'.$score.'</td>
				<td align="center">'.$wholesale_price.'</td>
				<td align="center">'.$salePrice.'</td>
			</tr>';
			
			$headerInvoice = '
				<th width="80" height="30">Code</th>
				<th width="200" align="left">Product Name</th>
				<th width="140">Quantity</th>
				<th width="140">Score</th>
				<th width="140">Wholesale Price</th>
				<th width="140">Sale Price</th>
			';
			
			$totalFooter = '
				<tr style="color:#000000; font-family:Century Gothic; font-size:14px; line-height:30px">
					<td style="border-top:1px solid #ccc"></td>
					<td style="border-top:1px solid #ccc" align="right">Total</td>
					<td style="border-top:1px solid #ccc" align="center">'.$quantity.'</td>
					<td style="border-top:1px solid #ccc" align="center">'.$totalScore.'</td>
					<td style="border-top:1px solid #ccc" align="center">$'.$totalWholesale.'</td>
					<td style="border-top:1px solid #ccc" align="center">$'.$totalPrice.'</td>
				</tr>
			';
			
			$buyerName = $_SESSION['user_name'];
			$buyerPhone = $_SESSION['user_phone'];
			$buyerAddress = $_SESSION['user_address'];
			
			$skuTitle = "";
					if($product_sku_id != ""){
						$db->where('skuValue',$product_sku_id);
						$getSKU = $db->getOne('tbl_sku');
						$skuTitle = $getSKU['skuDisplay'];
					}
	
			$invoice_id = getPrimaryKey($db);
	
			$data = array(
				"id"=> $invoice_id,
				"request_id" => $requestId,
				"date" =>$currentDate,
				"status"=>'0'
			);
	
			if(!$db->insert('tbl_invoice',$data)){
				$hasError = true;
			}else{
				$primaryKey = getPrimaryKey1($db);
				$data1 = array(
					"id"=> $primaryKey,
					"invoice_id" => $invoice_id,
					"product_id" => $product_id,
					"product_name" => $product_name,
					"product_code" => $product_code,
					"quantity" => $quantity,
					"unit_price" => $unit_price,
					"member_price" => $member_price,
					"customer_price" => $customer_price,
					"retail_price" => $retail_price,
					"product_sku_id" => $product_sku_id,
					"product_sku_title" => $skuTitle, 
					"product_owner" => $owner,
					"score" => $manual_score,
					"status" => '1'
				);
		
				if(!$db->insert('tbl_invoice_detail',$data1)){
					$hasError = true;
				}
			}
		}
		

   }else{ //If user request product form cart
   
		$invoice_id = getPrimaryKey($db);
		$requestId = getidRequest($db);

		$buyerEmail = $_SESSION['user_email'];
		$memberType = 'Member';
		$buyerName  = $_SESSION['user_name'];
		$buyerPhone = $_SESSION['user_phone'];
		$buyerAddress = $_SESSION['user_address'];

		$db->where('buy_type','add-to-cart');
		$db->where('user_id',$user_id);
		$getProductsCart = $db->get('tbl_user_buy');
		$currentDate = date("Y-m-d H:i:s");

		// Create Request
		$request = array(
			"id"		=> $requestId,
			"userID"	=> $user_id,
			"name" 		=> $_SESSION['user_name'],
			"sex" 		=> $_SESSION['user_sex'],
			"email" 	=> $_SESSION['user_email'],
			"address"	=>$_SESSION['user_address'],
			"phone"		=>$_SESSION['user_phone'],
			"date"		=> $currentDate,
			"request_by" 		=> $request_by,
			"shippingMethod" 	=> $shippingMethod,
			"paymentMethod" 	=> $paymentMethod,
			"status" 			=> 0
		);
		
		if($shippingMethod == 2){
			$request['senderPhone']		= $db->filter(@$_POST['senderPhone']);
			$request['receivePhone']	= $db->filter($_POST['receivePhone']);
			$request['sellPrice']		= $db->filter(@$_POST['sellPrice']);
			$request['shippingAddress']	= $db->filter($_POST['shippingAddress']);
			$request['otherNote']		= $db->filter($_POST['otherNote']);
		}

		if(!$db->insert('tbl_request_order',$request)){
			$hasError = true;
		}else{

			//Create Invoice
			$data = array(
				"id"			=> $invoice_id,
				"request_id" 	=> $requestId,
				"date" 			=>$currentDate,
				"status"		=> 0
			);
			if(!$db->insert('tbl_invoice',$data)){
				$hasError = true;
			}else{
				foreach($getProductsCart as $productCart){
					$primaryKey = getPrimaryKey1($db);
					$score ='1,1,1,1,1';
					// Get Product Info
					$db->where('id',$productCart['product_id']);
					$db->where('status',1);
					$getProduct = $db->getOne('tbl_products');
	
					if($getProduct['is_manual_score']==1){
						$score = $getProduct['manual_score'];
					}
	
					$unit_price = $getProduct['price'];
					$member_price = $getProduct['member_price'];
					$customer_price = $getProduct['customer_price'];
					$retail_price = $productCart['retail_price'];
					$product_sku_id = $productCart['product_sku'];
					$owner = $getProduct['owner'];
					$product_name 	= $getProduct['title'];
					$product_code 	= $getProduct['code'];
				
					if($retail_price == 0 ){
						$retail_price = $member_price;
					}
	
					$skuTitle = "";
					if($product_sku_id != ""){
						$db->where('skuValue',$product_sku_id);
						$getSKU = $db->getOne('tbl_sku');
						$skuTitle = $getSKU['skuDisplay'];
					}
	
					$data1 = array(
						"id"=> $primaryKey,
						"invoice_id" => $invoice_id,
						"product_id" => $productCart['product_id'],
						"product_name" => $product_name,
						"product_code" => $product_code,
						"quantity" => $productCart['quantity'],
						"unit_price" => $unit_price,
						"member_price" => $member_price,
						"customer_price" => $customer_price,
						"retail_price" => $retail_price,
						"product_sku_id" => $product_sku_id,
						"product_sku_title" => $skuTitle,
						"product_owner" => $owner,
						"score" => $score,
						"status" => '1'
					);
					
					
					if(!$db->insert('tbl_invoice_detail',$data1)){
						$hasError = true;
					}
					
					$salePrice = $retail_price != 0 ? $retail_price : $getProduct['member_price'];
					$wholesale_price = $getProduct['member_price'];
								
					// $quantity = $item['quantity'];
					$quantity = 1;
					
					$profit = ($salePrice - $wholesale_price) * $quantity;
					
					//create product score
					$multiScore = $getProduct['multi_score'];
					$manual_score = $getProduct['manual_score'];
					if($getProduct['manual_score'] == '') $manual_score = '1,1,1,1,1';
	
					$manual_score_list = explode(',',$manual_score);
					$firstScore		= ($manual_score_list[1] * $quantity);
					$secondScore	= ($manual_score_list[2] * $quantity);
					$thirdScore		= ($manual_score_list[3] * $quantity);
					$forthScore		= ($manual_score_list[4] * $quantity);
					
					if(empty($getFirstUser)){
						$score = ($productCount * 4) + $firstScore;
					}elseif(!empty($getFirstUser) && empty($getSecondUser)){
						$score = ($productCount*3) + $secondScore;
					}elseif(!empty($getSecondUser) && empty($getThirdUser)){
						$score = ($productCount * 2) + $thirdScore;
					}else{
						$score = ($productCount * 1 ) + $forthScore;
					}
					
					if(empty($retail_price)){
						$retail_price = $member_price;
					}
					
					$data1['code'] = $getProduct['code'];
					$data1['title'] = $getProduct['title'];
					$data1['score'] = $score;
					
					$totalQty += $data1['quantity'];
					$totalScore += ($score * $data1['quantity']);
					$totalWholesale += ($data1['member_price'] * $data1['quantity']);
					$totalPrice += ($data1['retail_price'] * $data1['quantity']);
					
					$products_list .='
						<tr style="color:#000000; font-family:Century Gothic; font-size:14px; line-height:30px">
							<td align="center">'.$data1['code'].'</td>
							<td>'.limitText_return($data1['title'],30).'</td>
							<td align="center">'.$data1['quantity'].'</td>
							<td align="center">'.$data1['score'].'</td>
							<td align="center">'.$data1['member_price'].'</td>
							<td align="center">'.$data1['retail_price'].'</td>
						</tr>
					';
				}
			}

			
			$headerInvoice = '
				<th width="80" height="30">Code</th>
				<th width="200" align="left">Product Name</th>
				<th width="140">Quantity</th>
				<th width="140">Score</th>
				<th width="140">Wholesale Price</th>
				<th width="140">Sale Price</th>
			';
			
			$totalFooter = '
			<tr style="color:#000000; font-family:Century Gothic; font-size:14px; line-height:30px">
				<td style="border-top:1px solid #ccc"></td>
				<td style="border-top:1px solid #ccc" align="right">Total</td>
				<td style="border-top:1px solid #ccc" align="center">'.$totalQty.'</td>
				<td style="border-top:1px solid #ccc" align="center">'.$totalScore.'</td>
				<td style="border-top:1px solid #ccc" align="center">$'.$totalWholesale.'</td>
				<td style="border-top:1px solid #ccc" align="center">$'.$totalPrice.'</td>
			</tr>';

			// remove all items in shopping cart after user buy
			if(!$hasError){
				$db->where('buy_type','add-to-cart');
				$db->where('user_id',$user_id);
				if(!$db->delete('tbl_user_buy')){
					$hasError = true;
				}
			}
		}
    }

	//save transaction
	if(!$hasError){
		$db->commit();
	}else{
		$db->rollback();
	}
   
   $body_msg = '
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			
			<title>Baellerryasia Invoice</title>
			</head>

			<body bgcolor="#ffffff" >
			
			<table style="max-width:790px; width:100%" height="200" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" >
				<tbody style="vertical-align:top" >
				  <tr style="text-align:right; font-family:Century Gothic; font-size:17px; font-weight:bold">
						<td width="100%">
							<img src="'.BASE_URL.'assets/imgs/logo_160.jpg" style="width:120px"><br>
							<span style="color:#000000; font-family:Century Gothic; font-size:11px;">Email : '.$_SESSION['mail'].'</span><br>
							<span style="color:#000000; font-family:Century Gothic; font-size:11px;">Phone : '.$_SESSION['admin_phone'].'</span>
						</td>
					</tr>
					<tr>
					  <td align="left" width="100%">
							<span style="color:#000000; font-family:Century Gothic; font-size:11px;">Invoice No: '.str_pad($invoice_id, 7, '0', STR_PAD_LEFT).'</span><br>
							<span style="color:#000000; font-family:Century Gothic; font-size:11px;">Date/Time : '.date("Y-m-d h:i a").'</span><br>
							<span style="color:#000000; font-family:Century Gothic; font-size:11px;">Customer : '.$memberType.'</span><br>
							<span style="color:#000000; font-family:Century Gothic; font-size:11px;">Name : '.$buyerName.'</span><br>
							<span style="color:#000000; font-family:Century Gothic; font-size:11px;">Phone : '.$buyerPhone.'</span><br>
							<span style="color:#000000; font-family:Century Gothic; font-size:11px;">Address : '.$buyerAddress.'</span>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr style="text-align:center; font-family:Century Gothic; font-size:17px; font-weight:bold">
						<td colspan="2" width="100%">
							<font> Order Detail </font>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
					
				  </tbody>
				  
			</table>
			
			<table style="max-width:790px; width:100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
				
				<tbody style="vertical-align:top">
					<tr width="600" height="auto">
						<td align="center">
						<table border="0" cellspacing="0" cellpadding="0">
							<thead>
								<tr width="600" cellspacing="0" cellpadding="0" bgcolor="#f2f2f2" style="color:#000000; font-family:Century Gothic; font-size:14px;">
									'.$headerInvoice.'
								</tr>
							</thead>
							<tbody>
								'.$products_list.$totalFooter.'
							</tbody>
							
						</table>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
					<tr style="text-align:center; font-family:Century Gothic; font-size:15px; font-weight:bold">
						<td>
							<font>Thank you for your order.</font>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
				   </tr>
					<tr>
						<td><hr style="width:90%; height:2px; background:#08bbff; border:none;"></td>
				   </tr>
				  
					<tr>
						<td align="center"><font style="font-family:Myriad Pro, Helvetica, Arial, sans-serif; color:#231f20; font-size:12px"><strong><a href="'.BASE_URL.'" style="text-decoration:none; color: #000">Bosdom.net</a> &copy; '.date('Y').' </strong></font></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
					</tr>
				</tbody>
			</table>
			</body>
			</html>
		';
		
		//Send confirm order to member
		$subject = 'Thank you for your order';
		
		if(!empty($buyerEmail) && !send_mail($buyerEmail, 'noreply@bosdom.net', $subject, $body_msg)){
			echo 'Send Mail Unsuccess!';
		}

	$_SESSION['session_token'] = md5(mt_rand(99,9999));
	$redirect = BASE_URL.'my-profile';
   	header("Location:$redirect");
	   
}elseif(!empty($db->filter($_POST['remove_request']))){
	$request_id = $_POST['request_id'];

	$db->where('id',$request_id);
	$db->delete('tbl_request_order');

}

/**** IN GOD WE TRUST ****/
?>
