<?php
/****** CITA ******
 * CODING: HCK0011 / 2019-12-25
 * Description: Bosdom upload wallet transfer images
 */ 

session_start();
include_once '_config_inc.php';
include_once BASE_PATH.'_libs/site_class.php';
$db = new gen_class($configs);


    /* Private Function for this page */
function getPrimaryKey($db){
    $result = $db->getOne("tbl_products","MAX(`proID`) AS id");
    if(empty($result['id'])){
        $result['id'] = 0;
    }
    return ((int)$result['id'] + 1);
}

 $hasError = false;
 $status = '';
 $image_name = '';
 
 if(isset($_FILES) && isset($_POST['tranID']) && isset($_SESSION['wallet_transfer']) && isset($_POST['token']) && ($_POST['token'] == $_SESSION['wallet_transfer'])){

    $tranID  = $db->filter($_POST['tranID']);

    $uploadDir = BASE_PATH.'files/wallet_transfer';

    $allowed =  array('gif','png' ,'jpg','PNG');

    if (!empty($_FILES)) {
        $tmpFile = $_FILES['file']['tmp_name'];
		
        $image_name = $tranID.'_'. $_FILES['file']['name'];

        $image_name = str_replace(',','',$image_name);

        $ext = pathinfo($image_name, PATHINFO_EXTENSION);
		
		//$image_name = 'wi_'.strtotime(date('d-m-Y h:i:s')).'.'.$ext;
		
		// $file = explode('.', $_FILES['file']['name']);
		// $ext  = end($file);
		// $image_name       = 'wi_'.strtotime(date('d-m-Y h:i:s')).'.'.$ext;
		

        if(in_array($ext,$allowed) ) {
            $filename = $uploadDir.'/'.$image_name;
            if(move_uploaded_file($tmpFile,$filename)){
			
				$db->where('uwaEntryNo',$tranID);
				$getWalletDetail = $db->getOne('tbl_user_wallet_detail');
				
				$currentImageList = [];
				$newImageList = '';
				if(!empty($getWalletDetail)){
					
					if(!empty($getWalletDetail['uwaImage'])){
						$currentImageList = explode(',',$getWalletDetail['uwaImage']);
					}
					
					$currentImageList[] = $image_name;
					$newImageList = implode(',',$currentImageList);
					
					//$idUpdate = $getWalletDetail['uwa_wauID'];
					
					$dataUpdateTransfer = array(
						'uwtImage'	=> $newImageList
					);
					
					//update transaction wallet image
					$db->where('uwtID',$tranID);
					if(!$db->update('tbl_user_wallet_transfer',$dataUpdateTransfer)){
						$hasError = true;
						$status = 'Unable to save file image.';
					}
					
					//update wallet detail image
					$dataUpdateWallet = array(
						'uwaImage'	=> $newImageList
					);
					//update transaction wallet image
					$db->where('uwaEntryType',1);
					$db->where('uwaEntryNo',$tranID);
					if(!$db->update('tbl_user_wallet_detail',$dataUpdateWallet)){
						$hasError = true;
						$status = 'Unable to save file image.';
					}
				}else{
					$hasError = true;
					$status = 'Invalid transaction id.';
				}
            }else{
				$hasError = true;
				$status = 'Unable to upload file image.';
			}
        }else{
            $hasError = true;
            $status = 'Unsupported file image.';
        }
    }

    //store image uploaded on session to preparing data

    $jsonData = array(
        'hasError'   => $hasError,
        'status'     => $status
    );

    echo json_encode($jsonData);

 }else{

    die('Bad access.');

 }


   /**** IN GOD WE TRUST ****/
?>