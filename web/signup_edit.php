<?php
require_once('config.php');
require_once('functions.php');

session_start();

// ログインチェック

// セッションからユーザー情報を取得
$user = $_SESSION['USER'];

$pdo = connectDb();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	// 初めて画面にアクセスした時の処理

	$user_name = $user['user_name'];
	$user_email = $user['user_email'];
	$user_password = $user['user_password'];

	// CSRF対策
	setToken();
} else {
	// フォームからサブミットされた時の処理
// 	checkToken();

	$user_name = $_POST['user_name'];
	$user_email = $_POST['user_email'];
	$user_password = $_POST['user_password'];
	$old_user_email = $user['user_email'];

	$err = array();
	$complete_msg = "";

	// 氏名が空
	if ($user_name == '') {
		$err['user_name'] = '氏名を入力してください。';
	} else {
		// 文字数チェック
		if (strlen(mb_convert_encoding($user_name, 'SJIS', 'UTF-8')) > 30) {
			$err['user_name'] = '氏名は30バイト以内で入力してください。';
		}
	}

	// メールアドレスが空
	if ($user_email == '') {
		$err['user_email'] = 'メールアドレスを入力してください。';
	} else {
		// メールアドレス形式チェック
		if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
			$err['user_email'] = 'メールアドレスの形式が不正です。';
		}
	}

	// パスワードが空
	if ($user_password == '') {
		$err['user_password'] = 'パスワードを入力してください。';
	}

	if (empty($err)) {
		// ユーザー登録処理
		$sql = "update cm_user
				set
				user_name = :user_name,
				user_email = :user_email,
				user_password = :user_password,
				updated_at = now()
				where
				user_email = :old_user_email";
		$stmt = $pdo->prepare($sql);
		$params = array(
			":user_name" => $user_name,
			":user_email" => $user_email,
			":user_password" => $user_password,
			":old_user_email" => $old_user_email
		);
		$stmt->execute($params);
		$complete_msg = "ユーザが更新されました。";
	}
}
unset($pdo);
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <title>ユーザー編集 | 人気動画サイト作成ツール</title>
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/contentsmaker.css" rel="stylesheet">
    <style src="js/bootstrap.min.js"></style>
  </head>

   <body id="main">

  <div class="container">
  	<div class="btn-group btn-group-justified" role="group" aria-label="....">
  		<a class="btn btn-default" href="./index.php">HOME</a>
		<a class="btn btn-default" href="./setting.php">投稿設定</a>
		<a class="btn btn-default active" href="./signup_edit.php">アカウント</a>
		<a class="btn btn-default" href="./logout.php">ログアウト</a>
	</div>
	<br />
	アカウント情報修正
	<br /><br/>

	<?php if ($complete_msg): ?>
	<div class="alert alert-success">
		<?php echo nl2br($complete_msg); ?>
	</div>
	<?php endif; ?>
	<form class="panel panel-default panel-body" method="post">
		<div class="form-group <?php if ($err['user_name'] != '') echo 'has-error'; ?>">
			<label>氏名</label>
		  	<input type="text" class="form-control" name="user_name" value="<?php echo $user_name; ?>" placeholder="氏名" /><span class="help-block"><?php echo $err['user_name']; ?></span>
		</div>
	    <div class="form-group <?php if ($err['user_email'] != '') echo 'has-error'; ?>">
	    	<label>メールアドレス</label>
	    	<input type="text" class="form-control" name="user_email" value="<?php echo $user_email; ?>"  placeholder="メールアドレス" /><span class="help-block"><?php echo $err['user_email']; ?></span>
  		</div>
  		<div class="form-group <?php if ($err['user_password'] != '') echo 'has-error'; ?>">
  			<label>パスワード</label>
			<input type="password"  class="form-control" name="user_password" placeholder="パスワード" /><span class="help-block"><?php echo $err['user_password']; ?></span>
		 </div>

	    <div class="form-group">
  			<input type="submit" class="btn btn-success btn-block" name="edit_add" size="30" value="修正">
  		</div>

  		<div class="form-group">
  			<a herf="javascript:void(0);" onclick="var ok=confirm('退会してもよろしいですか?'); if(ok) location.href='delete_user.php'; return false;" class="btn btn-danger btn-block" >退会</a>
  		</div>
  	</form>
  </div>

	<hr>
	<footer class="footer">
	  		<p></p>
	</footer>
</body>
</html>