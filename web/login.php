<?php
require_once('config.php');
require_once('functions.php');
require_once('lib/password.php');

session_start();

// データベースに接続する（PDOを使う）
$pdo = connectDb();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // 初めて画面にアクセスした時の処理

    if (isset($_COOKIE['CONTENTSMAKER'])) {
        // 自動ログイン情報があればキーを取得
        $auto_login_key = $_COOKIE['CONTENTSMAKER'];

        // 自動ログインキーをDBに照合
        $sql = "SELECT * FROM cm_auto_login WHERE c_key = :c_key AND expire >= :expire LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(":c_key" => $auto_login_key, ":expire" => date('Y:m:d H:i:s')));
        $row = $stmt->fetch();

        if ($row) {
            // 照合成功、自動ログイン
            $user = getUserbyUserId($row['user_id'], $pdo);

            if ($user) {
                // セッションハイジャック対策
                session_regenerate_id(true);
                $_SESSION['USER'] = $user;

                // HOME画面に遷移する。
                unset($pdo);
                header('Location:'.SITE_URL.'index.php');
                exit;
            }
        }
    }

    // CSRF対策
    setToken();

} else {
    // フォームからサブミットされた時の処理

    // CSRF対策
    checkToken();

    // メールアドレス、パスワードを受け取り、変数に入れる。
    $user_password = $_POST['user_password'];
    $user_email = $_POST['user_email'];
    $auto_login = $_POST['auto_login'];

    // 入力チェックを行う。
    $err = array();

    // [メールアドレス]未入力チェック
    if ($user_email == '') {
        $err['user_email'] = 'メールアドレスを入力して下さい。';
    } else {
        // [メールアドレス]形式チェック
        if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $err['user_email'] = 'メールアドレスが不正です。';
        } else {
            // [メールアドレス]存在チェック
            if (!checkEmail($user_email, $pdo)) {
                $err['user_email'] = 'このメールアドレスは登録されていません。';
            } else {
                // ログイン認証
//                 $user = getUser($user_email, $user_password, $pdo);
//                 if (!$user) {
//                     $err['user_password'] = 'パスワードが正しくありません。';
//                 }
            	$user = getUserByEmail($user_email, $pdo);
				if (!$user || !password_verify($user_password, $user['user_password'])) {
					$err['user_password'] = 'パスワードが正しくありません。';
				}
            }
        }
    }
    // [パスワード]未入力チェック
    if ($user_password == '') {
        $err['user_password'] = 'パスワードを入力して下さい。';
    }

    // もし$err配列に何もエラーメッセージが保存されていなかったら
    if (empty($err)) {

        session_regenerate_id(true);

        // ログインに成功したのでセッションにユーザデータを保存する。
        $_SESSION['USER'] = $user;

        // 自動ログイン情報を一度クリアする。
        if (isset($_COOKIE['CONTENTSMAKER'])) {
            $auto_login_key = $_COOKIE['CONTENTSMAKER'];

            // Cookie情報をクリア
            setcookie('CONTENTSMAKER', '', time()-86400, COOKIE_PATH);

            // DB情報をクリア
            $sql = "DELETE FROM cm_auto_login WHERE c_key = :c_key";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(":c_key" => $auto_login_key));
        }


        // チェックボックスにチェックが入っていた場合
        if ($auto_login) {

            // 自動ログインキーを生成
            $auto_login_key = sha1(uniqid(mt_rand(), true));

            // Cookie登録処理
            setcookie('CONTENTSMAKER', $auto_login_key, time()+3600*24*365, COOKIE_PATH);
            // DB登録処理
            $sql = "INSERT INTO cm_auto_login (user_id, c_key, expire, created_at, updated_at)
            VALUES (:user_id, :c_key, :expire, now(), now())";
            $stmt = $pdo->prepare($sql);
            $params = array(":user_id" => $user['id'], ":c_key" => $auto_login_key, ":expire" => date('Y-m-d H:i:s', time()+3600*24*365));
            $stmt->execute($params);
        }

        // HOME画面に遷移する。
        unset($pdo);
//         header('Location:'.SITE_URL.'./index.php');
        header('Location:'.SITE_URL.'index.php');
        exit;
    }

    unset($pdo);
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title><?php echo SERVICE_NAME; ?></title>
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
            </div>
        </div>
    </div>

    <div class="container">

        <div class="row">

            <div class="col-md-9">
                <div class="jumbotron">
                    <h1>コンテンツメーカー</h1>
                    <p>人気動作サイト自動作成ツール</p>
                    <p><a href="./signup.php" class="btn btn-success btn-lg">新規ユーザー登録</a></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="sidebar-nav panel panel-default">
                    <div class="panel-heading">
                        <h2 class="panel-title">ログイン</h2>
                    </div>
                    <div class="panel-body">
                        <form method="POST" >
                            <div class="form-group <?php if ($err['user_email'] != '') echo 'has-error'; ?>">
                                <label>メールアドレス</label>
                                <input type="text" class="form-control" name="user_email" value="<?php echo h($user_email); ?>" placeholder="メールアドレス" />
                                <span class="help-block"><?php echo h($err['user_email']); ?></span>
                            </div>
                            <div class="form-group <?php if ($err['user_password'] != '') echo 'has-error'; ?>">
                                <label>パスワード</label>
                                <input type="password" class="form-control" name="user_password" value="" placeholder="パスワード" />
                                <span class="help-block"><?php echo h($err['user_password']); ?></span>
                            </div>
                            <div class="form-group">
                                <input type="submit" value="ログイン" class="btn btn-primary btn-block">
                            </div>
                            <div class="form-group">
                                <label><input type="checkbox" name="auto_login" value="1"> 次回から自動でログイン</label>
                            </div>
                            <div class="form-group">
                                <a href="./remind_password.php">パスワードをお忘れの方</a>
                            </div>
                            <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
                        </form>
                    </div>
                </div>
            </div><!--/col-md-3-->

        </div><!--/row-->

        <hr>
        <footer class="footer">
            <p><?php echo COPYRIGHT; ?></p>
        </footer>
    </div><!--/.container-->

    <script src="//code.jquery.com/jquery.js"></script>
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
    <script src="js/bootstrap.min.js"></script>

</body>
</html>