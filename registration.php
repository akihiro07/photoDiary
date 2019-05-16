<!-- //ユーザー登録 -->
<?php

// 画面共通の機能を1つにまとめたファイルを読み込み
require('function.php');

// ========== 「画面処理開始時」のデバッグログ ==========
debug('=========================================================================');
debug('ユーザー登録画面');
debug('=========================================================================');
debugLogStart();

// ========== POST送信チェック ==========
if(!empty($_POST)){
  debug('POST送信があるべ。');

  // POST送信された値を変数に追加
  $name = $_POST['name'];
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $re_pass = $_POST['re_pass'];

  // ========== バリデーションチェック ==========
  //未入力チェック(名前)
  ValidRequired($name, 'name');
  //未入力チェック(Email)
  ValidRequired($email, 'email');
  //未入力チェック(パスワード)
  ValidRequired($pass, 'pass');
  //未入力チェック(パスワード再入力)
  ValidRequired($re_pass, 're_pass');

  if(empty($err_msg)){
    debug('未入力チェックOK!');
    //名前のバリデーションチェック(形式(特殊文字以外であればOK))
    ValidName($name, 'name');
    ValidMaxLen($name, 'name');

    //emailのバリデーションチェック(形式、長さ)
    ValidEmail($email, 'email');
    ValidMaxLen($email, 'email');

    //passwordのバリデーションチェック(半角英数字、最大文字数、最小文字数)
    ValidHalf($pass, 'pass');
    ValidMinLen($pass, 'pass');
    ValidMaxLen($pass, 'pass');

    //password(再入力)のバリデーションチェック(入力と一致しているか)
    ValidMatch($pass, $re_pass, 're_pass');

    if(empty($err_msg)){
      //emailのバリデーションチェック(重複)
      ValidEmailDup($email);

      if(empty($err_msg)){
        debug('バリデーションチェックOK！');

        // ========== DB接続 ==========
        // ========== レコード挿入 ==========
        try {
          // DB接続
          $dbh = dbConnect();
          // SQL文準備・作成
          $sql = 'INSERT INTO users (name, email, pass, login_time, create_date) VALUES (:name, :email, :pass, :login_time, :create_date)';
          $data = array(
                    ':name' => $name,
                    ':email' => $email,
                    ':pass' => password_hash($pass, PASSWORD_DEFAULT), // パスワードハッシュ化
                    ':login_time' => date("Y-m-d H:i:s"),
                    ':create_date' => date("Y-m-d H:i:s")
                  );
          // SQL文(クエリ)実行 = DBへレコード(データ)を挿入
          $stmt = queryPost($dbh, $sql, $data);

          if($stmt){
            debug('クエリ成功');

            // session変数に情報を格納する
            // === ①最終ログイン日時 ===
            $_SESSION['login_date'] = time();
            // === ②ログイン有効期限 ===
            $_SESSION['login_limit'] = 60*60;
            // === ③ユーザーID ===
            $_SESSION['user_id'] = $dbh->lastInsertId(); //lastInsertIdはPDOオブジェクトの関数
            debug('session変数の中身：'.print_r($_SESSION, true));

            debug('マイページへ遷移します。');
            header("Location:mypage.php");
          }else{
            debug('クエリ失敗');
          }

        } catch (Exception $e) {
          error_log('エラー発生：'.$e->getMessage());
          $err_msg['common'] = ERR10;
        }//catch部分
      }//バリデーションチェック2/2OK用
    }//バリデーションチェック1/2OK用
  }//未入力チェックOK用
}//POST送信チェック用
?>
<!-- ===== head ===== -->
<?php
  $title = 'ユーザー登録';
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
          <div class="title-frame">
            <h2 class="title">ユーザー登録</h2>
          </div>
          <div class="form-frame">
            <form class="form" action="" method="post">
              <div class="msg-form">
                <?php ErrorMessage('common'); ?>
              </div>

              <!-- 名前フォーム -->
              <label class="item">
                名前
                <input class="<?php if(!empty($err_msg['name'])) echo 'err'; ?>" type="text" name="name" value="<?php echo TextKeep('name'); ?>">
              </label>
              <div class="msg-form">
                <?php ErrorMessage('name'); ?>
              </div>

              <!-- メールアドレスフォーム -->
              <label class="item">
                メールアドレス
                <input class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>" type="text" name="email" value="<?php echo TextKeep('email'); ?>">
              </label>
              <div class="msg-form">
                <?php ErrorMessage('email'); ?>
              </div>

              <!-- パスワードフォーム -->
              <label class="item">
                パスワード
                <input class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>" type="password" name="pass" value="<?php echo TextKeep('pass'); ?>">
              </label>
              <div class="msg-form">
                <?php ErrorMessage('pass'); ?>
              </div>

              <!-- パスワード再入力フォーム -->
              <label class="item">
                パスワード(再入力)
                <input class="<?php if(!empty($err_msg['re_pass'])) echo 'err'; ?>" type="password" name="re_pass" value="<?php echo TextKeep('re_pass'); ?>">
              </label>
              <div class="msg-form">
                <?php ErrorMessage('re_pass'); ?>
              </div>

              <label class="submit-frame">
                <input type="submit" name="submit" value="旅に出る">
              </label>
            </form>
          </div>
        </section>
      </div>
    </main>

    <!-- ③footer -->
    <?php require('footer.php'); ?>
