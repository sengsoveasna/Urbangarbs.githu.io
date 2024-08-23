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
$hasError = false;
if(isset($_POST['send_invoice'])){
	$invoice_id = $_POST['invoice_id'];
	$name = $_POST['name'];
	$request_id = $_POST['request_id'];

	//get order 
	$db->where('id',$request_id);
	$getRequest = $db->getOne('tbl_request_order');
	$userID = $getRequest['userID'];
	
	$db->startTransaction();
	// var_dump($getRequest);
	if(!empty($getRequest)){
		//add notification
		$dataNotification = array(
			'uno_usrID'	=> $userID,
			'unoTitle'	=> 'Order has been accepted',
			'unoDetail'	=> 'Your order ID '.str_pad($invoice_id, 7, '0', STR_PAD_LEFT).' has been accepted.',
			'uno_invoice_id'	=> $invoice_id,
			'unoType'	=> 1,
			'unoRead'	=> 0,
			'unoURL'	=> 'order_id='.$invoice_id,
			'unoIcon'	=> 'noti_icon_order'
		);
		
		if(!$db->insert('tbl_user_notifications',$dataNotification)){
			$hasError = true;
		}

		
	}else{
		$hasError = true;
	}
	
	
	//update invoice status
	$updateUpdate = array(
		'acceptOrder' => 1
	);

	$db->where('id',$request_id);
	if(!$db->update('tbl_request_order',$updateUpdate)){
		$hasError = true;
	}

	if(!$hasError){
		$db->commit();
	}else{
		$db->rollback();
	}

	
		//Begin of Mail Body design
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
						<td align="center"><h3 style="font-family:Cambria,Hanuman; font-size:19px; color: #f59937">Order has been accepted</h3></td>
					</tr>
					
					
					<tr style="width:100%">
					  <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
						  <tr>
							<td width="10%">&nbsp;</td>
							<td width="80%" align="left" valign="top"><br /><br />
								<font style="font-family: Century Gothic; color:#666766; font-size:13px; line-height:21px">
									<p>Dear '.$name.',</p><br>
									<p>Your order ID '.str_pad($invoice_id, 7, '0', STR_PAD_LEFT).' has been accepted.</p>
									
									<p style="width:90%;">
										Currently, your order is being packaged and ready for delivery to your address. You may charge for shipping on delivery.
									</p>
									<br>
									<p>Thank you for your order.</p>
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
		
		//Send Mail to Referral Member alert has someone signed up by his refer link
		$to =  $_POST['email'];
		$from = $_SESSION['mail'];
		$subject = 'Your order ' .str_pad($invoice_id, 7, '0', STR_PAD_LEFT). ' has been accepted';
		
		if(send_mail($to, 'noreply@bosdom.net', $subject, $body_msg, $from)){
			//echo 'success';
		}else{
			echo 'Send Mail Unsuccess!';
		}

}elseif(isset($_POST['member_buy'])){
	
	$invoice_id = $_POST['invoice_id'];
	$request_id = $_POST['request_id'];
	$name = $_POST['name'];

	//get order 
	$db->where('id',$request_id);
	$getRequest = $db->getOne('tbl_request_order');
	$userID = $getRequest['userID'];
	
	$db->startTransaction();

	if(!empty($getRequest)){
		//add notification
		$dataNotification = array(
			'uno_usrID'	=> $userID,
			'unoTitle'	=> 'Order has been accepted',
			'unoDetail'	=> 'Your order ID '.str_pad($invoice_id, 7, '0', STR_PAD_LEFT).' has been accepted.',
			'uno_invoice_id'	=> $invoice_id,
			'unoType'	=> 1,
			'unoRead'	=> 0,
			'unoURL'	=> 'order_id='.$invoice_id,
			'unoIcon'	=> 'noti_icon_order'
		);

		if(!$db->insert('tbl_user_notifications',$dataNotification)){
			$hasError = true;
		}

	}else{
		$hasError = true;
	}
	
	//update invoice status
	$updateUpdate = array(
		'acceptOrder' => 1
	);
 
	$db->where('id',$request_id);
	if(!$db->update('tbl_request_order',$updateUpdate)){
		$hasError = true;
	}

	if(!$hasError){
		$db->commit();
	}else{
		$db->rollback();
	}
	
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
						<img src="'.BASE_URL.'assets/imgs/logo_160.jpg" width="160px" />
						<br>
						<span style="color:#387cd4; font-family:Century Gothic,Hanuman,sans-serif; font-size:11px;">Whosale / Supplyer / Online Shop</span>
					</td>
				</tr>
				<tr>
				  <td><hr style="width:90%; height:1px; background:#eee; border:none;"></td>
				</tr>
				<tr>
					<td align="center"><h3 style="font-family:Cambria,Hanuman; font-size:19px; color: #f59937">Order has been accepted</h3></td>
				</tr>
				
				<tr style="width:100%">
				  <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
					  <tr>
						<td width="10%">&nbsp;</td>
						<td width="80%" align="left" valign="top"><br /><br />
							<font style="font-family: Century Gothic; color:#666766; font-size:13px; line-height:21px">
								<p>Dear '.$name.',</p><br>
								<p>Your order ID <a href="'.BASE_URL.'my-profile">'.str_pad($invoice_id, 7, '0', STR_PAD_LEFT).'</a> has been accepted.</p>
								
								<p style="width:90%;">
									Currently, your order is being packaged and ready for delivery to customer. <br> Once, customer received the package we will send you an email to confirm your order and get profits.
								</p>
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
	
	//Send confirm order to member
	$to =  $_POST['email'];
	$subject = 'Your order ' .str_pad($invoice_id, 7, '0', STR_PAD_LEFT). ' has been accepted';
	
	if(!send_mail($to, 'noreply@bosdom.net', $subject, $body_msg)){
		echo 'Send Mail Unsuccess!';
	}
	
}

/**** IN GOD WE TRUST ****/
?>
