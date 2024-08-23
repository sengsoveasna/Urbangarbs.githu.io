<?php
/****** CITA ******
 * CODING: HCK0011 / 2019-01-16
 * Description: Send request notify admin to withdraw wallet
 */ 
include_once '_config_inc.php';
include_once BASE_PATH.'_libs/site_class.php';
$db = new gen_class($configs);

$db = new gen_class($configs);

$hasError = false;
 
 if($_POST['walletID']){
	 
	$walletID = $_POST['walletID'];
	
	$dateUpdate = array(
		"wapNotify"=>1
	);
	$db->where('wapNotify',0);
	$db->where('wapBalance',10,'>=');
	$db->where('wapID',$walletID);
	if(!$db->update('tbl_partner_wallet',$dateUpdate,false)){
		$hasError = true;
	}
 }
 
 echo json_encode(array("hasError"=>$hasError));