<?php
// 設定ファイル読み込み
require_once('config.php');
require_once('functions.php');

// セッション開始
session_start();
// DB接続
$pdo = connectDb();

// セッション格納確認
if (!isset($_SESSION['USER'])) {
	header('Location:' . SITE_URL . 'login.php');
	exit;
}

// セッション情報取得
$user = $_SESSION['USER'];

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

	// CSRF対策
	setToken();

	// 初期画面表示
	$youtube_category = !empty($user['youtube_category']) ? $user['youtube_category'] : '';
	$blog_host = !empty($user['blog_host']) ? $user['blog_host'] : '';
	$blog_xmlrpc = !empty($user['blog_xmlrpc']) ? $user['blog_xmlrpc'] : '';
	$blog_account = !empty($user['blog_account']) ? $user['blog_account'] : '';
	$blog_password = !empty($user['blog_password']) ? $user['blog_password'] : '';
	$blog_id = !empty($user['blog_id']) ? $user['blog_id'] : '';
	$blog_category = !empty($user['blog_category']) ? $user['blog_category'] : '';
	$twitter_consumer_key = !empty($user['twitter_consumer_key']) ? $user['twitter_consumer_key'] : '';
	$twitter_consumer_secret = !empty($user['twitter_consumer_secret']) ? $user['twitter_consumer_secret'] : '';
	$twitter_access_token = !empty($user['twitter_access_token']) ? $user['twitter_access_token'] : '';
	$twitter_access_token_secret = !empty($user['twitter_access_token_secret']) ? $user['twitter_access_token_secret'] : '';
	$process_hour = !empty($user['process_hour']) ? $user['process_hour'] : '';
} else {

	// CSRF対策
	checkToken();

	// エラー配列
	$err = array();
	// 登録完了メッセージ
	$complete_msg = '';
	// youtube情報
	$youtube_category = $_POST['youtube_category'];
	// blog情報
	$blog_host = $_POST['blog_host'];
	$blog_xmlrpc = $_POST['blog_xmlrpc'];
	$blog_account = $_POST['blog_account'];
	$blog_password = $_POST['blog_password'];
	$blog_id = $_POST['blog_id'];
	$blog_category = $_POST['blog_category'];
	// twitter情報
	$twitter_consumer_key = $_POST['twitter_consumer_key'];
	$twitter_consumer_secret = $_POST['twitter_consumer_secret'];
	$twitter_access_token = $_POST['twitter_access_token'];
	$twitter_access_token_secret = $_POST['twitter_access_token_secret'];
	// 処理時間設定
	$process_hour = $_POST['process_hour'];

	// [youtubeカテゴリー]未入力
	if ($youtube_category == '') {
		$err['youtube_category'] = '動画カテゴリーを指定して下さい。';
	}
	// [blogホスト]未入力チェック
	if ($blog_host == '') {
		$err['blog_host'] = 'ブログHOSTを入力して下さい。';
	}
	// [blogXMLRPC]未入力チェック
	if ($blog_xmlrpc == '') {
		$err['blog_xmlrpc'] = 'XMLRPCパスを入力して下さい。';
	}
	// [blogアカウント]未入力チェック
	if ($blog_account == '') {
		$err['blog_account'] = 'アカウントを入力して下さい。';
	}
	// [blogパスワード]未入力チェック
	if ($blog_password == '') {
		$err['blog_password'] = 'パスワードを入力して下さい。';
	}
	// [blogID]未入力チェック
	if ($blog_id == '') {
		$err['blog_id'] = 'ブログIDを入力して下さい。';
	}
	// [blogカテゴリー]未入力チェック
	if ($blog_category == '') {
		$blog_category = 'カテゴリーを入力して下さい。';
	}
	// [twitter_consumer_key]未入力チェック
	if ($twitter_consumer_key == '') {
		$err['twitter_consumer_key'] = 'Consumer Keyを入力して下さい。';
	}
	// [Twitter Consumer Secret]未入力チェック
	if ($twitter_consumer_secret == '') {
		$err['twitter_consumer_secret'] = 'Consumer Secretを入力して下さい。';
	}
	// [Twitter Access Token]未入力チェック
	if ($twitter_access_token == '') {
		$err['twitter_access_token'] = 'Access Tokenを入力して下さい';
	}
	// [Twitter Access Token Secret]未入力チェック
	if ($twitter_access_token_secret == '') {
		$err['twitter_access_token_secret'] = 'Access Token Secretを入力して下さい。';
	}
	// [処理時間設定]未選択チェック
	if ($process_hour == '') {
		$err['process_hour'] = '処理時間が指定してください。';
	}

	// エラー配列に値が入っているか判定
	if (empty($err)) {
		// userテーブル更新
		$sql = "UPDATE cm_user SET
				process_hour = :process_hour,
				twitter_consumer_key = :twitter_consumer_key,
				twitter_consumer_secret = :twitter_consumer_secret,
				twitter_access_token = :twitter_access_token,
				twitter_access_token_secret = :twitter_access_token_secret,
				blog_host = :blog_host,
				blog_xmlrpc = :blog_xmlrpc,
				blog_account = :blog_account,
				blog_password = :blog_password,
				blog_id = :blog_id,
				blog_category = :blog_category,
				youtube_category = :youtube_category,
				updated_at = now()
				WHERE id = :id";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(":process_hour" => $process_hour,
							":twitter_consumer_key" => $twitter_consumer_key,
							":twitter_consumer_secret" => $twitter_consumer_secret,
							":twitter_access_token" => $twitter_access_token,
							":twitter_access_token_secret" => $twitter_access_token_secret,
							":blog_host" => $blog_host,
							":blog_xmlrpc" => $blog_xmlrpc,
							":blog_account" => $blog_account,
							":blog_password" => $blog_password,
							":blog_id" => $blog_id,
							":blog_category" => $blog_category,
							"youtube_category" => $youtube_category,
							":id" => $user['id']));

		// セッション上のユーザーデータを更新
		$user['blog_host'] = $blog_host;
		$user['blog_xmlrpc'] = $blog_xmlrpc;
		$user['blog_account'] = $blog_account;
		$user['blog_password'] = $blog_password;
		$user['blog_id'] = $blog_id;
		$user['blog_category'] = $blog_category;
		$user['twitter_consumer_key'] = $twitter_consumer_key;
		$user['twitter_consumer_secret'] = $twitter_consumer_secret;
		$user['twitter_access_token'] = $twitter_access_token;
		$user['twitter_access_token_secret'] = $twitter_access_token_secret;
		$user['youtube_category'] = $youtube_category;
		$user['process_hour'] = $process_hour;
		$_SESSION['USER'] = $user;

		// 完了メッセージ表示
		$complete_msg = '変更されました。';
	}
	// DB切断
	unset($pdo);
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>投稿設定 | <?php echo SERVICE_NAME; ?></title>
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
                    <li class="active"><a href="./setting.php">投稿設定</a></li>
                    <li><a href="./user_edit.php">アカウント</a></li>
                    <li><a href="./logout.php">ログアウト</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container">
        <?php if ($complete_msg): ?>
            <div class="alert alert-success">
                <?php echo $complete_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2 class="panel-title">Youtube設定</h2>
                </div>
                <div class="panel-body">
                    <div class="form-group <?php if ($err['youtube_category'] != '') echo 'has-error'; ?>">
                        <label>動画のカテゴリー<br />（以下で指定したカテゴリーの人気動画が投稿されます）</label>
                        <?php echo getYoutubeCategoryList("youtube_category", $user['youtube_category']); ?>
                        <span class="help-block"><?php echo h($err['youtube_category']); ?></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h2 class="panel-title">ブログ設定</h2>
                        </div>
                        <div class="panel-body">
                            <div class="form-group <?php if ($err['blog_host'] != '') echo 'has-error'; ?>">
                                <input type="text" name="blog_host" class="form-control" value="<?php echo h($blog_host); ?>" placeholder="ブログHOST" />
                                <span class="help-block"><?php echo h($err['blog_host']); ?></span>
                            </div>
                            <div class="form-group <?php if ($err['blog_xmlrpc'] != '') echo 'has-error'; ?>">
                                <input type="text" name="blog_xmlrpc" class="form-control" value="<?php echo h($blog_xmlrpc); ?>" placeholder="XMLRPCパス" />
                                <span class="help-block"><?php echo h($err['blog_xmlrpc']); ?></span>
                            </div>
                            <div class="form-group <?php if ($err['blog_account'] != '') echo 'has-error'; ?>">
                                <input type="text" name="blog_account" class="form-control" value="<?php echo h($blog_account); ?>" placeholder="アカウント" />
                                <span class="help-block"><?php echo h($err['blog_account']); ?></span>
                            </div>
                            <div class="form-group <?php if ($err['blog_password'] != '') echo 'has-error'; ?>">
                                <input type="password" name="blog_password" class="form-control" value="<?php echo h($blog_password); ?>" placeholder="パスワード" />
                                <span class="help-block"><?php echo h($err['blog_password']); ?></span>
                            </div>
                            <div class="form-group <?php if ($err['blog_id'] != '') echo 'has-error'; ?>">
                                <input type="text" name="blog_id" class="form-control" value="<?php echo h($blog_id); ?>" placeholder="ブログID" />
                                <span class="help-block"><?php echo h($err['blog_id']); ?></span>
                            </div>
                            <div class="form-group <?php if ($err['blog_category'] != '') echo 'has-error'; ?>">
                                <input type="text" name="blog_category" class="form-control" value="<?php echo h($blog_category); ?>" placeholder="カテゴリー" />
                                <span class="help-block"><?php echo h($err['blog_category']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h2 class="panel-title">Twitter設定</h2>
                        </div>
                        <div class="panel-body">
                            <div class="form-group <?php if ($err['twitter_consumer_key'] != '') echo 'has-error'; ?>">
                                <input type="text" name="twitter_consumer_key" class="form-control" value="<?php echo h($twitter_consumer_key); ?>" placeholder="Consumer Key" />
                                <span class="help-block"><?php echo h($err['twitter_consumer_key']); ?></span>
                            </div>
                            <div class="form-group <?php if ($err['twitter_consumer_secret'] != '') echo 'has-error'; ?>">
                                <input type="text" name="twitter_consumer_secret" class="form-control" value="<?php echo h($twitter_consumer_secret); ?>" placeholder="Consumer Secret" />
                                <span class="help-block"><?php echo h($err['twitter_consumer_secret']); ?></span>
                            </div>
                            <div class="form-group <?php if ($err['twitter_access_token'] != '') echo 'has-error'; ?>">
                                <input type="text" name="twitter_access_token" class="form-control" value="<?php echo h($twitter_access_token); ?>" placeholder="Access Token" />
                                <span class="help-block"><?php echo h($err['twitter_access_token']); ?></span>
                            </div>
                            <div class="form-group <?php if ($err['twitter_access_token_secret'] != '') echo 'has-error'; ?>">
                                <input type="text" name="twitter_access_token_secret" class="form-control" value="<?php echo h($twitter_access_token_secret); ?>" placeholder="Access Token Secret" />
                                <span class="help-block"><?php echo h($err['twitter_access_token_secret']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2 class="panel-title">処理時間設定</h2>
                </div>
                <div class="panel-body">
                    <div class="form-group <?php if ($err['process_hour'] != '') echo 'has-error'; ?>">
                        <?php echo arrayToSelect("process_hour", $process_hour_array, $user['process_hour']); ?>
                        <span class="help-block"><?php echo h($err['process_hour']); ?></span>
                    </div>
                </div>
            </div>

            <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

            <div class="form-group">
                <input type="submit" class="btn btn-success btn-block" value="登録">
            </div>
        </form>

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