<?php
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <title>管理者からのお知らせ | 人気動画サイト作成ツール</title>
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/contentsmaker.css" rel="stylesheet">
    <style src="js/bootstrap.min.js"></style>
  </head>

  <body>
     <div class="container">
	  	<div class="btn-group btn-group-justified" role="group" aria-label="....">
	  		<a class="btn btn-default active" href="./admin_notice.php">ダッシュボード</a>
			<a class="btn btn-default" href="./admin_userlist.php">ユーザー管理</a>
			<a class="btn btn-default" href="./logout.php">ログアウト</a>
		</div>
			<br />
		 	<br /><br/>
		 	<form action="#">
		 		<h1>運営側からのお知らせ</h1>
		 		<textarea rows="5" class="form-control" name="notice"></textarea>
		 		<br /><br />
	  			<input type="submit" value="登録"  class="btn btn-success btn-block" name="login" method="post">
		 	</form>
  	</div>
  </body>
</html>