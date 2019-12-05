<?php
echo send_get("https://graph.facebook.com/100030089564311/picture?type=square&width=64&height=64");

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