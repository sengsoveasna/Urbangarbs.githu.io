<?php
/****** CITA ******
 * CODING: HCK0011 / 2016-12-31
 * Description:Register New User
 */
 session_start();
 include_once '_config_inc.php';
 include_once BASE_PATH.'_libs/site_class.php';
 $db = new gen_class($configs);
 $pin = mt_rand(1000, 9999);

 $_SESSION['pin'] =  $pin;

 //send mail function
 function send_mail($to, $email,  $name, $subject, $messages){
 	$headers  = 'MIME-Version: 1.0' . "\r\n";
 	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
 	$headers .= 'From: '."<$email>" . "\r\n";
 	$messages = $messages;
 	if(@mail($to, $subject, $messages, $headers)){
 		return true;
 	}
 	return false;
 }
 
	//function get Id from table Register
	function getPrimaryKey($db){
		$result = $db->getOne("tbl_user_admin","MAX(`user_id`) AS id");
		if(empty($result['id'])){
			$result['id'] = 0;
		}
		return ((int)$result['id'] + 1);
	}

// $To = $_SESSION['mail'];
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit']) && !empty($_POST['return-url'])) {
    $return_url = urldecode($_POST['return-url']);
    $company = $_POST['companyName'];
    $email   = $_POST['email'];
    $name    = $_POST['name'];
    $phone   = $_POST['phone'];
    $password= $_POST['password'];
    $confirm = $_POST['confirm'];
    $address = $_POST['address'];
    $captcha = $_POST['captcha'];

    $_SESSION['re']['companyName'] 	= $company;
    $_SESSION['re']['email'] 	= $email;
	$_SESSION['re']['name']= $name;
	$_SESSION['re']['phone'] 	= $phone;
	$_SESSION['re']['password'] 	= $password;
	$_SESSION['re']['confirm'] 	= $confirm;
    $_SESSION['re']['address'] 	= $address;

    $db->where('email',$email);
    $find = $db->getOne('tbl_user_admin');

    	if($email == '' || $name == '' || $phone== '' || $password == '' || $confirm == '' || $captcha == '' || $address == ''){
    		$_SESSION['error'] = 1;
    		$_SESSION['msg']   = 'All field required.';
    	}
    	elseif (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    	    $_SESSION['error'] = 1;
    		$_SESSION['msg']   = 'Invalid email address.';
    	}
    	else if($password != $confirm){
    		$_SESSION['error'] = 1;
    		$_SESSION['msg']   = 'Password does not match!';
    	}
      else if(!empty($find)){
        $_SESSION['error'] = 1;
    		$_SESSION['msg']   = 'Email address is already registered.';
        unset($_SESSION['captcha']);
      }
    	else if ($captcha != $_SESSION['captcha']) {
    		$_SESSION['error'] = 1;
        $_SESSION['msg']   = 'Invalid captcha';
    	}
    	else{	// all field has been validated and send confirm code.
                     //insert into table confirm
            		           //Begin of Mail Body design
                                $body_msg='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                                <html xmlns="http://www.w3.org/1999/xhtml">
                                <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                                <title>Mail Design CITA</title>
                                </head>

                                <body bgcolor="#8d8e90" >
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#8d8e90" >
                                  <tr>
                                    <td><table width="600" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="center">
                                        <tr>
                                          <td align="center">

                                              <strong style="color:#F75459; font-family:Century Gothic;  font-size:23px;">Bosdom.net</strong><br>
                                                <span style="color:#690d0d; font-family:Century Gothic; font-size:9px;">Wholesale / Supplyer / Online Shop</span>
                                            </td>
                                        </tr>
                                        <tr>
                                          <td><hr style="width:90%; height:2px; background:#F75459; border:none;"></td>
                                        </tr>
                                        <tr>
                                            <td align="center"><h3 style="font-family:Cambria; font-size:17px;">Confirm Your Registration</h3></td>
                                        </tr>
                                        <tr>
                                          <td align="center">&nbsp;</td>
                                        </tr>
                                        <tr>
                                          <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                          <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
                                              <tr>
                                                <td width="10%">&nbsp;</td>
                                                <td width="80%" align="left" valign="top"><font style="font-family: Cambria; color:#010101; font-size:12px"><strong>Dear, Value Customer</strong></font><br /><br />
                                                  <font style="font-family: Century Gothic; color:#666766; font-size:13px; line-height:21px">

                                                      &ensp;&ensp;&ensp; Please verify your your Registration by click on link below or using Verification code.
            										    <br/><br/>

            							
                                                      <br /><br />
                                                    Here is your verify code : <strong style="font-size:18px;">'.$pin.'</strong><br>


                                <br /><br />
                                Best Regards,<br />
                                Bosdom.net Team</font></td>
                                                <td width="10%">&nbsp;</td>
                                              </tr>
                                              <tr>
                                                <td>&nbsp;</td>
                                                <td align="right" valign="top"><table width="108" border="0" cellspacing="0" cellpadding="0">

                                                </table></td>
                                                <td>&nbsp;</td>
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
                                          <td><hr style="width:90%; height:2px; background:#F75459; border:none;"></td>
                                        </tr>
                                        <tr>
                                          <td>&nbsp;</td>
                                        </tr>

                                        <tr>
                                          <td align="center"><font style="font-family:Myriad Pro, Helvetica, Arial, sans-serif; color:#231f20; font-size:12px"><strong>Liza Shop &copy; '.date('Y').' </strong></font></td>
                                        </tr>
                                        <tr>
                                          <td>&nbsp;</td>
                                        </tr>
                                      </table></td>
                                  </tr>
                                </table>
                                </body>
                                </html>'; //End of mail Design
            					//Send Mail to Referral Member alert has someone signed up by his refer link
                        $To =  $_POST['email'];
                        
                        $subject = 'Bosdom Verify Code';

                send_mail($To, 'noreply@bosdom.net',  $name, $subject, $body_msg);
                  
                  //Set Session verify
                
                $_SESSION['partner_verify'] = sha1('cita'.$pin);

            	$located = BASE_URL.'partner_register/?verify=true';
                $return_url = $located;
    	}
		   unset($_SESSION['captcha']);
		   header("Location:$return_url");
}else{

  die("Invalid request");

}


/**** IN GOD WE TRUST ****/
?>
