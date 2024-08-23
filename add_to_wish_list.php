<?php
/****** CITA ******
 * CODING: HCK0011 / 2017-01-05
 * Description: Add To Wish List
 */
 session_start();
 include_once '_config_inc.php';
 include_once BASE_PATH.'_libs/site_class.php';
 $db = new gen_class($configs);

 $login_status = 'false';
 if(isset($_SESSION['login_status'])){
     $login_status = $_SESSION['login_status'];
 }
 if($login_status=='true'){
    $user_id = $_SESSION['user_id'];
    function getPrimaryKey($db){
        $result = $db->getOne("tbl_user_buy","MAX(`id`) AS id");
        if(empty($result['id'])){
            $result['id'] = 0;
        }
        return ((int)$result['id'] + 1);
    }
    $primaryKey       = getPrimaryKey($db);
    $dateNow = date('Y-m-d H:i:s');

    if(isset($_POST['add-to-whis-list'])){

        $product_id = $_POST['product_id'];

        $data = array(
            "id" =>$primaryKey ,
            "buy_type"=>'wish-list',
            "user_id"=>$user_id,
            "product_id"=>$product_id,
            "quantity"=>'0',
            "order_date"=>$dateNow
        );
        // In Case Existed remove
        $db->where('user_id',$user_id);
        $db->where('buy_type','wish-list');
        $db->where('product_id',$product_id);
        $db->delete('tbl_user_buy');

        // and then insert
        $db->insert('tbl_user_buy',$data);

        $db->where('user_id',$user_id);
        $db->where('buy_type','wish-list');
        $getWishList = $db->get('tbl_user_buy');
        $totalWish = count($getWishList);
        echo '<i class="fa fa-check-circle" aria-hidden="true"></i> A new item has been added to your Wish List. You now have '.$totalWish.' items in your Wish List.
           <span id="totalCart" count-cart="'.$totalWish.'"></span>
        ';
    }
 }else{
	$return_url = @$_POST['return_url'];
	 ?>
		
		<span>Please <a href="<?php echo BASE_URL.'login'?>">Login</a> to your account to view items in your wish list.</span>
		
		<hr>
		
		<form action="<?php echo BASE_URL.'user_login.php';?>" method="post" class="col-sm-12 form-wrap" style="padding-top:0px;">
		<input type="hidden" name="add-to-whis-list">
		<input type="hidden" name="product_id" value="<?php echo @$_POST['product_id'];?>">
                <input  type="hidden" name="return-url" value="<?php echo $return_url; ?>"/>
                    <strong class="info-title">Sign In to Your Account</strong>
                <div class="clearfix"></div>
                <dl class="input-wrap">
                    <dt><i class="fa fa-envelope" aria-hidden="true" style="color:#000"></i></dt>
                    <dd>
                        <input type="email" style="font-weight:normal" placeholder="Email Address" name="email" value="<?php if(isset($_SESSION['re']['email'])) echo $_SESSION['re']['email'];?>" required>
                    </dd>
                </dl>
                <dl class="input-wrap">
                    <dt><i class="fa fa-unlock" aria-hidden="true" style="color:#000"></i></dt>
                    <dd>
                        <input type="password" style="font-weight:normal" placeholder="Password (at least 8 digits)" name="password" pattern=".{8,}" required>
                    </dd>
                    <dd style="float:right; margin-right:20px;">
                        <a href="<?php echo BASE_URL.'reset-password'?>" style="font-weight:normal">Forgot Password?</a>
                    </dd>
                </dl>
                <div class="clearfix"></div>
                <button class="btn-larg center-block" name='login' type="submit">Sign In</button>
        </form>
	 
	 <?php
 }
/**** IN GOD WE TRUST ****/
?>
