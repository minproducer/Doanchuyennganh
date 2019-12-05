<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json;charset=utf8mb4");
date_default_timezone_set("Asia/Bangkok");
require_once('Facebook/autoload.php');
$conn = new mysqli("localhost", "root", "", "db_arrow");
$conn->set_charset("utf8");
$conn->query("SET NAMES UTF8");
$return = array();
$error = "";
/*  FACEBOOK CONFIGS*/


// tu tao lai app khac

$fb = new Facebook\Facebook([
  'app_id' => '2467482420159112',
  'app_secret' => 'e1ee95ef36ebb65a94905b5c5b955697',
  'default_graph_version' => 'v2.7',
]);

if (isset($_GET['access_token'])) {
	$access_token = $_GET['access_token'];
	try {
		$response = $fb->get('/me?fields=id,name,email,first_name,last_name,location,birthday', $access_token);
	} catch (Facebook\Exceptions\FacebookResponseException $e) {
		$error = 'Graph returned an error: ' . $e->getMessage();
	} catch (Facebook\Exceptions\FacebookSDKException $e) {
		$error = 'Facebook SDK returned an error: ' . $e->getMessage();
	}
	$usr = $response->getGraphUser();
	$user_data = array();
	foreach ($usr as $field => $value) {
		$user_data[$field] = $value;
	}
	$return['self'] = $user_data;
	$userid = $user_data['id'];
	if (isset($_GET['deviceID'])) {
		$check = $conn->query("SELECT `owner` FROM `devices` WHERE `deviceID` = '".$_GET['deviceID']."'");
		if ($check->num_rows == 1) {
			$device_data = $check->fetch_assoc();
			if ($device_data['owner'] !== $userid) {
				die ('failed!');
			}
		} else {
			die('failed!');
		}
	}
	$user_query = $conn->query("SELECT * FROM `users` WHERE `fbid` = '$userid'");
	if ($user_query->num_rows == 0) {
		$verify_code = random_string(8);
		$verify_code_check = $conn->query("SELECT `id` FROM `users` WHERE `verify_code` = '$verify_code'");
		while ($verify_code_check->num_rows !== 0) {
			$verify_code_check = $conn->query("SELECT `id` FROM `users` WHERE `verify_code` = '$verify_code'");
			$verify_code = random_string(8);
		}
		$conn->query("INSERT INTO `users`(`fbid`, `access_token`, `email`, `gender`, `fullname`, `verify_code`)
		VALUES ('$userid', '$access_token', '".$user_data['email']."', '".$user_data['gender']."','".$user_data['name']."','$verify_code')");
	} else {
		$user_data_db = $user_query->fetch_assoc();
		$verify_code = $user_data_db['verify_code'];
	}
	$return['self']['verify_code'] = $verify_code;
	if (isset($_GET['action'])) {
		$action = $_GET['action'];
		if ($action == 'list_devices') {
			$self_devices_query = $conn->query("SELECT `id`, `name`, `os`, `ip`, `time`, `update_time`, `owner`, `deviceID`, `activated` FROM `devices` WHERE `owner` = '$userid' AND `activated` = '1'");
			if ($self_devices_query) {
				while ($device = $self_devices_query -> fetch_assoc()) {
					$diff_to_now = time() - $device['time'];
					if ($diff_to_now < 60) {
						$device['online_status'] = 'online';
						$device['time_text'] = $diff_to_now . ' giây trước.';
					} else {
						$device['online_status'] = 'offline';
						if ($diff_to_now < 3600) {
							$device['time_text'] = round($diff_to_now/60) . ' phút trước.';
						} elseif ($diff_to_now < 3600*24) {
							$device['time_text'] = round($diff_to_now/3600) . ' tiếng trước.';
						} else {
							$device['time_text'] = round($diff_to_now/(3600*24)) . ' ngày trước.';
						}
					}
					$return['devices'][] = $device;
				}
			}
		} elseif ($action == 'settings') {
			$self_devices_query = $conn->query("SELECT * FROM `devices` WHERE `deviceID` = '".$_GET['deviceID']."' LIMIT 1");
			if ($self_devices_query) {
				while ($device = $self_devices_query -> fetch_assoc()) {
					$diff_to_now = time() - $device['time'];
					if ($diff_to_now < 60) {
						$device['online_status'] = 'online';
						$device['time_text'] = $diff_to_now . ' giây trước.';
					} else {
						$device['online_status'] = 'offline';
						if ($diff_to_now < 3600) {
							$device['time_text'] = round($diff_to_now/60) . ' phút trước.';
						} elseif ($diff_to_now < 3600*24) {
							$device['time_text'] = round($diff_to_now/3600) . ' tiếng trước.';
						} else {
							$device['time_text'] = round($diff_to_now/(3600*24)) . ' ngày trước.';
						}
					}
					$return['devices'] = $device;
				}
			}
		} elseif ($action == 'change_config') {
		    $cf_name = $_GET['cf_name'];
		    $cf_value = $_GET['cf_value'];
		    $device_query = $conn->query("SELECT `config` FROM `devices` WHERE `deviceID` = '".$_GET['deviceID']."' LIMIT 1");
		    $device = $device_query->fetch_assoc();
		    $device_config = json_decode($device['config'], true);
		    $device_config['config'][$cf_name] = $cf_value;
		    $device_config_new = json_encode($device_config);
		    $conn->query("UPDATE `devices` SET `config` = '$device_config_new', `update_time`='".time()."' WHERE `deviceID` = '".$_GET['deviceID']."'");
		} elseif ($action == 'change_password') {
			if (isset($_GET['deviceID'])) {
				$psw = $_GET['password'];
				if (strlen($psw) >= 3) {
					$device_query = $conn->query("SELECT `config` FROM `devices` WHERE `deviceID` = '".$_GET['deviceID']."' LIMIT 1");
					$device = $device_query->fetch_assoc();
					$device_config = json_decode($device['config'], true);
					$device_config['config']['password'] = $psw;
					$device_config_new = json_encode($device_config);
					if (!$conn->query("UPDATE `devices` SET `config` = '$device_config_new', `update_time`='".time()."' WHERE `deviceID` = '".$_GET['deviceID']."'")) {
						$return['error'] = "Có lỗi trong quá trình thay đổi mật khẩu";
					}
				} else {
					$return['error'] = "Mật khẩu phải dài hơn 6 ký tự.";
				}
			}
		} elseif ($action =='list_log') {
			if (isset($_GET['deviceID'])) {
				$deviceID = $_GET['deviceID'];
				$device_query = $conn->query("SELECT `id` FROM `devices` WHERE `deviceID` = '$deviceID'");
				if ($device_query->num_rows == 1) {
					$log_query = $conn->query("SELECT `id`, `owner`, `type`, `time` FROM `logs` WHERE `owner` = '$deviceID'");
					//$return['query'] = "SELECT `id`, `owner`, `type`, `time` FROM `logs` WHERE `owner` = '$deviceID'";
					while ($log = $log_query->fetch_assoc()) {
						$log['time'] = date("Y/m/d H:i:s", $log['time']);
						if ($log['type'] == 'screenshot') {
							$log['type_text'] = "Ảnh chụp màn hình";
						} elseif ($log['type'] == 'history') {
							$log['type_text'] = "Lịch sử web";
						} else {
							$log['type_text'] = "Khác";
						}
						$return['logs'][] = $log;
					}
				}
			}
		} elseif ($action == 'block_web') {
			if (isset($_GET['deviceID'])) {
				$link = $_GET['url'];
				preg_match_all('#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si', $link, $parse);
				if (isset($parse[0][0])) {
					$domain = $parse[0][0];
					$device_query = $conn->query("SELECT `config` FROM `devices` WHERE `deviceID` = '".$_GET['deviceID']."' LIMIT 1");
					$device = $device_query->fetch_assoc();
					$device_config = json_decode($device['config'], true);
					$device_config['block_web']['custom_list'][$domain] = 1;
					$return['devices']['config'] = $device_config;
					$device_config_new = json_encode($device_config);
					if (!$conn->query("UPDATE `devices` SET `config` = '$device_config_new', `update_time`='".time()."' WHERE `deviceID` = '".$_GET['deviceID']."'")) {
						$return['error'] = "Có lỗi trong quá trình chặn web";
					} else {
						$return['msg'] = "Đã chặn " . $domain;
					}
				}
			}
		} elseif ($action == 'block_app') {
			if (isset($_GET['deviceID'])) {
				if (preg_match('/^([-\.\w]+)$/',$_GET['appname']) > 0) {
					$device_query = $conn->query("SELECT `config` FROM `devices` WHERE `deviceID` = '".$_GET['deviceID']."' LIMIT 1");
					$device = $device_query->fetch_assoc();
					$device_config = json_decode($device['config'], true);
					$device_config['block_windows']['app'][$_GET['appname']] = 1;
					$return['devices']['config'] = $device_config;
					$device_config_new = json_encode($device_config);
					if (!$conn->query("UPDATE `devices` SET `config` = '$device_config_new', `update_time`='".time()."' WHERE `deviceID` = '".$_GET['deviceID']."'")) {
						$return['error'] = "Có lỗi trong quá trình chặn Ứng dụng";
					} else {
						$return['msg'] = "Đã chặn " . $_GET['appname'];
					}
				} else {
					$return['error'] = "Có lỗi xảy ra!";
				}
			}
		} elseif ($action == 'block_title') {
			if (isset($_GET['deviceID'])) {
				if (strlen($_GET['title']) > 0 && strlen($_GET['title']) < 10) {
					$device_query = $conn->query("SELECT `config` FROM `devices` WHERE `deviceID` = '".$_GET['deviceID']."' LIMIT 1");
					$device = $device_query->fetch_assoc();
					$device_config = json_decode($device['config'], true);
					$device_config['block_windows']['title'][$_GET['title']] = 1;
					$return['devices']['config'] = $device_config;
					$device_config_new = json_encode($device_config);
					if (!$conn->query("UPDATE `devices` SET `config` = '$device_config_new', `update_time`='".time()."' WHERE `deviceID` = '".$_GET['deviceID']."'")) {
						$return['error'] = "Có lỗi trong quá trình chặn Tiêu đề";
					} else {
						$return['msg'] = "Đã chặn " . $_GET['title'];
					}
				} else {
					$return['error'] = "Có lỗi xảy ra!";
				}
			}
		} elseif ($action == 'instant_msg') {
			if (isset($_GET['deviceID'])) {
				if (strlen($_GET['title'])> 0) {
					if (strlen($_GET['body']) > 0) {
						$input_to_db = base64_encode('msgbox(64,"'.str_replace('"', "`", $_GET['title']) . '","'. str_replace('"', "`", $_GET['body']) . '")');
						if ($conn->query("UPDATE `devices` SET `instant_cmd_count`=(`instant_cmd_count`+1), `instant_cmd` = '$input_to_db' WHERE `deviceID` = '".$_GET['deviceID']."'")) {
							$return['msg'] = "Đã gửi thành công";
						} else {
							$return['msg'] = "Đã có lỗi xảy ra";
						}
					} else {
						$return['msg'] = "Đã có lỗi xảy ra";
					}
				} else {
					$return['msg'] = "Đã có lỗi xảy ra";
				}
			} else {
				$return['msg'] = "Đã có lỗi xảy ra";
			}
		} elseif ($action == 'shutdown') {
			if (isset($_GET['deviceID'])) {
				$input_to_db = base64_encode('shutdown(8)');
				if ($conn->query("UPDATE `devices` SET `instant_cmd_count`=(`instant_cmd_count`+1), `instant_cmd` = '$input_to_db' WHERE `deviceID` = '".$_GET['deviceID']."'")) {
					$return['msg'] = "Đã gửi thành công";
				} else {
					$return['query'] = "UPDATE `devices` SET `instant_cmd_count`=(`instant_cmd_count`+1), `instant_cmd` = '$input_to_db' WHERE `deviceID` = '".$_GET['deviceID']."'";
					$return['msg'] = "Đã có lỗi xảy ra";
				}
			} else {
				$return['msg'] = "Đã có lỗi xảy ra";
			}
		} else {
			$error = "Action is not valid";
		}
	} else {
		$error = "Action not found";
	}
} else {
	$error = "Access Token not found";
}
$return['error'] = $error;
echo (json_encode($return));
function random_string($length = 10) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>