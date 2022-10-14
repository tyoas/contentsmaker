<?php
require_once('config.php');
require_once('functions.php');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>退会完了 | <?php echo SERVICE_NAME; ?></title>
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
        <h1>退会完了</h1>

        <div class="alert alert-success">
            退会処理が完了しました。
        </div>

        <a href="<?php echo SITE_URL ?>">TOPへ</a>

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