<?php
/****** CITA ******
 * CODING: HCK0011 / 2019-09-27
 * Description: Get Order Detail of member referral
 */
 session_start();
 include_once '_config_inc.php';
 include_once BASE_PATH.'_libs/site_class.php';
 $db = new gen_class($configs);
 
 if(!isset($_SESSION['user_id'])) die("Session expired");
 
 if(isset($_POST['view_detail']) && isset($_POST['iv_id'])){
	 
    $invoice_id = $_POST['iv_id'];
	
	
	$db->where('invoice_id',$invoice_id);
	$getInvoiceDetail = $db->get('tbl_invoice_detail');
	
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
}
?>
    <div class="invoice-wrap col-sm-12" style="border:none">

		<div style="width: 100%; height: 30px; display:block;"></div>
		<h4 align="center">Order Detail</h4>
        <table class="table table-striped">
            <thead>
                <tr class="table-header">
                <th width="250">Code</th>
                <th width="500">Product Name</th>
                <th width="110">Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php
					$AllTotal=0;
					foreach($getInvoiceDetail as $invoice_detail){
						$db->where('id',$invoice_detail['product_id']);
						$product = $db->getOne('tbl_products');
						
							// $quantity = $item['quantity'];
								// $quantity = 1;
								
								
								// $manual_score = $invoice_detail['score'];
								// if($invoice_detail['score'] == '') $manual_score = '1,1,1,1,1';

								// $manual_score_list = explode(',',$manual_score);
								// $firstScore		= ($manual_score_list[1] * $quantity);
								// $secondScore	= ($manual_score_list[2] * $quantity);
								// $thirdScore		= ($manual_score_list[3] * $quantity);
								// $forthScore		= ($manual_score_list[4] * $quantity);
								
								// if(empty($getFirstUser)){
									
									// $score = ($productCount * 4) + $firstScore;

								// }elseif(!empty($getFirstUser) && empty($getSecondUser)){
									
									// $score = ($productCount*3) + $secondScore;
									
								// }elseif(!empty($getSecondUser) && empty($getThirdUser)){
								
									// $score = ($productCount * 2) + $thirdScore;
									
								// }else{
									
									// $score = ($productCount * 1 ) + $forthScore;
									
								// }
								
								// $totalScore += ($score * $invoice_detail['quantity']) ;
								
								$totalQty += $invoice_detail['quantity'];

								$skuInfo = '';
								if($invoice_detail['product_sku_title'] != ""){
									$skuInfo = '('.$invoice_detail['product_sku_title'].')';
								}
						
							?>
							<tr>
								<td><?php echo $product['code']; ?></td>
								<td><div class="title"><?php echo $product['title'].$skuInfo; ?></div></td>
								<td align="center"><?php echo $invoice_detail['quantity']; ?></td>
							</tr>
							<?php
							
					}
                ?>
				<tr class="total_row1">
					<td colspan="2">Total :</td>
					<td class="qty"><?php echo $totalQty.' items';?></td>
				</tr>
            </tbody>
        </table>
        
    </div>
	<div class="clearfix"></div>
		
	<style>
		
		.total_row1{
			text-align:center;
			border-top: 2px solid #08bbff;
			font-weight: bold;
			font-size: 15px;
			color: #f00;
		}
		.total_row1 td:first-child,.shipping_row td:first-child{
			text-align:right;
		}
		
	</style>

 <?php
/**** IN GOD WE TRUST ****/
?>
