<?php
/****** CITA ******
 * CODING: HCK0011 / 2017-01-30
 * Description: Send request notify admin to withdraw score money
 */ 
include_once '_config_inc.php';
include_once BASE_PATH.'_libs/site_class.php';
$db = new gen_class($configs);

$db = new gen_class($configs);
 if($_POST['withdraw-request']){
	$request_id = $_POST['request_id'];
	
	$dateUpdate = array(
		"notify"=>1
	);
	
	$db->where('id',$request_id);
	$update = $db->update('tbl_score_money',$dateUpdate,false);
	
 }