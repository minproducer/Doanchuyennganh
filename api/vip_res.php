<?php
$conn = new mysqli("localhost", "admin_root", "nhan1o12oo1", "admin_arrow");
$resID    = $_GET['resID'];
$res_query = $conn->query("SELECT * FROM `logs` WHERE `id` = '$resID'");
$res = $res_query->fetch_assoc();
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
?>