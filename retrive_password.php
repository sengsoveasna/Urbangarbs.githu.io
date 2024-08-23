<?php
/****** CITA ******
 * CODING: HCK0011 / 2017-01-07
 * Description: Send Verification code to user to reset passowrd
 */
 //send mail function

 function send_mail($to, $email,  $name, $subject, $messages, $from=''){
   $headers  = 'MIME-Version: 1.0' . "\r\n";
   $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
   $headers .= 'From: '."<$email>" . "\r\n";
   $messages = $messages;
   if(@mail($to, $subject, $messages, $headers)){
       return true;
   }
   return false;
 }
 $pin = mt_rand(1000, 9999);
 //function get Id from table Register
 function getPrimaryKey($db){
     $result = $db->getOne("tbl_user_register_temp","MAX(`id`) AS id");
     if(empty($result['id'])){
         $result['id'] = 0;
     }
     return ((int)$result['id'] + 1);
 }

$primaryKey       = getPrimaryKey($db);
$currentTime = date("Y-m-d H:i:s");

$db->where('email',$email_to_reset);
$getUser = $db->getOne('tbl_user_register');

    $name = $getUser['name'];
    $sex = $getUser['sex'];
    $password = $getUser['password'];
    $phone = $getUser['phone'];
    $address = $getUser['address'];

 //Delete User if existed befor insert
 $db->where('email', $email_to_reset);
 $db->delete('tbl_user_register_temp');
 // insert mail to table: tbl_register
 $data = Array (

        "id" => $primaryKey,
        "image" => '--None--',
        "email" => $email_to_reset,
        "name" => $name,
        "sex" => $sex,
        "password" => md5($password),
        "phone" => $phone,
        "address" => $address,
        "verify_code" => $pin
         );
 $db->insert ('tbl_user_register_temp', $data);
  //insert into table confirm
$To = $_SESSION['mail'];

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

                 <strong style="color:#F75459; font-family:Century Gothic;  font-size:23px;">Baellerryasia</strong><br>
                   <span style="color:#690d0d; font-family:Century Gothic; font-size:9px;">Best Online Shopping in Cambodia</span>
               </td>
           </tr>
           <tr>
             <td><hr style="width:90%; height:2px; background:#F75459; border:none;"></td>
           </tr>
           <tr>
               <td align="center"><h3 style="font-family:Cambria; font-size:17px;">Reset Your Account Password</h3></td>
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

                         &ensp;&ensp;&ensp; Please verify your resetting password by click on the link below or use verification code.
                           <br/><br/>

                       Your Verification Link : '.BASE_URL.'/reset-password/'.md5($email_to_reset.$pin).'
                         <br /><br />
                       Here is your verify code : <strong style="font-size:18px;">'.$pin.'</strong><br>
   <br /><br />
   Best Regards,<br />
   Baellerryasia Team</font></td>
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

$to =  $_POST['email'];
$from = $To;
$subject = 'Baellerryasia: Reset Password - ' .$from;

send_mail($to, 'Baellerryasia',  $name, $subject, $body_msg, $from);
$located = BASE_URL.'reset-password/'.md5($email_to_reset);

// create new session user eamil
$_SESSION['user_email_reset'] = $email_to_reset;

$return_url = $located;
header("Location:$return_url");

/**** IN GOD WE TRUST ****/
?>
