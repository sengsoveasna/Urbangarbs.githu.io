<?php
/****** CITA ******
 * CODING: HCK0011 / 2019-01-16
 * Description: Send request notify admin to withdraw wallet
 */ 
 session_start();
include_once '_config_inc.php';
include_once BASE_PATH.'_libs/site_class.php';
$db = new gen_class($configs);

$db = new gen_class($configs);

$status = true;
$hasError = false;

 
 if(isset($_SESSION['user_id']) && isset($_SESSION['wallet_transfer']) && isset($_POST['token']) && ($_SESSION['wallet_transfer'] == $_POST['token']) && isset($_POST['request_amount']) && isset($_POST['note'])){
	 
	$request_amount = $db->filter($_POST['request_amount']);
	$note = $db->filter($_POST['note']);

	//get wallet info 
	$user_id = $_SESSION['user_id'];
	
	$db->where('wauUserID',$user_id);
	$getWallet = $db->getOne('tbl_user_wallet');
	
	//find if user is stil in pending request
	$db->where('uwrUserID',$user_id);
	$db->where('uwrStatus',1);
	$getRequested = $db->getOne('tbl_user_wallet_request');
		
	if(empty($getWallet)){
		$hasError = true;
		$status = 'Invalid wallet ID.';
	}else if (!empty($getRequested)){
		$hasError = true;
		$status = 'You are still in pending request.';
	}else if($request_amount == '' || !is_numeric($request_amount)){
		$hasError = true;
		$status = 'Invalid request input.';
	}else if($getWallet['wauBalance'] < $request_amount){
		$hasError = true;
		$status = 'Insufficient balance.';
	}else{
		
		$walletNotify = array(
			'wauNotify'	=> 1
		);
		
		$db->where('wauUserID',$user_id);
		$db->update('tbl_user_wallet',$walletNotify);
		
		
		$dataInsert = array(
			'uwrID_wauID'	=> $getWallet['wauID'],
			'uwrUserID'		=> $user_id,
			'uwrAmount'		=> $request_amount,
			'uwrNote'		=> $note
		);
		
		$db->insert('tbl_user_wallet_request',$dataInsert);
		
	}
	
	
 }else{
	$hasError = true;
	$status = 'Bad request.';
 }
 
 $jsonData = array(
	'hasError'	=> $hasError,
	'status'	=> $status
 );
 
 
 echo json_encode($jsonData);