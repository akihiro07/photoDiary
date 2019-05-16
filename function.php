<?php
// ========================================
// 画面共通の機能を1つのファイルにまとめたもの
// ========================================

// ========== ログ出力 ==========
ini_set('log_erorrs', 'on');
ini_set('error_log', 'php.log');

// ========== デバッグログ出力関数 ===========
// 開発中のみフラグをtrue(有効)にする　サービス開始時はフラグをfalse(無効)に変える
$debug_flg = true;
// デバッグログ関数
function debug($str){
  global $debug_flg;
  if($debug_flg){
    error_log('デバッグ：'.$str);
  }
}

// ========== 「画面処理開始時」のデバッグログ出力関数 ===========
function debugLogStart(){
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID：'.session_id());
  debug('セッション変数：'.print_r($_SESSION, true));
  debug('現在日時：'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug('ログイン有効期限日時：'.( $_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}

// ========== セッション有効期限延長・セッション使用開始 ==========
// セッションファイルの保管場所を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("/var/tmp/");
// ガーベージコレクションが削除する有効期限を設定(今回は30日)
ini_set('session.gc_maxlifetime', 60*60*24*30);
//クッキー自体の有効期限を設定
ini_set('session.cookie_lifetime', 60*60*24*30);
// セッション使用開始
session_start();
// セッションIDを自動生成(セキュリティ対策の為、毎回セッションIDを変える)
session_regenerate_id();

// ========== メッセージを定数格納 ==========
// エラーメッセージ
const ERR01 = '入力必須です';
const ERR02 = 'Emailの形式で入力して下さい';
const ERR03 = 'そのEmailは既に登録されています';
const ERR04 = '半角英数字のみご利用いただけます';
const ERR05 = '6文字以上で入力してください';
const ERR06 = '256文字以内で入力してください';
const ERR07 = 'パスワードと一致しません';
const ERR08 = '正しく入力して下さい';
const ERR09 = 'メールアドレスまたはパスワードが違います';
const ERR10 = 'エラーが発生しました.しばらく経ってからやり直してください';
const ERR11 = '半角数字で入力して下さい';
const ERR12 = '古いパスワードが違います';
const ERR13 = 'パスワード再入力と一致しません';
const ERR14 = '8文字で入力してください';
const ERR15 = '送信した認証キーと一致しません';
const ERR16 = '認証キーの有効期限が切れました';
const ERR17 = '写真を１枚以上載せてください';
const ERR18 = '300文字以内で入力してください';

// サクセスメッセージ
const SUC01 = 'パスワード変更が変更されました';
const SUC02 = 'メールを送信しました';
const SUC03 = 'パスワード再発行が完了しました';
const SUC04 = '編集が完了しました';
const SUC05 = '登録が完了しました';

// ========== エラーメッセージ を変数に格納(配列の形) ==========
$err_msg = array();

// ========== バリデーションチェック関数 ==========
// 未入力チェック関数
function ValidRequired($str, $key){
  if(empty($str)){
    global $err_msg;
    $err_msg[$key] = ERR01;
  }
}

// 未入力チェック関数（画像）
function ValidRequiredImg($str, $key){
  if(empty($str)){
    global $err_msg;
    $err_msg[$key] = ERR17;
  }
}

//  名前入力形式チェック関数 // TODO: 英語での入力の許可したい
function ValidName($str, $key){
  if(!preg_match('/^[ぁ-んァ-ヶー一-龠]+$/', $str)){
    global $err_msg;
    $err_msg[$key] = ERR08;
  }
}

// Email形式チェック関数
function ValidEmail($str, $key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
    global $err_msg;
    $err_msg[$key] = ERR02;
  }
}

// Email重複チェック関数
function ValidEmailDup($email){
  global $err_msg;
  try {
    //DB接続関数
    $dbh = dbConnect();
    //SQL文準備・作成
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    //SQL文実行
    $stmt = queryPost($dbh, $sql, $data);
    //DBデータを取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //データが取得できた = Emailがすでに登録されている
    if(!empty(array_shift($result))){
      $err_msg['email'] = ERR03;
    }
  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = ERR10;
  }
}

// 半角英数字チェック関数
function ValidHalf($str, $key){
  if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = ERR04;
  }
}

// 最小文字数チェック関数
function ValidMinLen($str, $key, $min = 6){
  if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = ERR05;
  }
}

// 最大文字数チェック関数
function ValidMaxLen($str, $key, $max = 255){
  if(mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = ERR06;
  }
}

// パスワードとパスワード再入力の一致チェック関数(再入力側)
function ValidMatch($str1, $str2, $key){
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = ERR07;
  }
}
// パスワードとパスワード再入力の一致チェック関数()
function ValidMatchSub($str1, $str2, $key){
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = ERR13;
  }
}

// 年齢入力形式(半角数字)チェック関数
function ValidAge($age){
  if(!preg_match("/^[0-9]+$/", $age)){
    global $err_msg;
    $err_msg['age'] = ERR11;
  }
}

// 認証キー文字数チェック関数(8文字)
function ValidAuthLen($auth, $key, $length = 8){
  if(mb_strlen($auth) !== $length){
    global $err_msg;
    $err_msg[$key] = ERR14;
  }
}

// ========== ユーザー情報取得関数 ==========
// DBのusersテーブル取得関数
function TableUsers($u_id){
  global $err_msg;
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM users WHERE user_id = :user_id';
    $data = array(':user_id' => $u_id);
    // SQL文実行
    $stmt = queryPost($dbh, $sql, $data);
    // SQL文実行結果
    if($stmt){
      debug('クエリ成功。');
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result;
    }else{
      debug('クエリ失敗。');
      $err_msg['common'] = ERR10;
    }

  } catch (Exception $e) {
    error_log('エラー発生'.$e->getMessage());
    $err_msg['common'] = ERR10;
  }
}

// DBのsexテーブルの取得関数
function TableSex(){
  global $err_msg;
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM sex';
    $data = array();
    // SQL文実行
    $stmt = queryPost($dbh, $sql, $data);
    // SQL文実行結果
    if($stmt){
      debug('クエリ成功。');
      $result = $stmt->fetchAll();
      return $result;
    }else{
      debug('クエリ失敗。');
      $err_msg['common'] = ERR10;
    }

  } catch (Exception $e) {
    error_log('エラー発生'.$e->getMessage());
    $err_msg['common'] = ERR10;
  }
}

// DBのcontentsテーブル取得関数
function TableContents($c_id, $u_id){
  debug('商品情報を取得します。');
  debug('ユーザーID：'.$u_id);
  debug('商品ID：'.$c_id);
  global $err_msg;
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM contents WHERE content_id = :c_id AND user_id = :u_id AND delete_flg = 0';
    $data = array(':c_id' => $c_id,
                  ':u_id' => $u_id);
    // SQL文実行
    $stmt = queryPost($dbh, $sql, $data);
    // SQL文実行結果
    if($stmt){
      debug('クエリ成功！！');
      return $stmt->fetch(PDO::FETCH_ASSOC);

    }else{
      debug('クエリ失敗・・・。');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = ERR10;
  }
}

// DBのfavoritesテーブル取得関数(お気に入りアイコンを変化させるため)
function isFavorite($contentId, $userId){
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favorites WHERE content_id = :c_id AND user_id = :u_id';
    $data = array(':c_id' => $contentId,
                  ':u_id' => $userId);
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->rowCount(); //結果を数字で取得
    debug('クエリ成功。');
    // DBにデータがあれば、クラスacticeを表示
    if(!empty($result)){
      debug('お気に入り');
      return true;
    }else{
      debug('特に好きくないべ。');
      return false;
    }

  } catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

// DBのcontentsテーブルとusersテーブルを結合したデータクォ取得関数
function ContentDetailData($c_id){
  debug('商品ID'.print_r($c_id, true));
  try {
    // DB接続
    $dbh = dbConnect();
    // SQL文作成
    // TODO: boardテーブルは別のSQL文に分けてあげる
    $sql = 'SELECT c.user_id, c.content_id, c.title, c.place, c.pic1, c.pic2, c.pic3, c.comment,
            u.name, u.prof_img
            FROM contents AS c
            LEFT JOIN users AS u ON c.user_id = u.user_id
            WHERE c.content_id = :c_id AND c.delete_flg = 0 AND u.delete_flg = 0';
    // データ流し込み
    $data = array(':c_id' => $c_id);
    // SQL文実行
    $stmt = queryPost($dbh, $sql, $data);
    if(!$stmt){
      debug('クエリ失敗・・・。');
      return false;
    }else{
      debug('クエリ成功！！');
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      debug('クエリ結果：'.print_r($result, true));
      return $result;
    }

  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

// boardテーブル生成
function createBoard($c_id){
  try{
    // DB接続
    $dbh = dbConnect();
    // SQL文作成(content_idが重複した場合はboardテーブル作成をキャンセルする)
    $sql = 'INSERT INTO board (content_id, create_date) SELECT :c_id, :create_date FROM dual
            WHERE NOT EXISTS (SELECT * FROM board WHERE content_id = :c_id)';
    $data = array(':c_id' => $c_id,
                  ':create_date' => date('Y-m-d H:i:s'));
    // SQL文実行
    $stmt = queryPost($dbh, $sql, $data);
    // SQL文(クエリ)実行結果判定
    if(!$stmt){
      debug('boardテーブル生成失敗・・・。');
    }else{
      debug('boardテーブル作成成功！');
    }

  }catch(Exception $e){
    error_log('boardテーブル生成時エラー発生：'.$e->getMessage());
  }
}

// boardテーブルデータ取得
function getBoardData($c_id){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM board WHERE content_id = :c_id';
    $data = array(':c_id' => $c_id);
    $stmt = queryPost($dbh, $sql, $data);
    if(!$stmt){
      debug('boardテーブルデータ取得失敗・・・。');
    }else{
      debug('boardテーブルデータ取得成功！！');
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }

  } catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}

// messageテーブルのデータ取得
// function getMeassageData($boardId){
//   try {
//     $dbh = dbConnect();
//     $sql = 'SELECT *
//             FROM message AS m
//             LEFT JOIN board AS b
//             ON m.board_id = b.board_id
//             WHERE m.board_id = :board_id AND m.delete_flg = 0 ORDER BY send_date DESC';
//     $data = array(':board_id' => $boardId);
//     $stmt = queryPost($dbh, $sql, $data);
//     if($stmt){
//       return $stmt->fetchAll();
//     }else{
//       debug('messageテーブル生成失敗');
//     }
//
//   } catch(Exception $e){
//     error_log('messageテーブル生成時エラー発生：'.$e->getMessage());
//   }
// }

// DBのcontentsテーブル取得関数(コンテンツ一覧で詳細データ取得)
function TableContentsEdit($currentMinNum, $sort, $PageShow = 10){
  try {
    // ===== 総レコード数・総ページ数を取得 =====
    // DB接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT content_id FROM contents WHERE delete_flg = 0';
    // $sortに値が入っていれば、SQL文に追加
    if(!empty($sort)){
      switch($sort){
        case 1:
          $sql .= ' ORDER BY create_date DESC ';
          break;
        case 2:
          $sql .= ' ORDER BY create_date ASC ';
          break;
      }
    }
    $data = array();
    debug('SQL:'.$sql);
    // SQL文実行
    $stmt = queryPost($dbh, $sql, $data);

    if(!$stmt){
      debug('クエリ失敗・・・。');
      return false;
    }else{
      debug('クエリ成功！');
      // 総レコード数取得
      $result['total_record'] = $stmt->rowCount();
      // 総ページ数取得
      $result['total_page'] = ceil($result['total_record'] / $PageShow);
    }
    // ===== ページング用 =====
    // SQL文作成
    // 半角スペースが必要　（だめ→$PageShow.'OFFSET'.$currentMinNum）
    $sql = 'SELECT * FROM contents'; // TODO: ここをプレースホルダーにするには？
    // $sortに値が入っていれば、SQL文に追加
    if(!empty($sort)){
      switch ($sort) {
        case 1:
          $sql .= ' ORDER BY create_date DESC';
          break;
        case 2:
          $sql .= ' ORDER BY create_date ASC';
          break;
      }
    }
    $sql .= ' LIMIT '.$PageShow.' OFFSET '.$currentMinNum;
    $data = array();
    debug('SQL:'.$sql);
    // SQL文実行
    $stmt = queryPost($dbh, $sql, $data);

    if(!$stmt){
      debug('クエリ失敗・・・。');
      return false;
    }else{
      debug('クエリ成功！！');
      $result['records_data'] = $stmt->fetchAll();
      return $result;
    }

  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
  }
}

// Contentsテーブルのデータ取得
function getMyContents($user_id){
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM contents WHERE user_id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $user_id);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      debug('クエリ成功！！');
      return $stmt->fetchAll();
    }else{
      debug('クエリ失敗・・・。');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
  }
}

// favoritesテーブルのデータ取得
function getMyFavorites($user_id){
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favorites AS f LEFT JOIN contents AS c ON f.content_id = c.content_id WHERE f.user_id = :u_id';
    $data = array(':u_id' => $user_id);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      debug('クエリ成功！！');
      return $stmt->fetchAll();
    }else{
      debug('クエリ失敗・・・。');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
  }
}

// messageテーブルの同じboard_id内で最新のもののみ取得
// messageテーブルのデータ取得(マイページに最新メッセージ表示用)
function getMessage($user_id){
  try {
    $dbh = dbConnect();
    $sql = 'SELECT *
            FROM message AS m
            LEFT JOIN users AS u
            ON m.guest_id = u.user_id
            WHERE m.owner_id = :u_id AND NOT m.guest_id = :u_id';
    $data = array(':u_id' => $user_id);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      debug('クエリ成功！！');
      return $stmt->fetchAll(); // TODO: 最新の情報だけを返したい
    }else{
      debug('クエリ失敗・・・。');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
  }
}

// ========== メール送信実行関数 ==========
function SendMail($from, $subject, $to, $comment){
  if(!empty($from) && !empty($subject) && !empty($to) && !empty($comment)){

    // 文字化け防止
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");
    // メール送信関数
    $result = mb_send_mail($to, $subject, $comment, 'From:'.$from);

    if($result){
      debug('メール送信成功！');
    }else{
      debug('メール送信失敗・・・。');
    }
  }
}

// ========== エラーメッセージ表示関数 ==========
function ErrorMessage($key){
  global $err_msg;
  if(!empty($err_msg[$key])){
    echo $err_msg[$key];
  }
}

// ========== DB接続(PDOオブジェクト作成)関数 ==========
function dbConnect(){
  $dsn = 'mysql:dbname=photodiary;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    // SQL実行失敗時に例外をスロー
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

// ========== SQL文(クエリ)実行関数 ==========
//DB接続(PDOオブジェクト作成)関数、各々のSQL文を挿入
function queryPost($dbh, $sql, $data){
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);
  return $stmt;
}

// ========== 入力フォーム保持関数 ==========
function TextKeep($key, $flg = true){
  if($flg){
    $method = $_POST;
  }else{
    $method = $_GET;
  }
  if(isset($method[$key])){
    return $method[$key];
  }
}
// ========== プロフ編集画面用：入力フォーム保持関数(DBかPOST送信した値を表示) ==========
function FormData($key, $flg = true){
  if($flg){
    $method = $_POST;
  }else{
    $method = $_GET;
  }
  global $tb_users;
  global $err_msg;
  // 1.DBにデータが「ある」場合
  if(!empty($tb_users[$key])){

    // 1-1.エラーがあった場合
    if(!empty($err_msg[$key])){
      // 1-1-1.POST送信があった場合
      if(isset($_POST[$key])){
        return $_POST[$key]; //POST送信の値を表示
      // 1-1-2.(ありえないけど)POST送信がなかった場合
      }else{
        return $tb_users[$key]; //DBの値を表示
      }
    // 1-2.エラーがなかった場合
    }else{
      // 1-2-1.POST送信があり、DBと異なっていた場合
      if(isset($_POST[$key]) && $_POST[$key] !== $tb_users[$key]){
        return $_POST[$key]; //POST送信の値を表示
      // 1-2-2.POST送信がない場合
      }else{
        return $tb_users[$key]; //DBの値を表示
      }
    }

  // 2.そもそもDBにデータが「ない」場合
  }else{
    if(empty($tb_users[$key])){
      // 2-1.POST送信があった場合
      if(isset($_POST[$key])){
        return $_POST[$key]; //POST送信の値を表示
      // 2-2.POST送信がなかった場合
      }
    }
  } //DBにデータがない場合用
} //入力フォーム保持関数用

// ========== コンテンツ投稿・編集画面用：入力フォーム保持関数(DBかPOST送信した値を表示) ==========
function FormContentsData($key){
  global $ContentData;
  global $err_msg;
  // 1.DBにデータが「ある」場合
  if(!empty($ContentData[$key])){

    // 1-1.エラーがあった場合
    if(!empty($err_msg[$key])){
      // 1-1-1.POST送信があった場合
      if(isset($_POST[$key])){
        return $_POST[$key]; //POST送信の値を表示
      // 1-1-2.(ありえないけど)POST送信がなかった場合
      }else{
        return $ContentData[$key]; //DBの値を表示
      }
    // 1-2.エラーがなかった場合
    }else{
      // 1-2-1.POST送信があり、DBと異なっていた場合
      if(isset($_POST[$key]) && $_POST[$key] !== $ContentData[$key]){
        return $_POST[$key]; //POST送信の値を表示
      // 1-2-2.POST送信がない場合
      }else{
        return $ContentData[$key]; //DBの値を表示
      }
    }

  // 2.そもそもDBにデータが「ない」場合
  }else{
    if(empty($ContentData[$key])){
      // 2-1.POST送信があった場合
      if(isset($_POST[$key])){
        return $_POST[$key]; //POST送信の値を表示
      // 2-2.POST送信がなかった場合
      }
    }
  } //DBにデータがない場合用
} //入力フォーム保持関数用

// ========== セッション一回のみ利用して削除する関数 ==========
function GetSessionFlash($key){
  if(!empty($_SESSION[$key])){
    $msg = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $msg;
  }
}

// ========== 認証キーを生成 ==========
function CreateNumber($length = 8){
  $number = '';
  $key = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  for ($i = 0; $i < $length; ++$i) {
    $number .= $key[mt_rand(0, 61)];
  }
  return $number;
}

// ========== 画像をアップロード ==========
function UploadImg($file, $key){
  debug('画像アップロード処理開始');
  debug('FILE情報：'.print_r($file,true));

  if(isset($file['error']) && is_int($file['error'])){ //なぜ必要？？
    try {
      // エラー判別のswitch文
      switch ($file['error']){
        // 問題なしの場合
        case UPLOAD_ERR_OK:
          break;
        //ファイルが未入力の場合
        case UPLOAD_ERR_NO_FILE:
          throw new RuntimeException('ファイルが選択されていません');
        //ファイルのサイズが大きい場合
        case UPLOAD_ERR_INI_SIZE: //php.iniファイルで再在サイズ定義
        case UPLOAD_ERR_FORM_SIZE: //フォームで再在サイズ定義(input type='file'の上に定義)
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default: //その他
          throw new RuntimeException('その他のエラーが発生しました');
      }
      // MIMEタイプチェック(ブラウザとサーバー間でやり取りする時に使用)
        //「 exif_imagetype 」:「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
          // (引数にはファイルのパスが含まれているもの → [tmp_name]は一時的に画像を保存するのでパスあり)
        // 前に「 @ 」を必須でつける : エラーになっても無視して後続を実行する
      $mime_type = @exif_imagetype($file['tmp_name']);
      debug('MIMEタイプ：'.print_r($mime_type, true));
      // 画像の形式をチェック（アップロードする画像が指定の形式がチェック）
      if (!in_array($mime_type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)){
        throw new RuntimeException('画像形式が未対応です');
      }
      // 画像のアップロード先を指定
        //「アップロード先ファイル」+「アップロードする画像ファイル(ハッシュ化)(tmp_nameに一時保存)」+「拡張子」
        //「sha1_file」: ハッシュ化
        //「image_type_to_extension」:「exif_imagetype」などから返される画像形式のMIMEタイプを取得する・拡張子を付ける
      $path = 'img/'.sha1_file($file['tmp_name']).image_type_to_extension($mime_type);
      // 画像アップロード
        // $file['tmp_name']から$pathに保存先を変更
      if(!move_uploaded_file($file['tmp_name'], $path)){
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッション(権限)を変更する
      chmod($path, 0644);

      debug('ファイルアップロード成功！');
      debug('ファイルパス：'.print_r($path, true));
      return $path;

    } catch (RuntimeException $e) {
      error_log('エラー発生：'.$e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    } //try&catch用
  } //エラーが入っているか確認用
} //function用

// ========== 画像表示関数 ==========
function showImg($path){
  if(!empty($path)){
    return $path;
  }
}

// ========== コンテンツ詳細画面のURL部分の処理関数(GETパラメータを一部除去) ==========
function removeParam($getArray = array()){
  if(!empty($_GET)){
    foreach ($_GET as $key => $val){
      if(in_array($key, $getArray, true)){
        $result = '?'.$key.'='.$val;
        return $result;
      }
    }
  }
}
