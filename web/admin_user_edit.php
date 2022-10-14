<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'lib/password.php';

session_start();

$pdo = connectDb();

if (!isset($_SESSION['ADMIN'])) {
	header('Location:' . SITE_URL . 'admin_login.php');
	exit;
}

$admin = $_SESSION['ADMIN'];

$id = $_GET['id'];
$get_email = $_GET['user_email'];

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	// 対象ユーザーのデータ取得
	$sql = 'SELECT * FROM cm_user WHERE id = :id LIMIT 1';
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(':id' => $id));
	$user = $stmt->fetch();

	$user_name = $user['user_name'];
	$user_email = $user['user_email'];

} else {
	$user_name = $_POST['user_name'];
	$user_email = $_POST['user_email'];
	$user_password = $_POST['user_password'];

	$err = array();

	// [氏名]未入力、文字数チェック
	if ($user_name == '') {
		$err['user_name'] = '氏名を入力してください。';
	} else {
		if (strlen(mb_convert_encoding($user_name, 'SJIS', 'UTF-8')) > 30) {
			$err['user_name'] = '氏名は30字以内で入力してください。';
		}
	}

	// [メールアドレス]未入力、形式チェック
	if ($user_email == '') {
		$err['user_email'] = 'メールアドレスを入力してください。';
	} else {
		if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
			$err['user_email'] = '正しい形式で入力してください。';
		} else {
			if (checkEmail($user_email, $pdo) && $get_email != $user_email) {
				$err['user_email'] = 'このメールアドレスは既に登録されています。';
			}
		}
	}

	if (empty($err)) {
		$sql = 'UPDATE cm_user SET user_name = :user_name, user_email = :user_email, ';
		if ($user_password) {
			// パスワードは入力された場合のみ更新する
			$sql .= ' user_password = :user_password, ';
		}
		$sql .= ' updated_at = now() WHERE id = :id';
		$stmt = $pdo->prepare($sql);

		$params = array();
		$params['user_name'] = $user_name;
		$params['user_email'] = $user_email;
		if ($user_password) {
			$params['user_password'] = password_hash($user_password, PASSWORD_DEFAULT);
		}
		$params['id'] = $id;
		$stmt->execute($params);

		$complete_msg = '変更しました。';
	}
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ユーザー情報修正 | <?php echo SERVICE_NAME; ?></title>
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
        <h1>ユーザー情報修正</h1>

        <?php if ($complete_msg): ?>
            <div class="alert alert-success">
                <?php echo $complete_msg; ?>
            </div>
        <?php endif; ?>

        <div class="panel panel-default">
            <div class="panel-body">
                <form method="POST">
                    <div class="form-group <?php if ($err['user_name'] != '') echo 'has-error'; ?>">
                        <label>氏名</label>
                        <input type="text" class="form-control" name="user_name" value="<?php echo h($user_name); ?>" placeholder="氏名" />
                        <span class="help-block"><?php echo h($err['user_name']); ?></span>
                    </div>

                    <div class="form-group <?php if ($err['user_email'] != '') echo 'has-error'; ?>">
                        <label>メールアドレス</label>
                        <input type="text" class="form-control" name="user_email" value="<?php echo h($user_email); ?>" placeholder="メールアドレス" />
                        <span class="help-block"><?php echo h($err['user_email']); ?></span>
                    </div>

                    <div class="form-group <?php if ($err['user_password'] != '') echo 'has-error'; ?>">
                        <label>パスワード</label>
                        <input type="password" class="form-control" name="user_password" placeholder="未入力の場合は変更されません。" />
                        <span class="help-block"><?php echo h($err['user_password']); ?></span>
                    </div>

                    <div class="form-group">
                        <input type="submit" class="btn btn-success btn-block" value="修正" >
                    </div>
                    <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
                </form>
                <a href="javascript:void(0);" class="btn btn-danger btn-lg" onclick="var ok=confirm('退会しても宜しいですか?'); if (ok) location.href='admin_user_delete.php?id=<?php echo h($id); ?>'; return false;">退会</a>
            </div>
        </div>

        <a href="./admin_user_list.php">戻る</a>

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