<?php
date_default_timezone_set("Asia/Bangkok");
include "menus.php";
$access_token = "EAAe4bnRugQ4BAMdjtl5Fy1voZC8CntTIPZAldqOz9ny2NEdPYGGJZA2NnRvH8BRh76CXFwYQtHcg4mJ9Kc7zE5wFvciKpmSWZA6ONG2mzaqXYcf58UlzQLSO10Mfc7jy48zyal9KU0SPPaawZCdtQwNoYtSX5PFVJD0J1TxUNHwZDZD";
$input = json_decode(file_get_contents('php://input'), true);
$no_msg = false;
global $device, $me;
file_put_contents('log', file_get_contents("log") . PHP_EOL . json_encode($input));
if (isset($input['entry'][0]['messaging'][0]['message']['text'])) {
	$conn = new mysqli('localhost', 'admin_monokaijs', 'nhan1o12oo1', 'admin_arrow');
	$sender = $input['entry'][0]['messaging'][0]['sender']['id'];
	$message = $input['entry'][0]['messaging'][0]['message']['text'];

	$url = "https://graph.facebook.com/v2.6/me/messages?access_token=$access_token";
	$ch = curl_init($url);

	$check_query = $conn->query ("SELECT * FROM `conversations` WHERE `uid` = '$sender'");
	if ($check_query->num_rows == 0) {
		$conn->query("INSERT INTO `conversations` (`uid`, `personal_code`, `current_device`, `state`) VALUES ('$sender', '', '', '')");
	}

	$me_query = $conn->query("SELECT * FROM `conversations` WHERE `uid` = '$sender'");
	$me = $me_query->fetch_assoc();
	
	$data = array();
	$data['recipient']['id'] = $sender;
	// =======================================
	$msg = "Chào!!!";
	if ($me['state'] == '') {
		if ($me['personal_code'] == '') {
			if ($message !== 'Đăng nhập') {
				$msg = "Chào mừng bạn đến với trình điều khiển arrow qua Messenger.";
				show_menu('login');
			} else {
				$msg = "Vui lòng gửi mã cá nhân vào đây.";
				$conn->query("UPDATE `conversations` SET `state` = 'waiting_personal_code' WHERE `uid` = '$sender'");
			}
		} else {
			if ($message == 'Danh sách thiết bị') {
				$msg = "Danh sách thiết bị:";
				show_menu('devices_list');
				$conn->query("UPDATE `conversations` SET `state` = 'waiting_device' WHERE `uid` = '$sender'");
			} else {
				$msg = "Chào mừng bạn quay lại với trình điều khiển arrow qua Messenger.";
				show_menu('main');
			}
		}
	} elseif ($me['state'] == 'waiting_personal_code') {
		$qq = $conn->query("SELECT * FROM `users` WHERE `verify_code` = '$message'");
		if ($qq->num_rows == 0) {
			$msg = "Mã cá nhân không hợp lệ, vui lòng nhập lại!";
		} else {
			$msg = "Đã nhận mã cá nhân thành công!";
			$usr = $qq->fetch_assoc();
			$conn->query("UPDATE `conversations` SET `state` = '', `fbid` = '{$usr['fbid']}', `personal_code`='$message' WHERE `uid` = '$sender'");
			show_menu('devices_list');
		}
	} elseif ($me['state'] == 'waiting_device') {
		$msgs = explode(".", $message);
		if (count($msgs) < 1) {
			$msg = "Thiết bị không hợp lệ, vui lòng chọn lại.";
			show_menu('devices_list');
		} else {
			$dID = $msgs[0];
			$check_qq = $conn->query("SELECT * FROM `devices` WHERE `id` = '$dID' AND `owner` = '{$me['fbid']}'");
			if ($check_qq->num_rows == 0) {
				$msg = "Thiết bị không hợp lệ, vui lòng chọn lại.";
				show_menu('devices_list');
			} else {
				$msg = "Kết nối thiết bị thành công.";
				$device = $check_qq->fetch_assoc();
				$conn->query("UPDATE `conversations` SET `state` = 'managing_device', `current_device` = '{$device['id']}' WHERE `fbid` = '{$me['fbid']}'");
				show_menu('device_menu_1');
			}
		}
	} elseif ($me['state'] == 'managing_device') {
		$device_qq = $conn->query("SELECT * FROM `devices` WHERE `id` = '{$me['current_device']}'");
		$device = $device_qq->fetch_assoc();
		if ($message == 'Quản lí') {
			$msg = "Nội dung quản lí";
			show_menu('device_manage_1');
		} elseif ($message == 'Giám sát') {
			setState("tracking_device");
			$msg = "Giám sát thiết bị";
			show_menu('device_tracking');
		} elseif ($message == 'Thông tin') {
			setState('view_device_info');
			$msg = "IP: {$device['ip']}" . PHP_EOL . "Tên thiết bị: {$device['name']}" . PHP_EOL;
			$msg .= "Lần cuối trực tuyến: " . date('d/m/Y H:i:s', $device['time']);
			AddQuickReply("Quay lại");
		} elseif ($message == 'Thiết bị khác') {
			$conn->query("UPDATE `conversations` SET `state` = 'waiting_device', `current_device` = '' WHERE `fbid` = '{$me['fbid']}'");
			$msg = "Chọn thiết bị";
			show_menu('devices_list');
		} elseif ($message == "Các thiết lập khác") {
			$msg = "Tùy chọn các thiết lập";
			show_menu('device_manage_2');
		} elseif ($message == "Quản lí web") {
			setState('managing_web');
			show_menu('manage_web');
		} elseif ($message == "Quản lí ứng dụng") {
			$msg = "Quản lí ứng dụng";
			setState('managing_app');
			show_menu('manage_app');
		} elseif ($message == "Bật chụp màn hình") {
			$config = json_decode($device['config'], true);
			$config['config']['CaptureScreen'] = '1';
			$config_json = json_encode($config);
			if ($conn->query("UPDATE `devices` SET `config` = '$config_json', `update_time`='".time()."' WHERE `id` = '{$device['id']}'")) {
				$msg = "Đã bật chụp màn hình";
				show_menu('device_manage_2');
			}
		} elseif ($message == "Tắt chụp màn hình") {
			$config = json_decode($device['config'], true);
			$config['config']['CaptureScreen'] = '0';
			$config_json = json_encode($config);
			if ($conn->query("UPDATE `devices` SET `config` = '$config_json', `update_time`='".time()."' WHERE `id` = '{$device['id']}'")) {
				$msg = "Đã tắt chụp màn hình";
				show_menu('device_manage_2');
			}
		} elseif ($message == "Đổi mật khẩu") {
			$msg = "Nhập mật khẩu mới";
			setState('change_password');
		} elseif ($message == "Gửi thông báo") {
			$msg = "Nhập nội dung thông báo";
			setState("send_notice");
		} else {
			$msg = "Chọn tác vụ";
			show_menu('device_menu_1');
		}
	} elseif ($me['state'] == 'tracking_device') {
		if ($message == 'Ảnh màn hình') {
			$device_qq = $conn->query("SELECT `deviceID` FROM `devices` WHERE `id` = '{$me['current_device']}'");
			$device = $device_qq->fetch_assoc();
			$deviceID = $device['deviceID'];
			$logs = $conn->query("SELECT * FROM `logs` WHERE `owner` = '$deviceID' AND `type` = 'screenshot' ORDER BY `time` DESC LIMIT 1");
			file_put_contents('query.txt', "SELECT * FROM `logs` WHERE `owner` = '$deviceID' AND `type` = 'screenshot' ORDER BY `time` DESC LIMIT 1");
			if ($logs->num_rows == 0) {
				$msg = "Chưa có bản ghi nào trong hệ thống";
			} else {
				$log = $logs->fetch_assoc();
				$no_msg = true;
				$msg = "Ảnh chụp màn hình lúc " . date('d/m/Y H:i:s', $log['time']);
				AddImage("http://api.arrow.edu.vn/vip_res.php?resID={$log['id']}");
			}
			show_menu('device_tracking');
		} elseif ($message == 'Thời gian hôm nay') {
			$device_qq = $conn->query("SELECT `deviceID` FROM `devices` WHERE `id` = '{$me['current_device']}'");
			$device = $device_qq->fetch_assoc();
			$deviceID = $device['deviceID'];
			$today_d = date('d');
			$today_m = date('m');
			$today_y = date('Y');
			$time_query = $conn->query("SELECT `timer` FROM `time_stats` WHERE `day` = '$today_d' AND `month` = '$today_m' AND `year` = '$today_y' AND `deviceID` = '$deviceID'");
			if ($time_query->num_rows > 0) {
				$time_stat = $time_query->fetch_assoc();
				$tt = gmdate('H', $time_stat['timer']) . " giờ, " . gmdate('i', $time_stat['timer']) . " phút, " . gmdate('s', $time_stat['timer']) . ' giây.';
				$msg = "Hôm nay đã sử dụng $tt";
			} else {
				$msg = "Hôm nay chưa sử dụng!";
			}
			show_menu('device_tracking');
		} elseif ($message == 'Quay lại') {
			setState('managing_device');
			show_menu('device_menu_1');
		} else {
			$msg = "Theo dõi hoạt động";
			show_menu('device_tracking');
		}
	} elseif ($me['state'] == 'send_notice') {
		$input_to_db = base64_encode('msgbox(64,"arrow","'. str_replace('"', "`", $message) . '")');
		if ($conn->query("UPDATE `devices` SET `instant_cmd_count`=(`instant_cmd_count`+1), `instant_cmd` = '$input_to_db' WHERE `id` = '{$me['current_device']}'")) {
			$msg = "Đã gửi thông báo thành công!";
			setState("managing_device");
		} else {
			$msg = "Có lỗi trong quá trình gửi.";
		}
		show_menu('device_menu_1');
	} elseif ($me['state'] == 'view_device_info') {
		if ($message == 'Quay lại') {
			$msg = "Quản lí";
			setState('managing_device');
			show_menu('device_menu_1');
		}
	} elseif ($me['state'] == 'managing_web') {
		$device_qq = $conn->query("SELECT * FROM `devices` WHERE `id` = '{$me['current_device']}'");
		$device = $device_qq->fetch_assoc();
		if ($message == 'Chặn thêm web') {
			
		} elseif ($message == 'Quay lại') {
			$msg = "Quản lí";
			setState('managing_device');
			show_menu('device_manage_1');
		} elseif ($message == 'Bật chặn web') {
			$msg = "Đã bật chặn web";
			$config = json_decode($device['config'], true);
			$config['config']['BlockWeb'] = '1';
			$config_json = json_encode($config);
			$conn->query("UPDATE `devices` SET `config` = '$config_json', `update_time`='".time()."' WHERE `id` = '{$device['id']}'");
			show_menu('manage_web');
		} elseif ($message == 'Tắt chặn web') {
			$msg = "Đã tắt chặn web";
			$config = json_decode($device['config'], true);
			$config['config']['BlockWeb'] = '0';
			$config_json = json_encode($config);
			$conn->query("UPDATE `devices` SET `config` = '$config_json', `update_time`='".time()."' WHERE `id` = '{$device['id']}'");
			show_menu('manage_web');
		} else {
			show_menu('manage_web');
		}
	} elseif ($me['state'] == 'managing_app') {
		$device_qq = $conn->query("SELECT * FROM `devices` WHERE `id` = '{$me['current_device']}'");
		$device = $device_qq->fetch_assoc();
		if ($message == 'Chặn thêm ứng dụng') {
			
		} elseif ($message == 'Quay lại') {
			$msg = "Quản lí";
			setState('managing_device');
			show_menu('device_manage_1');
		} elseif ($message == 'Bật chặn ứng dụng') {
			$msg = "Đã bật chặn ứng dụng";
			$config = json_decode($device['config'], true);
			$config['config']['BlockApp'] = '1';
			$config_json = json_encode($config);
			$conn->query("UPDATE `devices` SET `config` = '$config_json', `update_time`='".time()."' WHERE `id` = '{$device['id']}'");
			show_menu('manage_app');
		} elseif ($message == 'Tắt chặn ứng dụng') {
			$msg = "Đã tắt chặn ứng dụng";
			$config = json_decode($device['config'], true);
			$config['config']['BlockApp'] = '0';
			$config_json = json_encode($config);
			$conn->query("UPDATE `devices` SET `config` = '$config_json', `update_time`='".time()."' WHERE `id` = '{$device['id']}'");
			show_menu('manage_app');
		} else {
			$msg = "Quản lí duyệt web";
			show_menu('manage_app');
		}
	}
	
	if (!$no_msg) {
		$data['message']['text'] = $msg;
	}
	
	// =======================================
	$jsonData = json_encode($data);
	
	file_put_contents("json_data.json", $jsonData);

	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	if(!empty($input['entry'][0]['messaging'][0]['message'])){
	  $result = curl_exec($ch);
	  file_put_contents("curl_log", $result);
	}
}
function setState($state) {
	global $conn, $me;
	$conn->query("UPDATE `conversations` SET `state` = '$state' WHERE `fbid` = '{$me['fbid']}'");
}
function AddImage($img_url) {
	global $data;
	$data['message']['attachment']['type'] = "image";
	$data['message']['attachment']['payload'] = array();
	$data['message']['attachment']['payload']['url'] = $img_url;
	$data['message']['attachment']['payload']['is_reusable'] = false;
}
function AddQuickReply($text, $payload = "") {
	global $data;
	$btn = Array();
	$btn['content_type'] = "text";
	$btn['title'] = $text;
	$btn['payload'] = $payload;
	$data['message']['quick_replies'][] = $btn;
}
function InitButton($text) {
	global $data, $is_buttons;
	$is_buttons = true;
	$data['message']['attachment']['type'] = 'template';
	$data['message']['attachment']['payload']['template_type'] = 'button';
	$data['message']['attachment']['payload']['text'] = $text;
	$data['message']['attachment']['payload']['buttons'] = Array();
}
function AddButton($text, $payload = "") {
	global $data;
	$btn = Array();
	$btn['type'] = 'postback';
	$btn['title'] = $text;
	$btn['payload'] = $payload;
	$data['message']['attachment']['payload']['buttons'][] = $btn;
}
?>