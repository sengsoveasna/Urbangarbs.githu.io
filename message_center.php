<?php
/****** CITA ******
 * CODING: HCK0011 / 2017-01-14
 * Description: Message Center from admin to user
 */
	$user_id = $_SESSION['user_id'];
	$db->orderBy('id','DESC');
	$db->where('to_user_id',$user_id);
	$getMessage = $db->get('tbl_message_center');

	$db->where('status',1);
	$db->where('to_user_id',$user_id);
	$getNewMessage = $db->get('tbl_message_center');

	$countNewMessage = count($getNewMessage);
?>
<h3>Message Center<small id="unread_count"> (<?php echo $countNewMessage;?> unread)</small></h3>
                  <table class="table user-profile">
                      <thead>
                          <tr>
                              <th></th>
                              <th>Title</th>
                              <th>Date</th>
                              <th></th>
                          </tr>
                      </thead>
                      <tbody>

					  <?php
					  $i=0;
						foreach($getMessage as $message){
							$i++;
							$unread = '';
							if($message['status']==1){
								$unread = 'unread';
							}
					  ?>
                        <tr data-toggle="modal" class="message <?php echo $unread;?>" message-id="<?php echo $message['id'];?>">
                            <td scope="row"><?php echo $i;?></th>
                            <td><?php if($message['status']==1) echo'<span class="new">(New) &ensp;</span>'; echo limitText($message['subject'],50);?></td>
                            <td><?php echo $message['date'];?></td>
                            <td align="center"><i class="fa fa-trash-o delete-message" aria-hidden="true" title="Delete this message"></i></td>
						</tr>
					<?php
						}
					?>
                      </tbody>
                  </table>

	              <!-- Profile Modal -->
	              <div class="modal fade" id="readMessage" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	                <div class="modal-dialog" role="document">
	                  <div class="modal-content">
	                    <div class="modal-header">
	                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	                      <h4 class="modal-title" id="myModalLabel">Message Center</h4>
	                    </div>
	                    <div class="modal-body">
	                      <div id="message_display"></div>
	                    </div>

	                  </div>
	                </div>
	              </div>

<script>
$(".delete-message").click(function(e){
	var message_id = $(this).closest('.message').attr('message-id');
	$(this).closest('.message').remove();

	$.ajax({
		url:"<?php echo BASE_URL.'read_message.php'?>",
		type:"POST",
		async:"false",
		data:{
			"delete_message":1,
			"message_id":message_id
		},
		success: function(data){

		}
	});
});
$(".message td").click(function(){

	$("#readMessage").modal();

	var message_id = $(this).closest('.message').attr('message-id');
	$(this).closest('.message').find('.new').remove();
	$(this).closest('.message').removeClass('unread');

		$.ajax({
			url:"<?php echo BASE_URL.'read_message.php'?>",
			type:"POST",
			async:"false",
			data:{
				"read_message":1,
				"message_id":message_id
			},
			success: function(data){
				$("#message_display").html(data);
			}
		});

});


</script>

 <?php
/**** IN GOD WE TRUST ****/
?>
