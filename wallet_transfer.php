<?php
/****** CITA ******
 * CODING: HCK0011 / 2019-12-12
 * Description: Show User Wallet Transfer Transaction
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
 $transferList = '';
 $pageCount = 0;
 $walletID = '';
 $currentMonthTotal = 0;
 $totalWithdraw = 0;
 $totalDeposit = 0;
 
 $request_wallet_button = '<a class="request_withdraw_wallet">Request Withdraw</a>';
 
 if(!empty($getWallet)){
	$avaliableBalance = $getWallet['wauBalance'];
	$isNoTransaction = '';
	$walletID = $getWallet['wauID'];
	
	//Get transfer details
	$db->orderBy('uwtDate','DESC');
	$db->where('(select MONTH(uwtDate) = '.$currentMonth.')');
	$db->where('(select YEAR(uwtDate) = '.$currentYear.')');
	$db->where('uwtFromWalletID',$walletID);
	$db->orwhere('uwtToWalletID',$walletID);
	$counters    = $ct->getOne('tbl_user_wallet_transfer', "COUNT(*) AS numRow");
	$getTransfers = $db->get('tbl_user_wallet_transfer',$Limit);
	$num_rows 	  = $counters['numRow'];
	$pageCount = ceil($num_rows / $per_page);
	
	if(!empty($getTransfers)){
	
			foreach($getTransfers as $transfer){
				
				$image = '<a>N/A</a>';
				if($transfer['uwtImage'] != ''){
					$image = '<a href="'.BASE_URL.'files/wallet_transfer/'.$transfer['uwtImage'].'" target="_blank"><img src="'.BASE_URL.'assets/imgs/photos_icon.png"></a>';
				}
				
				$transferType = 'deposit';
				if($transfer['uwtToWalletID'] == $walletID) $transferType = 'withdraw';
				
					
				$transferList .= '
					<tr class="'.$transferType.'">
						<td class="usd">'. number_format($transfer['uwtAmount'],2).' USD</td>
						<td>'.$transfer['uwtFromUserName'].'</td>
						<td>'.$transfer['uwtToUserName'].'</td>
						<td>'.$transfer['uwtComment'].'</td>
						<td>'.$image.'</td>
						<td>'.date('d-M-Y h:ia',strtotime($transfer['uwtDate'])).'</td>
					</tr>
				';
			}
	}else{
		$transferList = '
		<tr><td colspan="6"><h5 align="center">No Transaction of current month</h5></td></tr>
		';
	}
	
	
 }  
 

?>
   	<div class="tab-content">
		<div id="wallet" class="tab-pane fade in active">

			<div class="wallet_balance">
				<h3>Total Balance in USD: <span>$<?php echo number_format($avaliableBalance,2);?></span><a class="btnTransfer"><i class="fa fa-exchange" aria-hidden="true"></i>Transfer</a></h3>
			</div>
			<div class="dateTimeWallet">
 				<label for="transfer_date_filter">Transaction of : </label>
		    	<input type="text" name="date" value="<?php echo date('m-Y');?>" id="transfer_date_filter"/>
 			</div>
		    <h4>Transfer History</h4>
		    
		   <table class="table table-striped tblWallet">
				<thead class="dark">
				  <tr>
					  <th>Amount</th>
					  <th>Transfer From</th>
					  <th>Transfer To</th>
					  <th>Remark</th>
					  <th>Image</th>
					  <th style="text-align:center; width:200px">Date/Time</th>
				  </tr>
				</thead>
				<tbody class="transfer_table">
 					<?php echo $transferList;?>
				</tbody>
			</table>
			<?php echo $isNoTransaction;?>
		</div>
		<hr>
		
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
	
	
	<style>
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
		.btn_create_transfer{
			width: 130px;
			height: 35px;
			background-color:#097bf1;
			border-radius: 5px;
			color:#fff;
			border: 1px solid #097bf1;
			margin: 0 auto;
			display:block;
		}
		.btn_create_transfer:hover{
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
		.create_transfer_status{
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
	</style>
	<script>
		
		$(".wallet_page").on("change",function(){
			var getDate = $("#transfer_date_filter").val();
			var getPage = $(this).val();
				getDate = "01-" + getDate;
			$.ajax({
				url : "<?php echo BASE_URL.'transferPage'?>",
				type: "post",
				dataType:"json",
				data: {page:getPage,date:getDate},
				success:function(e){
					$(".transfer_table").html(e.tranData);
				}
			});
		});
		
		$("#transfer_date_filter").datepicker({
			dateFormat: 'm-yy',
			changeMonth: true,
			changeYear: false,
			showButtonPanel: true,
			onClose: function(dateText, inst) {
				var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
				var year = $("#ui-datepicker-div .ui-datepicker-year").text();
				var valDate = $.datepicker.formatDate('m-yy', new Date(year, month, 1));
				$(this).val(valDate);
				valDate = "01-" + valDate;

				$.ajax({
					url : "<?php echo BASE_URL.'transferPage'?>",
					type: "post",
					dataType:"json",
					data: {date:valDate,page:1},
					success:function(e){
		
						$(".transfer_table").html(e.tranData);
						$(".wallet_page").html(e.pagination);
						$("#total_earnings").text("$"+e.earnings);
						$("#total_debit").text("-$"+e.debit);
						$("#total_profit").text("$"+e.profit);
						$("#total_month").text(e.month);
					}
				});
			}
		});
		
		$("#transfer_date_filter").focus(function () {
        $(".ui-datepicker-calendar").hide();
        $("#ui-datepicker-div").position({
            my: "center top",
            at: "center bottom",
            of: $(this)
		});
	
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
		
	});
	
	$('#view_reference').on('show.bs.modal', function (e) {
      modalAnimate('zoomInDown');
	});
	$('#view_reference').on('hide.bs.modal', function (e) {
		  modalAnimate('zoomOut');
	});
	
	$(".request_withdraw_wallet").click(function(){
		var walletID = <?php echo $walletID;?>;
		if(!$(this).hasClass('pending')){
			$.ajax({
				url: "<?php echo BASE_URL.'request_withdraw_wallet.php'; ?>",
				type: "post",
				data: {walletID : walletID},
				dataType:'json',
				beforeSend: function(e){
					$(".request_withdraw_wallet").prepend("<div class='loading'></div>");
				},
				success: function(e){
					
					if(e.status){
						$('.loading').remove();
						$(".request_withdraw_wallet").text("Pending Withdraw Request");
						$(".request_withdraw_wallet").addClass("pending");
					}
				}
			});
		}
		
	});
	
	$("#transfer_date_filter").datepicker({
			dateFormat: 'm-yy',
			changeMonth: true,
			changeYear: false,
			showButtonPanel: true,
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
		
						$(".transfer_table").html(e.tranData);
						$(".wallet_page").html(e.pagination);
					}
				});
			}
		});
		
		
		$("#transfer_date_filter").focus(function () {
			$(".ui-datepicker-calendar").hide();
			$("#ui-datepicker-div").position({
				my: "center top",
				at: "center bottom",
				of: $(this)
			});
		
		});
		
		
		
		
	</script>
 <?php
/**** IN GOD WE TRUST ****/
?>