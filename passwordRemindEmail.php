<?php

// ========== 共通ファイル読み込み ==========
require('function.php');

// ========== 「画面処理開始時」のデバッグログ ==========
debug('=========================================================================');
debug('パスワード再発行メール送信画面');
debug('=========================================================================');
debugLogStart();

// ========== ログイン認証なし ==========

// ========== POST送信チェック ==========
if(!empty($_POST)){
  debug('POST送信があります。');

  $email = $_POST['email'];

  // ========== バリデーションチェック ==========
  // 未入力チェック
  ValidRequired($email, 'email');

  if(empty($err_msg)){
    debug('未入力チェックOK!!');

    // emailのバリデーションチェック(形式、最大文字数)
    ValidEmail($email, 'email');
    ValidMaxLen($email, 'email');

    if(empty($err_msg)){
      debug('バリデーションチェックOK!!');

      // ========== DB接続・EmailがDBに登録されているかチェック ==========
      try {
        // DB接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        // SQL文実行
        $stmt = queryPost($dbh, $sql, $data);
        // SQL文実行結果取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        // SQL文（クエリ）結果判定
        if($stmt && array_shift($result)){ //array_shiftの理由：検索HITなし → [count(*)] => 0 つまり0 = false
          // ========== 認証キーを生成してメールを送信 ==========
          debug('クエリ成功！');

          //パスワード再発行メール送信メッセージ格納
          $_SESSION['msg_success'] = SUC02;

          // 認証キー作成
          $auth_key = CreateNumber();

          // メール送信準備
          $from = 'info@sample.co.jp';
          $subject = '【パスワード再発行認証】｜photodiary';
          $to = $email;
          $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：http://localhost:8888/photodiary/passwordRemindKey.php
認証キー：{$auth_key}
※認証キー有効期限は60分以内となります。

認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
http://localhost:8888/photodiary/passwordRemindEmail.php
>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
photodiaryカスタマーセンター
Email : {$from}
>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
EOT;
        // メール送信
        SendMail($from, $subject, $to, $comment);
        // ========== 認証に必要な情報をセッションへ保存 ==========
        // セッションに「メールアドレス」・「認証キー」・「認証キー有効期限」を保存
        $_SESSION['auth_email'] = $email;
        $_SESSION['auth_key'] = $auth_key;
        $_SESSION['auth_key_limit'] = time() + (60*60); //今回は１時間に設定
        debug('セッションの中身：'.print_r($_SESSION, true));

        debug('認証キー入力画面に遷移します。');
        header("Location:passwordRemindKey.php");

        }else{
          debug('クエリ失敗した or DBにないメールアドレス。');
          $err_msg['common'] = ERR10;
        }

      } catch (Exception $e) {
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = ERR10;
      }
    }
  }
}

?>

<!-- ===== head ===== -->
<?php
  $title = '確認メール入力画面';
  require('head.php');
?>

  <!-- ===== body ===== -->
  <!-- ①header -->
  <?php
    require('header.php');
  ?>

    <!-- ②main -->
    <main>
      <div class="site-width">
        <section id="colum-1">
          <div class="form-frame notitle_form-frame">
            <form class="form" action="" method="post">
              <span class="password_remind_msg">ご指定のメールアドレス宛にパスワード再発行用のURLの認証キーをお送り致します</span>

              <div class="msg-form">
                <?php ErrorMessage('common'); ?>
              </div>

              <label class="item">
                メールアドレス
                <input class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>" type="text" name="email" value="<?php echo TextKeep('email'); ?>">
              </label>
              <div class="msg-form">
                <?php ErrorMessage('email'); ?>
              </div>

              <label class="submit-frame">
                <input type="submit" name="submit" value="送信">
              </label>
              <!-- パスワードリマインダー画面：マイページへ遷移 -->
              <a class="to_pass-remind" href="login.php">&gt;&gt; ログイン画面へ</a>
            </form>
          </div>
        </section>
      </div>
    </main>

    <!-- ③footer -->
    <?php require('footer.php'); ?>
