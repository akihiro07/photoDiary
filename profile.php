<?php

// ========== 共通ファイル読み込み ==========
require('function.php');

// ========== 「画面処理開始時」のデバッグログ ==========
debug('=========================================================================');
debug('プロフィール編集画面');
debug('=========================================================================');
debugLogStart();

// ========== ログイン認証(既にログインしているユーザーがチェック) ==========
require('auth.php');

// DBからユーザー情報取得($_SESSION内)
$tb_users = TableUsers($_SESSION['user_id']);
$tb_sex = TableSex();
debug('DBから取得したユーザー情報の中身：'.print_r($tb_users, true));
debug('DBから取得したSEXテーブルの中身：'.print_r($tb_sex, true));

// ========== POST送信チェック ==========
if(!empty($_POST)){

  // 変数にPOST送信されたユーザー情報を代入
  $name = $_POST['name'];
  $age = $_POST['age'];
  $sex = $_POST['sex_id'];
  $prof_img = (!empty($_FILES['prof_img']['name'])) ? UploadImg($_FILES['prof_img'], 'prof_img') : '';
  $prof_img = ( empty($prof_img) && !empty($tb_users['prof_img']) ) ? $tb_users['prof_img'] : $prof_img;
  $email = $_POST['email'];

  // ========== DBのユーザー情報とPOST送信されたユーザー情報を比較 => 異なればバリデーションチェック ==========
  // TODO: 変更時、空白フォーム部分はバリデーションチェックをしないようにしたい
  // 名前のバリデーションチェック
  if($tb_users['name'] !== $name){
    ValidName($name, 'name');
    ValidMaxLen($name, 'name');
  }

  // 年齢のバリデーションチェック
  if($tb_users['age'] !== $age){
    validAge($age);
  }

  // メールアドレスのバリデーションチェック
  if($tb_users['email'] !== $email){
    ValidEmail($email, 'email');
    ValidMaxLen($email, 'email');
  }

  // プロフィール画像のバリデーションチェック
  // TODO: PNG と JPG の場合のみOK

  if(empty($err_msg)){
    debug('バリデーションチェックOK!！');

    // ========== DB接続・レコードアップデート ==========
    try {
      // DB接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'UPDATE users SET name = :name, age = :age, sex_id = :sex_id, prof_img = :prof_img, email = :email WHERE user_id = :user_id';
      // SQL文にデータ流し込み
      $data = array(
                ':name' => $name,
                ':age' => $age,
                ':sex_id' => $sex,
                ':prof_img' => $prof_img,
                ':email' => $email,
                ':user_id' => $tb_users['user_id']
              );
      // SQL文実行
      $stmt = queryPost($dbh, $sql, $data);
      // SQL文実行結果
      if($stmt){
        debug('クエリ成功！');
        debug('マイページへ遷移');
        header("Location:mypage.php");

      }else{
        debug('クエリ失敗・・・');
        $err_msg['common'] = ERR10;
      }

    } catch (Exception $e) {
      error_log('エラー発生：'.$e->getMessage());
      $err_msg['common'] = ERR10;
    } //try&catch用
  } //バリデーションチェックOK用
} //POST送信用
debug('画面表示処理終了 =================================================');

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
        <div id="colum-2">
          <!-- 画面タイトル -->
          <div class="title-frame">
            <h2 class="title">プロフィール編集</h2>
          </div>
          <!-- 画面 -->
          <div class="form-frame">
            <!-- 左側のカラム -->
            <section id="colum-2-1">
              <form class="form" action="" method="post" enctype="multipart/form-data" novalidate="novalidate">
                <div class="msg-form">
                  <?php ErrorMessage('common'); ?>
                </div>

                <?php // TODO: 変更時、空白フォーム部分はバリデーションチェックをしないようにしたい ?>
                <label class="item-left">
                  名前
                  <input class="<?php if(!empty($err_msg['name'])) echo 'err'; ?>" type="text" name="name" value="<?php echo FormData('name'); ?>">
                </label>
                <div class="msg-form">
                  <?php ErrorMessage('name'); ?>
                </div>

                <label class="item-left age-frame">
                  年齢
                  <?php // TODO: 年齢を入れなくてもOKにしたい ?>
                  <input class="age <?php if(!empty($err_msg['age'])) echo 'err'; ?>" type="number" name="age" value="<?php echo FormData('age'); ?>">
                </label>
                <div class="msg-form">
                  <?php ErrorMessage('age'); ?>
                </div>

                <label class="item-left">
                  性別
                  <select class="select" name="sex_id">
                    <?php // TODO: SELECTタグをDBに保存する方法(登録したボタンを保持する)　 ?>
                    <option value="0">--- 選択して下さい ---</option>
                    <?php foreach ($tb_sex as $key => $val) { ?>
                        <option value="<?php echo $val['sex_id']; ?>" <?php if($val['sex_id'] == FormData('sex_id')) {echo 'selected';} ?>>
                          <?php echo $val['sex']; ?>
                        </option>
                    <?php } ?>
                  </select>
                </label>

                <label class="item-left">
                  メールアドレス
                  <input class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>" type="text" name="email" value="<?php echo FormData('email'); ?>">
                </label>
                <div class="msg-form">
                  <?php ErrorMessage('email'); ?>
                </div>

                <label class="item-left">
                  プロフィール画像
                  <?php // TODO: Ajaxでリアルタイムで画像を表示 ?>
                  <div class="picture-frame" style="margin-top: 10px;">
                    ドラッグ＆ドロップ
                    <input type="hidden" name="MAX_FILE_SIZE" value="10485760"> <!-- 画像最大サイズ指定(フォーム＋php.iniファイル) -->
                    <input class="input-photo" type="file" name="prof_img">
                    <?php // TODO: 画像を表示するようにする(現在なぜかDBにArrayとしてデータが入っている状態) ?>
                    <img class="photo-img" src="<?php echo FormData('prof_img');  ?>" style="<?php if(empty(FormData('prof_img'))) echo 'display:none;' ?>">
                  </div>
                </label>
                <div class="msg-form">
                  <?php ErrorMessage('prof_img'); ?>
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
