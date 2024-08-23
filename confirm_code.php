<?php
/****** CITA ******
 * CODING: HCK0011 / 2017-01-03
 * Description: Confirm and verify code
 */
session_start();
include_once '_config_inc.php';
include_once BASE_PATH.'_libs/site_class.php';
$db = new gen_class($configs);

//function get Id from table Register
function getPrimaryKey($db){
    $result = $db->getOne("tbl_user_register","MAX(`id`) AS id");
    if(empty($result['id'])){
        $result['id'] = 0;
    }
    return ((int)$result['id'] + 1);
}

if(isset($_POST['confirm']) && !empty($_POST['return-url'])) {
    if(isset($_POST['user_confirm_change_pw'])){
        $return_url = urldecode($_POST['return-url']);
        $email = $_SESSION['user_email_reset'];
        $code = $_POST['verify_code'];

        if($code == ''){
            $_SESSION['error'] = 1;
            $_SESSION['msg']   = 'You Must Enter a Valid Code.';
        }else{
            $db->where('email',$email);
            $db->where('verify_code',$code);
            $getUser = $db->getOne('tbl_user_register_temp');
            if(!empty($getUser)){
                $_SESSION['verified']='true';

                $email      = $getUser['email'];
                $password   = $getUser['password'];

                $data = Array (
                       "password" => $password
                        );
                $db->where('email',$email);
                $db->update('tbl_user_register', $data);

                //remove confirm code
                $db->where("email",$email);
                $db->delete('tbl_user_register_temp');
                $return_url = BASE_URL.'reset-password/'.md5('confirm_completed');

            }else{
                $_SESSION['verified'] = 'false';
                $_SESSION['error'] = 1;
                $_SESSION['msg']   = 'Invalid Verification Code.';
            }
        }
        header("Location:$return_url");
    }else{

        $return_url = urldecode($_POST['return-url']);
        $email = $_POST['email'];
        $code = $_POST['verify_code'];

        if($code == ''){
            $_SESSION['error'] = 1;
            $_SESSION['msg']   = 'You Must Enter a Valid Code.';
        }else{
            $db->where('email',$email);
            $db->where('verify_code',$code);
            $getUser = $db->getOne('tbl_user_register_temp');
            if(!empty($getUser)){
                $_SESSION['verified']='true';

                $email = $getUser['email'];
                $name = $getUser['name'];
                $phone = $getUser['phone'];
                $sex = $getUser['sex'];
                $password = $getUser['password'];
                $address = $getUser['address'];
                $bank_id = $getUser['bank_id'];
                $currentTime = date("Y-m-d H:i:s");

                $primaryKey       = getPrimaryKey($db);
        		// insert mail to table: tbl_register
        		$data = Array (

        			   "id" => $primaryKey,
        			   "image" => '--None--',
        			   "email" => $email,
                       "name" => $name,
                       "sex" => $sex,
        			   "password" => $password,
                       "phone" => $phone,
                       "address" => $address,
                       "bank_id" => $bank_id,
                       "register_date"=>$currentTime
        		);
        		$db->insert('tbl_user_register', $data);
				
				//Insert to Table Score
				
				//function get Id from table score
				function getScoreId($db){
					$result = $db->getOne("tbl_score_money","MAX(`id`) AS id");
					if(empty($result['id'])){
						$result['id'] = 0;
					}
					return ((int)$result['id'] + 1);
				}
				$scoreId = getScoreId($db);
				$currentTime = date("Y-m-d H:i:s");
				$scoreDate = Array (

        			   "id" => $scoreId,
        			   "user_id" => $primaryKey,
					   "score"=>'0',
					   "payment"=>'0',
					   "date"=> $currentTime,
                       "status"=>'0',					                          "notify"=>'0'
        		);
        		$db->insert('tbl_score_money', $scoreDate);
				
				
                //User Referal
                //function get Id from table Register
                function getPrimaryKey1($db){
                    $result = $db->getOne("tbl_referal","MAX(`id`) AS id");
                    if(empty($result['id'])){
                        $result['id'] = 0;
                    }
                    return ((int)$result['id'] + 1);
                }
                $primaryKey1       = getPrimaryKey1($db);

                if(isset($_POST['rfid'])){
                    $rfid = $_POST['rfid'];
                    $userRefer = array(
                        'id' => $primaryKey1,
                        'register_id' => $primaryKey,
                        'refer_by_id' => $rfid
                    );
                    $db->insert('tbl_referal',$userRefer);
                }


                //remove confirm code
                $db->where("email",$email);
                $db->delete('tbl_user_register_temp');
                $return_url = BASE_URL.'register/'.md5('register_completed');

            }else{
                $_SESSION['verified'] = 'false';
                $_SESSION['error'] = 1;
                $_SESSION['msg']   = 'Invalid Verification Code.';
            }
        }
        header("Location:$return_url");

}
}
/**** IN GOD WE TRUST ****/
?>
