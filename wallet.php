<?php
/****** CITA ******
 * CODING: HCK0011 / 2018-12-01
 * Description: Show User Wallet
 */ 

 // Get Selected Product of detail
 //    Including Pagination
 $pagLink = $BASE_URL.$main_page."/";
 if(!empty($main_id)){
	 $pagLink .= $main_id."/";
 }

 $pag = isset($_GET['pag'])? $_GET['pag']: 1;
 $per_page = 10;
 $pg = new Paginator($pag, $per_page, $pagLink, false, false);
 $start = $pg->limit_start();
 
 $Limit = array($start, $per_page);
 
 $currentMonth = date("m");
 $currentYear = date("Y");

 $avaliableBalance = 0;
 $db->where('wauUserID',$_SESSION['user_id']);
 $getWallet   = $db->getOne('tbl_user_wallet');
 $isNoTransaction = '<h5 align="center">No Transaction</h5>';
 $detailList = '';
 $pageCount = 0;
 $walletID = '';
 $currentMonthTotal = 0;
 $totalWithdraw = 0;
 $totalDeposit = 0;
 
 $request_wallet_button = '<a class="request_withdraw_wallet">Request Withdraw</a>';
 $getUsers['bank_id'] = '';
 
 if(!empty($getWallet)){
	$avaliableBalance = $getWallet['wauBalance'];
	$isNoTransaction = '';
	//Get wallet detail
	
	$db->orderBy('uwaDate','DESC');
	$db->join('tbl_invoice','id=uwaInvoiceID','left');
	$db->where('(select MONTH(uwaDate) = '.$currentMonth.')');
	$db->where('(select YEAR(uwaDate) = '.$currentYear.')');
	$db->where('uwa_wauID',$getWallet['wauID']);
	$ct          = $db->copy ();
	$tt          = $db->copy ();
	$tm          = $db->copy ();
 	$counters    = $ct->getOne('tbl_user_wallet_detail', "COUNT(*) AS numRow");
	$getWalletDetail = $db->get('tbl_user_wallet_detail',$Limit);
	$num_rows 	  = $counters['numRow'];
	$pageCount = ceil($num_rows / $per_page);
	
	
	//Get total per momth
	$tt->where('uwaTranType',1);
	$deposit = $tt->getOne('tbl_user_wallet_detail','SUM(uwaAmount) as amount');
	
	$tm->where('uwaTranType',0);
	$withdraw = $tm->getOne('tbl_user_wallet_detail','SUM(uwaAmount) as amount');
	
	$totalDeposit = !empty($deposit['amount']) ? $deposit['amount'] : 0;
	$totalWithdraw = !empty($withdraw['amount']) ? $withdraw['amount'] : 0;
	
	$currentMonthTotal = ($totalDeposit - $totalWithdraw);
	
	
	$walletID = $getWallet['wauID'];
	
	if(!empty($getWalletDetail)){
		foreach($getWalletDetail as $wallet){
			$reference = 'N/A';
			$tranType = '';
			$isDeposit = 'withdraw';
			if($wallet['uwaTranType']==0){
				$tranType = "-";
				$isDeposit = 'deposit';
			}
			if(!empty($wallet['uwaInvoiceID'])){
				$reference = 'Invoice ID: #'. $wallet['uwaInvoiceID'].' &ensp; <a class="viewInvoice" href="javascript:;" invoice_id="'.$wallet['uwaInvoiceID'].'" request_id="'.$wallet['request_id'].'">View Detail</a>';
			}
			
			$detailList .= '
			<tr class="'.$isDeposit.'">
				<td class="usd">'.$tranType. number_format($wallet['uwaAmount'],2).' USD</td>
				<td>'. $wallet['uwaComment'].'</td>
				<td>'. $reference .'</td>
				<td>'. $wallet['uwaTranferVia'] .'</td>
				<td><a href="javascript:;" class="btn_view_tran_detail" trance-id="'.$wallet['uwaID'].'">View</a></td>
				<td align="right">'. date('d-M-Y h:i a',strtotime($wallet['uwaDate'])).'</td>
			</tr>
		';
		}
	}else{
		$detailList = '
		<tr><td colspan="5"><h5 align="center">No Transaction of current month</h5></td></tr>
		';
	}
	
	
	if($getWallet['wauNotify']==1){
		
		$request_wallet_button = '<a class="request_withdraw_wallet pending">Pending Withdraw Request</a>';
		
	}
	
	
	//get wallet detail
	$db->join('tbl_user_wallet','wauID=uwa_wauID','INNER');
	$db->where('wauUserID',$_SESSION['user_id']);
	$counterNotification   = $db->getOne('tbl_user_wallet_detail', "COUNT(*) AS numRow");
	
	//handle notification
	$newNotificationCount = 0;
	if(!isset($_COOKIE['ntw'])){
		setcookie('ntw', $counterNotification['numRow'] , time() + (86400 * 3600), "/");
	}else{
		$currentCount = $_COOKIE['ntw'];
		if($currentCount < $counterNotification['numRow']){
			$newNotificationCount = $counterNotification['numRow'];
		}
	}
	
	
	$user_id = $_SESSION['user_id'];
	$db->where('id',$user_id);
	$getUsers = $db->getOne('tbl_user_register','bank_id');

 }  
 
  $token_wallet_transfer = sha1(mt_rand(111,999));
  $_SESSION['wallet_transfer'] = $token_wallet_transfer;

?>
<link href="<?php echo BASE_URL; ?>css/magnific-popup.css" rel="stylesheet"/>
<script src="<?php echo BASE_URL; ?>js/jquery.magnific-popup.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/4.2.0/min/dropzone.min.js"></script>

   	<div class="tab-content">
		<div id="wallet" class="tab-pane fade in active">
			<input type="hidden" value="<?php echo $newNotificationCount; ?>" name="new_notification_count" id="new_notification_count" />
			<div class="wallet_balance">
				<input type="hidden" id="wallet_id" value="<?php echo str_pad($walletID,6,0,STR_PAD_LEFT); ?>"/>
				<h3><a class="wallet_id_info"> Wallet ID : <span id="copyTarget1"><?php echo str_pad($walletID,6,0,STR_PAD_LEFT); ?></span><i data-toggle="tooltip" data-placement="top" title="Copy Your ID" id="copyButton1" class="fa fa-files-o btn_copy_wallet_id" aria-hidden="true"></i></a> Total Balance in USD: <span>$<?php echo number_format($avaliableBalance,2);?></span> <a class="btnTransfer"><i class="fa fa-exchange" aria-hidden="true"></i>Transfer</a> <?php if($avaliableBalance > 0){ echo $request_wallet_button; }?> </h3>
			</div>
			<div class="dateTimeWallet">
 				<label for="dateFilter">Transaction of : </label>
		    	<input type="text" name="date" value="<?php echo date('m-Y');?>" id="dateFilter"/>
 			</div>
		    <h4>Wallet Transaction</h4>
		    
		   <table class="table table-striped tblWallet">
				<thead class="dark">
				  <tr>
					  <th>Amount</th>
					  <th>Remark</th>
					  <th>Reference</th>
					  <th>Method</th>
					  <th>Detail</th>
					  <th style="text-align:center; width:200px">Date/Time</th>
				  </tr>
				</thead>
				<tbody class="wallet_table">
 					<?php echo $detailList;?>
				</tbody>
			</table>
			<?php echo $isNoTransaction;?>
		</div>
		<hr>
		<div class="total_wallet_footer">
			<label>Transaction total of <span id="total_month"><?php echo date("F");?></span>:</label>
			<small style="margin-left: 10px;">
				* Deposit : <span class="total_footer" id="total_earnings"><?php echo '$'.number_format($totalDeposit,2);?></span>
			</small>
			
			<small style="margin-left: 30px;">
				* Withdraw : <span class="total_footer" id="total_debit"><?php echo '-$'.number_format($totalWithdraw,2);?></span>
			</small>
			
			<small style="margin-left: 30px;">
				* Remaining : <span class="total_footer" id="total_profit"><?php echo '$'.number_format($currentMonthTotal,2);?></span>
			</small>
		</div>
		<?php 
			if($pageCount!=0){
		?>
 		
		<!--Begin of Pagination-->
		<div class="pagination_ajax">
			<?php
				$pagination = '<select class="wallet_page">';
				for($i=0; $i<$pageCount; $i++){
					$pagination .= '
						<option value="'.($i + 1).'">'.($i + 1).'</option>
					';
				}
				$pagination .= '</select>';
			?>
			<span>View Page : </span>
			<?php echo $pagination;?>
		</div>
		
		<?php }?>

  	</div>
	
	<!-- Previce invoice Modal -->
	<div class="modal fade" id="view_reference" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document" style="width: 800px;" >
	  <div class="modal-content">
		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		  <h4 class="modal-title" id="myModalLabel">Order ID : <b class="orderID"></b></h4>
		</div>
		<div class="modal-body">
		  <div id="requests_show"></div>
		</div>

	  </div>
	</div>
	</div>
	
	<!-- Previce invoice Modal -->
	<div class="modal fade" id="create_transfer" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1">
	<div class="modal-dialog" role="document" style="width: 800px;" >
	  <div class="modal-content">
		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		  <h4 class="modal-title" id="myModalLabel1">Wallet Transfer <b class="orderID"></b></h4>
		</div>
		<div class="modal-body transfer_form_body">
			<div class="col-sm-12">
				<div class="alert alert-danger create_transfer_status" role="alert"></div>
			</div>
			<div class="col-sm-12 transfer_success_status">
				<img src="<?php echo BASE_URL.'assets/imgs/transfer_success.png'?>"/>
				<h5>Transfer successfully</h5>
			</div>
			
			<form class="col-sm-12" id="form_transfer" method="post" enctype="multipart/form-data" action="<?php echo BASE_URL.'create_transfer';?>">
				<input type="hidden" name="token" value="<?php echo $token_wallet_transfer; ?>"/>
				<div class="col-sm-6">
				  <div class="form-group row">
					<label for="my_balance" class="col-sm-4 col-form-label">Your Balance</label>
					<div class="col-sm-8">
					  <input type="text" readonly class="form-control" id="my_balance" value="$<?php echo number_format($avaliableBalance,2);?>">
					</div>
				  </div>
				  <div class="form-group row">
					<label for="receiver_id" class="col-sm-4 col-form-label">Transfer To</label>
					<div class="col-sm-8 invalid">
					  <input type="text" class="form-control" id="receiver_id" name="receiver_id" placeholder="Wallet ID"/>
					<span id="wallet_id_err_status"></span>
					</div>
				  </div>
				  <div class="form-group row" style="display:none;">
					<label for="receiver_name" class="col-sm-4 col-form-label">Receiver Name</label>
					<div class="col-sm-8 invalid">
					  <input type="text" class="form-control" readonly id="receiver_name" name="receiver_name" placeholder="Wallet Holder Name"/>
					</div>
				  </div>
				  <div class="form-group row">
					<label for="transfer_amount" class="col-sm-4 col-form-label">Amount ($)</label>
					<div class="col-sm-8">
					  <input type="text" class="form-control" id="transfer_amount" name="transfer_amount" placeholder="USD"/>
					</div>
				  </div>
				  <div class="form-group row">
					<label for="transfer_remark" class="col-sm-4 col-form-label">Remark</label>
					<div class="col-sm-8">
						<textarea class="form-control" name="transfer_remark" id="transfer_remark"></textarea>
					</div>
				  </div>
			  </div>
			</form>
			<div class="col-sm-12 image_uploader">
				<div class="col-sm-6">
					<div class="form-group row product_image_feedback">
						<label for="transfer_photo" class="col-sm-4 col-form-label">Photos</label>
						<!--<span class="img_ufb_span">You ca->n upload 5 files in total. (5MB Max : .jpg, .png, .gif)</span>-->
						<div class="col-sm-8">
							<form action="#" enctype="multipart/form-data" class="dropzone" id="image-upload">
								<input type="hidden" name="tranID" id="tranID" value="" />
								<input type="hidden" name="token" value="<?php echo $token_wallet_transfer; ?>"/>
							</form>
						</div>
						<div class="clearfix_20"></div>
					</div>
				 </div>
			</div>
			  <div class="clearfix_15"></div>
			  <button class="btn_create_transfer">Transfer Now</button>
			  <div class="clearfix_15"></div>
		</div>
	  </div>
	   <div class="clearfix_15"></div>
	</div>
	</div>
	
	
	<!-- Request Wallet Modal -->
	<div class="modal fade" id="wallet_request" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2">
	<div class="modal-dialog" role="document" style="width: 800px;" >
	  <div class="modal-content">
		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		  <h4 class="modal-title" id="myModalLabel2">Withdraw Request<b class="orderID"></b></h4>
		</div>
		<div class="modal-body row">
			<div class="col-sm-12">
				<div class="alert alert-danger wallet_request_status" role="alert"></div>
			</div>
			<div class="col-sm-12 transfer_success_status">
				<img src="<?php echo BASE_URL.'assets/imgs/transfer_success.png'?>"/>
				<h5>Request successfully</h5>
			</div>
			<form id="form_request_wallet" style="position:relative; width:100%" method="post" enctype="multipart/form-data" action="<?php echo BASE_URL.'view_request.php'?>">
				<input type="hidden" name="token" value="<?php echo $token_wallet_transfer;?>"/>
				<div class="col-sm-6">
				  <div class="form-group row">
					<label for="my_balance" class="col-sm-4 col-form-label">Your Balance</label>
					<div class="col-sm-8">
					  <input type="text" readonly class="form-control" id="my_balance" value="$<?php echo number_format($avaliableBalance,2);?>">
					</div>
				  </div>
				   <div class="form-group row">
					<label for="bank_account" class="col-sm-4 col-form-label">Bank Account</label>
					<div class="col-sm-8">
					  <input type="text" readonly class="form-control" id="bank_account" value="<?php echo $getUsers['bank_id'];?>">
					</div>
				  </div>
				  <div class="form-group row">
					<label for="request_amount" class="col-sm-4 col-form-label">Amount ($)</label>
					<div class="col-sm-8">
					  <input type="text" class="form-control" id="request_amount" name="request_amount" placeholder="USD"/>
					</div>
				  </div>
				  <div class="form-group row">
					<label for="note" class="col-sm-4 col-form-label">Note</label>
					<div class="col-sm-8">
						<textarea class="form-control" name="note" id="note"></textarea>
					</div>
				  </div>
				</div>
			  <div class="clearfix_15"></div>
			  <button class="btn_send_withdraw_request">Send Request</button>
			  <div class="clearfix_15"></div>
			</form>
		</div>
	  </div>
	   <div class="clearfix_15"></div>
	</div>
	</div>
	
	<!-- Transaction Detail Modal -->
	<div class="modal fade" id="view_transaction_detail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" style="top:40px; z-index:1041">
	<div class="modal-dialog" role="document" style="width:800px;">
	  <div class="modal-content">
		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		  <h4 class="modal-title" id="myModalLabel2">Transaction Detail</h4>
		</div>
		<div class="modal-body">
		  <div id="transaction_detail_wrap">
			<div class="form_loading"></div>
		  </div>
		</div>

	  </div>
	</div>
	</div>
	
	<style>
		.image-source-link {
			color: #98C3D1;
		}

		.mfp-with-zoom .mfp-container,
		.mfp-with-zoom.mfp-bg {
			opacity: 0;
			-webkit-backface-visibility: hidden;
			/* ideally, transition speed should match zoom duration */
			-webkit-transition: all 0.3s ease-out; 
			-moz-transition: all 0.3s ease-out; 
			-o-transition: all 0.3s ease-out; 
			transition: all 0.3s ease-out;
		}

		.mfp-with-zoom.mfp-ready .mfp-container {
				opacity: 1;
		}
		.mfp-with-zoom.mfp-ready.mfp-bg {
				opacity: 0.8;
		}

		.mfp-with-zoom.mfp-removing .mfp-container, 
		.mfp-with-zoom.mfp-removing.mfp-bg {
			opacity: 0;
		}
		img.mfp-img{
			max-width: 900px;
		}
	
	
		.tblWallet tbody .deposit{
			background-color: #ffdcdc !important;
		}
		.tblWallet tbody .deposit .usd{
			color: #e80000 !important;
		}
		.tblWallet tbody .withdraw{
			background-color: #dcffe7 !important;
		}
		.tblWallet tbody .withdraw .usd{
			color: #0bcc36 !important;
		}
		.dateTimeWallet{
			float:right;
			width:auto;
			height:30px;
			margin-bottom: 15px;
			line-height: 25px;
		}
		.dateTimeWallet *{
			float:left;
			line-height: 25px;
		}
		.dateTimeWallet input{
			width: 100px;
			border: 1px solid #ccc;
			border-radius: 5px;
			margin-left: 5px;
			padding-left: 15px;
			padding-right: 15px;
		}
		.ui-widget-header{
			color : #000;
		}
		.wallet_balance *{
			font-size: 15px !importany;
		}
		.wallet_balance h3{
			margin-top: 0px;
		}
		.request_withdraw_wallet{
				float: right;
				font-size: 14px;
				text-decoration:none !important;
				border: 1px solid #eee;
				padding: 10px 15px;
				border-radius: 5px;
				cursor: pointer;
				position:relative;
				background-color: #6ad492;
				color: #fff !important;
				margin-right: 15px;
				display: inline-block;
		}
		.request_withdraw_wallet:not(.pending):hover{
			background-color:#95efd2;
			color: #fff;
		}
		.request_withdraw_wallet.pending{
			background-color:#bfbfbf;
			color: #fff;
			cursor:default;
		}
		.total_footer{
			font-size: 15px;
			color: #f51111;
		}
		#total_earnings{
			color: #2aad72;
		}
		#total_profit{
			color: #0572ff
		}
		.pending{
			color: #f37070;
		}
		.pending:hover{
			color: #f37070;
		}
		.loading{
			display: block;
			width: 100%;
			height: 100%;
			background-image: url(<?php echo BASE_URL.'assets/imgs/loading.gif';?>);
			background-color: rgba(255, 255, 255, 0.85);
			background-size: 20px;
			position: absolute;
			background-repeat: no-repeat;
			text-align: center;
			background-position: center;
			top: 0px;
			left: 0px;
		}
		.wallet_id_info{
			text-decoration:none !important;
			font-size: 14px;
			color:#000;
			margin-right: 15px;
			display: inline-block;
		}
		.wallet_id_info::after{
			content : '|';
			display: block;
			float:right;
			margin-left: 15px;
		}
		.wallet_id_info span{
			color:#000 !important;
			margin-right: 5px;
			display: inline-block;
		}
		.wallet_id_info:hover{
			color:#000;
		}
		.wallet_balance h3{
			font-size: 15px;
		}
		.wallet_balance h3 span{
			font-size:16px;
		}
		.wallet_balance i{
			font-size: 18px;
			margin-left: 5px;
		}
		.btn_copy_wallet_id:hover{
			color:#f92f26;
			cursor:pointer;
		}
		
		
		.tblWallet tbody .deposit{
			background-color: #ffdcdc !important;
		}
		.tblWallet tbody .deposit .usd{
			color: #e80000 !important;
		}
		.tblWallet tbody .withdraw{
			background-color: #dcffe7 !important;
		}
		.tblWallet tbody .withdraw .usd{
			color: #0bcc36 !important;
		}
		.dateTimeWallet{
			float:right;
			width:auto;
			height:30px;
			margin-bottom: 15px;
			line-height: 25px;
		}
		.dateTimeWallet *{
			float:left;
			line-height: 25px;
		}
		.dateTimeWallet input{
			width: 100px;
			border: 1px solid #ccc;
			border-radius: 5px;
			margin-left: 5px;
			padding-left: 15px;
			padding-right: 15px;
		}
		.ui-widget-header{
			color : #000;
		}
		.wallet_balance h3{
			margin-top: 0px;
		}
		.request_withdraw_wallet{
				float: right;
				font-size: 14px;
				text-decoration:none !important;
				border: 1px solid #eee;
				padding: 10px 15px;
				border-radius: 5px;
				cursor: pointer;
				position:relative;
		}
		.request_withdraw_wallet:not(.pending):hover{
			background-color:#95efd2;
			color: #fff;
		}
		.total_footer{
			font-size: 15px;
			color: #f51111;
		}
		#total_earnings{
			color: #2aad72;
		}
		#total_profit{
			color: #0572ff
		}
		.pending{
			color: #f37070;
		}
		.pending:hover{
			color: #f37070;
		}
		.loading{
			display: block;
			width: 100%;
			height: 100%;
			background-image: url(<?php echo BASE_URL.'assets/imgs/loading.gif';?>);
			background-color: rgba(255, 255, 255, 0.85);
			background-size: 20px;
			position: absolute;
			background-repeat: no-repeat;
			text-align: center;
			background-position: center;
			top: 0px;
			left: 0px;
		}
		.btnTransfer{
			font-size: 14px;
			padding: 9px 15px;
			border: 1px solid #3463ce;
			text-decoration:none !important;
			border-radius: 5px;
			cursor: pointer;
			float:right;
			background-color: #167ef8;
			color: #fff;
		}
		.btnTransfer i{
			font-size: 15px;
			margin-right: 5px;
		}
		.btnTransfer:hover{
			color: #fff !important;
			background-color: #0856cc;
		}
		.form-group label{
			text-align:right;
			margin-bottom:0px !important;
			margin-top: 5px;
		}
		#transfer_photo{
			margin-bottom: 15px;
			overflow: hidden;
			width: 165px;
		}
		.img_preview{
			width: 150px;
			min-height: 150px;
			height: auto;
		}
		.img_preview img{
			width: 100%;
			height: auto;
			border: 1px solid #eee;
		}
		#form_transfer{
			display:block;
			position:relative;
		}
		#form_transfer input[readonly]{
			background-color:#fff;
		}
		
		#wallet_id_err_status{
			color:#fb4141;
		}
		.btn_create_transfer, .btn_send_withdraw_request{
			width: 130px;
			height: 35px;
			background-color:#097bf1;
			border-radius: 5px;
			color:#fff;
			border: 1px solid #097bf1;
			margin: 0 auto;
			display:block;
		}
		.btn_create_transfer:hover, .btn_send_withdraw_request:hover{
			background-color:#1467bd;
		}
		.clearfix_15{
			display:block;
			width: 100%;
			height: 15px;
			clear:both;
		}
		#receiver_name{
			background-color: #a7fbc0 !important;
		}
		.create_transfer_status,.wallet_request_status{
			display:none;
		}
		.in_progress::before{
			display:block;
			content: '';
			width:100%;
			height:100%;
			position:absolute;
			left:0px;
			top:0px;
			background-color:rgba(255, 255, 255, 0.68);
			z-index: 9999;
		}
		.in_progress::after{
			content: '';
			display:block;
			width:220px;
			height:220px;
			background-image: url('<?php echo BASE_URL.'assets/imgs/process_transfer.gif';?>');
			z-index:999999;
			position:absolute;
			left: 50%;
			margin-left: -110px;
			top: 10%;
		}
		.transfer_success_status{
			width: 100%;
			height: auto;
			position:absolute;
			text-align:center;
			display:none;
		}
		.transfer_success_status h5{
			font-size: 18px;
			font-weight:bold;
		}
		#transaction_detail_wrap{
			position:relative;
			min-height: 300px;
			height:auto;
		}
		.form_loading{
			width:220px;
			height:220px;
			background-image: url('<?php echo BASE_URL.'assets/imgs/process_transfer.gif';?>');
			position:absolute;
			left: 50%;
			margin-left: -110px;
			top: 10%;
		}
		.tran_detail_item{
			display:block;
			width: 100%;
		}
		
		.tran_detail_item dt{
			width: 200px;
			float:left;
			text-align:right;
			padding-right: 15px;
			line-height: 30px;
		}
		
		.tran_detail_item dd{
			font-weight: normal;
			font-size: 14px;
			color: #000000;
			overflow: hidden;
			position: relative;
			display: block;
			height: auto;
			line-height: 30px;
			padding-right: 15px;
		}
		
		.tran_img_group{
			width:100%;
			height:auto;
			position:relative;
		}
		
		.tran_img_group img{
			width:80px;
			height:80px;
			position:relative;
			object-fit: cover;
		}
		.tran_img_group li{
			list-style:none;
			float:left;
			margin: 15px;
		}
		
		#image-upload{
		min-width: 500px;
		min-height: 100px;
		width: auto;
		height: auto;
		border: 2px dashed #eee;
		border-radius: 5px;
		cursor: pointer;
		display: inline-block;
		transition: .3s;
		position: relative;
	}
	#image-upload:hover{
		border-color: #ccc;
	}
	.dz-drag-hover{
		border-color: #8dc4ff !important;
		transition: .3s;
	}

	.dz-details, .dz-success-mark,.dz-error-mark{
		display: none !important;
	}
	.dz-preview{
		float: left;
		margin: 7px;
		position: relative;
	}
	.dz-preview img{		
		width: 80px;
		height: 80px;
		border: 1px solid #eee;
	}
	.dz-error{
		filter: opacity(0.50);
	}
	.dz-error .dz-error-message{
		font-size: 11px;
		color: #ff2929;
		text-align: center;
	}
	.dz-progress {
		width: 60px;
    	height: 10px;
		position: absolute;
		top: 50%;
		left: 10px;
		background: #eee;
    	border-radius: 50px;
		box-shadow: 0px 2px 3px 0px rgba(0, 0, 0, 0.25882352941176473);
		display: none;
	}
	.dz-upload{
		width: 100%;
		height: 10px;
		background: #0b9aff;
		border-radius: 50px;
	}
	.dz-message{
		text-align: center;
		position: absolute;
		margin-left: 0px;
		margin-right: 0px;
		left: 0px;
		right: 0px;
		top: 50%;
		margin-top: -10px;
		color: #aba9a9;
	}
	.dz-started .dz-message{
		display: none;
	}
	.dz-processing .dz-progress, .dz-processing .dz-upload{
		display: block;
	}
	.dz-complete .dz-progress, .dz-complete .dz-upload{
		display: none;
	}
		
	</style>
	<script>
	
		$(document).ready(function() {
			$('.zoom-gallery').magnificPopup({
				delegate: 'a',
				type: 'image',
				closeOnContentClick: false,
				closeBtnInside: false,
				mainClass: 'mfp-with-zoom mfp-img-mobile',
				image: {
					verticalFit: true,
					titleSrc: function(item) {
						return item.el.attr('title') + ' &middot; <a class="image-source-link" href="'+item.el.attr('data-source')+'" target="_blank">image source</a>';
					}
				},
				gallery: {
					enabled: true
				},
				zoom: {
					enabled: true,
					duration: 300, // don't foget to change the duration also in CSS
					opener: function(element) {
						return element.find('img');
					}
				}
				
			});
			
			Dropzone.autoDiscover = false;
			
		});
	
		$(function () {
		  $('[data-toggle="tooltip"]').tooltip()
		});
			
		$(".wallet_page").on("change",function(){
			var getDate = $("#dateFilter").val();
			var getPage = $(this).val();
				getDate = "01-" + getDate;
			$.ajax({
				url : "<?php echo BASE_URL.'walletPage'?>",
				type: "post",
				dataType:"json",
				data:{page:getPage, date:getDate},
				success:function(e){
					$(".wallet_table").html(e.tranData);
				}
			});
		});
	
		$("#dateFilter").datepicker({
			dateFormat: 'm-yy',
			changeMonth: true,
			changeYear: false,
			showButtonPanel: true,
			beforeShow : function(input, inst) {
				var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
				var year = $("#ui-datepicker-div .ui-datepicker-year").text();
				var valDate = new Date(year, month, 1);
				
				$(this).datepicker('option','defaultDate', valDate);
				$(this).datepicker('setDate', valDate);
			},
			onClose: function(dateText, inst) {
				var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
				var year = $("#ui-datepicker-div .ui-datepicker-year").text();
				var valDate = $.datepicker.formatDate('m-yy', new Date(year, month, 1));
				$(this).val(valDate);
				valDate = "01-" + valDate;
				$.ajax({
					url : "<?php echo BASE_URL.'walletPage'?>",
					type: "post",
					dataType:"json",
					data: {date:valDate,page:1},
					success:function(e){
		
						$(".wallet_table").html(e.tranData);
						$(".wallet_page").html(e.pagination);
						$("#total_earnings").text("$"+e.earnings);
						$("#total_debit").text("-$"+e.debit);
						$("#total_profit").text("$"+e.profit);
						$("#total_month").text(e.month);
					}
				});
			}
		});
		
		$("#dateFilter").focus(function(){
			//$(".ui-datepicker-calendar").hide();
			// $("#ui-datepicker-div").position({
				// my: "center top",
				// at: "center bottom",
				// of: $(this)
			// });
		
		});
	
	
	$("body").on("click",".viewInvoice",function(){
			$("#view_reference").modal();

			var request_id = $(this).attr('request_id');
			var invoice_id = $(this).attr('invoice_id');
			
				$.ajax({
					url:"<?php echo BASE_URL.'view_request.php'?>",
					type:"POST",
					async:"false",
					data:{
						"view_request":1,
						"request_id":request_id,
						"invoice_id":invoice_id
					},
					success: function(data){
						$("#requests_show").html(data);
						$("body").find(".orderID").html("#"+invoice_id);
					}
				});
		
	}).on("click","#copyButton1",function(){
		//var targetValue = $("#copyTarget1").val();
		//copyStringToClipboard(targetValue);
		copyToClipboard(document.getElementById("copyTarget1"));
		
	}).on("click",".btn_create_transfer",function(){
		$("#form_transfer").submit();
	});
	
	$('#view_reference').on('show.bs.modal', function (e) {
      modalAnimate('zoomInDown');
	});
	$('#view_reference').on('hide.bs.modal', function (e) {
		  modalAnimate('zoomOut');
	});
	
	$(".request_withdraw_wallet").click(function(){
		if(!$(this).hasClass('pending')){
			$("#wallet_request").modal();
		}
		
	});
	
	
	// $(".request_withdraw_wallets").click(function(){
		// var walletID = <?php echo $walletID;?>;
		// if(!$(this).hasClass('pending')){
			// $.ajax({
				// url: "<?php echo BASE_URL.'request_withdraw_wallet.php';?>",
				// type: "post",
				// data: {walletID : walletID},
				// formData:'json',
				// beforeSend: function(e){
					// $(".request_withdraw_wallet").prepend("<div class='loading'></div>");
				// },
				// success: function(e){
					
					// if(e.status){
						// $('.loading').remove();
						// $(".request_withdraw_wallet").text("Pending Withdraw Request");
						// $(".request_withdraw_wallet").addClass("pending");
					// }
				// }
			// });
		// }
	// });
	
		$("body").on("click",".btnTransfer",function(){
			$("#create_transfer").modal({backdrop: 'static', keyboard: false});
			Dropzone.autoDiscover = false;
			$("#image-upload").dropzone({
				url: "<?php echo BASE_URL.'upload_image_wallet_transfer.php';?>",
				maxFilesize:5,
				acceptedFiles: ".jpeg,.jpg,.png,.gif,.PNG",
				dictDefaultMessage:"Click to browse or drage and drop to upload.",
				maxFiles:5,
				parallelUploads: 5,	
				thumbnailWidth: 80,
				thumbnailHeight:80,
				dictMaxFilesExceeded: "Exceed limit",
				addRemoveLinks: true,
				thumbnailMethod: 'contain',
				autoProcessQueue:false,
				preventDuplicates: true
			});
			
		}).on("change","#transfer_photo",function(){
			
			let getCurrnetThmb = $("body").find("#thmb_preview").val();
			var reader = new FileReader();
			reader.onload = function (e) {
				// get loaded data and render thumbnail.
				
				if(typeof getCurrnetThmb == 'undefined'){
					
					var preview_img = document.createElement('img');
						preview_img.setAttribute('src',e.target.result);
						preview_img.setAttribute('id','thmb_preview');
						
						$(".img_preview").append(preview_img);
				}else{
					$("body").find("#thmb_preview").attr('src',e.target.result);
				}
			};

			// read the image file as a data URL.
			reader.readAsDataURL(this.files[0]);
			
		}).on('change','#receiver_id',function(){
			var getID = $(this).val();
			if(getID != '' && getID.length >= 6){
				$("#form_transfer").addClass('in_progress');
				$.ajax({
					url : '<?php echo BASE_URL.'retrive_wallet';?>',
					type: 'post',
					data: {walletID:getID},
					dataType:'json',
					success: function(e){
						$("#form_transfer").removeClass('in_progress');
						if(e.hasError){
							$("#wallet_id_err_status").text(e.status);
							$("body").find("#receiver_name").val('');
							$("body").find("#receiver_name").closest('.form-group').css('display','none');
						}else{
							if(typeof e.wallet_data != 'undefined'){
								var walletData = JSON.parse(e.wallet_data);
								$("body").find("#receiver_name").val(walletData['holder_name']);
								$("body").find("#receiver_name").closest('.form-group').css('display','block');
								$("body").find("#wallet_id_err_status").text('');
							}
						}
					}
				});
			}else{
				$("#wallet_id_err_status").text("Invalid Wallet ID");
			}
			
		}).on("keydown","#transfer_amount",function(e){
			if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110,190]) !== -1 ||
				(e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
				(e.keyCode >= 35 && e.keyCode <= 40)) {
					 return;
			}
			if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
				e.preventDefault();
			}
		}).on("submit","#form_transfer",function(e){
			e.preventDefault();
			
			var formData = new FormData(this);
			
			$(".transfer_form_body").addClass('in_progress');
			
			$.ajax({
				url : '<?php echo BASE_URL.'validate_transfer'?>',
				type: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				dataType: 'json',
				success: function(e){
					// $(".transfer_form_body").removeClass('in_progress');
					if(e.hasError){
						$(".create_transfer_status").text(e.status);
						$(".create_transfer_status").css('display','block');
						$(".transfer_form_body").removeClass('in_progress');
					}else{
						// if(typeof e.transaction != 'undefined'){
							
						// }
						if(typeof e.tranID != 'undefined' && e.tranID != ''){
							//upload images
							$("body").find("#tranID").val(e.tranID);
							//upload image 
							let myDropzone = Dropzone.forElement(".dropzone");
							var dz_queue = myDropzone.getQueuedFiles().length;
							myDropzone.processQueue();
							
							myDropzone.on('queuecomplete',function(file){
								$(".transfer_success_status").css('display','block');
								$(".create_transfer_status").css('display','none');
								$("#form_transfer").css('visibility','hidden');
								$(".image_uploader").css('visibility','hidden');
								$(".btn_create_transfer").css('visibility','hidden');
								$(".transfer_form_body").removeClass('in_progress');
								setTimeout(function(){
									location.reload();
								}, 500);
							});

							if(dz_queue == 0){
								$(".transfer_success_status").css('display','block');
								$(".create_transfer_status").css('display','none');
								$("#form_transfer").css('visibility','hidden');
								$(".image_uploader").css('visibility','hidden');
								$(".btn_create_transfer").css('visibility','hidden');
								$(".transfer_form_body").removeClass('in_progress');
								setTimeout(function(){
									location.reload();
								}, 500);
							}
						}
					}
				}
			});
			
		}).on("submit","#form_request_wallet",function(e){
			e.preventDefault();
			var formData = new FormData(this);
			var eThis = $(this);
			$("#form_request_wallet").addClass('in_progress');
			$.ajax({
				type: 'POST',
				url : "<?php echo BASE_URL.'request_withdraw_wallet.php';?>",
				data: formData,
				contentType: false,
				processData: false,
				dataType: 'json',
				success: function(e){
					
					if(e.hasError){
						$(".wallet_request_status").text(e.status);
						$(".wallet_request_status").css('display','block');
						$("#form_request_wallet").removeClass('in_progress');
					}else{
						$(eThis).find(".transfer_success_status").css('display','block');
						$(eThis).find(".wallet_request_status").css('display','none');
						$(eThis).find("#form_transfer").css('visibility','hidden');
						
						setTimeout(function(){ 
							location.reload();
						}, 500);
					}
				}
			});
		});
		
		$(document).on('hidden.bs.modal', '#create_transfer', function (e) {
			$("#receiver_id").val('');
			$("#transfer_amount").val('');
			$("#transfer_remark").val('');
			$("#transfer_photo").val('');
			$("#receiver_name").text('');
			$(".img_preview").text('');
			$("body").find("#wallet_id_err_status").text('');
			$("#receiver_name").closest('.form-group').css('display','none');
		});
		
		$("body").on('click','.btn_view_tran_detail',function(){
			$("#view_transaction_detail").find("#transaction_detail_wrap").html('<div class="form_loading"></div>');
			$("#view_transaction_detail").modal();
			var getTranID = $(this).attr('trance-id');
			
				$.ajax({
					url: '<?php echo BASE_URL.'get_transaction_detail'?>',
					type: 'post',
					data: {tranID:getTranID},
					success:function(transaction_detail){
						$("#transaction_detail_wrap").html(transaction_detail);
					},
					error:function(err){
						console.log(err);
					}
				});
			//get transaction
			
		});
		
		function startUpload($tranID){
			$("body").find("#tranID").val($tranID);
			//upload image 
			let myDropzone = Dropzone.forElement(".dropzone");
			var dz_queue = myDropzone.getQueuedFiles().length;
			myDropzone.processQueue();
			
			myDropzone.on('queuecomplete',function(file){
				window.location.reload();
			});
			
			if(dz_queue == 0){
				window.location.reload();
			}
		}
		
		// $(".tran_img_group").magnificPopup({
		  // type: 'image',
		  // gallery:{
			// enabled:true
		  // }
		// });
	
		function copyStringToClipboard (str) {
		   // Create new element
		   var el = document.createElement('textarea');
		   // Set value (string to be copied)
		   el.value = str;
		   // Set non-editable to avoid focus and move outside of view
		   el.setAttribute('readonly', '');
		   el.style = {position: 'absolute', left: '-9999px'};
		   document.body.appendChild(el);
		   // Select text inside element
		   el.select();
		   // Copy text to clipboard
		   document.execCommand('copy');
		   // Remove temporary element
		   document.body.removeChild(el);
		}
			
	</script>
 <?php
/**** IN GOD WE TRUST ****/
?>