<?php
require_once('config.php');
require_once('functions.php');

session_start();

if (!isset($_SESSION['USER'])) {
    header('Location: '.SITE_URL.'login.php');
    exit;
}
$user = $_SESSION['USER'];

$pdo = connectDb();

// 処理実行ログを取得（１０件）
$cron_log_list = NULL;
$sql = "SELECT * FROM cm_cron_log WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":user_id" => $user['id']));
if ($stmt) {
	$cron_log_list = $stmt->fetchAll();
}

// お知らせを取得
$notice = NULL;
$sql = "SELECT notice FROM cm_administrator LIMIT 1";
$stmt = $pdo->query($sql);
if ($stmt) {
	$admin = $stmt->fetch();
	$notice = $admin['notice'];
}
unset($pdo);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>HOME | <?php echo SERVICE_NAME; ?></title>
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
                    <li class="active"><a href="./index.php">HOME</a></li>
                    <li><a href="./setting.php">投稿設定</a></li>
                    <li><a href="./user_edit.php">アカウント</a></li>
                    <li><a href="./logout.php">ログアウト</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container">
        <h1>HOME</h1>

        <div>
            <label>お知らせ</label>
            <textarea class="form-control" rows="3" readonly name="information"><?php echo $notice; ?></textarea>
        </div>
        <br/>

        <div>
            <label>cron処理の実行ログ（最新10件）</label>
            <?php if ($cron_log_list): ?>
            <ul class="list-group">
            	<?php foreach ($cron_log_list as $cron_log): ?>
                <li class="list-group-item">
                    <?php echo $cron_log['created_at'] ?><?php echo $cron_log['message'] ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
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
