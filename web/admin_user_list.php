<?php
// 設定ファイルの読み込み
require_once 'config.php';
// 関数ファイルの読み込み
require_once 'functions.php';
// セッション開始
session_start();

// DB接続
$pdo = connectDb();

// セッション格納確認
if (!isset($_SESSION['ADMIN'])) {
	header('Location:' . SITE_URL . 'admin_login.php');
	exit;
}

$admin = $_SESSION['ADMIN'];

// ユーザー一覧を取得
$sql = "SELECT * FROM cm_user";
$stmt = $pdo->query($sql);
$user_list = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ユーザ一覧 | <?php echo SERVICE_NAME; ?></title>
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
                    <li><a href="./admin_home.php">ダッシュボード</a></li>
                    <li class="active"><a href="./admin_user_list.php">ユーザー管理</a></li>
                    <li><a href="./admin_logout.php">ログアウト</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container">
        <h1>ユーザ一覧</h1>

		<table class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>ID</th>
					<th>名前</th>
					<th>編集</th>
				</tr>
			</thead>
			<?php foreach ($user_list as $user): ?>
				<tr>
					<td><?php echo h($user['id']);?></td>
					<td><?php echo h($user['user_name']);?></td>
					<td><a href="./admin_user_edit.php?id=<?php echo h($user['id']); ?>&user_email=<?php echo h($user['user_email']);?>">編集</a></td>
				</tr>
			<?php endforeach;?>
		</table>

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