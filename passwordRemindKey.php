<?php

// ========== 共通ファイル読み込み ==========
require('function.php');

// ========== 「画面処理開始時」のデバッグログ ==========
debug('=========================================================================');
debug('パスワード再発行認証キー入力画面');
debug('=========================================================================');
debugLogStart();

// ========== ログイン認証なし ==========

// ========== SESSIONに「認証キー」があるかチェック ==========
if(empty($_SESSION['auth_key'])){
  header("Location:passwordRemindEmail.php");
}

// ========== POST送信チェック ==========
if(!empty($_POST)){
  debug('POST送信があります。');
  $auth = $_POST['key'];

  // ========== バリデーションチェック ==========
  // 未入力チェック
  ValidRequired($auth, 'key');

  if(empty($err_msg)){
    debug('未入力チェックOK!!');

    // 8文字かチェック
    ValidAuthLen($auth, 'key');
    // 半角英数字かチェック
    ValidHalf($auth, 'key');

    if(empty($err_msg)){
      debug('バリデーションチェックOK！！');

      // 入力した認証キーとセッション変数の認証キーが一致しているかチェック
      if($auth !== $_SESSION['auth_key']){
        $err_msg['common'] = ERR15;
      }
      // 認証キーの有効期限をチェック
      if($_SESSION['auth_key_limit'] < time()){
        $err_msg['common'] = ERR16;
      }

      // ========== DB接続・パスワード変更 ==========
      if(empty($err_msg)){
        debug('認証キー一致 & 認証キー有効期限内');

        //パスワード生成
        $pass = CreateNumber();

        try {
          // DB接続
          $dbh = dbConnect();
          // SQL文作成
          $sql = 'UPDATE users SET pass = :pass WHERE email = :email AND delete_flg = 0';
          $data = array(':email' => $_SESSION['auth_email'],
                        ':pass' => password_hash($pass, PASSWORD_DEFAULT));
          // SQL文実行
          $stmt = queryPost($dbh, $sql, $data);

          // ========== メール送信 ==========
          // SQL文実行結果判定
          if($stmt){
            debug('クエリ成功。');

            // メール送信準備
            $from = 'info@sample.co.jp';
            $subject = '【パスワード再発行完了】｜photodiary';
            $to = $_SESSION['auth_email'];
            $comment = <<<EOT
本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。

ログインページ：http://localhost:8888/photodiary/login.php
再発行パスワード：{$pass}
※ログイン後、パスワードのご変更をお願い致します

>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
photodiaryカスタマーセンター
Email : {$from}
>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
EOT;
          // メール送信実行
          SendMail($from, $subject, $to, $comment);
          //
          // ========== セッション削除(session_unset) ==========
          session_unset();
          //パスワード再発行完了メッセージ格納
          $_SESSION['msg_success'] = SUC03;
          debug('セッション変数の中身：'.print_r($_SESSION, true));

          debug('ログイン画面へ遷移します。');
          return header("Location:login.php");


          }else{
            debug('クエリ失敗・・・。');
            $err_msg['common'] = ERR10;
          }

        } catch (Exception $e) {
          error_log('エラー発生：'.$e->getMessage());
          $err_msg['common'] = ERR10;
        }
      }
    }
  }
}

?>

<!-- ===== head ===== -->
<?php
  $title = '認証キー画面';
  require('head.php');
?>

  <!-- ===== body ===== -->
  <!-- ①header -->
  <?php
    require('header.php');
  ?>
  <p id="js-suc-msg" class="suc-msg" style="display: none;">
    <?php echo GetSessionFlash('msg_success'); ?>
  </p>

    <!-- ②main -->
    <main>
      <div class="site-width">
        <section id="colum-1">
          <div class="form-frame notitle_form-frame">
            <form class="form" action="" method="post">
              <span class="password_remind_msg">ご指定のメールアドレスにお送りした【パスワード再発行認証】メール内にある「認証キー」をご入力して下さい</span>

              <div class="msg-form">
                <?php ErrorMessage('common'); ?>
              </div>

              <label class="item">
                認証キー
                <input class="<?php if(!empty($err_msg['key'])) echo 'err'; ?>" type="text" name="key" value="<?php echo TextKeep('key'); ?>">
              </label>
              <div class="msg-form">
                <?php ErrorMessage('key'); ?>
              </div>

              <label class="submit-frame">
                <input type="submit" name="submit" value="再発行">
              </label>
            </form>
          </div>
        </section>
      </div>
    </main>

    <!-- ③footer -->
    <?php require('footer.php'); ?>
