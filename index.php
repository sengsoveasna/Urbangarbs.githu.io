<?php
/****** CITA ******
 * CODING: HCK0011 / 2016-12-02
 * Description: Home Index of Free Style Online
 */
    session_start();
	include_once '_config_inc.php';
	include_once BASE_PATH.'_libs/site_class.php';
	$db = new gen_class($configs);

	include_once BASE_PATH.'_libs/counter.php';
	$counter = new CT_COUNTER($db->getConnection());
	$counter->run();
	
	/*======== Detect device =========*/
    $ismobile = false;
    $useragent=$_SERVER['HTTP_USER_AGENT'];
    if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
    $ismobile = true;
    
    // if($ismobile==true){
    // 	$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    // 	$actual_link = str_replace('bosdom.net','bosdom.net/mobile',$actual_link);
    // 	header('Location: '.$actual_link);
    // 	exit;
    // }


  //Limite text
  include_once BASE_PATH.'_libs/limit_text.php';

  //Include Create Cookies
 include_once BASE_PATH.'config/set_cookies.php';

  //Include Create Session
 include_once BASE_PATH.'config/set_sessions.php';
  // Rating star function
 include_once BASE_PATH.'_libs/starrr.php';


	$main_page = 'home';
	if(isset($_GET['p']) && $_GET['p']!=''){
		$main_page = $db->filter($_GET['p']);
	}

	$main_id = '';
	if(isset($_GET['id']) && $_GET['id']!=''){
		$main_id = $db->filter($_GET['id']);
	}

    $lang = 'en';
	if(isset($_GET['lang']) && $_GET['lang']!=''){
		$lang = $db->filter($_GET['lang']);
	}
    $BASE_URL = BASE_URL.$lang.'/';
    
    //if users don't login, they can't view those pages 
    // Added date: 06 Feb 2021
    // $arr_redirect_login = array('home','products','news','videos','top-sales','categories','search','stores','page_suggestions','search');
    // if($login_status == 'false' && in_array($main_page,$arr_redirect_login)){
	   
	//  $login = BASE_URL.'login';
	//  header("Location:$login");
	//  exit();
	// }
	
	
	//handle notification
	$login_status = 'false';
	$newNotificationCount = 0;
	if(isset($_SESSION['user_id'])){
		$login_status = 'true';
		//get wallet detail
		$db->join('tbl_user_wallet','wauID=uwa_wauID','INNER');
		$db->where('wauUserID',$_SESSION['user_id']);
		$counterNotification   = $db->getOne('tbl_user_wallet_detail', "COUNT(*) AS numRow");
		
		
		if(!isset($_COOKIE['ntw'])){
			setcookie('ntw', $counterNotification['numRow'] , time() + (86400 * 3600), "/");
		}else{
			$currentCount = $_COOKIE['ntw'];
			if($currentCount < $counterNotification['numRow']){
				$newNotificationCount = ($counterNotification['numRow'] - $currentCount);
			}
		}
	}
		

	// INITIAL BUFFER STORE
	$_mainContent='';
	// INITIAL META VALUE
    $_metaTags = array();
	// GET BUFFER OUTPUT
	$filePath = 'views/'.$main_page.'.php';
	if(file_exists($filePath))
	{
        ob_start();
		include $filePath;
		date_default_timezone_set('Asia/Phnom_Penh');
        $_mainContent = ob_get_contents();
        ob_end_clean();
	}
	/* when page not found */
	else {
		$filePath = 'views/404.php';
		ob_start();
		include $filePath;
		$_mainContent = ob_get_contents();
		ob_end_clean();
	}

	//get Title function
	function getTitle($db,$lang,$title_alias){

		$db->where('alias',$title_alias);
		$db->where('lang',$lang);
		$result_title = $db->getOne('tbl_title');

		echo $result_title['title'];
	}
	//languages implement

	function language_switcher($lang,$to_lang)
	{
		$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$actual_link = rtrim(trim($actual_link),'/').'/'; 
		$new_link    = str_replace('/'.$lang.'/','/'.$to_lang.'/',$actual_link);
		if($actual_link == BASE_URL){
		   $new_link = BASE_URL .$to_lang.'/';
		}
		return $new_link;
	}
?>

<!DOCTYPE html>
<!--
Designed By :
  _____  _______   _______     __
 / ____||__   __| |__   __|   /  \
| /        | |       | |     / /\ \
| |        | |       | |    / /__\ \
| |____  __| |__     | |   / /____\ \
 \_____||_______|    |_|  /_/      \_\
 
 -->
<html lang="<?php echo $lang;?>">
  <head>
    <meta charset="utf-8">
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	<?php $_SITE_INFO = $db->getOne("tbl_site_description"); ?>
	<title><?php if(!empty($_metaTags['title'])){ echo $_metaTags['title'];}else{ echo  ucfirst($_SITE_INFO['title']); }; echo ' - '.$_SITE_INFO['site_name']; ?></title>
    <meta name="keywords" content="<?php echo $_SITE_INFO['key_word']; ?>">
    <meta name="author" content="CITA - www.thecita.net">
    <meta name="description" content="<?php echo $_SITE_INFO['description']; ?>"><?php
		if(empty($_metaTags['image'])){
			$_metaTags['image'] = BASE_URL . 'files/site_description/'.$_SITE_INFO['image'];
		}
        $_metaTags['site_name']= $_SITE_INFO['site_name'];
        $_metaTags['url']= "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$_SESSION['mail'] = $_SITE_INFO['received_mail'];
		$_SESSION['admin_phone'] = $_SITE_INFO['phone'];
        echo $db->site_meta($_metaTags);
    ?>

	<link rel="shortcut icon" href="<?php echo BASE_URL; ?>images/favicon1.ico" type="image/x-icon" />
	<link rel="icon"  		  href="<?php echo BASE_URL; ?>assets/imgs/favicon_bosdom.png" type="image/png" />

	<!-- Bootstrap -->
    <link href="<?php echo BASE_URL; ?>css/bootstrap.min.css" rel="stylesheet"/>
    <link href="<?php echo BASE_URL; ?>css/animate.css" rel="stylesheet"/>
	<link href="<?php echo BASE_URL; ?>css/font-awesome.min.css" rel="stylesheet"/>
	<link href="<?php echo BASE_URL; ?>css/style.css?v3.8" rel="stylesheet"/>
	<link href="<?php echo BASE_URL; ?>css/owl.carousel.css" rel="stylesheet"/>
	<link href="<?php echo BASE_URL; ?>css/owl.theme.css" rel="stylesheet"/>
	<link href="<?php echo BASE_URL; ?>css/jquery.materialripple.css" rel="stylesheet"/>
	<link href="https://fonts.googleapis.com/css?family=Montserrat:400,600" rel="stylesheet">
	<link href="<?php echo BASE_URL; ?>css/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
	
	<script src="<?php echo BASE_URL; ?>js/jquery.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE_URL; ?>js/jquery-ui.min.js"></script>
	<script src="<?php echo BASE_URL; ?>plugins/datetimepicker/jquery.datetimepicker.js"></script>
    <script src="<?php echo BASE_URL; ?>js/bootstrap.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE_URL; ?>js/owl.carousel.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE_URL; ?>js/jquery.materialripple.js" type="text/javascript"></script>
	<script src="<?php echo BASE_URL; ?>js/jquery.unveil.js" type="text/javascript"></script>
	<script src="<?php echo BASE_URL; ?>js/sc.js?v1" type="text/javascript"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.css" integrity="sha512-oe8OpYjBaDWPt2VmSFR+qYOdnTjeV9QPLJUeqZyprDEQvQLJ9C5PCFclxwNuvb/GQgQngdCXzKSFltuHD3eCxA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js" integrity="sha512-lbwH47l/tPXJYG9AcFNoJaTMhGvYWhVM9YI43CT+uteTRRaiLCui8snIgyAN8XWgNjNhCqlAUdzZptso6OCoFQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

  </head>
  <body>

    
    <!-- Load Facebook SDK for JavaScript -->
	<!-- <div id="fb-root"></div>
        <script>(function(d, s, id) {
          var js, fjs = d.getElementsByTagName(s)[0];
          if (d.getElementById(id)) return;
          js = d.createElement(s); js.id = id;
          js.src = 'https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js#xfbml=1&version=v2.12&autoLogAppEvents=1';
          fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>	 -->

    <!-- Your customer chat code -->
    <div class="fb-customerchat"
      attribution=setup_tool
      page_id="1299797636767574">
    </div>
    
	<!--Start Header-->
	<div class="header-cotainer">
		<!-- <div class="comming_up">
			<p>
				<span class="up_coming_text">Stay tuned! Our new features coming in :</span>
				<span class="timer">
					<a id="day">00</a>
					<a id="hour">00</a>
					<a id="minute">00</a>
					<a id="second">00</a>
				</span> 
			</p>
		</div> -->
		<div class="top-wraper">
			<div class="top-container">
				<div class="welcome-msg">Welcome our online store</div>
				
				<ul class="nav-bar list-unstyled" style="margin:0;">
					
					<li>
						<div class="dropdown">
							<a href="javascript:;" class="dropdown dropdown-toggle " data-hover="dropdown" id="dropdownMenu2" > 
								<?php 
								if($lang == 'kh'){
									echo '<span class="kh-lang"><span >Khmer</span></span>';
								}else{
									echo '<span class="en-lang"><span >English</span></span>';
								}
								?>
								<span class="fa fa-angle-down"></span>
							</a>	
							<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
								<li ><a href="<?php echo language_switcher($lang,'kh'); ?>"><span class="kh-lang"><span >Khmer</span></span></a></li>
								<li role="separator" class="divider"></li>
								<li ><a href="<?php echo language_switcher($lang,'en'); ?>"><span class="en-lang"><span >English</span></span></a></li>
							</ul>
						</div>
					</li>
					
				</ul>
			</div>
		</div>
		<div class="header-wrap">
			<a href="<?php echo $BASE_URL;?>"><div class="logo"></div></a>
			<div class="serach-container">
				<div class="frame">
					<form method="get" action="<?php echo $BASE_URL.'search';?>">
						<select class="" name="categories">
							<option value="">All categories</option>
							<?php
								$db->where('status',1);
								$db->where('lang',$lang);
								$db->where('parent_id IS NULL');
								$db->orderBy('order','asc');
								$getCategoriesSearch = $db->get('tbl_categories');

								foreach($getCategoriesSearch as $c){
									$sel = isset($_GET['categories']) && $_GET['categories'] == $c['id'] ? 'selected' : '';
									echo '<option value="'.$c['id'].'" '.$sel.'>'.$c['title'].'</option>';
								}
							?>
						</select>
						<input type="text" value="<?php if(isset($_GET['q'])) echo $_GET['q'];?>" name="q" placeholder="Search Product Code..." title="Enter Product Name or Code Number"/>
						<button style="margin-top:0px;"><i class="fa fa-search" style="font-size: 17px;" aria-hidden="true"></i></button>
					</form>
				</div>
			</div>
            <?php include BASE_PATH.'views/header_user_options.php';?>
			<div class="clearfix"></div>
		</div>
	</div>
<!--	<a href="https://www.facebook.com/messages/t/BaellerryAsia" id="fb_chat" target="_blank"></a>-->
	<!--End Header-->
	<!--Start Nav menu-->
	<div class="clearfix"></div>
	<div class="nav-menu-container">
		  <?php include(BASE_PATH.'views/menu.php');?>
	</div>
  <?php
    //all pages displayed
//   include(BASE_PATH.'views/message_line.php'); //Greating message line
    echo $_mainContent;
  ?>

  <div class="promo-container">
    <div class="footer-wrap">
      <div class="line-left"></div>
      <div class="promo-title">Bosdom.net</div>
      <div class="line-right"></div>

      <div class="clearfix"></div>

      <div class="size-4">
        <img class="img-responsive center-block" src="<?php echo BASE_URL.'assets/imgs/price.png'?>"/>
        <h4>Low Price Then Others</h4>
      </div>
      <div class="size-4">
        <img class="img-responsive center-block" src="<?php echo BASE_URL.'assets/imgs/love.png'?>"/>
        <h4>Customer Friendly Services</h4>
      </div>
      <div class="size-4">
        <img class="img-responsive center-block" src="<?php echo BASE_URL.'assets/imgs/new.png'?>"/>
        <h4>Luxary & Latest Products</h4>
      </div>
      <div class="clearfix"></div>
    </div>

  </div>

	<div class="clearfix"></div>
	<div class="footer-container">
		<div class="footer-wrap">
			<div class="size-3">
			<?php
				$db->where('page_name','footer-address');
				$getFooter = $db->getOne('tbl_articles');
			?>
				<h3>Contact Address</h3>
				<?php echo $getFooter['detail']; ?>
			</div>
			<div class="size-3">
				<h3>Contact Information</h3>
				<dl>
					<dt><i class="fa fa-phone" aria-hidden="true"></i></dt>
					<dd>: <?php echo $_SESSION['admin_phone'];?></dd>

					<dt><i class="fa fa-envelope" aria-hidden="true"></i></dt>
					<dd>: <?php echo $_SESSION['mail'];?></dd>

					<dt><i class="fa fa-mouse-pointer" aria-hidden="true"></i></dt>
					<dd>: www.bosdom.net </dd>
				</dl>
			</div>
			<div class="size-3">
				<h3>Stay Connected</h3>
				<?php
					$db->where('lang','en');
					$db->where('status',1);
					
					$getSocail = $db->get('tbl_social_link');
					
					
					
				?>
				<div class="social-icon">
				<?php
					foreach($getSocail as $social){
				?>
					<a href="<?php echo 'http://'.$social['link']; ?>" target="_blank"><img src="<?php echo BASE_URL.'files/social_link/'.$social['image'];?>"/></a>
				<?php
					}
				?>
				</div>
			</div>
			<div class="size-3 visitor">
				<h3>Visitor Counter</h3>
				
				<dl>
					<dt style="width:60px;">Online</dt>
					<dd>: <?php echo str_pad($counter->online, 7, 0, STR_PAD_LEFT); ?></dd>
					<dt style="width:60px;">Today</dt>
					<dd>: <?php echo str_pad($counter->day_value, 7, 0, STR_PAD_LEFT); ?></dd>
					<dt style="width:60px;">Yesterday</dt>
					<dd>: <?php echo str_pad($counter->yesterday_value, 7, 0, STR_PAD_LEFT); ?></dd>
					<dt style="width:60px;">Total</dt>
					<dd>: <?php echo str_pad($counter->all_value, 7, 0, STR_PAD_LEFT); ?></dd>

				</dl>
			</div>
		</div>
		<div class="clearfix"></div>
	<div class="nav-footer">
		<h5>Copyright Bosdom.net © <?php echo date("Y");?>. All Right Reserved. Web Design <a href="http://www.thecita.net" target="_blank">CITA</a>.</h5>
	</div>
	</div>
	
	<style>
			
	/* Up Comming Event Count Down */
	.comming_up{
		width: 100%;
		height: 100px;
		background: #353434;
		text-align:center;
		color: #ffffff;
		font-family: 'Montserrat', sans-serif;
		font-size: 27px !important;
		font-weight:600 !important;
		background: url('<?php echo BASE_URL.'assets/imgs/upcoming_u.jpg'?>');
	}
	.comming_up p{
		display:block;
		position:relative;
		text-shadow: #6f6f6f 0px 1px 3px;
		height:80px;
	}
	.comming_up p .timer{
		display:inline-flex;
		margin-top:30px;
	}

	.comming_up p .timer a{
		display:block;
		text-decoration:none;
		color: #ffffff;
		background-color:#232323;
		width:40px !important;
		height: 35px;
		line-height: 35px;
		border-radius: 5px;
		position:relative;
		float:left;
		margin-left:5px;
	}
	.comming_up p .timer a::after{
		display:block;
		width: 40px;
		height: 20px;
		position: absolute;
		bottom:-10px;
		left:0px;
		font-size: 10px;
		text-transform: uppercase;
		color: #000;
		text-shadow: none;
	}
	.comming_up p .timer a#day::after{
		content: 'days';
	}
	.comming_up p .timer a#hour::after{
		content: 'hours';
	}
	.comming_up p .timer a#minute::after{
		content: 'mins';
	}
	.comming_up p .timer a#second::after{
		content: 'secs';
	}
	</style>
	
<script>

	$(function(){
		$('.ripple').materialripple();
	});

	$(".banner-slide").owlCarousel({
		autoplay:true,
		items:1,
		lazyLoad: true,
		loop:true
	});

	var owl = $('.banner-slide');
		owl.owlCarousel();


		$("#next1").click(function() {
			owl.trigger('next.owl.carousel');
		});

		$("#prev1").click(function() {
			owl.trigger('prev.owl.carousel');
		});


	<?php if($main_page != 'home'){?>
	$(".best-of-sale").owlCarousel({
		autoplay:true,
		items:4,
    lazyLoad: true,
		loop:true,
		dots:false
	});
	var owl2 = $('.best-of-sale');
		owl2.owlCarousel();


		$("#next2").click(function() {
			owl2.trigger('next.owl.carousel');
		});

		$("#prev2").click(function() {
			owl2.trigger('prev.owl.carousel');
		});

	$(".new-arrival").owlCarousel({
		autoplay:true,
		items:4,
    lazyLoad: true,
		loop:true,
		dots:false
	});
	var owl3 = $('.new-arrival');
		owl3.owlCarousel();


		$("#next3").click(function() {
			owl3.trigger('next.owl.carousel');
		});

		$("#prev3").click(function() {
			owl3.trigger('prev.owl.carousel');
		});
	<?php }?>
	var owl4 = $('.videos');
		owl4.owlCarousel();


		$("#next4").click(function() {
			owl4.trigger('next.owl.carousel');
		});

		$("#prev4").click(function() {
			owl4.trigger('prev.owl.carousel');
		});

		$(document).ready(function() {
		  $(".items-wrap img").unveil();
		});
		
		
		//Upcoming Count Down
		// Set the date we're counting down to
		var countDownDate = new Date("Dec 14, 2018 07:00:00").getTime();

		// Update the count down every 1 second
		var x = setInterval(function() {

			// Get todays date and time
			var now = new Date().getTime();
			
			// Find the distance between now and the count down date
			var distance = countDownDate - now;
			
			// Time calculations for days, hours, minutes and seconds
			var days = Math.floor(distance / (1000 * 60 * 60 * 24));
			var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
			var seconds = Math.floor((distance % (1000 * 60)) / 1000);
			
			// Output the result in an element with id="demo"
			$("#day").text(days);
			$("#hour").text(hours);
			$("#minute").text(minutes);
			$("#second").text(pad(seconds, 2));
			
			// If the count down is over, write some text 
			if (distance < 0) {
				clearInterval(x);
				$(".comming_up p").text('Check it out! What\'s new?');
				$(".comming_up p").css("line-height","80px");
			}
		}, 1000);
		
		function pad(number, length) {
   
			var str = '' + number;
			while (str.length < length) {
				str = '0' + str;
			}
		   
			return str;

		}
</script>
	<!--<script type="text/javascript" async="async" defer="defer" data-cfasync="false" src="https://mylivechat.com/chatinline.aspx?hccid=36671961"></script>-->
  </body>
</html>
 <?php
/**** IN GOD WE TRUST ****/
?>
