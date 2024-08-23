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
	$request_id = isset($_POST['request_id']) ? $_POST['request_id'] : '' ;
	$invoice_id = isset($_POST['invoice_id']) ? $_POST['invoice_id'] : '' ;
	$getNewShippingPrice = isset($_POST['shippingPrice']) ? $_POST['shippingPrice'] : '';
    $userEmail = isset($_POST['email']) ? $_POST['email'] : '';

    //Get user info 
    $db->where('email',$userEmail);
    $db->where('status',1);
    $getUserInfo = $db->getOne('tbl_user_register');

    //get shipping order detail
    $db->where('id',$request_id);
    $db->where('status',1);
    $getOrderDetail = $db->getOne('tbl_request_order');

    $shippingPrice = 0;
    $totalWallet = 0;

    if(!empty($getOrderDetail) && $getNewShippingPrice != '' && !empty($getUserInfo)){

        //Get wallet detail
        $db->where('wauUserID',$getUserInfo['id']);
        $getWalletInfo = $db->getOne('tbl_user_wallet');

        if(!empty($getWalletInfo)){
            $userWalletID = $getWalletInfo['wauID'];
            $totalWallet = $getWalletInfo['wauBalance'];
        }else{
            $userWalletID = getMaxID($db,'tbl_user_wallet','wauID');
            $dataInsert = array(
                'wauID'     => $userWalletID,
                'wauUserID' => $getUserInfo['id'],
                'wauBalance'=> $totalWallet
            );
            
           $db->insert('tbl_user_wallet',$dataInsert);
        }
        
        $getOldShippingPrice = $getOrderDetail['shippingPrice'];
        $sign = '';
		
        if($getNewShippingPrice > $getOldShippingPrice){
            //Withdraw
            $sign = '-';
            $shippingPrice = ($getNewShippingPrice - $getOldShippingPrice);

            $primaryKey = getMaxID($db,'tbl_user_wallet_detail','uwaID');
            $dataShipping = array(
                'uwaID'			=> $primaryKey,
                'uwa_wauID'		=> $userWalletID,
                'uwaInvoiceID'	=> $invoice_id,
                'uwaAmount'		=> $shippingPrice,
                'uwaTranType'	=> 0,
                'uwaComment'	=> 'Shipping Costs'
            );
			
			if($shippingPrice > 0){
			    if($db->insert('tbl_user_wallet_detail',$dataShipping)){
				    $totalWallet -= $shippingPrice;
			    }
			}
        }else{
            //Deposit
            $shippingPrice = ($getOldShippingPrice - $getNewShippingPrice);

            $primaryKey = getMaxID($db,'tbl_user_wallet_detail','uwaID');
            $dataShipping = array(
                'uwaID'			=> $primaryKey,
                'uwa_wauID'		=> $userWalletID,
                'uwaInvoiceID'	=> $invoice_id,
                'uwaAmount'		=> $shippingPrice,
                'uwaTranType'	=> 1,
                'uwaComment'	=> 'Deposit Shipping'
            );
			if($shippingPrice > 0){
				if($db->insert('tbl_user_wallet_detail',$dataShipping)){
					$totalWallet += $shippingPrice;
				}
			}
        }

        //Update User wallet balance
        $dataUpdate = array(
            'wauBalance'   => $totalWallet
        );
		
		if($shippingPrice > 0){
			$db->where('wauID',$userWalletID);
			$db->update('tbl_user_wallet',$dataUpdate);
		}
    }

	
/**** IN GOD WE TRUST ****/
?>
