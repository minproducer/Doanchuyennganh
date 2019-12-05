<?php
function show_menu($menu) {
	global $conn, $me, $device;
	switch($menu) {
		case 'login':
				AddQuickReply('Đăng nhập');
			break;
		case 'main':
				AddQuickReply('Danh sách thiết bị');
				AddQuickReply('Hướng dẫn sử dụng');
			break;
		case 'device_menu_1':
				AddQuickReply("Quản lí");
				AddQuickReply("Giám sát");
				AddQuickReply("Thông tin");
				AddQuickReply("Thiết bị khác");
			break;
		case 'device_manage_1':
				AddQuickReply("Gửi thông báo");
				AddQuickReply("Tắt máy tính");
				AddQuickReply("Các thiết lập khác");
				AddQuickReply("Thiết bị khác");
			break;
		case 'device_manage_2':
				$device_qq = $conn->query("SELECT * FROM `devices` WHERE `id` = '{$me['current_device']}'");
				$device = $device_qq->fetch_assoc();
				$config = json_decode($device['config'], true);
				if (!isset($config['config']['CaptureScreen']) || $config['config']['CaptureScreen'] == '0') {
					AddQuickReply("Bật chụp màn hình");
				} else {
					AddQuickReply("Tắt chụp màn hình");
				}
				AddQuickReply("Quản lí web");
				AddQuickReply("Quản lí ứng dụng");
				AddQuickReply("Đổi mật khẩu");
				AddQuickReply("Quay lại");
			break;
		case 'device_tracking':
				/**/
				AddQuickReply("Ảnh màn hình");
				AddQuickReply("Thời gian hôm nay");
				AddQuickReply("Quay lại");
			break;
		case 'manage_web':
				AddQuickReply("Chặn thêm web");
				AddQuickReply("Gỡ chặn web");
				$device_qq = $conn->query("SELECT * FROM `devices` WHERE `id` = '{$me['current_device']}'");
				$device = $device_qq->fetch_assoc();
				$config = json_decode($device['config'], true);
				if ($config['config']['BlockWeb'] == '0') {
					AddQuickReply("Bật chặn web");
				} else {
					AddQuickReply("Tắt chặn web");
				}
				AddQuickReply('Quay lại');
			break;
		case 'manage_app':
				AddQuickReply("Chặn thêm ứng dụng");
				AddQuickReply("Gỡ chặn ứng dụng");
				$device_qq = $conn->query("SELECT * FROM `devices` WHERE `id` = '{$me['current_device']}'");
				$device = $device_qq->fetch_assoc();
				$config = json_decode($device['config'], true);
				if ($config['config']['BlockApp'] == '0') {
					AddQuickReply("Bật chặn ứng dụng");
				} else {
					AddQuickReply("Tắt chặn ứng dụng");
				}
				AddQuickReply('Quay lại');
			break;
		case 'devices_list':
				$list_devices = $conn->query("SELECT * FROM `devices` WHERE `owner` = '{$me['fbid']}' AND `activated` = '1'");
				while ($d = $list_devices->fetch_assoc()) {
					AddQuickReply("{$d['id']}. {$d['name']}", $d['id']);
				}
			break;
		default:
			break;
	}
}
?>