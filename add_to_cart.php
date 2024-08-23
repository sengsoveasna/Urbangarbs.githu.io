<?php
/****** CITA ******
 * CODING: HCK0011 / 2016-12-27
 * Description: Add To Cart Process
 */
 session_start();
 include_once '_config_inc.php';
 include_once BASE_PATH.'_libs/site_class.php';
 $db = new gen_class($configs);

 if(isset($_POST['add-to-cart'])){
     $product_id = $_POST['product_id'];
     $quantity = $_POST['quantity'];
     $sku_attr = $_POST['sku_attr'];
     $login_status = $_POST['login_status'];
     $new_quantity_list ='';
     $totalCart = 0;
     if($login_status == 'false'){ //User add to cart as customer, store to cookie


      
        //update 2019-06-03

        $return_url = @$_POST['return_url'];
	 ?>
		
		<span>Please <a href="<?php echo BASE_URL.'login'?>">Login</a> to your add item in your shopping cart.</span>
		
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


            // $UpdatedList = '';
            // $UpdatedQunatity = '';
            // $productExist = 'false';
            
            // $cookieProductList = '0';
            // $cookieProductQuantity = '0';
            // $cookieProductSKU = '0';
            // $countCart = 0;
			// //  Get Current Cookies
            // if(isset($_COOKIE['_Pl']) && isset($_COOKIE['_Pq']) && isset($_COOKIE['_Ps'])){
            //     $cookieProductList = base64_decode($_COOKIE['_Pl']);
            //     $cookieProductQuantity = base64_decode($_COOKIE['_Pq']);
            //     $cookieProductSKU = base64_decode($_COOKIE['_Ps']);
            // }
            // $ProductsList = explode(',',$cookieProductList);
            // $ProductsQuantity = explode(',',$cookieProductQuantity);
            // $ProductsSKU = explode(',',$cookieProductSKU);
            // $countCart = count($ProductsList);
            // //  Start Set New Cookies Value
            // // check if it's first product, Zero is default cart empty
            // if($ProductsList[0] == '0' || $ProductsQuantity[0] == '0'){
            //     $UpdatedList = $product_id;
            //     $UpdatedQunatity = $quantity;
            //     $UpdatedSKU = $sku_attr;
            //     $countCart = 1;
            // }else{
            //     $countCart = $countCart + 1;
            //     $UpdatedList = $product_id.','.$cookieProductList;
            //     $UpdatedQunatity = $quantity.','.$cookieProductQuantity;
            //     $UpdatedSKU = $sku_attr.','.$cookieProductSKU;

          	// 	foreach($ProductsList as $key=>$product) {
          	// 		// find index that exist when user already added to cart just update qunantity only
          	// 			if($product==$product_id && in_array($sku_attr,$ProductsSKU)){
            //                 $productExist = 'true';
            //                 $index_to_edit = $key;
          	// 			}
          	// 	}

    		// 	if($productExist=='true'){
    		// 		// set new quantity value to product existed
    		// 		$ProductsQuantity[$index_to_edit] = $quantity;
    		// 		$UpdatedQunatity = implode($ProductsQuantity,',');
            //         $countCart = count($ProductsList);
                    
    		// 	}
            // }

            // if($productExist=='false'){ //update product list when product is not added to cart already only
            //     setcookie ("_Pl", base64_encode($UpdatedList), time() + (86400 * 30), '/');
            //     setcookie ("_Ps", base64_encode($UpdatedSKU), time() + (86400 * 30), '/');
            // }
            // setcookie ("_Pq", base64_encode($UpdatedQunatity), time() + (86400 * 30), '/');

            // $totalCart = $countCart;

     }else{ //user logged in

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
         $data = array(
             "id" => $primaryKey ,
             "buy_type" => 'add-to-cart',
             "user_id" => $user_id,
             "product_id" => $product_id,
             "product_sku" => $sku_attr,
             "quantity" => $quantity,
             "order_date" => $dateNow
         );

         //Check if product exist just update qunantity
         $db->where('buy_type','add-to-cart');
         $db->where('product_sku',$sku_attr);
         $db->where('product_id',$product_id);
         $db->where('user_id',$user_id);
         $findExist = $db->getOne('tbl_user_buy');

         if(empty($findExist)){
             //if not exist, insert new cart item
            $db->insert('tbl_user_buy',$data);
        }else{
            // if exist update quantity and date
            $Udatedata = array(
                "quantity" => $quantity,
                "order_date" => $dateNow
            );
            $db->where('buy_type','add-to-cart');
            $db->where('product_id',$product_id);
            $db->where('user_id',$user_id);
            $db->update('tbl_user_buy',$Udatedata);
        }
        $db->where('buy_type','add-to-cart');
        $db->where('user_id',$user_id);
        $getCart = $db->get('tbl_user_buy');

        $totalCart = count($getCart);

        echo '<i class="fa fa-check-circle" aria-hidden="true"></i> A new item has been added to your Shopping Cart. You now have '.$totalCart.' items in your Shopping Cart.
            <span id="totalCart" count-cart="'.$totalCart.'"></span>
        ';
     }

     
 }
/**** IN GOD WE TRUST ****/
?>
