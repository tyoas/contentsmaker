<?php
require_once('config.php');
require_once('functions.php');
require_once('lib/password.php');

session_start();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // 初めて画面にアクセスした時の処理

    // CSRF対策
    setToken();
} else {
    // フォームからサブミットされた時の処理

    // CSRF対策
    checkToken();

    $user_name = $_POST['user_name'];
    $user_password = $_POST['user_password'];
    $user_email = $_POST['user_email'];

    // データベースに接続する（PDOを使う）
    $pdo = connectDb();

    // 入力チェックを行う。
    $err = array();

    // [氏名]未入力チェック
    if ($user_name == '') {
        $err['user_name'] = '氏名を入力して下さい。';
    }

    if (strlen(mb_convert_encoding($user_name, 'SJIS', 'UTF-8')) > 30) {
        $err['user_name'] = '氏名は30バイト以内で入力して下さい。';
    }

    // [パスワード]未入力チェック
    if ($user_password == '') {
        $err['user_password'] = 'パスワードを入力して下さい。';
    }

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
            // [メールアドレス]存在チェック
            if (checkEmail($user_email, $pdo)) {
                $err['user_email'] = 'このメールアドレスは既に登録されています。';
            }
        }
    }

    // もし$err配列に何もエラーメッセージが保存されていなかったら
    if (empty($err)) {
        // データベース（userテーブル）に新規登録する。
        $stmt = $pdo->prepare("INSERT INTO cm_user (user_name, user_email, user_password, process_hour, created_at, updated_at)
            VALUES (:user_name, :user_email, :user_password, 99,  now(),  now())");
        $params = array(":user_name" => $user_name, ":user_email" => $user_email, ":user_password" => password_hash($user_password, PASSWORD_DEFAULT));
        $stmt->execute($params);

        session_regenerate_id(true);

        // 自動ログイン
        $user = getUserbyUserId($pdo->lastInsertId(), $pdo);
        $_SESSION['USER'] = $user;

        // 管理者にメール
        mb_language("japanese");
        mb_internal_encoding("UTF-8");

        $from = "test@example.com";

        $mail_title = '【コンテンツメーカー】新規ユーザ登録がありました。';
        $mail_body = '氏名：'.$user['user_name'].PHP_EOL;
        $mail_body.= 'メールアドレス：'.$user['user_email'];

        mb_send_mail(ADMIN_MAIL_ADDRESS, $mail_title, $mail_body, "From:".$from);

        // signup_complete.phpに画面遷移する。
        unset($pdo);
        header('Location: '.SITE_URL.'signup_complete.php');
        exit;
    }

    unset($pdo);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>新規ユーザー登録 | <?php echo SERVICE_NAME; ?></title>
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
        <h1>新規ユーザー登録</h1>

        <form method="POST" class="sidebar-nav panel panel-default">
            <br />
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
                <input type="password" class="form-control" name="user_password" value="" placeholder="パスワード" />
                <span class="help-block"><?php echo h($err['user_password']); ?></span>
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-success btn-block" value="アカウントを作成する">
            </div>

            <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />
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