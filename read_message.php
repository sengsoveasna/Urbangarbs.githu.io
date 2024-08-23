<?php
/****** CITA ******
 * CODING: HCK0011 / 2017-01-16
 * Description: Get Message Detail
 */
 session_start();
 include_once '_config_inc.php';
 include_once BASE_PATH.'_libs/site_class.php';
 $db = new gen_class($configs);
 if(isset($_POST['read_message'])){
    $message_id = $_POST['message_id'];

    $db->where('id',$message_id);
    $getMessage = $db->getOne('tbl_message_center');

    $data = array(
        'status' => '0'
    );
    $db->where('id',$message_id);
    $db->update('tbl_message_center',$data);


    $user_id = $_SESSION['user_id'];

	$db->where('status',1);
	$db->where('to_user_id',$user_id);
	$getNewMessage = $db->get('tbl_message_center');

	$countNewMessage = count($getNewMessage);

}elseif(isset($_POST['delete_message'])){
    $message_id = $_POST['message_id'];

    $db->where('id',$message_id);
    $db->delete('tbl_message_center');

    $user_id = $_SESSION['user_id'];

	$db->where('status',1);
	$db->where('to_user_id',$user_id);
	$getNewMessage = $db->get('tbl_message_center');

	$countNewMessage = count($getNewMessage);
}
?>
<strong>Subject : </strong>  <span> <?php echo $getMessage['subject']; ?></span>
<hr>
<div class="well-sm" style="min-height:400px;">
    <i class="date"><?php echo 'Administator - '.$getMessage['date'];?></i>
    <div class="clearfix">

    </div>
    <p><?php echo $getMessage['detail'];?></p>
</div>

<style media="screen">
    .date{
        float: right;
        color:#ccc;
    }
</style>
<script type="text/javascript">
var unread_message = '<?php echo $countNewMessage;?>';
    $('body').find("#unread_count").text(' ('+unread_message+' unread)');
    $('body').find(".message-notify-count").text(unread_message);
</script>
 <?php
/**** IN GOD WE TRUST ****/
?>
