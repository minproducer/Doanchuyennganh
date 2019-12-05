<?php
date_default_timezone_set("Asia/Bangkok");
$conn = new mysqli('localhost', 'admin_root', 'nhan1o12oo1', 'admin_arrow');
include "socola/Chatfuel.php";

use Socola\Chatfuel;

$bot = new Chatfuel();
file_put_contents('request_data.txt', json_encode($_REQUEST));
$check_user = $conn->query("SELECT * FROM `conversations` WHERE `uid` = '{$_REQUEST['messenger_user_id']}'");
if ($check_user->num_rows == 0) {
	if ($conn->query("INSERT INTO `conversations` (`uid`, `personal_code`, `current_device`, `state`) VALUES ('{$_REQUEST['messenger_user_id']}', '', '', '')")) {
		$bot->sendText('Chào mừng bạn đến với trình quản lí arrow trên Messenger!');
	} else {
		$bot->sendText('Đã có lôi [0x01]');
	}
} else {
	$bot->sendText('Chào mừng quay lại với arrow!');
}
$me_query = $conn->query("SELECT * FROM `conversations` WHERE `uid` = '{$_REQUEST['messenger_user_id']}'");
$me = $me_query->fetch_assoc();
$state = $me['state'];
if ($state == '') {
	if ($me['personal_code'] == '') {
		$bot->sendText('Vui lòng đăng nhập để bắt đầu sử dụng.');
	} else {
		$bot->sendText('Chào mừng quay trở lại!');
	}
}