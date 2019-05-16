<?php

// ========== 共通ファイル読み込み ==========
require('function.php');

// ========== 「画面処理開始時」のデバッグログ ==========
debug('=========================================================================');
debug('お気に入りAjax通信');
debug('=========================================================================');
debugLogStart();

//投稿者ID
$contentId = $_POST['contentId'];
//ユーザーID
$userId = $_SESSION['user_id'];
// DBのfavoriteテーブルにデータ(content_idとuser_id)を挿入
try {
  // DB接続
  $dbh = dbConnect();
  // SQL文作成(レコードにデータが入っているかチェック)
  $sql = 'SELECT * FROM favorites WHERE content_id = :c_id AND user_id = :u_id';
  $data = array(':c_id' => $contentId,
                ':u_id' => $userId);
  // SQL文実行
  $stmt = queryPost($dbh, $sql, $data);
  // SQL文実行結果取得(数字で)
  $result = $stmt->rowCount();

  // ===== データがあれば削除、なければ登録 =====
  // === 削除(データあり) ===
  if(!empty($result)){ //レコード無しの場合0が返ってくる=>0はfalse判定
    $sql = 'DELETE FROM favorites WHERE content_id = :c_id AND user_id = :u_id';
    $data = array(':c_id' => $contentId,
                  ':u_id' => $userId);
    $stmt = queryPost($dbh, $sql, $data);
  // === 登録(データなし) ===
  }else{
    $sql = 'INSERT INTO favorites (content_id, user_id, create_date) VALUES (:c_id, :u_id, :create_date)';
    $data = array(':c_id' => $contentId,
                  ':u_id' => $userId,
                  'create_date' => date('Y-m-d H:i:s'));
    $stmt = queryPost($dbh, $sql, $data);
  }

} catch (Exception $e) {
  error_log('エラー発生：'.$e->getMessage());
}
