<?php
/****** CITA ******
 * CODING: HCK0011 / 2018-12-01
 * Description: feedback comment on product detail
 */ 
if(empty($_POST['ajax'])) exit('No direct script access allowed');
session_start();
include_once '_config_inc.php';
include_once BASE_PATH.'_libs/site_class.php';
 
$db = new gen_class($configs);

$messages = array('status'=>0);
// ob_start();

if(isset($_POST)){
    $pid     = isset($_POST['pid']) ? $db->filter($_POST['pid']) : '';
    $token   = isset($_POST['token']) ? $db->_decode($db->filter($_POST['token'])) : '';

    //validation params to be matching and equal.
    if($pid <> $token || $pid == '' || $token == ''){
        $messages['msg']    = "Invalid params";
        $messages['html']	= '';
        $db->jEncode($messages);
    }
    $rating  = isset($_POST['rating']) ? $db->filter($_POST['rating']) : 0;
    $comment = isset($_POST['usr_feedback']) ? $db->filter($_POST['usr_feedback']) : $db->strEmpty('');
    
    $fileList = array();
    // Count the number of uploaded files in array
    $total_count = count($_FILES['images']['name']);
    // Loop through every file
    for( $i=0 ; $i < $total_count ; $i++ ) {
        //The temp file path is obtained
        $tmpFilePath = $_FILES['images']['tmp_name'][$i];
        //The valid extension file 
        $validextensions = array("jpeg", "jpg", "png","gif");
        //The extension file 
        $extension =  strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
        // new file size in B
        $file_size = $_FILES['images']['size'][$i];

        //A file path needs to be present
        if ($tmpFilePath != "" && $file_size < 2097152 && in_array($extension, $validextensions)){
            $fName = strtotime("now").$_FILES['images']['name'][$i];
            $fileList[] = $fName;
            //Setup our new file path
            $newFilePath = BASE_PATH."files/feedback/" .$fName;
            
            //File is uploaded to temp dir
            move_uploaded_file($tmpFilePath, $newFilePath);
        }
    }

    $fields = array(
        "usr_id"       => $_SESSION['user_id'],
        "pro_id"       => $pid,
        "rating_stars" => $rating,
        "comment"      => $comment,
        "images"       => json_encode($fileList)
    );

    $db->startTransaction();
    $inserted_id = $db->insert('tbl_feedback', $fields);
    if($inserted_id !== false)
    {
        $db->commit();
        $messages['status'] = "1";
        $messages['msg']    = "Thank for your feedback. Your feedback will display after verified by system.";

    }else{
        $db->rollback();
        $messages['msg']    = "Error: ".$db->getLastError();
    }


}
// $html = ob_get_contents();
// ob_end_clean();
// $messages['html']    = $html;
// $messages['msg']    = "Your data was inserted!";
$db->jEncode($messages);

/**** IN GOD WE TRUST ****/
?>