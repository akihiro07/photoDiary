<?php

// ========== 共通ファイル読み込み ==========
require('function.php');

// ========== 「画面処理開始時」のデバッグログ ==========
debug('=========================================================================');
debug('パスワード変更画面');
debug('=========================================================================');
debugLogStart();

// ========== ログイン認証(既にログインしているユーザーがチェック) ==========
require('auth.php');

// DBからユーザー情報取得($_SESSION内)
$tb_users = TableUsers($_SESSION['user_id']);
debug('DBから取得したユーザー情報の中身：'.print_r($tb_users, true));

// ========== POST送信チェック ==========
if(!empty($_POST)){
  debug('POST送信があります。');

  // 変数にPOST送信されたユーザー情報を代入
  $old_pass = $_POST['old_pass'];
  $new_pass = $_POST['new_pass'];
  $re_new_pass = $_POST['re_new_pass'];

  // ========== バリデーションチェック ==========
  // 未入力チェック
  ValidRequired($old_pass, 'old_pass');
  ValidRequired($new_pass, 'new_pass');
  ValidRequired($re_new_pass, 're_new_pass');

  if(empty($err_msg)){
    debug('未入力チェックOK！！');

    // 古いパスワードのバリデーションチェック
    ValidHalf($old_pass, 'old_pass');
    ValidMinLen($old_pass, 'old_pass');
    ValidMaxLen($old_pass, 'old_pass');
    // 新しいパスワードのバリデーションチェック
    ValidHalf($new_pass, 'new_pass');
    ValidMinLen($new_pass, 'new_pass');
    ValidMaxLen($new_pass, 'new_pass');

    if(empty($err_msg)){
      // 古いパスワードがDBに登録されているかチェック
      if(!password_verify($old_pass, $tb_users['pass'])){
        global $err_msg;
        $err_msg['old_pass'] = ERR12;
      }

      // 新しいパスワードと新しいパスワード(再入力)のバリデーションチェック(一致しているか)
      ValidMatchSub($new_pass, $re_new_pass, 'new_pass');

      // ========== DB接続・レコード更新 ==========
      if(empty($err_msg)){
        debug('バリデーションチェックOK！！');

        try {
          // DB接続準備
          $dbh = dbConnect();
          // SQL文作成
          $sql = 'UPDATE users SET pass = :pass WHERE user_id = :user_id';
          $data = array(':pass' => password_hash($new_pass, PASSWORD_DEFAULT),
                        ':user_id' => $tb_users['user_id']
                       );
          // SQL文実行
          $stmt = queryPost($dbh, $sql, $data);
          // SQL文実行結果
          if($stmt){
            debug('クエリ成功。');
            // パスワード変更メッセージ
            $_SESSION['msg_success'] = SUC01;

            // ========== パスワード変更メール送信 ==========
            // メール送信準備
            $from = 'info@sample.co.jp';
            $subject = 'パスワード変更通知:photodiary';
            $name = $tb_users['name'];
            $to = $tb_users['email'];
            $comment = <<<EOT
{$name} さん
パスワードが変更されました。

>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
photodiaryカスタマーセンター
Email : {$from}
>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
EOT;
            // メール送信実行
            SendMail($from, $subject, $to, $comment);

            debug('マイページへ遷移します。');
            header('Location:mypage.php');

          }else{
            debug('クエリ失敗・・・。');
            $err_msg['common'] = ERR10;
          }

        } catch (Exception $e) {
          error_log('エラー発生：'.$e->getMessage());
          $err_msg['common'] = ERR10;

        } //try&catch用
      } // バリデーションチェック用
    } // バリデーションチェック用
  } //未入力チェック用
} //POST送信用

?>
<!-- ===== head ===== -->
<?php
  $title = 'パスワード変更画面';
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
        <div id="colum-2">
          <!-- 画面タイトル -->
          <div class="title-frame">
            <h2 class="title">パスワード変更</h2>
          </div>
          <!-- 画面 -->
          <div class="form-frame">
            <!-- 左側のカラム -->
            <section id="colum-2-1">
              <form class="form" action="" method="post">
                <div class="msg-form">
                  <?php ErrorMessage('common'); ?>
                </div>

                <label class="item-left">
                  古いパスワード
                  <input type="password" name="old_pass" value="">
                </label>
                <div class="msg-form">
                  <?php ErrorMessage('old_pass'); ?>
                </div>

                <label class="item-left">
                  新しいパスワード
                  <input type="password" name="new_pass" value="">
                </label>
                <div class="msg-form">
                  <?php ErrorMessage('new_pass'); ?>
                </div>

                <label class="item-left">
                  新しいパスワード(再入力)
                  <input type="password" name="re_new_pass" value="">
                </label>
                <div class="msg-form">
                  <?php ErrorMessage('re_new_pass'); ?>
                </div>

                <label class="submit-frame">
                  <input type="submit" name="submit" value="変更">
                </label>
                <!-- マイページ画面へ遷移 -->
                <a class="back_mypage" href="mypage.php">&gt;&gt; マイページへ</a>
              </form>
            </section>

            <!-- 右側のカラム -->
            <?php require('sidebar.php'); ?>

          </div>
        </div>
      </div>
    </main>

    <!-- ③footer -->
    <?php require('footer.php'); ?>
