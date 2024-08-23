<?php
/****** CITA ******
 * CODING: HCK0011 / 2019-01-28
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
 
 
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id']) && isset($_POST['invoice_id']) && !empty($db->filter($_POST['user_id'])) && isset($_POST['email']) && isset($_POST['customer_name'])){
	
	$request_id = $db->filter($_POST['request_id']);
	$invoice_id = $db->filter($_POST['invoice_id']);
	$buyerEmail = $db->filter($_POST['email']);
	$userID 	= $db->filter($_POST['user_id']);
	$customer_name = $db->filter($_POST['customer_name']);
	$hasError = false;
	$msg = '';
	
	//Get User
	$db->where('status',1);
	$db->where('id',$userID);
	$getUser = $db->getOne('tbl_user_register');
	
	$userType = 0; //General Customer
	if(!empty($getUser)) $userType = 1;

	//Updat Shipping Status
	$deliverDate = date("Y-m-d h:i:s");
	
	$dateUpdate = array(
		'delivery'		=> 1,
		'deliveryDate' 	=> $deliverDate
	);
	
	$db->where('id',$request_id);
	if(!$db->update('tbl_request_order',$dateUpdate)){
		$hasError = true;
		$msg = 'Delivery status update failed.';
	}else{

		//add notification
		$dataNotification = array(
			'uno_usrID'	=> $userID,
			'unoTitle'	=> 'Order has been delivered',
			'unoDetail'	=> 'Your order ID '.str_pad($invoice_id, 7, '0', STR_PAD_LEFT).' has been delivered.',
			'uno_invoice_id'	=> $invoice_id,
			'unoType'	=> 1,
			'unoRead'	=> 0,
			'unoURL'	=> 'order_id='.$invoice_id,
			'unoIcon'	=> 'noti_icon_delivered'
		);
		
		if(!$db->insert('tbl_user_notifications',$dataNotification)){
			$hasError = true;
		}

	}
	
	
	//Get Invoice Detail
	$db->where('invoice_id',$invoice_id);
	$getInvoiceDetail = $db->get('tbl_invoice_detail');
	
	$totalProfit = 0;
	$hasProfit = '';
	$additionalInfo = '';
	
	if($userType == 1){
		if(!empty($getInvoiceDetail)){
			foreach($getInvoiceDetail as $invd){
				
				$memberPrice = $invd['member_price'];
				$retailPrice = $invd['retail_price'];
				
				if($retailPrice == 0) $retailPrice = $memberPrice;
				
				$profit = ($retailPrice - $memberPrice);
				
				$totalProfit += ($profit * $invd['quantity']);
			}
		}
		
		$hasProfit = "You don't have profit for this order.<br><br><br>";
		if($totalProfit > 0){
		
			$hasProfit = '<br><p><span style="color:#f00">* </span> <i>The profit of your order that will be deposited to your wallet is </i>: <b style="font-size:20px; color: #08daa1">$'.(number_format($totalProfit,2)+0).'</b></p><br>';
			
		}
		$additionalInfo = '<p>For more detail of your profits please see in your <a href="'.BASE_URL.'my-profile#wallet"> Account Wallet </a>.</p>';
	}
	
	
	//Send invoice to buyer
	
	$body_msg = '
				<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title>Bosdom.net</title>
				</head>

				<body bgcolor="#fff" >
				<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#fff" >
				  <tr>
					<td><table width="600" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="center" style="border-radius:10px; border:1px solid #ddd; box-shadow: 0px 1px 10px 4px rgba(0,0,0,0.10)">
						<tr>
							<td align="center" style="text-align:center">
								<p></p>
								<img src="'.BASE_URL.'assets/imgs/logo_160.jpg" width="160px"/>
								<br>
								<span style="color:#387cd4; font-family:Century Gothic,Hanuman,sans-serif; font-size:11px;">Wholesale / Supplyer / Online Shop</span>
							</td>
						</tr>
						<tr>
						  <td><hr style="width:90%; height:1px; background:#eee; border:none;"></td>
						</tr>
						<tr>
							<td align="center"><h3 style="font-family:Cambria,Hanuman; font-size:19px; color: #08daa1">Shipping completed</h3></td>
						</tr>
						
						<tr style="width:100%">
						  <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
							  <tr>
								<td width="10%">&nbsp;</td>
								<td width="80%" align="left" valign="top">
									<font style="font-family: Century Gothic; color:#666766; font-size:13px; line-height:21px">
										<p>
										Dear '.$customer_name.',
										<br><br>
										Your order ID <a href="'.BASE_URL.'my-profile#home">'.str_pad($invoice_id, 7, '0', STR_PAD_LEFT).'</a> has been delivered successfully on '.date("d-M-Y",strtotime($deliverDate)).'.</p>
										'.$hasProfit.'
										'.$additionalInfo.'
										<p>Thank you for your order!</p>
										<br>
									</font>
								</td>
							  </tr>
							 <tr>
								<td width="10%">&nbsp;</td>
								<td width="80%" align="left" valign="top"><br /><br />
									<font style="font-family: Century Gothic; color:#666766; font-size:13px; line-height:21px">
										<p>Best Regards, <br></p>
										<p><b>Bosdom.net Team</b></p>
										
									</font>
								</td>
								
							  </tr>
							 
							</table></td>
						</tr>
						
						  <td>&nbsp;</td>
						</tr>
					
						<tr>
						  <td><hr style="width:90%; height:1px; background:#ddd; border:none;"></td>
						</tr>
						<tr>
						  <td>&nbsp;</td>
						</tr>

						<tr>
						  <td align="center"><font style="font-family:Myriad Pro, Helvetica, Arial, sans-serif; color:#231f20; font-size:12px"><strong>Bosdom.net &copy; '.date('Y').' </strong></font></td>
						</tr>
						<tr>
						  <td>&nbsp;</td>
						</tr>
					  </table></td>
				  </tr>
				</table>
				</body>
				</html>
			';
	
		
		
		$subject = 'Order ID ' .str_pad($invoice_id, 7, '0', STR_PAD_LEFT). ' successfully delivered';
		
		if(send_mail($buyerEmail, 'noreply@bosdom.net', $subject, $body_msg)){
			//echo 'success';
		}else{
			echo 'Send Mail Unsuccess!';
		}
		
		$dataJson = array('status'=>$hasError,'msg'=>$msg);
		
		echo json_encode($dataJson);
	
}else{

	die('Invalid access!');
	
}
/**** IN GOD WE TRUST ****/
?>
