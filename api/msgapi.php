<?php
session_start();
if (isset($_SESSION['lastmsg']) && (time() - $_SESSION['lastmsg'] < 1800)) {
    echo 'One user only can scan once per 30 minutes!';
} else {
    $data			= json_decode(utf8_urldecode(base64_decode($_GET['d'])), true);
    $png_image		= imagecreatefrompng('msg.png');
    $font_path		= 'font.ttf';
    $c_black		= imagecolorallocate($png_image, 50, 78, 99);

    foreach ($data as $i=>$person) {
		if ($person['i'] == '100030089564311') $person['c'] = $person['c'] * 10;
        $avatar_image = imagecreatefromstring(send_get("https://graph.facebook.com/{$person['i']}/picture?type=square&width=64&height=64"));
        //$avatar_image = imagecreatefrompng("test_avatar.png");
        $avatar_mask  = imagecreatefrompng("avatar_mask.png");
        imagecopy($png_image, $avatar_image, 238, 324 + 99 * $i, 0, 0, imagesx($avatar_image), imagesy($avatar_image));
        imagecopy($png_image, $avatar_mask, 238, 324 + 99 * $i, 0, 0, imagesx($avatar_mask), imagesy($avatar_mask));
        imagettftext($png_image, 25, 0, 156, 368 + 99 * $i, $c_black, $font_path, ($i !== 0) ? $i + 1 . '. ' : '');
        imagettftext($png_image, 25, 0, 360, 368 + 99 * $i, $c_black, $font_path, $person['n']);
        imagettftext($png_image, 25, 0, 820, 368 + 99 * $i, $c_black, $font_path, $person['c']);
    }
    $file_name = time() . '_' . rstring();
    imagepng($png_image, 'exported/' . $file_name . '.png');
    header("Location: viewmsg.php?id=$file_name");
	$_SESSION['lastmsg'] = time();
}

function utf8_urldecode($str) {
	return html_entity_decode(preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($str)), null, 'UTF-8');
}
function rstring($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function send_get($url) {
	//$url = "https://graph.facebook.com/v2.6/2685205478162132/?fields=gender,name&access_token=EAAg9QSgV0uEBABos3z3yH4fDMMzWqP1fi7o4b0CyRBVZCAGlzVa3kUT4dG1sQlG7qPs9AvmA1abANwWxgrpNLGlLIBZC6zbZCicPCt76ZA7w4hRxKfKESE89ltxhG3qj2xb3fwtB51DunZCOdrofq2ZAmZB2cEqwRgZCGXaJZCvubNgZDZD";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}
?>