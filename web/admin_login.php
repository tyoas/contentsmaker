<?php
// 設定ファイルの読み込み
require_once 'config.php';
// 関数ファイルの読み込み
require_once 'functions.php';

// セッション開始
session_start();

// DB接続
$pdo = connectDb();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	// はじめて画面にアクセスした時の処理

	// CSRF対策
	setToken();

} else {
	// フォームからサブミットされた時の処理

	// CSRF対策
	checkToken();

	// メールアドレス、パスワードを受け取り変数に入れる
	$admin_account = $_POST['admin_account'];
	$admin_password = $_POST['admin_password'];

	// 入力チェックを行う
	$err = array();

	// [管理者アカウント]未入力
	if ($admin_account == '') {
		$err['admin_account'] = '管理者アカウントを入力してください。';
	}

	// [パスワード]未入力
	if ($admin_password == '') {
		$err['admin_password'] = 'パスワードを入力してください。';
	}

	$sql = "SELECT * FROM cm_administrator WHERE admin_account = :admin_account AND admin_password = :admin_password LIMIT 1";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(':admin_account' => $admin_account, ':admin_password' => $admin_password));
	$admin = $stmt->fetch();

	if (!$admin) {
		$err['admin_password'] = '認証に失敗しました。';
	}
	// もし$err配列に何もエラーメッセージが保存されていなかったら
	if (empty($err)) {
		// セッション格納
		$_SESSION['ADMIN'] = $admin;
		// DB切断
		unset($pdo);
		// 画面遷移
		header('Location:' . SITE_URL . 'admin_home.php');
	}
	unset($pdo);
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>管理者ログイン | <?php echo SERVICE_NAME; ?></title>
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
            </div>
        </div>
    </div>
    <div class="container">
        <h1>管理者ログイン</h1>

        <div class="panel panel-default">
            <div class="panel-body">
                <form method="POST">
                    <div class="form-group <?php if ($err['admin_account'] != '') echo 'has-error'; ?>">
                        <label>管理者アカウント</label>
                        <input type="text" class="form-control" name="admin_account" value="<?php echo h($admin_account); ?>" placeholder="管理者アカウント" />
                        <span class="help-block"><?php echo h($err['admin_account']); ?></span>
                    </div>

                    <div class="form-group <?php if ($err['admin_password'] != '') echo 'has-error'; ?>">
                        <label>パスワード</label>
                        <input type="password" class="form-control" name="admin_password" placeholder="パスワード" />
                        <span class="help-block"><?php echo h($err['admin_password']); ?></span>
                    </div>

                    <div class="form-group">
                        <input type="submit" class="btn btn-success btn-block" value="ログイン">
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