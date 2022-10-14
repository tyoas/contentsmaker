<?php
// 設定ファイルの読み込み
require_once('config.php');
// 関数ファイルの読み込み
require_once('functions.php');
// パスワード暗号化に対してのファイル読み込み
require_once('lib/password.php');

// セッション開始
session_start();
// DB接続
$pdo = connectDb();

// セッション確認
if (!isset($_SESSION['USER'])) {
    header('Location: '.SITE_URL.'login.php');
    exit;
}

$user = $_SESSION['USER'];

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

	// CSRF対策
	 setToken();

	// フォームから入力された値を代入
    $user_name = $user['user_name'];
    $user_email = $user['user_email'];


} else {
	// フォームからサブミットされた時の処理

    // CSRF対策
    checkToken();

	// フォームに入力された値
    $user_name = $_POST['user_name'];
    $user_email = $_POST['user_email'];
    $user_password = $_POST['user_password'];

    // エラー処理
    $err = array();
    // 成功処理
    $complete_msg = "";

    // [氏名]未入力チェック
    if ($user_name == '') {
        $err['user_name'] = '氏名を入力して下さい。';
    }

    // [氏名]文字数チェック
    if (strlen(mb_convert_encoding($user_name, 'SJIS', 'UTF-8')) > 30) {
        $err['user_name'] = '氏名は30バイト以内で入力して下さい。';
    }

    // [パスワード]文字数チェック
    if (strlen(mb_convert_encoding($user_password, 'SJIS', 'UTF-8')) > 30) {
        $err['user_password'] = 'パスワードは30バイト以内で入力して下さい。';
    }

    // [メールアドレス]未入力チェック
    if ($user_email == '') {
        $err['user_email'] = 'メールアドレスを入力して下さい。';
    } else {
        // [メールアドレス]形式チェック
        if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $err['user_email'] = 'メールアドレスが不正です。';
        } else {
        	if (checkEmail($user_email, $pdo) && $user_email != $user['user_email']) {
        		$err['user_email'] = 'このメールアドレスは既に登録されています。';
        	}
        }
    }

    if (empty($err)) {
        // userテーブルの更新
//         $sql = "UPDATE cm_user SET user_name = :user_name, user_email = :user_email, user_password = :user_password, updated_at = now() where id = :id";
    		$sql = "UPDATE cm_user SET user_name = :user_name, user_email = :user_email, ";
    		if ($user_password) {
    			$sql .= "user_password = :user_password, ";
    		}
    		$sql .= "updated_at = now() where id = :id";
        $stmt = $pdo->prepare($sql);
//         $params = array(":user_name" => $user_name, ":user_email" => $user_email, ":user_password" => password_hash($user_password, PASSWORD_DEFAULT), ":id" => $user['id']);
				$params = array();
				$params['user_name'] = $user_name;
				$params['user_email'] = $user_email;
				if ($user_password) {
					$params['user_password'] = password_hash($user_password, PASSWORD_DEFAULT);
				}
				$params['id'] = $user['id'];
        $stmt->execute($params);

        // セッション上のデータ更新
        $user['user_name'] = $user_name;
        $user['user_email'] = $user_email;
        $user['user_password'] = $user_password;
        $_SESSION['USER'] = $user;

        // 完了メッセージ表示
        $complete_msg = "修正が完了しました。";
    }

    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ユーザー情報編集 | <?php echo SERVICE_NAME; ?></title>
    <meta name="description" content="人気動画サイト自動作成ツール「コンテンツメーカー」" />
    <meta name="keywords" content="コンテンツメーカー,動画,ツール" />
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mykakugen.css" rel="stylesheet">
</head>

<body id="main">
    <div class="nav navbar-inverse navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <a class="navbar-brand" href="<?php echo SITE_URL; ?>"><?php echo SERVICE_SHORT_NAME; ?></a>
                <ul class="nav navbar-nav">
                    <li><a href="./index.php">HOME</a></li>
                    <li><a href="./setting.php">投稿設定</a></li>
                    <li class="active"><a href="./user_edit.php">アカウント</a></li>
                    <li><a href="./logout.php">ログアウト</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container">
        <h1>アカウント情報修正</h1>

        <?php if ($complete_msg): ?>
            <div class="alert alert-success">
                <?php echo $complete_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="sidebar-nav panel panel-default">
            <br />
            <div class="form-group <?php if ($err['user_name'] != '') echo 'has-error'; ?>">
                <label>氏名</label>
                <input type="text" class="form-control" name="user_name" value="<?php echo h($user_name); ?>" placeholder="氏名" value="<?php echo h($user_name); ?>" />
                <span class="help-block"><?php echo h($err['user_name']); ?></span>
            </div>

            <div class="form-group <?php if ($err['user_email'] != '') echo 'has-error'; ?>">
                <label>メールアドレス</label>
                <input type="text" class="form-control" name="user_email" value="<?php echo h($user_email); ?>" placeholder="メールアドレス" value="<?php echo h($user_email); ?>" />
                <span class="help-block"><?php echo h($err['user_email']); ?></span>
            </div>

            <div class="form-group <?php if ($err['user_password'] != '') echo 'has-error'; ?>">
                <label>パスワード</label>
                <input type="password" class="form-control" name="user_password" placeholder="未入力の場合は変更されません。" value="<?php echo h($user_password); ?>" />
                <span class="help-block"><?php echo h($err['user_password']); ?></span>
            </div>
            <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

            <div class="form-group">
                <input type="submit" class="btn btn-success btn-block" value="修正">
            </div>
        </form>
        <a href="javascript:void(0);" class="btn btn-danger btn-lg" onclick="var ok=confirm('退会しても宜しいですか?'); if (ok) location.href='user_delete.php?id=<?php echo h($user['id']); ?>'; return false;">退会</a>

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
