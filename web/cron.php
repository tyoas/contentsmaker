<?php
// XMLRPCライブラリをインポート
require_once 'XML/RPC.php';
// Twitterライブラリをインポート
require_once 'twitteroauth-master/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;
// 設定ファイルの読み込み
require_once 'config.php';
// 関数ファイルの読み込み
require_once 'functions.php';

// if ($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'POST') {
// 	echo '不正なアクセスです。';
// 	exit;
// } else {

	// 現在時刻の取得
	$now_hour = date("H");
	// DB接続
	$pdo = connectDb();
	// 配信設定ユーザを取得
	$sql = "SELECT * FROM cm_user WHERE process_hour != 99";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	// 抽出したユーザでループ
	while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
		// ログメッセージをクリア
		$log_message = NULL;

		// 現在時刻を投稿間隔で割り、余りがなければ投稿処理を行う
		if ($now_hour % $user['process_hour'] == 0) {
			$youtube_category = $user['youtube_category'];
			$blog_host = $user['blog_host'];
			$blog_xmlrpc = $user['blog_xmlrpc'];
			$blog_account = $user['blog_account'];
			$blog_password = $user['blog_password'];
			$blog_id = $user['blog_id'];
			$blog_category = $user['blog_category'];
			$twitter_consumer_key = $user['twitter_consumer_key'];
			$twitter_consumer_secret = $user['twitter_consumer_secret'];
			$twitter_access_token = $user['twitter_access_token'];
			$twitter_access_token_secret = $user['twitter_access_token_secret'];

			if (!$youtube_category || !$blog_host || !$blog_xmlrpc || !$blog_account || !$blog_password || !$blog_id || !$blog_category || !$twitter_consumer_key || !$twitter_consumer_secret || !$twitter_access_token || !$twitter_access_token_secret) {
				// いずれかが不足していたら処理を行わない
				$log_message = '未設定項目があります。';
			} else {

				// -----------------------------------------
				// Youtube API 連動処理
				// -----------------------------------------

				// 指定カテゴリの人気の高い動画を50件取得
				$feedURL = 'https://www.googleapis.com/youtube/v3/videos?key=' . API_KEY . '&part=snippet';
				$feedURL .= '&regionCode=JP';
				$feedURL .= '&chart=mostPopular';
				$feedURL .= '&maxResults=50';
				if ($youtube_category) {
					$feedURL .= '&videoCategoryId=' . $youtube_category;
				}

				echo $feedURL;

				// 配列に変換してエントリーリストを取得
				$json = file_get_contents($feedURL);

				echo $json;

				$arr = json_decode($json, true);
				$items = $arr['items'];


				// 50件から1件をランダムに抽出
				$rand_no = array_rand($items);
				$target_item = $items[$rand_no];

				// 動画IDの取得
				$movie_id = $target_item['id'];

				// 動画のタイトルを取得
				$movie_title = $target_item['snippet']['title'];

				// 動画の説明文を取得
				$movie_description = $target_item['snippet']['description'];

				// -----------------------------------------
				// WordPress API(XMLRPC) 連動処理
				// -----------------------------------------

				$GLOBALS['XML_RPC_defencoding'] = "UTF-8";

				$blog_host = $blog_host;
				$blog_xmlrpc_path = $blog_xmlrpc;
				$blog_user = new XML_RPC_Value($blog_account, 'string');
				$blog_password = new XML_RPC_Value($blog_password, 'string');
				$appkey = new XML_RPC_Value('', 'string');
				$blog_id = new XML_RPC_Value($blog_id, 'string');

				// XML-RPCクライアントの作成
				$c = new XML_RPC_client($blog_xmlrpc_path, $blog_host, 80);

				// 投稿設定
				$title = $movie_title;
				$description = <<< EOD
<iframe width="425" height="350" src="//www.youtube.com/embed/$movie_id" frameborder="0" allowfullscreen></iframe>
EOD;

				$categories = array(
					new XML_RPC_Value($blog_category, 'string'),
								);

				$content = new XML_RPC_Value(
					array(
					'title' => new XML_RPC_Value($title, 'string'),
					'description' => new XML_RPC_Value($description, 'string'),
					'categories' => new XML_RPC_Value($categories, 'array')
				), 'struct');

				$publish = new XML_RPC_Value(1, 'boolean');

				// ブログ記事投稿
				$message = new XML_RPC_Message(
					'metaWeblog.newPost',
					array($blog_id, $blog_user, $blog_password, $content, $publish)
				);

				$result = $c->send($message);
				if (!$result) {
					$log_message .= 'ブログホストに接続できません';
				} elseif ($result->faultCode()) {
					$log_message .= $result->faultString();
					exit('XML-RPC fault (' . $result->faultCode() . '); ' . $result->faultString());
				}

				// 投稿ID取得
				$post_id = XML_RPC_decode($result->value());
				$post_id = new XML_RPC_Value($post_id, 'int');

				// 投稿した記事のパーマリンク取得
				$message = new XML_RPC_Message(
						'metaWeblog.getPost',
						array($post_id, $blog_user, $blog_password)
				);

				$result = $c->send($message);
				if (!$result) {
					$log_message .= 'ブログHOSTに接続できません。';
				} elseif ($result->faultCode()) {
					$log_message .= $result->faultString();
				}

				// パーマリンク取得
				$post = XML_RPC_decode($result->value());
				$blog_post_url = $post['link'];

				// -----------------------------------------
				// Twitter API 連動処理
				// -----------------------------------------

				// Twitterにログインするための事前準備(OAuthオブジェクト生成)
				$connection = new TwitterOAuth($twitter_consumer_key, $twitter_consumer_secret, $twitter_access_token, $twitter_access_token_secret);

				// ツイート
				$tweet_message = $movie_title . "： " . $blog_post_url;
				$res = $connection->post("statuses/update", array("status" => $tweet_message));

				$body = $connection->getLastBody();
				if ($connection->getLastHttpCode() != 200) {
					// エラーメッセージ群を取り出しループして画面表示
					$errors = $body->errors;
					foreach ($errors as $error) {
						$log_message .= 'Twitter投稿エラー： ' . $error->message;
					}
				}

				if (!$log_message) {
					$log_message = '投稿は正常に終了しました。';
				}
			}
			// ログを保存
			saveCronLog($user['id'], $log_message, $pdo);
		}
	}
	// DB切断
	unset($pdo);
	exit;
// }
?>