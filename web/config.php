<?php
error_reporting(E_ALL & ~E_NOTICE);

header("Content-Type: text/html; charset=UTF-8");

date_default_timezone_set('Asia/Tokyo');

define('SERVICE_NAME', 'コンテンツメーカー');
define('SERVICE_SHORT_NAME', 'contentsmaker');
define('COPYRIGHT', '&copy; 2015 Koheiji HeyHey');
define('COOKIE_PATH', '/');

// データベース接続用定数
define('SITE_URL', 'http://localhost/contentsmaker/web/');
define('DB_HOST', 'localhost');
define('DB_NAME', 'contentsmaker');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

// define('SITE_URL', 'http://koheiji.sakura.ne.jp/contentsmaker/web/');
// define('DB_HOST', 'mysql476.db.sakura.ne.jp');
// define('DB_USER', 'koheiji');
// define('DB_PASSWORD', 'himitu552000');
// define('DB_NAME', 'koheiji_contentsmaker');

// APIキー(google)
define('API_KEY', 'AIzaSyAmuY3UjwU_cSnK0_rwYU2PU3n2vPOrvYk');

// 管理者メールアドレス
define('ADMIN_MAIL_ADDRESS', 'phpphp552000@yahoo.co.jp');

$process_hour_array = array(
	"99" => "選択してください",
	"1" => "1時間ごと",
	"2" => "2時間ごと",
	"3" => "3時間ごと",
	"4" => "4時間ごと",
	"5" => "5時間ごと",
	"6" => "6時間ごと",
	"7" => "7時間ごと",
	"8" => "8時間ごと",
	"9" => "9時間ごと",
	"10" => "10時間ごと",
	"11" => "11時間ごと",
	"12" => "12時間ごと",
	"13" => "13時間ごと",
	"14" => "14時間ごと",
	"15" => "15時間ごと",
	"16" => "16時間ごと",
	"17" => "17時間ごと",
	"18" => "18時間ごと",
	"19" => "19時間ごと",
	"20" => "20時間ごと",
	"21" => "21時間ごと",
	"22" => "22時間ごと",
	"23" => "23時間ごと",
	"24" => "24時間ごと",
);
?>
