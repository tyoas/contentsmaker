<?php
// 設定ファイルの読み込み
require_once 'config.php';
// 関数ファイルの読み込み
require_once 'functions.php';
// セッション開始
session_start();
// ログアウト処理
$_SESSION = array();

session_destroy();
// 画面遷移
header('Location:' . SITE_URL . 'admin_login.php');
?>