<?php
	/****** CITA ******
	* CODING: HCK0011 / 2017-01-10
	* Description: Crop Image nad Update user Profile picture
	*/
	include_once '_config_inc.php';
	include_once BASE_PATH.'_libs/site_class.php';
	$db = new gen_class($configs);
	
	
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file']))
	{
		if(session_id() == ''){
            //session has not started
            session_start();
        }
		$user_id = $_SESSION['user_id'];
		$targ_w = $targ_h = 250;
		$src = $_FILES['file']['tmp_name'];
		$file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		
		switch(strtolower($file_extension)) {
			case 'jpg' : case 'jpeg' :
				$img_r = imagecreatefromjpeg($src);
			break;
			case 'png' :
				$img_r = imagecreatefrompng($src);
			break;
		}
		
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		$imageName = uniqid();
		$imageName = md5($imageName.$user_id);

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		switch(strtolower($file_extension)) {
			case 'jpg' : case 'jpeg' :
				imagejpeg($dst_r,BASE_PATH.'files/users/'.$imageName.'.'.strtolower($file_extension),100);
			break;
			case 'png' :
				imagepng($dst_r,BASE_PATH.'files/users/'.$imageName.'.'.strtolower($file_extension),9);
			break;
		}		
		
		echo BASE_URL.'files/users/'.$imageName.'.'.strtolower($file_extension);
		
		
		// Insert Image name to user table
		$data = array(
			"image"=>$imageName.'.'.strtolower($file_extension)
		);
		
		if(!isset($_POST['save-user-info'])){
			$db->where('id',$user_id);
			$db->update('tbl_user_register',$data);
			$_SESSION['user_image'] = $imageName.'.'.strtolower($file_extension);
		}
		
		
		exit;
	}
?>
	<script src="<?php echo BASE_URL.'js/jquery.Jcrop.js'; ?>"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL.'css/jquery.Jcrop.css'; ?>"/>

	<style type="text/css">
		.crop-box {
			width: 100%;
			min-height: 150px;
			background: #797979;
			display: inline-block;
			position: relative;
		}
		.jcrop-holder {
			margin:0 auto;
		}
	</style> 
 
	<div class="crop-box">
		<img src="" class="img_preview" id="img_crop" alt=""/>
	</div>

	<form action="" type="post" id="data_crop">
		 <input type="hidden" id="x" name="x" />
		 <input type="hidden" id="y" name="y" />
		 <input type="hidden" id="w" name="w" />
		 <input type="hidden" id="h" name="h" /> 
		 <input type="submit" value="Browse"     id="btn_browse"  class="btn-small" style="margin-top:0;width:49.3%;border-radius:0;"/>
		 <input type="submit" value="Crop Image" id="crop_submit" class="btn-small" style="margin-top:0;width:49.3%;border-radius:0;float:right;"/>
		 <input type="file" id="browse" name="file" style="display:none;"/>
	</form>  
 
	<script>
		$(function(){
			$('body').on('click', '#crop_submit', function(e){
				e.preventDefault();
				
				if(checkCoords()) {
					var formData = new FormData($('#data_crop')[0]);
									
					$.ajax({
						url : '<?php echo BASE_URL.'update_profile.php'?>',
						type: 'post',
						data: formData,
						processData: false,
						contentType: false,				
						success : function(msg){						
							$(".img-profile").attr('src',msg + '?' + new Date().getTime());
							$(".account-wrap img").attr('src',msg + '?' + new Date().getTime());
							$("#myModal").modal('hide');						
						},
						error : function(obj,err,msg){
							alert(msg);
						}
					});
				}
			});
			
			$('#myModal').on('hidden.bs.modal', function (e) {
				$('.img_preview').removeAttr('src');
				jcrop_api.destroy();
				resetCoords();
			});		
			
		});
	</script>
 	<script>
		var jcrop_api;
		
		function readURL(input) {
			var fileExtension = $("#browse").val().split('.').pop();
			if(fileExtension.toLowerCase() =='jpg' || fileExtension.toLowerCase() =='jpeg' || fileExtension.toLowerCase() =='png') {
				if (input.files && input.files[0]) {
					var reader = new FileReader();
					reader.onload = function (e) {
						$('.img_preview').attr('src', e.target.result);
						jcrop_api.setImage(e.target.result);
					}
					reader.readAsDataURL(input.files[0]);
				}
				
				/* Initial Jcrop */
				resetCoords();
				$('#img_crop').Jcrop({
					boxWidth: 350,
					boxHeight: 350,
					onSelect: updateCoords,
					aspectRatio: 1
				}, function(){
				  jcrop_api = this;
				});
			}
		}		

		$("#browse").change(function(){
			readURL(this);			
		});
		
		$("#btn_browse").click(function(e){
			e.preventDefault();
			$("#browse").click();
		});

		function updateCoords(c)
		{
			$('#x').val(c.x);
			$('#y').val(c.y);
			$('#w').val(c.w);
			$('#h').val(c.h);
		}
		function resetCoords(c)
		{
			$('#x').val('');
			$('#y').val('');
			$('#w').val('');
			$('#h').val('');
		}
		function checkCoords()
		{
			if (parseInt($('#w').val())) return true;
			alert('Please select a crop region then press Crop Image.');
			return false;
		};		
	</script>
 
<?php
/**** IN GOD WE TRUST ****/
?>
