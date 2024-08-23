<?php
	/****** CITA ******
	* CODING: HCK0011 / 2018-11-15
	* Description: Update Member wallet transaction
	*/
	include_once '_config_inc.php';
	include_once BASE_PATH.'_libs/site_class.php';
	$db = new gen_class($configs);

    //function get Id from table score
	function getMaxID($db,$table,$field){
		$result = $db->getOne($table,"MAX($field) AS id");
		if(empty($result['id'])){
			$result['id'] = 0;
		}
		return ((int)$result['id'] + 1);
	}

	$hasError = false;
    //Get Admin website
    $adminIDList = [];
    $db->where('user_type',3,'<>');
    $getAdmin = $db->get('tbl_user_admin');
    foreach($getAdmin as $admin){
        $adminIDList[] = $admin['user_id'];
    }

	$invoice_id = $_POST['invoice_id'];
	$orderID = $_POST['row_id'];
	$currentTime = date("Y-m-d H:i:s");
	$userEmail = $_POST['email'];
	$userID   = $_POST['user_id'];

    //Get Invoice shipping
    $db->join('tbl_request_order req','req.id=inv.request_id','inner');
    $db->where('inv.id',$invoice_id);
    $getShipping = $db->getOne('tbl_invoice inv','shippingPrice,name,email,address,phone,inv.date as orderDate');

	
	$orderDate 		= $getShipping['orderDate'];
	$customerName 	= $getShipping['name'];
	$customerEmail 	= $getShipping['email'];
	$customerPhone 	= $getShipping['phone'];
	$customerAddress= $getShipping['address'];
	
	
	// Get user info
	$db->where('id',$userID);
	$getUser = $db->getOne('tbl_user_register');
	
	if(empty($getUser)) die('Invalid user');
	
	//Get order detail
	$db->join('tbl_user_admin a','a.user_id=i.product_owner','left');
	$db->join('tbl_products p','p.id=i.product_id','left');
	$db->where('invoice_id',$invoice_id);
	$getInvDetail = $db->get('tbl_invoice_detail i',null,'i.product_owner as i_pro_owner,
	i.member_price as i_mem_price, i.unit_price as i_unit_price, i.quantity as i_qty, code, title, a.email as ownerEmail, retail_price');
	
	
	$totalMemberPrice  = 0;
	$totalRetailPrice  = 0;
	$totalEarning      = 0;
    $currentBalance    = 0;
    $shippingPrice     = 0;

    $userWalletID = 0;
    $partnerList = [];
	$partner_product = [];

    if($getShipping['shippingPrice'] > 0){
        $shippingPrice = $getShipping['shippingPrice'];
    }
    //End get shipping price

	foreach($getInvDetail as $ind){
		
		$totalMemberPrice += ($ind['i_mem_price'] * $ind['i_qty']);		
		$totalRetailPrice += ($ind['retail_price'] * $ind['i_qty']);
		
		//partner product
		$newpro_id = [];
		
		if(isset($partner_product[$ind['i_pro_owner']])) {
			$pre_id = $partner_product[$ind['i_pro_owner']];
			foreach($pre_id as $pid){
				$newpro_id[] = $pid;
			}
		}
		
		//add new pid
		$newpro_id[] = $ind;
		$partner_product[$ind['i_pro_owner']] = $newpro_id;
        
        if(!in_array($ind['i_pro_owner'],$adminIDList)){
            
            if(isset($partnerList[$ind['i_pro_owner']])){
                
                $partnerList[$ind['i_pro_owner']] += ($ind['i_mem_price'] * $ind['i_qty']);                 
            }else{
                
                $partnerList[$ind['i_pro_owner']] = ($ind['i_mem_price'] * $ind['i_qty']);   
                
            }                
        }
		
	}

    $totalEarning = $totalRetailPrice - $totalMemberPrice;
    
    //Get last current balance
    $db->where('wauUserID',$getUser['id']);
    $getUserWallet = $db->getOne('tbl_user_wallet');
    
    $db->startTransaction();
    
    //Deposite amount to user wallet
    if(!empty($getUserWallet)){
        
        $userWalletID = $getUserWallet['wauID'];
        $currentBalance = $getUserWallet['wauBalance'];
        $currentBalance += $totalEarning;
        
        $dataUpdate = array(
            'wauBalance'    => $currentBalance
        );

        $db->where('wauID',$userWalletID);
        if($db->update('tbl_user_wallet',$dataUpdate)){
            $db->commit();
        }else{
            $db->rollback();
			$hasError = true;
        }
        
    }else{
        
        $userWalletID = getMaxID($db,'tbl_user_wallet','wauID');
        $currentBalance = $totalEarning;
        $dataInsert = array(
            'wauID'     => $userWalletID,
            'wauUserID' => $getUser['id'],
            'wauBalance'=> $currentBalance
        );
        
        if($db->insert('tbl_user_wallet',$dataInsert)){
            $db->commit();
        }else{
            $db->rollback();
			$hasError = true;
        }
    }
    //end of insert wallet


	$transactionType = 1;
	
	$transactionComment = 'Profit form invoice #'.$invoice_id;
	if($totalEarning < 0){
		$transactionType = 0;
		$transactionComment = 'Withdraw balance on invoice #'.$invoice_id;
		$totalEarning = abs($totalEarning);
	}
	
	if($totalEarning != 0){
        //start insert wallet detail        
        $db->startTransaction();
        
		$primaryKey = getMaxID($db,'tbl_user_wallet_detail','uwaID');
		$dataInsert = array(
			'uwaID'			=> $primaryKey,
			'uwa_wauID'		=> $userWalletID,
			'uwaInvoiceID'	=> $invoice_id,
			'uwaAmount'		=> $totalEarning,
			'uwaTranType'	=> $transactionType,
			'uwaComment'	=> $transactionComment
		);
        
        //  Walllet transaction type (walTranType) : 1 is deposit amount, 0 is widraw amount
        $hasError = false;
		if($db->insert('tbl_user_wallet_detail',$dataInsert)){
    
			//create notification
			$dataNotification = array(
				'uno_usrID'	=> $userID,
				'unoTitle'	=> 'Received profit',
				'unoDetail'	=> 'You earned $'.$totalEarning.' on your order #'.str_pad($invoice_id,7,0,STR_PAD_LEFT),
				'unoType'	=> 1,
				'unoRead'	=> 0,
				'unoURL'	=> '{\'id\': \'wallet_tran_detail_page\', \'title\': '.$primaryKey.'}',
				'unoIcon'	=> 'noti_icon_income'
			);
			
			if(!$db->insert('tbl_user_notifications',$dataNotification)){
				$hasError = true;
			}

        }else{

            $hasError = true;
			
        }
	}
	 //End of user wallet transaction

    //Shipping Price
    if($shippingPrice != 0){
        $primaryKey = getMaxID($db,'tbl_user_wallet_detail','uwaID');
        $dataShipping = array(
            'uwaID'			=> $primaryKey,
			'uwa_wauID'		=> $userWalletID,
			'uwaInvoiceID'	=> $invoice_id,
			'uwaAmount'		=> $shippingPrice,
			'uwaTranType'	=> 0,
			'uwaComment'	=> 'Shipping Costs'
        );

        if(!$db->insert('tbl_user_wallet_detail',$dataShipping)){
			$hasError = true;
		}else{
			$currentBalance -= $shippingPrice;

			//create notification
			$dataNotification = array(
				'uno_usrID'	=> $userID,
				'unoTitle'	=> 'Shipping fee',
				'unoDetail'	=> 'You have shipping charge $'.$shippingPrice.' on your order #'.str_pad($invoice_id,7,0,STR_PAD_LEFT),
				'unoType'	=> 1,
				'unoRead'	=> 0,
				'unoURL'	=> '{\'id\': \'wallet_tran_detail_page\', \'title\': '.$primaryKey.'}',
				'unoIcon'	=> 'noti_icon_outtran'
			);
			
			if(!$db->insert('tbl_user_notifications',$dataNotification)){
				$hasError = true;
			}

			//update wallet balance
			$dataUpdate = array(
				'wauBalance'   => $currentBalance
			);
			$db->where('wauID',$userWalletID);
			if(!$db->update('tbl_user_wallet',$dataUpdate)){
				$hasError = true;
			}
		}
    }


	if(!$hasError){
		$db->commit();
	}else{
		$db->rollback();
	}

    //Partner Earning
    if(!empty($partnerList)){

        foreach($partnerList as $k=>$profit){

            if($profit > 0){
                
                //Get last current balance
                $db->where('wapPartnerID',$k);
                $getPartnerWallet = $db->getOne('tbl_partner_wallet');
                
                $db->startTransaction();
                if(!empty($getPartnerWallet)){//update current wallet
                    
                    $pertnerWalletID = $getPartnerWallet['wapID'];
                
                    $currentBalance = $getPartnerWallet['wapBalance'];
            
                    $currentBalance += $profit;
                    
                    $dataUpdate = array(
                        'wapBalance' => $currentBalance
                    );
                    
                    $db->where('wapID',$pertnerWalletID);
                   if( $db->update('tbl_partner_wallet',$dataUpdate)){
                       $db->commit();
                   }else{
                       $db->rollback();
                   }
                }else{ //create new wallet
                    $pertnerWalletID = getMaxID($db,'tbl_partner_wallet','wapID');
                    $dataInsert = array(
                        'wapID'         => $pertnerWalletID,
                        'wapPartnerID'  => $k,
                        'wapBalance'    => $profit
                    );
                    
                     if( $db->insert('tbl_partner_wallet',$dataInsert)){
                        $db->commit();
                     }else{
                        $db->rollback();
                     }
                }
                //Deposite amount to partner wallet

                $primaryKey = getMaxID($db,'tbl_partner_wallet_detail','pwaID');
                $dataInsert = array(
                    'pwaID'			=> $primaryKey,
                    'pwa_wapID'     => $pertnerWalletID,
                    'pwaInvoiceID'	=> $invoice_id,
                    'pwaAmount'		=> $profit,
                    'pwaTranType'	=> 1,
                    'pwaComment'	=> 'Deposit amount'
                );
                
                $db->insert('tbl_partner_wallet_detail',$dataInsert);
                
            }
                

        }// loop partner list
   
    }
	
	//send email to partner on order thire product
	if(!empty($partner_product)){
		
		foreach($partner_product as $k=>$p_product){
			
			$productInvoiceList = '';
			$totalQty = 0;
			$totalCost = 0;
			$totalDeposit = 0;
			$partnerEmail = '';
			$partnerID = $k;
			$current_partner_product =  $p_product;
			
			foreach($current_partner_product as $pro){
				
				$productInvoiceList .= '
					<tr style="color:#000000; font-family:Century Gothic; font-size:14px; line-height:30px">
						<td align="center">'.$pro['code'].'</td>
						<td>'.limitText_return($pro['title'],25).'</td>
						<td align="center">'.$pro['i_qty'].'</td>
						<td align="center">'.$pro['i_unit_price'].'</td>
						<td align="center">'.$pro['i_mem_price'].'</td>
					</tr>
				';
				
				$partnerEmail = $pro['ownerEmail'];
				$totalQty += $pro['i_qty'];
				$totalCost += ($pro['i_unit_price'] * $pro['i_qty']);
				$totalDeposit += ($pro['i_mem_price'] * $pro['i_qty']);
				
			}
			
			//send invoice
			$body_msg = '
				<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
				
				<title>Bosdom.net</title>
				</head>
				<body bgcolor="#fff" >
				<table style="max-width:790px; min-width:600px; width:100%; margin:auto;" height="200" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff" >
				  <tr>
					<td><table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="center" style="border-radius:10px; border:1px solid #ddd; box-shadow: 0px 1px 10px 4px rgba(0,0,0,0.10)">
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
							<td align="center"><h3 style="font-family:Cambria,Hanuman; font-size:19px; color: #119aff">Congratulations!</h3></td>
						</tr>

						<tr>
						  <td><table width="90%" border="0" cellspacing="0" cellpadding="0">
							  <tr>
								<td width="5%">&nbsp;</td>
								<td width="80%" align="left" valign="top">
									<font style="font-family: Century Gothic; color:#666766; font-size:13px; line-height:21px">
										<p style="width:90%; margin:auto;">
											Dear Partner,<br><br>
											A customer placed a new order of your product on <a href="http://www.bosdom.net" target="_blank">www.bosdom.net</a> <br> on date '.date("d-M-Y h:s a",strtotime($orderDate)).'. <br><br>
											<b>Below is the information ordered:</b>
										</p>
									</font>
									<br>
									<font style="font-family: Century Gothic; color:#666766; font-size:13px; line-height:21px">
										<p style="width:90%; margin:auto;">
											Order ID: <b>'.str_pad($invoice_id, 7, '0', STR_PAD_LEFT).'</b><br>
											Customer Name: <b>'.$customerName.'</b><br> 
											Email: <b>'.$customerEmail.'</b><br> 
											Phone: <b>'.$customerPhone.'</b><br> 
											Address: <b>'.$customerAddress.'</b><br> 
										</p>
									</font>
								</td>
							  </tr>
							 
							</table></td>
						</tr>
						<tr>
						  <td>&nbsp;</td>
						</tr>
						<tr>
						  <td>&nbsp;</td>
						</tr>
						<tr style="text-align:center; font-family:Century Gothic; font-size:14px;">
							<td colspan="2">
								<font> Product Detail </font>
							</td>
						</tr>
						<tr>
						  <td>&nbsp;</td>
						</tr>
						<tr width="width:100%;" height="auto">
							<td align="center">
							<table border="0" cellspacing="0" cellpadding="0">
								<thead>
									<tr width="100%" cellspacing="0" cellpadding="0" bgcolor="#f2f2f2" style="color:#000000; font-family:Century Gothic; font-size:14px;">
										<th width="80" height="30">Code</th>
										<th width="180" align="left">Product Name</th>
										<th width="40">Qty</th>
										<th width="100">Cost Price</th>
										<th width="100">Order Price</th>
									</tr>
								</thead>
								<tbody>
									'.$productInvoiceList.'
									<tr style="color:#f77777; font-family:Century Gothic; font-weight:bold; font-size:14px; line-height:30px">
										<td style="border-top:1px solid #ccc"></td>
										<td style="border-top:1px solid #ccc" align="right">Total</td>
										<td style="border-top:1px solid #ccc" align="center">'.$totalQty.'</td>
										<td style="border-top:1px solid #ccc" align="center">$'.$totalCost.'</td>
										<td style="border-top:1px solid #ccc" align="center">$'.$totalDeposit.'</td>
									</tr>
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
						<tr style="width:100%">
						  <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
							  <tr>
								<td width="5%">&nbsp;</td>
								<td width="80%" align="left" valign="top">
									<font style="font-family: Century Gothic; color:#000; font-size:13px; line-height:21px">
										<p style="width:90%; margin:auto;">
											<span style="color:#f00">*</span><i> The amount that will be deposited to your wallet is </i>: <b style="color:#0cda86; font-size:20px">$'.$totalDeposit.'</b><br><br>
										</p>
									</font>
								</td>
							  </tr>
							  <tr><td>&nbsp;</td></tr>
							  <tr>
								<td width="5%">&nbsp;</td>
								<td width="80%" align="left" valign="top">
									<font style="font-family: Century Gothic; color:#666766; font-size:13px; line-height:21px">
										<p style="width:90%; margin:auto;">
											Thank you for your cooperation!
										</p>
									</font>
									<br><br>
									<font style="font-family: Century Gothic; color:#666766; font-size:13px; line-height:21px">
										<p style="width:90%; margin:auto;">
											Best Regards,<br><b>Bosdom.net Team</b>
										</p>
									</font>
								</td>
							  </tr>
							</table></td>
						</tr>
						<tr>
						  <td>&nbsp;</td>
						</tr>
						<tr>
						  <td><hr style="width:90%; height:1px; background:#ddd; border:none;"></td>
						</tr>
						<tr>
						  <td>&nbsp;</td>
						</tr>

						<tr>
						  <td align="center"><font style="font-family:Myriad Pro, Helvetica, Arial, sans-serif; color:#231f20; font-size:12px"><strong><a href="'.BASE_URL.'">Bosdom.net</a> &copy; '.date('Y').' </strong></font></td>
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
			$to =  $partnerEmail;
			$subject = 'Bosdom Profit';
			
			if(!send_mail($to, 'noreply@bosdom.net', $subject, $body_msg)){
				echo 'Send Mail Unsuccess!';
			}
			
		}
		
	}
	
/**** IN GOD WE TRUST ****/
?>
