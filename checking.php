<?php
/****** CITA ******
 * CODING: HCK0011 / 2017-01-06
 * Description: To Check condition if corrected to submit another process
 */
 session_start();
 include_once '_config_inc.php';
 include_once BASE_PATH.'_libs/site_class.php';
 $db = new gen_class($configs);
if(isset($_POST['before_request'])){
    $before_request = $_POST['before_request'];
	$return_url = urldecode($_POST['return-url']);
    if($before_request=='true'){
        echo'<form class="submit_order" action="'.BASE_URL.'request_order.php" method="post">
            <input  type="hidden" name="return-url" value="'.$return_url.'"/>
            <button type="submit" class="btn-larg center-block" name="send-request">Request Order</button>
            <button type="button" class="btn-larg center-block" name="paynow">Pay Now <img src="'.BASE_URL.'assets/imgs/visa_master.png"></button>
        </form>
		<style>
		
			.submit_order button{
				border-radius: 50px;
			}
			.submit_order button img{
				width: 100px;
				margin-top: -3px;
			}
			.submit_order button[name="paynow"]{
				background-color:#fff;
				color: #3ab6ff;
				border: 2px solid #3ab6ff;
				font-weight: bold;
				font-size: 15px;
				line-height: 40px;
			}
		
		</style>
		';
    }else{
        echo'<small style="color:red">Please confirm to proceed your order.</small>';
    }
}elseif (isset($_POST['user_info']) && !empty($_POST['return-url'])) {
    $return_url = urldecode($_POST['return-url']);
    $email = $_POST['email'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $sex = $_POST['sex'];
    $address = $_POST['address'];

    $_SESSION['temp']['email'] 	= $email;
	$_SESSION['temp']['name']= $name;
	$_SESSION['temp']['phone'] 	= $phone;
    $_SESSION['temp']['sex'] 	= $sex;
    $_SESSION['temp']['address'] 	= $address;

    if($email == '' || $name == '' || $sex == '' || $phone== '' || $address == ''){
        $_SESSION['error'] = 1;
        $_SESSION['msg']   = 'All field required.';
    }else{
        $_SESSION['user_info'] = 'completed';
        $_SESSION['user_complete_step']='true';
    }
    header("Location:$return_url");
}elseif (isset($_POST['request_changePassword'])){

    ?>
    <form class="change_Password_form" action="<?php echo BASE_URL.'checking.php'?>" method="post">
        <div class="form-group row">
            <label for="Password" class="col-sm-4 col-form-label">Your New Passwrod</label>
            <div class="col-sm-7">
            <input type="password" class="form-control" name="new-password" id="new-password" value="">
            </div>
        </div>
        <div class="form-group row">
            <label for="Password" class="col-sm-4 col-form-label">Re-type Passwrod</label>
            <div class="col-sm-7">
            <input type="password" class="form-control" name="confirm-new-password" id="confirm-new-password" value="">
            </div>
        </div>
        <hr>
        <small id="status"></small>
        <input id="changePassword_action" class="btn-small center-block" name="changePassword_action" type="button" value="Change Your Password"/>
    </form>
    <?php
}elseif (isset($_POST['changePassword_action'])) {
    $user_id = $_SESSION['user_id'];

    // $old_password = $_POST['old-password'];
    $new_password = $_POST['new-password'];
    $confirm_password = $_POST['confirm-new-password'];

    if($new_password=='' || $confirm_password==''){
        echo 'All fields required.';
    }else if(strlen($new_password) < 8){
        echo 'Password required at least 8 characters';
    }
    else if($new_password!=$confirm_password){
        echo 'Your confirm password are not matched.';
    }else{
		echo 'Your password has been changed';
        $data = array(
            'password' => md5($new_password)
        );
        $db->where('id',$user_id);
        $db->update('tbl_user_register',$data);
        
    }
}
?>
<script type="text/javascript">

    $('body').on('click',"#changePassword_action",function(){

        var formData = $(".change_Password_form").serialize();
        $.ajax({
            url:"<?php echo BASE_URL.'checking.php'?>",
            type:"POST",
            async:"false",
            data:formData+'&changePassword_action=1',
            success: function(data){
                $("#status").html(data);
            }
        });
    });
</script>

<?php
/**** IN GOD WE TRUST ****/
?>
