<?php
/****** CITA ******
 * CODING: HCK0011 / 2017-01-12
 * Description: Member Referal
 */

// get first referal (owner)
$user_id = $_SESSION['user_id'];

$db->join('tbl_referal RF','UR.id=RF.register_id','inner');
$db->where('RF.refer_by_id',$user_id);
$getFirstReferal = $db->get('tbl_user_register UR');
?>

<div class="tab-content">
  <div id="referal" class="tab-pane fade in active">

      <div class="well suggest-share col-sm-12">

          <h5><strong>Please Refer Your Link Below To Earn More Score.</strong></h5>
          <div class="col-sm-3">
              <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=<?php echo BASE_URL.'register?rfid='.md5($_SESSION['user_id']);?>" title="Scan QR code to link this product." />
          </div>
          <div class="input-group col-sm-8">

              <div class="input-group col-sm-12">
                <span class="input-group-addon">Your Referal Link</span>
                <input type="text" readonly="read-only" class="form-control" id="copyTarget" value="<?php echo BASE_URL.'register?rfid='.md5($_SESSION['user_id']);?>">
                <span class="input-group-btn">
                  <button style="margin-top:0px;" class="btn btn-success" id="copyButton" type="button" title="Copy Your Link to Clipboard">Copy</button>
                </span>
              </div>
              <div class="padding-line-50">

              </div>
              <label style="float:left">OR Share :</label>
              <div class="social-share-wrap referal_share">
                  <img id="fb_share" link="<?php echo BASE_URL.'register?rfid='.md5($_SESSION['user_id']);?>" src="<?php echo BASE_URL.'assets/imgs/fb_share_icon.png'?>" />
                  <img id="gp_share" link="<?php echo BASE_URL.'register?rfid='.md5($_SESSION['user_id']);?>" src="<?php echo BASE_URL.'assets/imgs/gp_share_icon.png'?>" />
              </div>
			  <div class='account-status'>Your Account Status: 
				<?php 
					$db->where("(SELECT MONTH(`date`) = MONTH(NOW()))");
					$db->where("(SELECT YEAR(`date`) = YEAR(NOW()))");
					$db->where('user_id',$_SESSION['user_id']);
					$getUserSatus = $db->getOne('tbl_score_money');
					if($getUserSatus['payment'] < 50){
						echo '<i class="fa fa-circle inactive" aria-hidden="true"></i><span> Inactive</span>';
					}else{
						echo '<i class="fa fa-circle active1" aria-hidden="true"></i><span> Active</span>';
					}
				?>
			  </div>
          </div>
      </div>
      <table class="table table-striped">
          <thead class="dark">
              <tr>
                  <th></th>
                  <th>Name</th>
                  <th>Gender</th>
                  <th>Register Date</th>
                  <th style="text-align:center">Activity Stauts</th>
              </tr>
          </thead>
          <tbody>
              <?php
                $i=0;
				
                foreach ($getFirstReferal as $FirstReferal) {
					$status = '';
					$status_info='Active';
                    $i++;
					$db->where("(SELECT MONTH(`date`) = MONTH(NOW()))");
					$db->where("(SELECT YEAR(`date`) = YEAR(NOW()))");
					$db->where('user_id',$FirstReferal['register_id']);
					$getUserSatus = $db->getOne('tbl_score_money');
					if(@$getUserSatus['payment'] < 50){
						$status ='inactive';
						$status_info='Inactive';
					}else{
						$status ='';
						$status_info='Active';
					}
					
               ?>
                  <tr class="<?php echo $status; ?>">
                    <td scope="row"><?php echo $i;?></td>
                    <td><?php echo $FirstReferal['name']; ?></td>
                    <td><?php echo $FirstReferal['sex']; ?></td>
                    <td><?php echo $FirstReferal['register_date']; ?></td>
                    <td align="center"><?php echo $status_info; ?></td>
                  </tr>
              <?php
                }
               ?>
          </tbody>
      </table>
  </div>
  <div id="subreferal" class="tab-pane fade">
      <table class="table table-striped">
          <thead class="dark">
              <tr>
                  <th></th>
                  <th>Name</th>
                  <th>Gender</th>
                  <th>Referal By</th>
                  <th>Register Date</th>
                  <th>Activity Stauts</th>
              </tr>
          </thead>
          <tbody>
              <?php
              $user_id = $_SESSION['user_id'];

              $db->join('tbl_referal RF','UR.id=RF.register_id','inner');
              $db->where('RF.refer_by_id',$user_id);
              $getFirstReferal = $db->get('tbl_user_register UR');
              $i=0;
                foreach ($getFirstReferal as $FirstReferal) {
                    $user_id = $FirstReferal['register_id'];

                    $db->join('tbl_referal RF','UR.id=RF.register_id','inner');
                    $db->where('RF.refer_by_id',$user_id);
                    $getSubReferal = $db->get('tbl_user_register UR');
                    foreach ($getSubReferal as $SubReferal) {
                
						$status = '';
						$status_info='Active';
						$i++;
						$db->where("(SELECT MONTH(`date`) = MONTH(NOW()))");
						$db->where("(SELECT YEAR(`date`) = YEAR(NOW()))");
						$db->where('user_id',$SubReferal['register_id']);
						$getUserSatus = $db->getOne('tbl_score_money');
						if($getUserSatus['payment'] < 50){
							$status ='inactive';
							$status_info='Inactive';
						}else{
							$status ='';
							$status_info='Active';
						}
						   ?>
                          <tr class="<?php echo $status; ?>">
                            <td scope="row"><?php echo $i;?></td>
                            <td><?php echo $SubReferal['name']; ?></td>
                            <td><?php echo $SubReferal['sex']; ?></td>
                            <td><?php echo $FirstReferal['name']; ?></td>
                            <td><?php echo $SubReferal['register_date']; ?></td>
                            <td align="center"><?php echo $status_info; ?></td>
                          </tr>
                      <?php
                    }
                }


               ?>
          </tbody>
      </table>
  </div>

    <div id="lastreferal" class="tab-pane fade">
      <table class="table table-striped">
          <thead class="dark">
              <tr>
                  <th></th>
                  <th>Name</th>
                  <th>Gender</th>
                  <th>Referal By</th>
                  <th>Register Date</th>
                  <th>Activity Stauts</th>
              </tr>
          </thead>
          <tbody>
              <?php
              $user_id = $_SESSION['user_id'];

              $db->join('tbl_referal RF','UR.id=RF.register_id','inner');
              $db->where('RF.refer_by_id',$user_id);
              $getFirstReferal = $db->get('tbl_user_register UR');
              $i=0;
                foreach ($getFirstReferal as $FirstReferal) {
                    $user_id = $FirstReferal['register_id'];

                    $db->join('tbl_referal RF','UR.id=RF.register_id','inner');
                    $db->where('RF.refer_by_id',$user_id);
                    $getSubReferal = $db->get('tbl_user_register UR');
                    foreach ($getSubReferal as $SubReferal) {
					
						$db->join('tbl_referal RF','UR.id=RF.register_id','inner');
						$db->where('RF.refer_by_id',$SubReferal['register_id']);
						$getLastReferal = $db->get('tbl_user_register UR');
						foreach ($getLastReferal as $lastreferal) {
							$status = '';
							$status_info='Active';
							$i++;
							$db->where("(SELECT MONTH(`date`) = MONTH(NOW()))");
							$db->where("(SELECT YEAR(`date`) = YEAR(NOW()))");
							$db->where('user_id',$lastreferal['register_id']);
							$getUserSatus = $db->getOne('tbl_score_money');
							if($getUserSatus['payment'] < 50){
								$status ='inactive';
								$status_info='Inactive';
							}else{
								$status ='';
								$status_info='Active';
							}
							   ?>
							  <tr class="<?php echo $status; ?>">
								<td scope="row"><?php echo $i;?></td>
								<td><?php echo $lastreferal['name']; ?></td>
								<td><?php echo $lastreferal['sex']; ?></td>\
								<td><?php echo $SubReferal['name']; ?></td>
								<td><?php echo $lastreferal['register_date']; ?></td>
								<td align="center"><?php echo $status_info; ?></td>
							  </tr>
						  <?php
						}
                    }
                }


               ?>
          </tbody>
      </table>
  </div>
</div>

<script>
$(document).ready(function(){
	

		//social Share
	$("#fb_share").on("click",function(){
		var link_user = $(this).attr('link');
		var url = 'https://www.facebook.com/sharer/sharer.php?u='+link_user;
		var fbpopup = window.open(url, "pop", "width=600, height=400, scrollbars=no");
		return false;
	});
	$("#gp_share").on("click",function(){
    var link_user = $(this).attr('link');
	var url = 'https://plus.google.com/share?url='+link_user;
    var fbpopup = window.open(url, "pop", "width=600, height=400, scrollbars=no");
    return false;
});
	
});
</script>
 <?php
/**** IN GOD WE TRUST ****/
?>
