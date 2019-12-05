<?php
set_time_limit (100000);
ini_set('max_execution_time', 1000000);
ini_set("log_errors", 1);
ini_set("error_log", "php-error.log");
$page_token = "EAAg9QSgV0uEBALq3VxHD9DNnoZANy541W3b3KlzideX74emOQdbvetMiDiDPquSSZB055HEEoiLHykrNvQKreK0FLWLHLZBAkIq14aigXtXRCWXQGEOLHkcVdFdqtJcZA1PA4H6VvMJHQlMaPrM6bZAOQZAwnlEGmCsNDw0SubeQZDZD";

$conn = new mysqli('localhost', 'admin_monokaijs', 'nhan1o12oo1', 'admin_quests');

$users = $conn->query("SELECT * FROM `users` WHERE `chat_state` = 0");

/*send_message(array(
		"attachment" => array(
			"type" => "image",
			"payload" => array(
				"attachment_id" => "354989855150920"
			)
		)
), '2296261210467474');*/

/*
"title": "View",
					"type": "web_url",
					"url": "https://facebook.com",
					"messenger_extensions": true,
					"webview_height_ratio": "tall",
					"fallback_url": "https://google.com"
*/

while ($user = $users->fetch_assoc()) {
	echo send_message(array(
		"attachment" => array (
			"type" => "template",
			"payload" => array(
				"template_type" => "button",
				"text" => "Nào nào... Tham gia buổi Livestream làm trắc nghiệm cùng nhau thôi nào!!!!",
				"buttons" => [
					array(
                        "title" => "Tham gia ngay",
                        "type" => "postback",
                        "payload" => json_encode(array(
							"type" => "web_url",
							"url" => "https://www.facebook.com/330213177689000/videos/424451231444471/",
							"messenger_extensions" => true,
							"webview_height_ratio" => "tall"
                        ))
					)
				]
			)
		)
	), $user['messenger_id']);
}

function send_message($msg, $rec) {
	global $page_token;
	$url = "https://graph.facebook.com/v2.6/me/messages?access_token=$page_token";
	$ch = curl_init($url);
	$jsonData = array(
		"recipient" => array(
			"id" => $rec
		),
		"message" => $msg
	);

	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonData));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	$result = curl_exec($ch);
	return $result;
}

function send_get($url) {
	//$url = "https://graph.facebook.com/v2.6/2685205478162132/?fields=gender,name&access_token=EAAg9QSgV0uEBABos3z3yH4fDMMzWqP1fi7o4b0CyRBVZCAGlzVa3kUT4dG1sQlG7qPs9AvmA1abANwWxgrpNLGlLIBZC6zbZCicPCt76ZA7w4hRxKfKESE89ltxhG3qj2xb3fwtB51DunZCOdrofq2ZAmZB2cEqwRgZCGXaJZCvubNgZDZD";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}
?>