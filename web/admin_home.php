<?php
// 設定ファイルの読み込み
require_once 'config.php';
// 関数ファイrの読み込み
require_once 'functions.php';
// セッション開始
session_start();
// DB接続
$pdo = connectDb();

if (!isset($_SESSION['ADMIN'])) {
	header('Location:' . SITE_URL . 'admin_login.php');
	exit;
}
// セッション情報格納
$admin = $_SESSION['ADMIN'];
// エラー配列の初期化
$err = array();
// 画面表示
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	// 初期表示画面

	$notice = $admin['notice'];

	// CSRF対策
	setToken();

} else {
	// フォームからサブミットされた時の処理

	// CSRF対策
	checkToken();

	// 入力された項目を代入
	$notice = $_POST['notice'];

	// 入力された内容でデータベースを更新
	$sql = "UPDATE cm_administrator SET notice = :notice, updated_at = now() WHERE id = :id";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(":notice" => $notice, ":id" => $admin['id']));

	// セッション内容を更新
	$admin['notice'] = $notice;
	$_SESSION['ADMIN'] = $admin;
}
unset($pdo);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ダッシュボード | <?php echo SERVICE_NAME; ?></title>
    <meta name="description" content="人気動画サイト自動作成ツール「コンテンツメーカー」" />
    <meta name="keywords" content="コンテンツメーカー,動画,ツール" />
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mykakugen.css" rel="stylesheet">
</head>

<body id="main">
    <div class="nav navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <a class="navbar-brand" href="<?php echo SITE_URL ?>admin_home.php"><?php echo SERVICE_SHORT_NAME; ?></a>
                <ul class="nav navbar-nav">
                    <li class="active"><a href="./admin_home.php">ダッシュボード</a></li>
                    <li><a href="./admin_user_list.php">ユーザー管理</a></li>
                    <li><a href="./admin_logout.php">ログアウト</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container">
        <h1>運営側からのお知らせ編集</h1>

        <div class="panel panel-default">
            <div class="panel-body">
                <form method="POST">
                    <div class="form-group <?php if ($err['notice'] != '') echo 'has-error'; ?>">
                        <label>お知らせ</label>
                        <textarea class="form-control" rows="3" name="notice"><?php echo h($notice); ?></textarea>
                        <span class="help-block"><?php echo h($err['notice']); ?></span>
                    </div>

                    <div class="form-group">
                        <input type="submit" class="btn btn-success btn-block" value="お知らせ変更" >
                    </div>
                    <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
                </form>
            </div>
        </div>

        <hr>
        <footer class="footer">
            <p><?php echo COPYRIGHT; ?></p>
        </footer>
    </div>

    <script src="//code.jquery.com/jquery.js"></script>
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
    <script src="js/bootstrap.min.js"></script>

</body>
</html>