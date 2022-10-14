<?php
// データベースに接続する
function connectDb() {
    $param = "mysql:dbname=".DB_NAME.";host=".DB_HOST;

    try {
        $pdo = new PDO($param, DB_USER, DB_PASSWORD);
        $pdo->query('SET NAMES utf8;');
        return $pdo;
    } catch (PDOException $e) {
        echo $e->getMessage();
        exit;
    }
}

function h($original_str) {
    return htmlspecialchars($original_str, ENT_QUOTES, "UTF-8");
}

// トークンを発行する処理
function setToken() {
    $token = sha1(uniqid(mt_rand(), true));
    $_SESSION['sstoken'] = $token;
}

// トークンをチェックする処理
function checkToken() {
    if (empty($_SESSION['sstoken']) || ($_SESSION['sstoken'] != $_POST['token'])) {
        echo '<html><head><meta charset="utf-8"></head><body>不正なアクセスです。</body></html>';
        exit;
    }
}

// メールアドレスとパスワードからuserを検索する
function getUser($user_email, $user_password, $pdo) {
    $sql = "SELECT * FROM cm_user WHERE user_email = :user_email AND user_password = :user_password LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_email" => $user_email, ":user_password" => $user_password));
    $user = $stmt->fetch();
    return $user ? $user : false;
}

// メールアドレスからuserを検索する
function getUserByEmail($user_email, $pdo) {
	$sql = "SELECT * FROM cm_user WHERE user_email = :user_email LIMIT 1";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(":user_email" => $user_email));
	$user = $stmt->fetch();
	return $user ? $user : false;
}

// メールアドレスの存在チェック
function checkEmail($user_email, $pdo) {
    $sql = "SELECT * FROM cm_user WHERE user_email = :user_email LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_email" => $user_email));
    $user = $stmt->fetch();
    return $user ? true : false;
}

// ユーザIDからuserを検索する
function getUserbyUserId($user_id, $pdo) {
    $sql = "SELECT * FROM cm_user WHERE id = :user_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":user_id" => $user_id));
    $user = $stmt->fetch();

    return $user ? $user : false;
}

// ランダム文字列生成 (英数字)
function makeRandStr($length) {
    $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
    $r_str = null;
    for ($i = 0; $i < $length; $i++) {
        $r_str .= $str[rand(0, count($str))];
    }
    return $r_str;
}

// 配列からプルダウンメニューを生成
function arrayToSelect($inputName, $srcArray, $selectedIndex = "") {
	$temphtml = '<select class="form-control" name="' . $inputName . '">' . "\n";

	foreach ($srcArray as $key => $val) {
		if ($selectedIndex == $key) {
			$selectedText = ' selected="selected"';
		} else {
			$selectedText = '';
		}
		$temphtml .= '<option value="' . $key . '"' . $selectedText . '>' . $val . '</option>' . "\n";
	}
	$temphtml .= '</select>' . "\n";
	return $temphtml;
}

// Youtubeカテゴリ選択のプルダウンメニューを作成する
function getYoutubeCategoryList($inputName, $selectedIndex = "") {
	$temphtml = '<select class="form-control" name="' . $inputName . '">' . "\n";

	// カテゴリ取得
	$feedURL = 'https://www.googleapis.com/youtube/v3/videoCategories?key=' . API_KEY . '&part=snippet&regionCode=JP';
	$json = file_get_contents($feedURL);
	$arr = json_decode($json, true);

	foreach ($arr['items'] as $item) {
		if ($selectedIndex == $item['id']) {
			$selectedText = ' selected="selected"';
		} else {
			$selectedText = '';
		}
		$temphtml .= '<option value="' . $item['id'] . '"' . $selectedText . '>' . $item['snippet']['title'] . '</option>' . "\n";
	}

	$temphtml .= '</select>' . "\n";
	return $temphtml;
}

// ログテーブルに保存する
function saveCronLog($user_id, $message, $pdo) {
	$sql = "INSERT INTO cm_cron_log (user_id, message, created_at, updated_at) VALUES (:user_id, :message, now(), now())";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(":user_id" => $user_id, ":message" => $message));
}
?>