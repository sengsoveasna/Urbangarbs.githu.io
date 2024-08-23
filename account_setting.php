<?php
/****** CITA ******
 * CODING: HCK0011 / 2017-01-09
 * Description: User account Setting
 */


 function checkPhone($db,$phone){
    $db->where('phone',$phone);
    $getPhoneExist = $db->getOne('tbl_user_register');
    return empty($getPhoneExist);
 }

 // Get User
 $return_url = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
 if(!empty($_SESSION['user_id'])){
     $user_id = $_SESSION['user_id'];
     $db->where('id',$user_id);
     $User = $db->getOne('tbl_user_register');

  }
  
  if(!empty($User)){
      
?>
    <div class="col-sm-12">
        <h3>Your Account Setting</h3>
        <hr>
        <div class="clearfix"></div>
        <div class="information-wrap">
            <?php
                $img = '';
                if($_SESSION['user_sex']=='female'){
                    $img = 'assets/imgs/female.png';
                }else{
                    $img = 'assets/imgs/male.png';
                }
				if(isset($_SESSION['user_id'])){
					$db->where('id',$_SESSION['user_id']);
					$getUserProfile = $db->getOne('tbl_user_register');
					if(!empty($getUserProfile) && ( !empty($getUserProfile['image']) && $getUserProfile['image']!='--None--')){
						$img = 'files/users/'.$getUserProfile['image'];
					}
				}
				
            ?>
            <img class="img-profile" src="<?php echo BASE_URL.$img;?>"/>
            <a href="#UpdateProfile" id="UpdateProfile" data-toggle="modal" data-target="#myModal">Update Profile Photo</a>
            <a style="margin-left:150px; line-height:50px;" id="changePassword" data-toggle="modal" data-target="#myModalPassword" href="#updatePassword">Update Account Passoword</a>
            <!-- Profile Modal -->
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Upload Your Profile</h4>
                  </div>
                  <div class="modal-body">
                    <?php include(BASE_PATH.'update_profile.php'); ?>
                  </div>

                </div>
              </div>
            </div>
            <!-- Password Modal -->
            <div class="modal fade" id="myModalPassword" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Change Your Password</h4>
                  </div>
                  <div class="modal-body">
                      <div class="moduoal_password_wrap"></div>
                  </div>

                </div>
              </div>
            </div>

            <div class="padding-line-50"></div>
            <label>Member Information</label>
            <hr>
            <form class="form_user_info">
                <input  type="hidden" name="return-url" value="<?php echo $return_url; ?>"/>
                <div class="form-group row">
                    <label for="Name" class="col-sm-1 col-form-label">Name</label>
                    <div class="col-sm-3">
                    <input type="text" class="form-control" name="name" id="name" value="<?php echo $User['name'];?>">
                    </div>
                    <label for="Email" class="col-sm-1 col-form-label">Gender</label>
                    <div class="col-sm-3">
                        <?php
                        $male='';
                        $female='';

                            if($User['sex']=='male'){
                                $male = 'selected="selected"';
                            }else if($User['sex']=='female'){
                                $female = 'selected="selected"';
                            }
                        ?>
                        <select class="form-control" name="gender" id="gender">
                          <option <?php echo $male;?> value="male">Male</option>
                          <option <?php echo $female;?> value="female">Female</option>
                        </select>
                    </div>
                </div>

                  <div class="form-group row">
                      <label for="Email" class="col-sm-1 col-form-label">Email</label>
                      <div class="col-sm-3">
                        <input type="email" class="form-control" name="email" id="email" value="<?php echo $User['email'];?>">
                      </div>
                      <label for="Phone" class="col-sm-1 col-form-label">Phone</label>
                        <div class="col-sm-3">
                        <input type="tel" class="form-control" value="<?php echo $User['phone'];?>"  name="phone" id="phone">
                        <div class="verification_wrap">
                            <div class="clearfix_20"></div>
                            <input type="hidden" name="login_type" value="phone">
                            <input type="text" name="user_verify_code" class="form-control user_verify_code" placeholder="Verification code" autocapitalize="off">
                            <small class="resend_wait_info">You can resend code in <b id="count_down_resend" style="color: #0839ec; ">60</b>s</small>
                            <small class="resend_code_info"><a href="javascript:;" class="_recall_update_code">Click here </a> to resend verification code</small>
                      </div>
                    </div>
                  </div>
                <div class="form-group row">

                    <label for="Address" class="col-sm-1 col-form-label">Address</label>
                    <div class="col-sm-3">
                    <input type="text" class="form-control" value="<?php echo $User['address'];?>"  name="address" id="address">
                    </div>
					<label for="Address" class="col-sm-1 col-form-label">Bank Account</label>
                    <div class="col-sm-3">
                    <input type="number" class="form-control" value="<?php echo $User['bank_id'];?>"  name="bank" id="bank_id">
                    </div>
                </div>
				
                <hr>
                <input class="btn-larg center-block btn_save_user_info" type="button" name="save-user-info" value="Save Change"/>
            </form>
        </div>
    </div>
<script type="text/javascript">
    $("#changePassword").click(function(){
        $.ajax({
            url:"<?php echo BASE_URL.'checking.php'?>",
            type:"POST",
            async:"false",
            data:{
                "request_changePassword":1
            },
            success: function(data){
                $(".moduoal_password_wrap").html(data);
            }
        });
    });
    $("._recall_update_code").click(function(){
        $(".user_verify_code").val('');
        $(".btn_save_user_info").click();
    });
    $(".btn_save_user_info").click(function(e){
        var getFormData = $(".form_user_info").serialize();
        $.ajax({
            url:"<?php echo BASE_URL.'ajax.user_update_info'?>",
            type:"POST",
            async:"false",
            dataType: 'json',
            data:getFormData,
            success: function(e){
                if(!e.hasError){
                    toastr.success(e.status);

                    if(typeof e.verify_code != 'undefined' && e.verify_code){
                    
                        $("body").find(".verification_wrap").addClass('show');
                        $("body").find(".resend_wait_info").addClass('show');
                        $("body").find(".resend_code_info").removeClass('show');
                        var count_num = 60;
                        count_int = setInterval(() => {
                            count_num--;
                            $("body").find("#count_down_resend").text(count_num);
                                if(count_num == 0) {
                                    clearInterval(count_int);
                                    $("body").find("#count_down_resend").text(60);
                                    $("body").find(".resend_wait_info").removeClass('show');
                                    $("body").find(".resend_code_info").addClass('show');
                                    $("body").find("#error_form_msg.red").remove();
                                    $(".btn_register").prop('disabled',false);
                                }
                        }, 1000);
                        
                    }else{
                        clearInterval(count_int);
                        $("body").find("#count_down_resend").text(60);
                        $("body").find(".verification_wrap").removeClass('show');
                        $("body").find(".resend_wait_info").removeClass('show');
                        $("body").find(".resend_code_info").removeClass('show');
                        $(".user_verify_code").val('');
                        $(".btn_update_info").addClass('disabled');
                        // $("body").find("._user_name").text(getUserName);

                    }

                }else{
                    toastr.error(e.status);
                }
            }
        });
    });
</script>
 <?php
  }
 /**** IN GOD WE TRUST ****/
?>
