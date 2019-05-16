<?php

// ========== 共通ファイル読み込み ==========
require('function.php');

// ========== 「画面処理開始時」のデバッグログ ==========
debug('=========================================================================');
debug('メッセージAjax通信');
debug('=========================================================================');
debugLogStart();

  // ===============================
  $guestId = '';
  $ownerId = '';
  // ===============================
  //bordのID
  $boardId = $_POST['boardId'];
  debug('掲示板のID：'.print_r($boardId, true));

  //送信者のID
  $guestId = $_SESSION['user_id'];
  debug('ゲストユーザーID：'.print_r($guestId, true));

  //投稿者のID
  $ownerId = $_POST['ownerUser']; // 商品登録した人のuser_idを取得
  debug('オーナーユーザーID：'.print_r($ownerId, true));

  //送信メッセージ内容
  $msg = (isset($_POST['message'])) ? $_POST['message'] : '';
  debug('メッセージ内容：'.print_r($msg, true));
  // もしも、空文字だったら処理終了
  if($msg == '') exit;

  // messageテーブルにデータを挿入
  try{
    $dbh = dbConnect();
    $sql = 'INSERT INTO message (board_id, send_date, guest_id, owner_id, message, create_date)
            VALUES (:b_id, :send_date, :guest_id, :owner_id, :msg, :create_date)';
    $data = array(':b_id' => $boardId,
                  ':send_date' => date('Y-m-d H:i:s'),
                  ':guest_id' => $guestId,
                  ':owner_id' => $ownerId,
                  ':msg' => $msg,
                  ':create_date' => date('Y-m-d H:i:s'));
    $stmt = queryPost($dbh, $sql, $data);

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }

  // messageテーブルにデータを取得
  try{
    $dbh = dbConnect();
    $sql = 'SELECT board_id, send_date, guest_id, owner_id, message FROM message WHERE board_id = :b_id AND owner_id = :owner_id AND delete_flg = 0 ORDER BY create_date DESC';
    $data = array(':b_id' => $boardId,
                  ':owner_id' => $ownerId);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      $result = $stmt->fetch();
      // "messageData" : [{"send_date":"2019-02-28 00:18:46","guest_id":"13","owner_id":"8","message":"これから"},
      //                  {"send_date":"2019-02-28 00:18:46","guest_id":"13","owner_id":"8","message":"これから"},
      //                  {・・・} ・・・] と言う形でjsファイルに返されてしまう
      // "":[], "":[], "":[], "":[]
      debug('messageテーブルのデータを取得:'.print_r($result, true));
      echo json_encode(array("messageData" => $result), JSON_UNESCAPED_UNICODE);
    }else{
      debug('messageテーブルのデータを取得失敗・・・。');
    }

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }

// インサートして
// SELECTでmessageテーブルの最新(送信された)データを取得して(fetch)
// SELECTで取得したデータを、jsファイルに渡す
// jsファイルでデータをHTML形式にしてメッセージ表示エリアに表示
// TODO: ・メッセージ用にCSS(SCSS)を書き加える
// TODO: ・コンテンツ詳細ファイルで、messageテーブルに登録された値を全て表示するように設定(画面離れると初期化されるから)(多分foreach文でできる)
