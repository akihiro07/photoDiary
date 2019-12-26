<?php

// ========== 共通ファイル読み込み ==========
require('function.php');

// ========== 「画面処理開始時」のデバッグログ ==========
debug('=========================================================================');
debug('ログイン画面');
debug('=========================================================================');
debugLogStart();

// ========== 1. ログイン認証(既にログインしているユーザーがチェック) ==========
require('auth.php');

// ========== 2. POST送信チェック ==========
if(!empty($_POST)){
  debug('POST送信があります。');

  // 変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  // ========== 3. バリデーションチェック ==========
  // 未入力チェック
  ValidRequired($email, 'email');
  ValidRequired($pass, 'pass');

  if(empty($err_msg)){
    debug('未入力チェックOK！');

    // emailのバリデーションチェック
    ValidEmail($email, 'email');
    ValidMaxLen($email, 'email');
    // パスワードのバリデーションチェック
    ValidHalf($pass, 'pass');
    ValidMinLen($pass, 'pass');
    ValidMaxLen($pass, 'pass');

    // ========== 4. DB接続・パスワード取得 ==========
    if(empty($err_msg)){
      debug('バリデーションチェックOK！');
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文準備・作成
        $sql = 'SELECT pass, user_id FROM users WHERE email = :email AND delete_flg = 0'; //退会済みのユーザーは排除
        $data = array(':email' => $email);
        // SQL文実行
        $stmt = queryPost($dbh, $sql, $data);
        // SQL文実行結果(クエリ結果)を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // ========== 5. パスワード照合 ==========
        //「入力したパスワード」と「DBのパスワード」が一致していた場合
        if(!empty($result) && password_verify($pass, array_shift($result))){
          debug('パスワードがマッチしました。');

          // ===== セッション変数に「①最終ログイン日時」「②ログイン有効期限」「③ユーザーID」を保存 =====
          // === ①最終ログイン日時 ===
          $_SESSION['login_date'] = time();

          // === ③ユーザーID ===
          $_SESSION['user_id'] = $result['user_id']; //$resultにはpass,user_idが配列で格納されていた

          // === ②ログイン有効期限 ===
          $def_limit = 60 * 60; //デフォルトのログイン有効期限を変数に格納(1時間)
          //ログイン保持にチェックがある場合(30日)
          if(!empty($_POST['login_save'])){
            debug('ログイン保持にチェックあり！');
            $_SESSION['login_limit'] = $def_limit * 24 * 30;
          //ログイン保持にチェックがない場合(デフォルト：1時間)
          }else{
            debug('ログイン保持にチェックなし。');
            $_SESSION['login_limit'] = $def_limit;
          }

          debug('セッション変数の中身：'.print_r($_SESSION, true));
          // マイページへ遷移
          debug('マイページへ遷移します。');
          header("Location:mypage.php");

        //「入力したパスワード」と「DBのパスワード」が違っていた場合
        }else{
          debug('パスワードがアンマッチです・・・。');
          $err_msg['common'] = ERR09; //曖昧な言い方にする(メールアドレスの悪用を防ぐ為)
        }

      } catch (Exception $e) {
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = MSG10;
      } //try&catch部分
    } //バリデーションチェック1/2
  } //バリデーションチェック1/2
} //POST送信用

?>

<!-- ===== head ===== -->
<?php
  $title = 'ログイン';
  require('head.php');
?>

  <!-- ===== body ===== -->
  <!-- ①header -->
  <?php
    require('header.php');
  ?>
  <p id="js-suc-msg" class="suc-msg" style="display: none;">
    <?php echo GetSessionFlash('msg_success');  ?>
  </p>

    <!-- ②main -->
    <main>
      <div class="site-width">
        <section id="colum-1">
          <div class="title-frame">
            <h2 class="title">ログイン</h2>
          </div>
          <div class="form-frame">
            <form class="form" action="" method="post">
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

              <label class="item">
                パスワード
                <input class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>" type="password" name="pass" value="<?php echo TextKeep('pass'); ?>">
              </label>
              <div class="msg-form">
                <?php ErrorMessage('pass'); ?>
              </div>

              <!-- ログイン画面：次回ログイン省略チェックボックス -->
              <label class="item">
                <input type="checkbox" name="login_save"><span class="login_save_text">次回ログインを省略する</span>
              </label>
              <label class="submit-frame">
                <input type="submit" name="submit" value="旅に戻る">
              </label>
              <!-- ログイン画面：パスワードリマインダー -->
              <a class="to_pass-remind" href="passwordRemindEmail.php">&gt;&gt; パスワードを忘れた方はこちら</a>
            </form>
          </div>
        </section>
      </div>
    </main>

    <!-- ③footer -->
    <?php require('footer.php'); ?>
