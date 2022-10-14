<?php
// 設定ファイルの読み込み
require_once 'config.php';
// 関数ファイルの読み込み
require_once 'functions.php';
// セッション開始
session_start();

// セッション格納確認
if (!isset($_SESSION['USER'])) {
	header('Location:' . SITE_URL . 'login_admin.php');
	exit;
}

// DB接続
$pdo = connectDb();
// userデータ格納
$users = array();
// 画面遷移判定
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	// はじめてのリクエストした時の処理

	// user一覧取得
	$sql = "SELECT * FROM cm_user";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$users = $stmt->fetchAll();

	// CSRF対策
// 	setToken();

} else {
	// フォームからサブミットされた時の処理

	// CSRF対策
// 	checkToken();

}


?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <title>管理者HOME | <?php echo SERVICE_NAME; ?></title>
    <meta name="description" content="人気動画サイト自動作成ツール「コンテンツメーカー」" />
    <meta name="keywords" content="コンテンツメーカー,動画,ツール" />
		<link href="css/bootstrap.min.css" rel="stylesheet">
	  <style src="js/bootstrap.min.js"></style>
	  <link href="css/contentsmaker.css" rel="stylesheet">
	</head>

	<body class="main">

		<div class="container">
			<div class="nav navbar-inverse navbar-fixed-top">
				<div class="navbar-inner">
					<div class="container">
						<a class="navbar-brand" href="<?php echo SITE_URL; ?>"><?php echo SERVICE_SHORT_NAME; ?></a>
						<ul class="nav navbar-nav">
							<li><a href="./index_admin.php">ダッシュボード</a></li>
							<li class="active"><a href="./user_list.php">ユーザー管理</a></li>
							<li><a href="./logout_admin.php">ログアウト</a></li>
						</ul>
					</div>
				</div>
			</div>

			<div class="from-group">
				<div class="col-mb-12">
					<h1>ユーザー一覧</h1>
					<table class="table table-striped table-bordered">
						<tr><th>氏名</th><th>メールアドレス</th><th>登録日時</th><th></th></tr>
<?php foreach ($users as $user) {?>
						<tr><td><?php echo $user['user_name']; ?></td><td><?php echo $user['user_email']; ?></td><td><?php echo $user['created_at']?></td><td><a href="./user_edit.php?id=<?php echo $user['id']; ?>">[変更]</a></td></tr>
<?php } ?>
					</table>
				</div>
			</div>
	  	<footer class="footer">
	  		<p><?php echo COPYRIGHT; ?></p>
	  	</footer>

		</div><!-- /.container -->

		<script src="//code.jquery.com/jquery.js"></script>
	    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	    <!--[if lt IE 9]>
	    <script src="js/html5shiv.js"></script>
	    <script src="js/respond.min.js"></script>
	    <![endif]-->
	    <script src="js/bootstrap.min.js"></script>
	</body>
</html>