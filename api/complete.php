<?php
session_start();
if (!isset($_SESSION['access_token']) || $_SESSION['access_token'] == '') {
	die ('session_undefined');
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
		$conn->query("UPDATE `devices` SET `verified` = '1' WHERE `deviceID` = '".$_SESSION['code']."' AND `verified` <> '1'")
	} else {
		Header("Location: https://arrow.nstudio.pw");
	}
} else {
	Header("Location: https://arrow.nstudio.pw");
}
?><html>
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
		<div style="display:none;" id="status">success</div>
		<div style="display:none;" id="token"><?=$_SESSION['access_token'];?></div>
		<div class="block">
			<div style="padding-bottom:32px;"> </div>
			<img src="img/arrow_logo.png" alt="arrow" class="centered-image"></img>
			<div style="padding-bottom:32px;"> </div>
			<center>
				<h1 class="title" id="device-name">Hoàn tất!</h1>
			</center>
			<div style="padding-bottom:32px;"> </div>
			<center>
				<p style="color:white">Đã thêm thành công <?=$_SESSION['device-name'];?> vào hệ thống</p>
			</center>
			<div style="padding-bottom:32px;"> </div>
			<center>
				<a href="close.php" class="button button-big button-fill col">Đóng cửa sổ</a>
			</center>
			<div style="padding-bottom:16px;"> </div>
		</div>
	</body>
</html>