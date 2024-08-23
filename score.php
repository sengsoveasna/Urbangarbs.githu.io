<?php
/****** CITA ******
 * CODING: HCK0011 / 2017-01-23
 * Description: Show User Score
 */ 
 
 // Get User Score
 $db->orderBy('id','DESC');
 $user_id = $_SESSION['user_id'];
 $db->where('user_id',$user_id);
 $getUserScore = $db->get('tbl_score_money');
 
 //Get Score Logs, Define that where current user got scores from.
 $db->join('tbl_score_logs S','S.child_id=U.id','inner');
 
 $db->where('S.parent_id',$user_id);
 $db->where("(SELECT MONTH(`date`) = MONTH(NOW()))");
 $db->where("(SELECT YEAR(`date`) = YEAR(NOW()))");
 
 $getScoreLogs = $db->get('tbl_user_register U');

?>
   <div class="tab-content">
		<div id="owner" class="tab-pane fade in active">
		   <table class="table table-striped">
				<thead class="dark">
				  <tr>
					  <th></th>
					  <th>Score of Month</th>
					  <th>Total Cost</th>
					  <th>Score Reocrd</th>
					  <th style="text-align:center">Status</th>
					  <th style="text-align:center">Payment Status</th>
					  <th style="text-align:center">Withdraw Request</th>
				  </tr>
				</thead>
				<tbody>
				  <?php
					$i=0;
					$status = '';
					$status_info = 'Payment Hold';
					foreach($getUserScore as $userScore){
						if($userScore['payment'] < 50){
							$status = 'bad';
							if($userScore['status'] ==2){
								$status_info = 'Paid Successful';
							}else{
								$status_info = 'Hold';
							}
						}else{
							$status = 'good';
							if($userScore['status'] ==2){
								$status_info = 'Paid Successful';
							}else{
								$status_info = 'Active';
							}
							
						}
				   ?>
					  <tr class="">
						<td scope="row"></td>
						
						<td><?php echo date("F-Y",strtotime($userScore['date'])); ?></td>
						<td><?php echo '$'.$userScore['payment']; ?></td>
						<td ><?php echo $userScore['score']; ?></td>
						<td align="center"><i class="fa fa-circle circle-status <?php echo $status;?>" aria-hidden="true"></i></td>
						<td align="center"><?php echo $status_info;?></td>
						<td align="center">
							<?php
						
							$currentMonth = date("m",strtotime($userScore['date']));
							$nowMonth = date("m");
							$currentYear = date("Y",strtotime($userScore['date']));
							$nowYear = date("Y");
							
						    
						    //updated new condition 02-Jan-2018
						
							$getRequestDate = date("Y-m",strtotime($userScore['date']));
							
							$currentDateYear = date("Y-m");
							
							$compareDate = strtotime($getRequestDate);
							$compareWith = strtotime($currentDateYear);
							
							if($userScore['status']==1 && $userScore['notify']==0 && $compareDate<$compareWith){
							?>
							<span class="send-withdraw-request" request_id="<?php echo $userScore['id']; ?>">
								Send Request
							</span>

							<?php
							}elseif($userScore['status']==1 && $userScore['notify']==2 || $userScore['notify']==1){
								?>
								<i class="withdraw-requested fa fa-check-circle" aria-hidden="true"></i>
								<?php
							}
							?>
						</td>
					  </tr>
				  <?php
					}
				   ?>
				</tbody>
		</table>
		
		</div>
  <div id="score_bounus" class="tab-pane fade">
			   <table class="table table-striped">
				<thead class="dark">
				  <tr>
					  <th></th>
					  <th width="150">Name</th>
					  <th>Phone Number</th>
					  <th>Email</th>
					  <th>Date</th>
					  <th>Detail</th>
					  <th style="text-align:center">Withdraw Score</th>
				  </tr>
				</thead>
				<tbody>
				  <?php
					$i=0;
					foreach($getScoreLogs as $scoreLogs){
					$i++;
				   ?>
					  <tr>
						<td scope="row"><?php echo $i;?></td>
						<td><?php echo $scoreLogs['name']; ?></td>
						<td><?php echo $scoreLogs['phone']; ?></td>
						<td><?php echo $scoreLogs['email']; ?></td>
						<td><?php echo date("d-m-Y",strtotime($scoreLogs['date'])); ?></td>
						<td><a href="javascript:;" data-id="<?php echo$scoreLogs['invoice_id'] ?>" class="view_detail">Detail</a></td>
						<td align="center"><?php echo $scoreLogs['score']; ?></td>
					  </tr>
				  <?php
					}
				   ?>
				</tbody>
		</table>
  </div>
  
       <!-- Invoice Preview Modal -->
	  <div class="modal fade" id="view_detail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document" style="width: 800px;" >
		  <div class="modal-content">
			<div class="modal-header">
			  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			  <h4 class="modal-title" id="myModalLabel">Invoice ID:  <b class="member_name"></b></h4>
			</div>
			<div class="modal-body">
			  <div id="referral_display"></div>
			</div>
		  </div>
		</div>
	  </div>
  
</div>
<script>
	$(".send-withdraw-request").click(function(){
		var request_id = $(this).attr('request_id');
		var current = $(this);
		$.ajax({
			url:"<?php echo BASE_URL.'withdraw_request.php'?>",
			type:"POST",
			async:false,
			data:{
				"withdraw-request":1,
				"request_id":request_id
			},
			success: function(data){
				
				current.text('');
				current.addClass('fa fa-check-circle');
			}
		});	
	});
	
	$(".view_detail").click(function(){
		$("#view_detail").modal();
		var getID = $(this).attr('data-id');
		if(getID != ''){
			$.ajax({
				url:"<?php echo BASE_URL.'view_referral_order.php'?>",
				type:"POST",
				async:false,
				data:{
					view_detail: 1,
					iv_id: getID
				},
				success: function(data){
					$("#referral_display").html(data);
					$(".member_name").html("#"+getID);
				}
			});
		}
	});
	
</script>
 <?php
/**** IN GOD WE TRUST ****/
?>