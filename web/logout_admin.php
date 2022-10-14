<?php
// 設定ファイルの読み込み
require_once 'config.php';
// 関数ファイルの読み込み
require_once 'functions.php';
// セッション開始
session_start();
// ログアウト処理
$_SESSION = array();
if (isset($_COOKIE[session_name()])) {
	setcookie(session_name(), '', time()-86400, COOKIE_PATH);
}
session_destroy();
// 画面遷移
header('Location:' . SITE_URL . 'login_admin.php');
?>