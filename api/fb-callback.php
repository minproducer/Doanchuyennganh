<?php
//fake file/.
if(!session_id()) {
    session_start();
}
$now = time();
$conn = new mysqli("localhost", "vietzrkz", "nhan1o12oo1", "vietzrkz_sentry_manager");
$conn->set_charset("utf8");
$conn->query("SET NAMES UTF8");
if (isset($_SESSION['code'])) {
	$device_query = $conn->query("SELECT * FROM `devices` WHERE `deviceID` = '".$_SESSION['code']."' AND `verified` <> '1'");
	if ($device_query -> num_rows == 1) {
		$device = $device_query ->fetch_assoc();
		$_SESSION['device-name'] = $device['name'];
		$_SESSION['code'] = $_SESSION['code'];
	} else {
		Header("Location: https://arrow.nstudio.pw");
	}
} else {
	Header("Location: https://arrow.nstudio.pw");
}
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("Asia/Bangkok");
require_once('Facebook/autoload.php');
$fb = new Facebook\Facebook([
  'app_id' => '497086490676190',
  'app_secret' => '960bafce339d1ed4c5caf91f8723d552',
  'default_graph_version' => 'v2.2',
]);
$helper = $fb->getRedirectLoginHelper();

try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  echo 'error';
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  echo 'error';
  exit;
}

if (! isset($accessToken)) {
  if ($helper->getError()) {
    echo 'error';
  } else {
    echo 'error';
  }
  exit;
}
$oAuth2Client = $fb->getOAuth2Client();
$tokenMetadata = $oAuth2Client->debugToken($accessToken);
$_SESSION['access_token'] = (string) $accessToken;
?>
<html>
	<head>
		<title>Login to arrow</title>
		<link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
		<style>
			body {
				font-family: 'Montserrat', sans-serif;
				overflow:hidden;
			}
			.title {
				color:white;
			}
			.button {
				background-color: #008CBA;
				border: none;
				color: white;
				padding: 20px;
				text-align: center;
				text-decoration: none;
				display: inline-block;
				font-size: 16px;
				margin: 4px 2px;
				cursor: pointer;
			}
			.centered-image {
				width: 128px;
				position: relative;
				left: 50%;
				margin-left: -76px;
			}
		</style>
	</head>
	<body oncontextmenu="return false" style="background-color:#191919; left:0; top:0; right:0; bottom:0;">
		<div class="block">
			<div style="padding-bottom:32px;"> </div>
			<img src="img/arrow_logo.png" alt="arrow" class="centered-image"></img>
			<div style="padding-bottom:32px;"> </div>
			<center>
				<h1 class="title" id="device-name"><?=$_SESSION['device-name'];?></h1>
			</center>
			<div style="padding-bottom:32px;"> </div>
			<center>
				<a href="complete.php" class="button button-big button-fill col">Thêm thiết bị vào hệ thống</a>
			</center>
			<div style="padding-bottom:16px;"> </div>
		</div>
	</body>
</html>
