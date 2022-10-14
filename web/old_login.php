<?php
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ログイン | 人気動画サイト作成ツール</title>
<!-- Bootstrap -->
<link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="css/new_contentsmaker.css" rel="stylesheet">
</head>
<body>
	<h1>Hello, world!</h1>

	<div class="container">

		<div class="col-sm-6 col-md-4">
			<div class="caption">
				<p>サービス説明</p>
				<p>サービス説明</p>
				<p>サービス説明</p>
			</div>
		</div>

		<div class="login-container">
			<div id="output"></div>
			<div class="avatar"></div>
			<div class="form-box">
				<form action="" method="">
					<input name="user" type="text" placeholder="username"> <input
						type="password" placeholder="password">
					<button class="btn btn-info btn-block login" type="submit">Login</button>
				</form>
			</div>
		</div>

	</div>

	<script src="http://code.jquery.com/jquery.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/contentsmaker.js"></script>
	<script src="http://mymaplist.com/js/vendor/TweenLite.min.js"></script>
	<!-- This is a very simple parallax effect achieved by simple CSS 3 multiple backgrounds, made by http://twitter.com/msurguy -->
</body>
</html>