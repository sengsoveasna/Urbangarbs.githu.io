<?php
/****** CITA ******
 * CODING: HCK0011 / 2020-08-31
 * Description: User Login
 */
session_start();
include_once '../_config_inc.php';
include_once BASE_PATH.'_libs/site_class.php';
include BASE_PATH ."_libs/site_paginator.class.php";
$db = new gen_class($configs);
$sb = $db->copy();

function createVerifyCode($db,$user_name,$code,$type){
    $st = $db->copy();
    $st->where('stvUserName',$user_name);
    $getVerify = $st->get('tbl_verify_code');
    
    //remove all expired code
    $db->where('(select NOW() > DATE_ADD(stvDate,INTERVAL 1 HOUR))');
    $db->delete('tbl_verify_code');

    //create new code 
    $dataInsert = array(
        'stvUserName'   => $user_name,
        'stvCode'       => $code,
        'stvType'       => $type
    );
    
    return $db->insert('tbl_verify_code',$dataInsert);
    
}

$hasError = false;
$status = '';
$rout = '';
$login_status = false;
$code_alive = false;
$arr_video = [];
$is_suspended = false;
$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';

if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['user_name']) && !empty($_POST['login-type'])){

        $phone_number = $db->filter($_POST['user_name']);
        $login_type   = $db->filter($_POST['login-type']);

        // $password = $db->filter($_POST['password']);

        if ($phone_number == '' || strlen($phone_number) < 8 || !is_numeric($phone_number) ) {
            $hasError   = true;
            $status     = 'Invalid phone number';
        }else if(empty($_POST['user_verify_code']) && !empty($_SESSION['verify_sent_time']) && time() - $_SESSION['verify_sent_time'] < 60){
            $hasError   = true;
            $code_alive = true;
            $status     = 'You cannot request new verification now';
        }else{

            $phone_number   = ltrim($phone_number,'0');
            $phone_number   = preg_replace('/^\+?855|\|855|\D/', '', ($phone_number));
            $phone_number   = '0'.$phone_number;

            $db->where('phone',$phone_number);
            $db->where('registerStatus',1);
            $db->where('phoneVerified',1);
            // $db->where('password',md5($password));
            $getUser = $db->getOne('tbl_user_register');
            
            if(!empty($getUser) && $getUser['status']==0){
                
                $status  = 'Your account has been suspended or not approved. Please contact Customer Service for more info and how to activate your account.';
                $is_suspended = true;
                
            }elseif(!empty($getUser) && $getUser['status']==1){

                if(empty($db->filter($_POST['user_verify_code'])) && $login_type == 'phone'){
                    //send verify code
                    $verify_code = mt_rand(111111,999999);
                    $currentDate = date('Y-m-d H:i:s');
                    if(createVerifyCode($sb,$phone_number,$verify_code,2)){
                        $_SESSION['verify_sent_time'] = time();
                    }else{
                        $hasError       = true;
                        $status         = 'Unable create user verification';
                    }

                }else{

                    if($login_type == 'password'){

                        if(!empty($_POST['login_password']) && md5($_POST['login_password']) == $getUser['password']){

                            //create user signed in data
                            $login_status = true;
                      
                        }else{
                            $hasError = true;
                            $status   = 'Invalid password';
                        }

                    }else{
                        
                        //send verify code 
                        $user_code = $db->filter($_POST['user_verify_code']);
                        //validate code with email 
                        $db->where('stvCode',$user_code);
                        $db->where('stvUserName',$phone_number);
                        $getVerify = $db->getOne('tbl_verify_code','count(*) as verified');
                        
                        if($getVerify['verified'] == 1){

                            //create user signed in data
                            $login_status = true;

                            //remove all code
                            $db->where('stvUserName',$phone_number);
                            $db->delete('tbl_verify_code');

                        }else{
                            $hasError = true;
                            $status  = 'Invalid verification code';
                        }
                    }

                }

                if($login_status){

                    $_SESSION['isLoggined'] = true;
                    $_SESSION['user_id']    = $getUser['id'];
                    $_SESSION['user_email'] = $getUser['email'];
                    $_SESSION['user_name']  = $getUser['name'];
                    $_SESSION['user_image'] = $getUser['image'];
                    $_SESSION['user_sex']   = $getUser['sex'];
                    $_SESSION['user_phone'] = $getUser['phone'];
                    $_SESSION['user_address'] = $getUser['address'];

                    if(!isset($_COOKIE['ue'])){ // ue User Email
                        setcookie ("ue", base64_encode($getUser['phone']), time() + (86400 * 30), '/');
                    }

                }
                
            }else{
                $hasError = true;
                $status  = 'Invalid account login';
            }
        }

}else{
    $hasError   = true;
    $status     = 'Bad request';
}

$dataJson = array(
    'hasError'  => $hasError,
    'status'    => $status,
    'login'     => $login_status
);

if($is_suspended){
    $dataJson['is_suspended'] = true;
}

if($code_alive){
    $dataJson['code_alive'] = $code_alive;
}

echo json_encode($dataJson);

 
/**** IN GOD WE TRUST ****/
?>
