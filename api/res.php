<?php
$access_token = $_GET['access_token'];
$resID    = $_GET['resID'];
require_once('Facebook/autoload.php');
$conn = new mysqli("localhost", "admin_root", "nhan1o12oo1", "admin_arrow");
if (!$conn) {
	die('Failed to connect MySQL');
}
$conn->set_charset("utf8");
$conn->query("SET NAMES UTF8");
$return = array();
$error = "";
/*  FACEBOOK CONFIGS*/
$fb = new Facebook\Facebook([
  'app_id' => '497086490676190',
  'app_secret' => '960bafce339d1ed4c5caf91f8723d552',
  'default_graph_version' => 'v2.2',
]);
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
$res_query = $conn->query("SELECT * FROM `logs` WHERE `id` = '$resID'");
$res = $res_query->fetch_assoc();
$device_query = $conn->query("SELECT * FROM `devices` WHERE `deviceID` = '".$res['owner']."'");
$device = $device_query -> fetch_assoc();
if ($device['owner'] == $userid) {
	$content = file_get_contents("logs/".$res['owner']."/" . $res['fname']);
	if ($res['type'] == 'screenshot') {
		header('Content-Type: image/jpeg');
	} else {
		echo '<pre>';
	}
	echo $content;
	if ($res['type'] !== 'screenshot') {
		echo '</pre>';
	}
}
?>