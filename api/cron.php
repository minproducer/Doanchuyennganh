<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    date_default_timezone_set("Asia/Bangkok");
	include 'config.php';
	$posts = $conn->query('SELECT * FROM `scheduled` WHERE `time` < ' . (time() * 1000) . ' AND `done` <> 1');
	while ($post = $posts->fetch_assoc()) {
		if ($post['img'] !== '') {
			$return = send_get("https://graph.facebook.com/me/photos?method=post&message={$post['msg']}&url={$post['img']}&access_token={$post['token']}");
		} else {
			$return = send_get("https://graph.facebook.com/me/feed?method=post&message={$post['msg']}&access_token={$post['token']}");
		}
		$conn->query("UPDATE `scheduled` SET `done` = 1 WHERE `token` = '{$post['token']}'");
	}

	file_put_contents('run.txt', time());

	function send_get($url) {
    	//$url = "https://graph.facebook.com/v2.6/2685205478162132/?fields=gender,name&access_token=EAAg9QSgV0uEBABos3z3yH4fDMMzWqP1fi7o4b0CyRBVZCAGlzVa3kUT4dG1sQlG7qPs9AvmA1abANwWxgrpNLGlLIBZC6zbZCicPCt76ZA7w4hRxKfKESE89ltxhG3qj2xb3fwtB51DunZCOdrofq2ZAmZB2cEqwRgZCGXaJZCvubNgZDZD";
    	$ch = curl_init($url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	$result = curl_exec($ch);
    	curl_close($ch);
    	return $result;
    }
?>