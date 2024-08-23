<?php
session_start();
function send_mail($to, $email,  $name, $subject, $messages){
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
	$headers .= 'From: '."<$email>" . "\r\n";
	$messages = '<h3>From:  '.$name.',</h3>'.$messages;
	if(@mail($to, $subject, $messages, $headers)){
		return true;
	}
	return false;
}


date_default_timezone_set('Asia/Phnom_Penh');


if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit']) && !empty($_POST['return-url']) && $_SESSION['token']==$_POST['token'] && isset($_SESSION['mail'])) {
    
    $to = $_SESSION['mail'];
    
	$return_url  = urldecode($_POST['return-url']);
	$first_name  = $_POST['first_name'];
	$last_name   = $_POST['last_name'];
	$email       = $_POST['email'];	
	$phone       = $_POST['phone'];	
	$message     = $_POST['message'];
	
	$_SESSION['sm']['first_name'] = $first_name;
	$_SESSION['sm']['last_name']  = $last_name;
	$_SESSION['sm']['email'] 	  = $email;
	$_SESSION['sm']['phone'] 	  = $phone;
	$_SESSION['sm']['message'] 	  = $message;

	if($first_name == '' || $last_name == '' || $email == '' || $phone == '' || $message == ''){
		$_SESSION['error'] = 1;
		$_SESSION['msg']   = 'All field required.';
	}elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	
	    $_SESSION['error'] = 1;
		$_SESSION['msg']   = 'Invalid email address.';
	}
	else{
		$bodyMail = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                    <title>The Spoon Reservation</title>
                    </head>

                    <body bgcolor="#8d8e90" >
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#8d8e90" >
                      <tr>
                        <td><table width="600" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="center">
                            <tr>
                              <td align="center">

                                  <strong style="color:#ff2626; font-family:Century Gothic;  font-size:23px;">Bosdom.net</strong><br>
                                    <span style="color:#000000; font-family:Century Gothic; font-size:9px;">Wholesale / Supplyer / Online Shop</span>


                                </td>
                            </tr>
                            <tr>
                              <td><hr style="width:90%; height:2px; background:#aaaaaa; border:none;"></td>
                            </tr>
                            <tr>
                                <td align="center"><h3 style="font-family:Cambria; font-size:17px;">Contact/Feedback</h3></td>
                            </tr>
                            <tr>
                              <td align="center">&nbsp;</td>
                            </tr>
                            <tr>
                              <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                  <tr>
                                    <td width="10%">&nbsp;</td>
                                    <td width="80%" align="left" valign="top"><font style="font-family: Cambria; color:#010101; font-size:12px"><strong>Message From Guest :</strong></font><br /><br />
                                    <font style="font-family: Century Gothic; color:#666766; font-size:13px; line-height:21px">
										 
											<table style="width:100%; font-family: Cambria; ">
											  <tr>
												<td style="width:60px" >Name</th>
												<td style="width:10px">:</th>
												<td>'.$first_name.' '.$last_name.'</th>
											  </tr>
											  <tr>
												<td style="width:60px" >Phone</th>
												<td style="width:10px" >:</th>
												<td>'.$phone.'</th>
											  </tr>											  
											  <tr>
												<td style="width:60px" >Email</th>
												<td style="width:10px" >:</th>
												<td>'.$email.'</th>
											  </tr>
											  <tr>
												<td style="width:80px" >Message</th>
												<td style="width:10px" >:</th>
												<td>'.$message.'</th>
											  </tr>
											  
											</table>
									</font></td>
                                 
                                  </tr>
                                 
                                </table></td>
                            </tr>
                            <tr>
                              <td>&nbsp;</td>
                            </tr>
                            <tr>
                              <td>&nbsp;</td>
                            </tr>
                            <tr>
                              <td><hr style="width:90%; height:2px; background:#aaaaaa; border:none;"></td>
                              <td><hr style="width:90%; height:2px; background:#aaaaaa; border:none;"></td>
                            </tr>
                            <tr>
                              <td>&nbsp;</td>
                            </tr>

                            <tr>
                              <td align="center"><font style="font-family:Myriad Pro, Helvetica, Arial, sans-serif; color:#231f20; font-size:12px"><strong>Bosdom.net &copy; '.date('Y').' </strong></font></td>
                            </tr>
                            <tr>
                              <td>&nbsp;</td>
                            </tr>
                          </table></td>
                      </tr>
                    </table>
                    </body>
                    </html>';
	
		$subject = 'Bosdom User Contact';
		if(send_mail($to, $email,  $first_name.' '.$last_name, $subject, $bodyMail)){
			$_SESSION['error'] = 0;
			$_SESSION['msg']   = 'Mail sent!';
			unset($_SESSION['sm']);
		}
	}
	unset($_SESSION['captcha_string']);
	header("Location:$return_url");
}else{
 die('Invalid access.');   
}
