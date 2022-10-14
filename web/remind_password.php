<?php
require_once('config.php');
require_once('functions.php');

session_start();

$pdo = connectDb();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {

    // 初めて画面にアクセスした時の処理

    // CSRF対策
    setToken();
} else {
    // フォームからサブミットされた時の処理

    // CSRF対策
    checkToken();

    $user_email = $_POST['user_email'];

    // 入力チェックを行う
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
            }
        }
    }

    // もし$err配列に何もエラーメッセージが保存されていなかったら
    if (empty($err)) {

        // ランダムの文字列生成
        $str_rand = makeRandStr(8);

        // データベースのパスワードを更新
        $sql = "UPDATE cm_user SET user_password = :user_password, updated_at = now() where user_email = :user_email";
        $stmt = $pdo->prepare($sql);
        $params = array(":user_password" => $str_rand, ":user_email" => $user_email);
        $stmt->execute($params);

        $flag = $stmt->execute();

        // メール送信
        mb_language("japanese");
        mb_internal_encoding("UTF-8");

        $mail_title = '【コンテンツメーカー】パスワード再設定メール';
        $mail_body = 'パスワードリセット要求があったため、パスワードを一時的に以下のものに変更しました。'.PHP_EOL;
        $mail_body.= 'パスワード：'.$str_rand.PHP_EOL.PHP_EOL;
        $mail_body.= 'セキュリティ向上のため、ログイン後にご自身でパスワードを変更して下さい。'.PHP_EOL;
        $mail_body.= SITE_URL;

        mb_send_mail($user_email, $mail_title, $mail_body);

        // 送信完了画面に遷移する
        unset($pdo);
        header('Location: '.SITE_URL.'remind_password_complete.php');
        exit;
    }

    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>パスワードをお忘れの方 | <?php echo SERVICE_NAME; ?></title>
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
        <h1>パスワードをお忘れの方</h1>

        <form method="POST" class="sidebar-nav panel panel-default">
            <br />

            <div class="form-group <?php if ($err['user_email'] != '') echo 'has-error'; ?>">
                <label>メールアドレス</label>
                <input type="text" class="form-control" name="user_email" value="<?php echo h($user_email); ?>" placeholder="メールアドレス" />
                <span class="help-block"><?php echo h($err['user_email']); ?></span>
            </div>
            <input type="hidden" name="token" value="<?php echo h($_SESSION['sstoken']); ?>" />

            <div class="form-group">
                <input type="submit" class="btn btn-success btn-block" value="パスワードをリセットする">
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