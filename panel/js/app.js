// Dom7
var $$ = Dom7;
var app = new Framework7({
	root: '#app',
	id: 'io.framework7.testapp',
	name: 'arrow',
	theme: 'ios',
	methods: {
		helloWorld: function() {
			app.dialog.alert('Hello World!');
		},
	},
	domCache: true,
	routes: routes,
});

var controlling_device = "";
var device_config = {};

// Init/Create views
var homeView, catalogView, settingsView;

var access_token = "";
var host = "../../api/app_api.php";
var user = {};
var device_item = $('#devices-item').html();
var log_item = $('#log-item').html();
var custom_block_item = $('#custom-block-item').html();
var item_blank = $("#list-item-blank").html();
Mustache.parse(item_blank);
Mustache.parse(device_item);
Mustache.parse(log_item);
Mustache.parse(custom_block_item);

$$(document).on('page:init', '.page[data-name="home"]', function(e) {
	$.get(host, {
		access_token: access_token,
		action: 'list_devices'
	}).done(function(data) {
		user = data['self'];
		$("#device-list").html("");
		if (!data['devices']) {
			$("#devices-list").append(Mustache.render(item_blank, {}));
		} else {
			for (index in data['devices']) {
				//console.log(data['devices'][index]);
				$('#devices-list').append(Mustache.render(device_item, data['devices'][index]));
			}
		}
	});
});

$$(document).on('page:init', '.page[data-name="change-log"]', function(e) {
	/*$.get("https://arrow.nstudio.pw/versions/change_log.txt").done(function(data) {
		$("#change-log").text(data);
	})*/
});

$$('.ptr-content').on('ptr:refresh', function (e) {
	$.get(host, {
		access_token: access_token,
		action: 'list_devices'
	}).done(function(data) {
		$("#device-list").html("");
		user = data['self'];
		if (!data['devices']) {
			$("#devices-list").append(Mustache.render(item_blank, {}));
		} else {
			for (index in data['devices']) {
				//console.log(data['devices'][index]);
				$('#devices-list').append(Mustache.render(device_item, data['devices'][index]));
			}
		}
	});
	app.ptr.done();
});

document.addEventListener("deviceready", function() {
	facebookConnectPlugin.getLoginStatus(function(loginStatus) {
		if (loginStatus.status === 'connected') {
			homeView = app.views.create('#view-home', {
				url: '/home/',
				xhrCache: false
			});
			catalogView = app.views.create('#view-changelog', {
				url: '/change-log/'
			});
			settingsView = app.views.create('#view-about', {
				url: '/about/'
			});
			// logged in
			access_token = loginStatus.authResponse.accessToken;
			//console.log(access_token);
		} else {
			app.popup.open($("#popup-login"));
		}
	}, function(error) {
		alert(error);
	});
	$$(document).on('page:init', '.page[data-name="add-device"]', function(e) {
		$("#verify_code").val(user.verify_code);
	});
}, false);

function change_cpanel_password() {
	app.dialog.prompt("Nhập mật khẩu mới cho việc đăng nhập vào quản lí trực tiếp trên máy tính:", function (value) {
		app.preloader.show();
		$.get(host, {
			access_token: access_token,
			action: 'change_password',
			deviceID: controlling_device,
			password: value
		}).done(function(data) {
			if (data.error !== '') {
				app.dialog.alert(data.console.error());
			} else {
				app.dialog.alert("Đã đổi mật khẩu thành công!");
			}
			app.preloader.hide();
		});
	}, function () {
		app.dialog.alert("Mật khẩu chưa được thay đổi");
	});
}

function instant_msg() {
	app.dialog.prompt("Nhập tiêu đề:", function (title_value) {
		if (title_value !== '') {
			app.dialog.prompt("Nhập nội dung nhắn:", function (title_body) {
				if (title_body !== '') {
					$.get(host, {
						access_token: access_token,
						action: 'instant_msg',
						deviceID: controlling_device,
						title: title_value,
						body: title_body
					}).done(function(data) {
						app.dialog.alert(data.msg);
					});
				} else {
					app.dialog.alert("Không được để trống nội dung!");
				}
			});
		} else {
			app.dialog.alert("Không được để trống nội dung!");
		}
	});
}

function shutdown() {
	$.get(host, {
		access_token: access_token,
		action: 'shutdown',
		deviceID: controlling_device
	}).done(function(data) {
		app.dialog.alert(data.msg);
	});
}

function block_web() {
	app.dialog.prompt("Nhập địa chỉ web muốn chặn vào:", function (value) {
		app.preloader.show();
		$.get(host, {
			access_token: access_token,
			action: 'block_web',
			deviceID: controlling_device,
			url: value
		}).done(function(data) {
			if (data.msg !== '') {
				app.dialog.alert(data.msg);
			} else {
				app.dialog.alert(data.error);
			}
			device_config = data['devices']['config'];
			//console.log(device_config);
			$("#custom-block-list").html("");
			var block_web = device_config['block_web'];
			var custom_block_list = block_web['custom_list'];
			for (index in custom_block_list) {
				//console.log(index + ": " + custom_block_list[index]);
				$("#custom-block-list").append(Mustache.render(custom_block_item, {
					address: index
				}));
			}
			app.preloader.hide();
		});
	}, function () {
		app.dialog.alert("Địa chỉ không hợp lệ");
	});
}

function block_app() {
	app.dialog.prompt("Nhập tên tệp thực thi muốn chặn vào (Ví dụ: game.exe):", function (value) {
		app.preloader.show();
		$.get(host, {
			access_token: access_token,
			action: 'block_app',
			deviceID: controlling_device,
			appname: value
		}).done(function(data) {
			if (data.msg !== '') {
				app.dialog.alert(data.msg);
			} else {
				app.dialog.alert(data.error);
			}
			device_config = data['devices']['config'];
			//console.log(device_config);
			$("#custom-block-list").html("");
			var block_web = device_config['block_windows'];
			var custom_block_list = block_web['app'];
			for (index in custom_block_list) {
				//console.log(index + ": " + custom_block_list[index]);
				$("#custom-block-list").append(Mustache.render(custom_block_item, {
					address: index
				}));
			}
			app.preloader.hide();
		});
	}, function () {
		app.dialog.alert("Địa chỉ không hợp lệ");
	});
}

function block_title() {
	app.dialog.prompt("Nhập cụm từ muốn chặn trong tiêu đề:", function (value) {
		app.preloader.show();
		$.get(host, {
			access_token: access_token,
			action: 'block_title',
			deviceID: controlling_device,
			title: value
		}).done(function(data) {
			if (data.msg !== '') {
				app.dialog.alert(data.msg);
			} else {
				app.dialog.alert(data.error);
			}
			device_config = data['devices']['config'];
			//console.log(device_config);
			$("#custom-block-list").html("");
			var block_web = device_config['block_windows'];
			var custom_block_list = block_web['title'];
			for (index in custom_block_list) {
				$("#custom-block-list").append(Mustache.render(custom_block_item, {
					address: index
				}));
			}
			app.preloader.hide();
		});
	}, function () {
		app.dialog.alert("Địa chỉ không hợp lệ");
	});
}

function device_settings(deviceID) {
	controlling_device = deviceID;
	app.router.navigate('/device-settings/');
}

$$(document).on('page:init', '.page[data-name="device-settings"]', function(e) {
	// first, load the settings from the server to make sure user has old settings inside workplace
	app.preloader.show();
	$.get(host, {
		access_token: access_token,
		action: 'settings',
		deviceID: controlling_device
	}).done(function(data) {
		////console.log(data['devices']['config']);
		$("#device-name").val(data['devices']['name']);
		$("#device-ip").val(data['devices']['ip']);
		device_config = JSON.parse(data['devices']['config']);
		setting_val('allow_offline');
		setting_val('BlockWeb');
		setting_val('BlockApp');
		setting_val('BlockTitle');
		setting_val('CUpload');
		setting_val('CSplash');
		setting_val('StartWithWin');
		setting_val('CaptureScreen');
		setting_val('LogHistory');
		app.preloader.hide();
	});
});

$$(document).on('page:init', '.page[data-name="block-web"]', function(e) {
	var block_web = device_config['block_web'];
	var custom_block_list = block_web['custom_list'];
	for (index in custom_block_list) {
		//console.log(index + ": " + custom_block_list[index]);
		$("#custom-block-list").append(Mustache.render(custom_block_item, {
			address: index
		}));
	}
});

$$(document).on('page:init', '.page[data-name="block-app"]', function(e) {
	var block_web = device_config['block_windows'];
	var custom_block_list = block_web['app'];
	for (index in custom_block_list) {
		$("#custom-block-list").append(Mustache.render(custom_block_item, {
			address: index
		}));
	}
});

$$(document).on('page:init', '.page[data-name="block-title"]', function(e) {
	var block_web = device_config['block_windows'];
	var custom_block_list = block_web['title'];
	for (index in custom_block_list) {
		$("#custom-block-list").append(Mustache.render(custom_block_item, {
			address: index
		}));
	}
});

function setting_val(name) {
	if (device_config['config'][name] === 1 || device_config['config'][name] === '1') {
		$('#'+name).prop('checked', true);
	}
	$('#'+name).change(function () {
		app.preloader.show();
		//console.log(name +": " + $('#'+name).is(":checked"));
		$.get(host, {
			access_token: access_token,
			action: 'change_config',
			deviceID: controlling_device,
			cf_name: name,
			cf_value: ($('#'+name).is(":checked")===true)?1:0
		}).done(function(data) {
			////console.log(data);
			app.preloader.hide();
		});
	});
}

function list_log(deviceID) {
	$('#list-log').html('');
	app.preloader.show();
	$.get(host, {
		access_token: access_token,
		action: 'list_log',
		deviceID: deviceID
	}).done(function(data) {
		var log_list = data['logs'];
		if (log_list) {
			for (index in log_list) {
				log_list[index]['access_token'] = access_token;
				$('#list-log').append(Mustache.render(log_item, log_list[index]));
				app.popup.open($(".log-popup"));
				app.preloader.hide();
			}
		} else {
			app.dialog.alert("Không có bản ghi nào trong hệ thống!");
			app.preloader.hide();
		}
	});
}

function LOGINFB() {
	facebookConnectPlugin.login(['public_profile', 'user_birthday', 'user_location', 'email'], function(user_data) {
		location.reload();
	}, function(error) {
		alert('Đã xảy ra lỗi, mã lỗi: LOG:LV3');
		alert(JSON.stringify(error));
	});
}

function logout() {
	facebookConnectPlugin.logout(function (success) {
		location.reload();
	});
}
