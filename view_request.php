<?php
/****** CITA ******
 * CODING: HCK0011 / 2017-02-01
 * Description: Get Request Detail
 */
 session_start();
 include_once '_config_inc.php';
 include_once BASE_PATH.'_libs/site_class.php';
 $db = new gen_class($configs);
 
 if(!isset($_SESSION['user_id'])) die("Session expired");
 
 if(isset($_POST['view_request'])){
    $request_id = $_POST['request_id'];
    $invoice_id = $_POST['invoice_id'];
	
	
	$db->where('invoice_id',$invoice_id);
	$getInvoiceDetail = $db->get('tbl_invoice_detail');
	
	//Get Shipping Detail
	$db->where('id',$request_id);
	$getShipping = $db->getOne('tbl_request_order');
}

// generate product score
	$currentUserID = $_SESSION['user_id'];
	$getFirstUser=null;
	$getSecondUser=null;
	$getThirdUser=null;
	
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

	$productCount = 0;
	
	$multiScore=0;
	$firstScore=0;
	$secondScore=0;
	$thirdScore=0;
	$forthScore=0;
	$manual_score_list = '';
	
	$score = 0;
	$totalScore = 0;
	$totalQty = 0;
	$totalWhosale = 0;
	$totalProfit = 0;

?>
    <div class="invoice-wrap col-sm-12" style="border:none">
	
		<div class="shipping_detail_info">
			
			<?php 
			
				//Payment info
				$paymentMethod = 'Pay on delivery';
				if($getShipping['paymentMethod'] == 1){
					$paymentMethod = 'Pay by creadit card';
				}
				
				//Delivery info
				$isDelivered = 'class="delivered"';
				$deliveryStatus = 'Picked up by Buyer';
				
				if($getShipping['delivery'] == 1){
					$deliveryStatus = 'Delivered';
				}else{
					$isDelivered = 'class="undelivered"';
				}
				
				if($getShipping['shippingMethod'] == 2){
					$deliveryStatus = 'Delivered to Customer';
				}else{
					$deliveryStatus = 'Picked up by Buyer';
				}
				
				$deliveryDate = 'Unknown';
				if(!empty($getShipping['deliveryDate'])){
					$deliveryDate = date("d-M-Y h:i a",strtotime($getShipping['deliveryDate']));
				}
				
				if($getShipping['delivery'] == 0) $deliveryStatus = 'Pending';
				
				$shippingCost = 'Free';
				if($getShipping['shippingPrice'] > 0){
					$shippingCost = '$'.$getShipping['shippingPrice'];
				}
				
				$shippingMethodDetail = 'Pickup by Buyer';
				if($getShipping['shippingMethod'] == 2){
					$shippingMethodDetail = 'Ship by company';
				}
				
			?>
			<b>Order Date/Time: </b>
			<span><?php echo date("d-M-Y h:i a",strtotime($getShipping['date']));?></span>
			<br>
			<b>Payment method: </b>
			<span><?php echo $paymentMethod;?></span>
			<br>
			<b>Sender: </b>
			<span><?php echo !empty($getShipping['senderPhone']) ? $getShipping['senderPhone'] : 'N/A' ;?></span>
			<br>
			<b>Receiver: </b>
			<span><?php echo !empty($getShipping['receivePhone']) ? $getShipping['receivePhone'] : $getShipping['phone'];?></span>
			<br>
			<b>Shipping Method: </b>
			<span><?php echo $shippingMethodDetail;?></span>
			<br>
			<b>Delivery address: </b>
			<span><?php echo !empty($getShipping['shippingAddress']) ? $getShipping['shippingAddress'] : $getShipping['address'];?></span>
			<br>
			<b>Delivery status: </b>
			<span <?php echo $isDelivered;?>><?php echo $deliveryStatus;?></span>
			<br>
			<b>Delivery Date/Time: </b>
			<span><?php echo $deliveryDate;?></span>
			<br>
			<b>Note: </b>
			<span><?php echo $getShipping['otherNote'];?></span>
		
		</div>
		<div style="width: 100%; height: 30px; display:block;"></div>
		<h4 align="center">Order Detail</h4>
        <table class="table table-striped">
            <thead>
                <tr class="table-header">
                <th width="250">Code</th>
                <th width="500">Product Name</th>
                <th width="110">Quantity</th>
                <th width="110">Score</th>
                <th width="320" style="text-align:center">Wholesale Price</th>
                <th width="320" style="text-align:center">Sale Price</th>
                <th style="text-align:center">Profit</th>
                </tr>
            </thead>
            <tbody>
                <?php
					$AllTotal=0;
					foreach($getInvoiceDetail as $invoice_detail){
						$db->where('id',$invoice_detail['product_id']);
						$product = $db->getOne('tbl_products');
						
						$salePrice = $invoice_detail['retail_price'];
						$whosale_price = $invoice_detail['member_price'];
						
							// $quantity = $item['quantity'];
								$quantity = 1;
								
								$profit = ($salePrice - $whosale_price) * $quantity;
								
								//create product score
								
								$multiScore = $product['multi_score'];
								$manual_score = $invoice_detail['score'];
								if($invoice_detail['score'] == '') $manual_score = '1,1,1,1,1';

								$manual_score_list = explode(',',$manual_score);
								$firstScore		= ($manual_score_list[1] * $quantity);
								$secondScore	= ($manual_score_list[2] * $quantity);
								$thirdScore		= ($manual_score_list[3] * $quantity);
								$forthScore		= ($manual_score_list[4] * $quantity);
								
								if(empty($getFirstUser)){
									
									$score = ($productCount * 4) + $firstScore;

								}elseif(!empty($getFirstUser) && empty($getSecondUser)){
									
									$score = ($productCount*3) + $secondScore;
									
								}elseif(!empty($getSecondUser) && empty($getThirdUser)){
								
									$score = ($productCount * 2) + $thirdScore;
									
								}else{
									
									$score = ($productCount * 1 ) + $forthScore;
									
								}

								$totalScore += ($score * $invoice_detail['quantity']) ;
								$totalQty += $invoice_detail['quantity'];
								$totalWhosale += ($whosale_price * $invoice_detail['quantity']);
								$totalProfit += ($profit * $invoice_detail['quantity']);

								$skuInfo = '';
								if($invoice_detail['product_sku_title'] != ""){
									$skuInfo = '('.$invoice_detail['product_sku_title'].')';
								}
						
							?>
							<tr class="invoice-item-wrap">
								<td><?php echo $product['code']; ?></td>
								<td><div class="title"><?php echo $product['title'].$skuInfo; ?></div></td>
								<td align="center"><?php echo $invoice_detail['quantity']; ?></td>
								<td align="center"><?php echo $score; ?></td>
								<td align="center"><?php echo (number_format($invoice_detail['member_price'],2)+0);?></td>
								<td align="center"><?php echo (number_format($invoice_detail['retail_price'],2)+0);?></td>
								<td align="center"><?php echo (number_format($profit,2)+0); ?></td>
							</tr>
							<?php
							
								$AllTotal = $AllTotal + ($invoice_detail['retail_price'] * $invoice_detail['quantity']);
					}
                ?>
				<tr class="total_row">
					<td colspan="2">Total :</td>
					<td class="qty"><?php echo $totalQty;?></td>
					<td class="score"><?php echo $totalScore;?></td>
					<td class="whosale"><?php echo '$'.(number_format($totalWhosale,2)+0);?></td>
					<td class="total"><?php echo '$'.(number_format($AllTotal,2)+0);?></td>
				
					<td class="totalProfit"><?php echo '$'.(number_format($totalProfit,2)+0);?></td>
				</tr>
				<tr class="shipping_row">
					<td colspan="2">Shipping :</td>
					<td colspan="5"><?php echo $shippingCost;?></td>
				</tr>
            </tbody>
        </table>
        
    </div>
	<div class="clearfix"></div>
		
	<style>
		
		.total_row{
			text-align:center;
			border-top: 2px solid #08bbff;
			font-weight: bold;
			font-size: 15px;
			color: #f00;
		}
		.total_row td:first-child,.shipping_row td:first-child{
			text-align:right;
			color: #f00;
			font-weight: normal;
		}
		.shipping_row{
			background-color:#fff !important;
			border-top: none;
			font-size: 12px;
		}
		
		.invoice-wrap .table-striped tbody tr:nth-last-child(3){
			height: 100px;
		}
		span.delivered{
			color:#16ad5b;
		}
		span.undelivered{
			color:#ff0016;
		}
	</style>

 <?php
/**** IN GOD WE TRUST ****/
?>
