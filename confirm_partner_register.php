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
    $result = $db->getOne("tbl_user_admin","MAX(`user_id`) AS id");
    if(empty($result['id'])){
        $result['id'] = 0;
    }
    return ((int)$result['id'] + 1);
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_code']) && !empty($_POST['return-url']) && isset($_SESSION['re']) && isset($_SESSION['partner_verify'])) {

    $verifyCode = $_POST['verify_code'];

    if($verifyCode != '' && $_SESSION['partner_verify'] == sha1('cita'.$verifyCode)){

        $primaryKey = getPrimaryKey($db);
        
        $dataInsert = array(
            'input_by'      => 1,    
            'user_id'       => $primaryKey,
            'status'        => 0,
            'user_name'     => $_SESSION['re']['name'],
            'companyName'   => $_SESSION['re']['companyName'],
            'address'       => $_SESSION['re']['address'],
            'phone'         => $_SESSION['re']['phone'],
            'user_type'     => 3,
            'email'         => $_SESSION['re']['email'],
            'password'      =>  sha1($_SESSION['re']['password'])
        );

        if($db->insert('tbl_user_admin',$dataInsert)){
            $return_url = BASE_URL.'partner_register/?success=true';
			$_SESSION['error'] = 0;
			$_SESSION['msg'] = 'Your account has been registered successfully.';
			$_SESSION['successful_register_partner'] = true;
        }else{
            $return_url = BASE_URL.'partner_register/?status=failed';
			$_SESSION['error'] = 1;
			$_SESSION['msg'] = 'Something when wrong, cannot create your account at the moment!';
        }
    }else{
        $return_url = BASE_URL.'partner_register/?verify=false';
		$_SESSION['error'] = 1;
		$_SESSION['msg'] = 'Invalid verification code';
    }

    header("Location:$return_url");
    
}else{

    die("Invalid request");

}
/**** IN GOD WE TRUST ****/
?>
