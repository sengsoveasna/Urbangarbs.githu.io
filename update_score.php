<?php
/****** CITA ******
 * CODING: HCK0011 / 2017-01-19
 * Description: Take score from products,  , add score to user and manager (admin)
 * Update : 2019-06-04
 */
 if(empty($_POST['user_id'])){
	 die("Invalid user id");
 }

 $user_id = $_POST['user_id'];

 $email = $_POST['email'];
 $invoice_id = $_POST['invoice_id'];
 $date = new DateTime();
 $currentTime = $date->format("Y-m-d H:i:s");

 $hasError = false;

 // Get Id of user requested order
	$db->where('id',$user_id );
	$getCurrentUser = $db->getOne('tbl_user_register');
	$currentUserID = $getCurrentUser['id'];
	$getFirstUser=null;
	$getSecondUser=null;

    // Find Referal User
	$db->where('register_id',$currentUserID);
	$getFirstUser = $db->getOne('tbl_referal');

	if($getFirstUser!=null){
		$db->where('register_id',$getFirstUser['refer_by_id']);
		$getSecondUser = $db->getOne('tbl_referal');
	}

	if($getSecondUser!=null){
		$db->where('register_id',$getSecondUser['refer_by_id']);
		$getThirdUser = $db->getOne('tbl_referal');
	}

 // Get Invoice Detail and product Detail
	$getSelected = 'P.id as pro_id, P.member_price as pro_member_price, P.in_stock as pro_in_stock,
					I.quantity as pro_quantity, P.ordered as pro_ordered,
					P.multi_score as pro_multi_score, P.manual_score as pro_manual_score, 
					P.is_manual_score as pro_is_manual_score, I.product_sku_id as skuID';

	$db->join('tbl_invoice_detail I','P.id=I.product_id','inner');
	$db->where('I.invoice_id',$invoice_id);
	$getProducts = $db->get('tbl_products P',null,$getSelected);
	$itemCount = count($getProducts);
	$newPayment=0;
	$currentProductStock = 0;
	$productCount = 0;
	$i=0;

	$multiScore=0;
	$adminScore=0;
	$firstScore=0;
	$secondScore=0;
	$thirdScore=0;
	$forthScore=0;
	$manual_score_list = '';
   /*
    ***Manual score means that admin assigned score to the product for custome score for each users.
        Example : When customer buy a product scores will applied to:
            - Admin         : 2
            - First User    : 2
            - Second User   : 4
            - Third User    : 4
            - Forth user    : 6
                ***Score above were set by admin wants
        => Total this product will spends 18 scores per 1qty every purchasing.

    For Opposits Default Score means that for each product we'll assign 5 scores for users but different giving to user by deppend on level.
        Example : When customer buy a product scores will applied to:
            if First User buy:
                - Admin         : 1
                - First User    : 4

            if Second User buy:
                - Admin         : 1
                - First User    : 1
                - Second User   : 3

            if Third User buy:
                - Admin         : 1
                - First User    : 1
                - Second User   : 1
                - Third User    : 2

            if Forth User buy:
                - Admin         : 1
                - First User    : 1
                - Second User   : 1
                - Third User    : 1
                - Forth user    : 1

        => Total this product will spends 5 scores per 1qty every purchasing.
    */
	
	$db->startTransaction();

	foreach($getProducts as $product){

		$multiScore = $product['pro_multi_score'];
		$manual_score = $product['pro_manual_score'];

		if($product['pro_in_stock']<$product['pro_quantity']){
			//if product out of stock, cancel all processes
			echo 'Product Out of Stock';
			$data = array(
				'status'=>0
			);
			$db->where('id',$_POST['row_id']);
			$db->update('tbl_request_order',$data,false);
			exit();
            //if product out of stock all transaction will denied.
		}else{
			$data = array(
				'status'=>1,
				'date'	=> $currentTime
			);
			$db->where('id',$invoice_id);
			if($db->update('tbl_invoice',$data,false)){
				//add notification
				$dataNotification = array(
					'uno_usrID'	=> $user_id,
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
		}

		//Total Payment per invoice reciept
		$newPayment =$newPayment+($product['pro_member_price']*$product['pro_quantity']);
		
		//get product stock after update stock in loop
		$db->where('id',$product['pro_id']);
		$getStock = $db->getOne('tbl_products','in_stock');

		$ProductStock = $getStock['in_stock'] - $product['pro_quantity']; //update quantity in stock

 		$ProductOrdered = $product['pro_ordered'] + 1; // get last order from join table
		$dataStock = Array(
			"in_stock" 	=> $ProductStock,
			"ordered" 	=> $ProductOrdered
		);

		$db->where('id',$product['pro_id']);
		if(!$db->update('tbl_products',$dataStock)){
			$hasError = true;
		}
		
		//update sku product
		if(!empty($product['skuID'])){
			$db->where('skuValue',$product['skuID']);
			$getSKU = $db->getOne('tbl_sku');
			if(!empty($getSKU)){
				$currentSKUStock = $getSKU['skuInStock'];
				$currentProductStock =  $currentSKUStock - $product['pro_quantity'];

				//start update stock
				$dataSKU = array(
					'skuInStock' => $currentProductStock
				);
				
				$db->where('skuValue',$product['skuID']);
				if(!$db->update('tbl_sku',$dataSKU)){
					$hasError = true;
				}
			}
		}else{
			$hasError = true;
		}

		if($product['pro_is_manual_score']==1){
			$manual_score_list = explode(',',$manual_score);
			$adminScore 	= $adminScore 	+ ($manual_score_list[0] * $product['pro_quantity']);
			$firstScore		= $firstScore 	+ ($manual_score_list[1] * $product['pro_quantity']);
			$secondScore	= $secondScore 	+ ($manual_score_list[2] * $product['pro_quantity']);
			$thirdScore		= $thirdScore 	+ ($manual_score_list[3] * $product['pro_quantity']);
			$forthScore		= $forthScore 	+ ($manual_score_list[4] * $product['pro_quantity']);
		}else{
			if($i<$itemCount){
				$productCount = $productCount + $product['pro_quantity'];
				$i++;
			}
		}
	}
	
	//function get Id from table score
	function getadminScoreId($db){
		$result = $db->getOne("tbl_admin_score","MAX(`id`) AS id");
		if(empty($result['id'])){
			$result['id'] = 0;
		}
		return ((int)$result['id'] + 1);
	}

	function addAdminScore($db,$productCount,$adminScore){
		
		$currentTime = date("Y-m-d H:i:s");
		$adminscoreId = getadminScoreId($db);
		$y=0;
		$getAdminScore = $db->get('tbl_admin_score');

		foreach($getAdminScore as $admin){
			$LastDate = strtotime(date("Y-m",strtotime($admin['date'])));
			$Now = strtotime(date("Y-m"));
			if($LastDate < $Now) $y=1; else $y=0;
		}
		if(empty($getAdminScore) || $y==1){ //Add new month score record
		
			$dataInsert = Array(
				"id" => $adminscoreId,
				"score"=> $productCount + $adminScore,
				"date"=> $currentTime,
				"status"=>'1'
			);
			return $db->insert('tbl_admin_score',$dataInsert);

		}else{ //Update current score record

			// Get Current Admin Score
			$db->where("(SELECT MONTH(`date`) = MONTH(NOW()))");
			$db->where("(SELECT YEAR(`date`) = YEAR(NOW()))");
			$getCurrentAdminScore = $db->getOne('tbl_admin_score');

			$totalAdminScore = $getCurrentAdminScore['score'] + $productCount + $adminScore;
			$dataUpdate = Array(
				"score"=> $totalAdminScore,
				"date"=> $currentTime
			);
			$db->where('id',$getCurrentAdminScore['id']);
			return $db->update('tbl_admin_score',$dataUpdate);
		}
	}//End function adminScore

	function getCurrentUser($db,$userId,$field){
		$db->where('user_id',$userId);
		$db->where("(SELECT MONTH(`date`) = MONTH(NOW()))");
		$db->where("(SELECT YEAR(`date`) = YEAR(NOW()))");
		$getPaymentInfo = $db->getOne('tbl_score_money');
		return $getPaymentInfo[$field];
	}

	//function get Id from table score
	function getUserScoreId($db){
		$result = $db->getOne("tbl_score_money","MAX(`id`) AS id");
		if(empty($result['id'])){
			$result['id'] = 0;
		}
		return ((int)$result['id'] + 1);
	}

    function setCurrentUserScore($db,$id,$userId,$score,$payment){
        $currentTime = date("Y-m-d H:i:s");
        $status = 0;
        // Checking for date and month to instert
        $db->where('user_id',$userId);
		$getUserScore = $db->get('tbl_score_money');
		$i=0;
		foreach($getUserScore as $UserScore){
			$LastDate = strtotime(date("Y-m",strtotime($UserScore['date'])));
			$Now = strtotime(date("Y-m"));
			if($LastDate < $Now){
				$i=1;
			}else{
				$i=0;
			}
		}
		
        if($i==1){//if It's new month insert new score record
    			if($payment >= 50){
    				$status = 1;
    			}else{
    				$status = 0;
    			}
    		$dataInsert = Array(
    			"id"=>$id,
    			"user_id"=>$userId,
    			"score"=>$score,
    			"payment"=>$payment,
    			"date"=>$currentTime,
    			"status"=>$status,
    			"notify"=>0
    		);
			
    		return $db->insert('tbl_score_money',$dataInsert);
        }else{//if It's current month update score record
    		if($payment >= 50){
    			$status = 1;
    		}else{
    			$status = 0;
    		}
    		$dataUpdate = Array(
    			"score"=>$score,
    			"payment"=>$payment,
    			"date"=>$currentTime,
    			"status" => $status
    		);
    		$db->where('user_id',$userId);
    		$db->where("(SELECT MONTH(`date`) = MONTH(NOW()))");
    		$db->where("(SELECT YEAR(`date`) = YEAR(NOW()))");
    		return $db->update('tbl_score_money',$dataUpdate);
        }
    }

    function setRefferalScore($db,$id,$userId,$score,$payment){
        $currentTime = date("Y-m-d H:i:s");
        $status = 0;
        // Checking for date and month to instert
        $db->where('user_id',$userId);
        $getUserScore = $db->get('tbl_score_money');
        $i=0;
        foreach($getUserScore as $UserScore){
            $LastDate = strtotime(date("Y-m",strtotime($UserScore['date'])));
            $Now = strtotime(date("Y-m"));
            if($LastDate < $Now){
                $i=1;
            }else{
                $i=0;
            }
        }
        if($i==1){//if It's new month insert new score record
                if($payment >= 50){ //if user pay above 50 dollars so user is active=1
                    $status = 1;
                }else{
                    $status = 0;
                }
            $dataInsert = Array(
                "id"=>$id,
                "user_id"=>$userId,
                "score"=>$score,
                "payment"=>$payment,
                "date"=>$currentTime,
                "status"=>$status,
                "notify"=>0
            );
            return $db->insert('tbl_score_money',$dataInsert);
        }else{//if It's current month update score record
            $dataUpdate = Array(
    			"score"=>$score,
    			"date"=>$currentTime
    		);
    		$db->where("(SELECT MONTH(`date`) = MONTH(NOW()))");
    		$db->where("(SELECT YEAR(`date`) = YEAR(NOW()))");
    		$db->where('user_id',$userId);
    		return $db->update('tbl_score_money',$dataUpdate);
        }
    }

	//function get Id from table score log
	function getScoreLogId($db){
		$result = $db->getOne("tbl_score_logs","MAX(`id`) AS id");
		if(empty($result['id'])){
			$result['id'] = 0;
		}
		return ((int)$result['id'] + 1);
	}

	//Insert every main referal(parent) to track score
	function InserScoreLogs($db,$id,$child_id,$parent_id,$score,$ivid){
		$currentTime = date("Y-m-d H:i:s");
		$dataInsert = Array(
			"id"=>$id,
			"child_id"=>$child_id,
			"parent_id"=>$parent_id,
			"invoice_id"=>$ivid,
			"score"=>$score,
			"date"=>$currentTime
		);
		return $db->insert('tbl_score_logs',$dataInsert);
	}

    $currentUserScore = 0;
    $currentUserPayment = 0;
    $currentReferalScore = 0;

    /*
    Note for score calcuation:
        *Tip: You may understand what is Manual Score first! See line : 50
        -> $productCount : has default value is Zero, and it will contain value when product is NOT SET manual score
          Example : $totalScore = $currentUserScore + (($productCount * 4) + $firstScore);
                if product is not set manual score so :
                    $totalScore = $currentUserScore + (($productCount * 4) + 0);
                    **$firstScore is Zero;

                if product is set manual score so :
                    $totalScore = $currentUserScore + ((0) + $firstScore);
                    **$productCount is Zero;

        -> $adminScore & $firstScore & $secondScore & $thirdScore & $forthScore : has default value is Zero, and it will contain value when product is SET manual score
    */

	if(empty($getFirstUser)){
		
		//echo 'first user , No user referal';
		$currentUserScore = getCurrentUser($db,$currentUserID,'score');
		$currentUserPayment = getCurrentUser($db,$currentUserID,'payment');
		$totalScore = $currentUserScore + (($productCount * 4) + $firstScore);
		$totalPayment = $currentUserPayment + $newPayment;

		$scoreId = getUserScoreId($db);
		if(!setCurrentUserScore($db,$scoreId,$currentUserID,$totalScore,$totalPayment)){
			$hasError = true;
		}

	}elseif(!empty($getFirstUser) && empty($getSecondUser)){
		//echo 'second user, has 1 main referal';
        //for Current user
        $currentUserScore = getCurrentUser($db,$currentUserID,'score');
        $currentUserPayment = getCurrentUser($db,$currentUserID,'payment');
        $totalScore = $currentUserScore + (($productCount*3) + $secondScore);
        $totalPayment = $currentUserPayment + $newPayment;
        $scoreId1 = getUserScoreId($db);
        if(!setCurrentUserScore($db,$scoreId1,$currentUserID,$totalScore,$totalPayment)){
			$hasError = true;
		}

        //for Referal user
        $currentReferalScore = getCurrentUser($db,$getFirstUser['refer_by_id'],'score');
        $TotalReferalScore = $currentReferalScore + (($productCount *1) + $firstScore);
        $scoreId2 = getUserScoreId($db);
        if(!setRefferalScore($db,$scoreId2,$getFirstUser['refer_by_id'],$TotalReferalScore,'0')){
			$hasError = true;
		}

		//Track all insert score to main referal
		$logId = getScoreLogId($db);
		InserScoreLogs($db,$logId,$currentUserID,$getFirstUser['refer_by_id'],($productCount *1) + $firstScore, $invoice_id);
	}elseif(!empty($getSecondUser) && empty($getThirdUser)){
		//echo 'Third User, has 2 main referal';
        // for current user
        $currentUserScore = getCurrentUser($db,$currentUserID,'score');
        $currentUserPayment = getCurrentUser($db,$currentUserID,'payment');
        $totalScore = $currentUserScore + (($productCount * 2) + $thirdScore);
        $totalPayment = $currentUserPayment + $newPayment;
        $scoreId1 = getUserScoreId($db);
        if(!setCurrentUserScore($db,$scoreId1,$currentUserID,$totalScore,$totalPayment)){
			$hasError = true;
		}

        // for first referal user
        $currentReferalScore = getCurrentUser($db,$getFirstUser['refer_by_id'],'score');
        $TotalReferalScore = $currentReferalScore + (($productCount * 1) + $secondScore);
        $scoreId2 = getUserScoreId($db);
        if(!setRefferalScore($db,$scoreId2,$getFirstUser['refer_by_id'],$TotalReferalScore,'0')){
			$hasError = true;
		}

        //for second referal user
        $currentReferalScore = getCurrentUser($db,$getSecondUser['refer_by_id'],'score');
        $TotalReferalScore = $currentReferalScore + (($productCount * 1) + $thirdScore);
        $scoreId3 = getUserScoreId($db);
        if(!setRefferalScore($db,$scoreId3,$getSecondUser['refer_by_id'],$TotalReferalScore,'0')){
			$hasError = true;
		}

		//Track all insert score to main referal
		$logId = getScoreLogId($db);
		InserScoreLogs($db,$logId,$currentUserID,$getFirstUser['refer_by_id'],($productCount *1) + $secondScore, $invoice_id);

		//Track all insert score to main referal
		$logId1 = getScoreLogId($db);
		InserScoreLogs($db,$logId1,$currentUserID,$getSecondUser['refer_by_id'],($productCount *1) + $thirdScore, $invoice_id);
	}else{
        // for current user
        $currentUserScore = getCurrentUser($db,$currentUserID,'score');
        $currentUserPayment = getCurrentUser($db,$currentUserID,'payment');
        $totalScore = $currentUserScore + (($productCount * 1 ) + $forthScore);
        $totalPayment = $currentUserPayment + $newPayment;
        $scoreId1 = getUserScoreId($db);
        if(!setCurrentUserScore($db,$scoreId1,$currentUserID,$totalScore,$totalPayment)){
			$hasError = true;
		}

        // for first referal user
        $currentReferalScore = getCurrentUser($db,$getFirstUser['refer_by_id'],'score');
        $TotalReferalScore = $currentReferalScore + (($productCount * 1) + $thirdScore);
        $scoreId2 = getUserScoreId($db);
        if(!setRefferalScore($db,$scoreId2,$getFirstUser['refer_by_id'],$TotalReferalScore,'0')){
			$hasError = true;
		}

        //for second referal user
        $currentReferalScore = getCurrentUser($db,$getSecondUser['refer_by_id'],'score');
        $TotalReferalScore = $currentReferalScore + (($productCount * 1) + $secondScore);
        $scoreId3 = getUserScoreId($db);
        if(!setRefferalScore($db,$scoreId3,$getSecondUser['refer_by_id'],$TotalReferalScore,'0')){
			$hasError = true;
		}

        //for third referal user
        $currentReferalScore = getCurrentUser($db,$getThirdUser['refer_by_id'],'score');
        $TotalReferalScore = $currentReferalScore + (($productCount * 1) + $firstScore);
        $scoreId4 = getUserScoreId($db);
        if(!setRefferalScore($db,$scoreId4,$getThirdUser['refer_by_id'],$TotalReferalScore,'0')){
			$hasError = true;
		}

		//Track all insert score to main referal
		$logId = getScoreLogId($db);
		InserScoreLogs($db,$logId,$currentUserID,$getFirstUser['refer_by_id'],($productCount *1) + $thirdScore, $invoice_id);

		//Track all insert score to main referal
		$logId1 = getScoreLogId($db);
		InserScoreLogs($db,$logId1,$currentUserID,$getSecondUser['refer_by_id'],($productCount *1) + $secondScore, $invoice_id);

		//Track all insert score to main referal
		$logId2 = getScoreLogId($db);
		InserScoreLogs($db,$logId2,$currentUserID,$getThirdUser['refer_by_id'],($productCount *1) + $firstScore, $invoice_id);
	}
	//Update Admin score
	if(!addAdminScore($db,$productCount,$adminScore)){
		$hasError = true;
	}


	if(!$hasError){
		$db->commit();
	}else{
		$db->rollback();
	}

/**** IN GOD WE TRUST ****/
?>
