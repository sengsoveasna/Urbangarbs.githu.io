<?php
/* Initial Timezoone */
date_default_timezone_set('Asia/Phnom_Penh');
/* DATABASE CONNECTIVITY SETTINGS */
$configs['hostname'] = '127.0.0.1';
$configs['username'] = 'root';
$configs['password'] = '';
$configs['database'] = 'bosdom_db';
$configs['char_set'] = 'utf8';
$configs['dbcollat'] = 'utf8_unicode_ci';
$configs['dbprefix'] = '';
$configs['dbport']	 = '';
$configs['sms_usr']  = 'smsbosdom84021';
$configs['sms_pwd']  = 'web96620';

$siteLanguages 		 = array('en' => 'English','kh'=>'Khmer');	
/* Application path */
define('BASE_PATH', str_replace("\\", "/", dirname(__FILE__)).'/');
define('FILE_PATH', '/Applications/XAMPP/xamppfiles/htdocs/bosdom/');
define('ADMIN_PATH',BASE_PATH . 'ct-admin/');

define('MBASE_URL','http://localhost:8080/bosdom/');

$selfPath  = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'http://':'http://';
$selfPath .= $_SERVER['HTTP_HOST'].'/';
$selfPath .= trim(str_replace($_SERVER['DOCUMENT_ROOT'],'',BASE_PATH),"/");
define('BASE_URL',$selfPath.'/');
define('FILE_URL','http://localhost:8080/bosdom/');
define('ADMIN_URL',BASE_URL . 'ct-admin/');
unset($selfPath);
