<?php

// ========== 共通ファイル読み込み ==========
require('function.php');

// ========== 「画面処理開始時」のデバッグログ ==========
debug('=========================================================================');
debug('退会画面');
debug('=========================================================================');
debugLogStart();

// ========== ログイン認証(既にログインしているユーザーがチェック) ==========
require('auth.php');

// ========== POST送信チェック ==========
if(!empty($_POST)){
  debug('POST送信があります。');

  // ========== DB接続・論理削除(delete_flgを「0」=>「1」へ変更) ==========
  try {
    // DB接続
    $dbh = dbConnect();
    // SQL文(クエリ)作成
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE user_id = :user_id';
    $sql2 = 'UPDATE contents SET delete_flg = 1 WHERE user_id = :user_id';
    $sql3 = 'UPDATE favorites SET delete_flg = 1 WHERE user_id = :user_id';
    // データの流し込み
    $data = array(':user_id' => $_SESSION['user_id']);
    // SQL文(クエリ)実行
    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);
    // SQL文(クエリ)成功の場合
    if($stmt1 && $stmt2 && $stmt3){
      debug('クエリ成功。');
      // session削除
      session_destroy();
      debug('セッション変数の中身：'.print_r($_SESSION, true));
      debug('ユーザー登録画面へ遷移します。');
      header("Location:registration.php");

    // SQL文(クエリ)失敗の場合
    }else{
      debug('クエリ失敗。');
      $err_msg['common'] = ERR10;
    }

  } catch (Exception $e) {
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = ERR10;
  }


}

?>
<!-- ===== head ===== -->
<?php
  $title = '退会';
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
            <h2 class="title">退会</h2>
          </div>
          <div class="form-frame">
            <form class="form" action="" method="post">
              <div class="msg-form">
                <?php ErrorMessage('common'); ?>
              </div>

              <label class="submit-frame">
                <input class="delete_submit" type="submit" name="submit" value="旅を終える">
              </label>
              <!-- 退会画面：マイページへ遷移 -->
              <a class="to_pass-remind" href="mypage.php">&gt;&gt; マイページへ</a>
            </form>
          </div>
        </section>
      </div>
    </main>

    <!-- ③footer -->
    <?php require('footer.php'); ?>
