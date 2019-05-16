<?php

// ========== 共通ファイル読み込み ==========
require('function.php');

// ========== 「画面処理開始時」のデバッグログ ==========
debug('=========================================================================');
debug('コンテンツ登録・編集画面');
debug('=========================================================================');
debugLogStart();

// ========== ログイン認証(既にログインしているユーザーがチェック) ==========
require('auth.php');


// ===============================
// GETパラメータ取得(登録画面と編集画面を判別する用)
// ===============================
// GETデータを格納
$get_param = (!empty($_GET['c_id'])) ? $_GET['c_id'] : ''; // URLを見て判断 => URLに「c_id=〇〇」があればtrue
// GETデータがあった場合、DBからコンテンツ(contents)情報を取得 (※自分が登録したコンテンツのみ取得可能に=>userのIDもチェック)
$ContentData = (!empty($get_param)) ? TableContents($get_param, $_SESSION['user_id']) : '';
//「新規登録画面」か「編集画面」かを判断
$screen_flg = (empty($ContentData)) ? false : true; // false:新規登録画面 true:編集画面
debug('商品ID：'.print_r($get_param, true));
debug('コンテンツ内容：'.print_r($ContentData, true));

// ===============================
// 編集画面:パラメータ改ざんチェック(自分以外のコンテンツ編集画面に入ろうとしていないか)
// ===============================
// GETパラメータはあるけどURLが改ざんされている（いじくられた）場合、正しい商品データが取れないのでマイページへ遷移
if(!empty($get_param) && empty($ContentData)){
  debug('GETパラメータのコンテンツIDが違います。マイページへ遷移します。');
  header("Location:mypage.php");
}

// ===============================
// POSTされているかチェック
// ===============================
if(!empty($_POST)){
debug('POST送信があります。');
debug('POST送信データ：'.print_r($_POST, true));
debug('FILES送信データ：'.print_r($_FILES, true));

  // 変数に情報を格納
  $title = $_POST['title'];
  $place = $_POST['place'];
  $comment = $_POST['comment'];
  $picture = $_FILES['pic1']['name'];
  // 画像が挿入されていれば、画像のアップロード処理($_FILESの中には配列の形式で[name]~[size]まで格納されている)
  // 画像がない場合は[name]部分は空
  $pic1 = (!empty($_FILES['pic1']['name'])) ? UploadImg($_FILES['pic1'], 'picture') : '';
  // POST送信がなかった時の処理
  $pic1 = (empty($pic1) && !empty($ContentData['pic1'])) ? $ContentData['pic1'] : $pic1;

  $pic2 = (!empty($_FILES['pic2']['name'])) ? UploadImg($_FILES['pic2'], 'picture') : '';
  $pic2 = (empty($pic2) && !empty($ContentData['pic2'])) ? $ContentData['pic2'] : $pic2;

  $pic3 = (!empty($_FILES['pic3']['name'])) ? UploadImg($_FILES['pic3'], 'picture') : '';
  $pic3 = (empty($pic3) && !empty($ContentData['pic3'])) ? $ContentData['pic3'] : $pic3;

  // ===============================
  // バリデーションチェック（新規登録画面と編集画面で分割）
  // ===============================
  // ===== 新規登録画面 =====
  if(empty($ContentData)){
    debug('===新規登録画面のバリデーションチェック===');
    // 未入力チェック（タイトル・画像は入力必須）
    ValidRequired($title, 'title');
    ValidRequiredImg($picture, 'picture'); // １枚以上(最低pic1だけ)必須

    if(empty($err_msg)){
      debug('未入力チェックOK!!');

      // タイトル：最大文字数
      ValidMaxLen($title, 'title');
      // 場所：最大文字数
      ValidMaxLen($place, 'place');
      // 思い出：300字以内
      ValidMaxLen($comment, 'comment', 300);
    }

  // ===== 編集画面 =====
  // DBとフォーム入力に違いがあれば、バリデーションチェック
  }else{
    debug('===編集画面のバリデーションチェック===');
    // 未入力チェック
    if($ContentData['title'] !== $title){
      ValidRequired($title, 'title');
    }
    if($ContentData['pic1'] !== $picture){
      ValidRequiredImg($picture, 'picture');
    }

    if(empty($err_msg)){
      debug('未入力チェックOK!!');
      // タイトル：最大文字数
      if($ContentData['title'] !== $title){
        ValidMaxLen($title, 'title');
      }
      // 場所：最大文字数
      if($ContentData['place'] !== $place){
        ValidMaxLen($place, 'place');
      }
      // 思い出：300字以内
      if($ContentData['comment'] !== $comment){
        ValidMaxLen($comment, 'comment', 300);
      }
    }
  }//編集画面用

  // 新規登録画面・編集画面共にバリデーションチェックOKの場合・・・
  // ===============================
  // DBへ新規登録(INSERT)・編集(UPDATE)　（新規登録画面と編集画面で分割）
  // ===============================
  if(empty($err_msg)){
    debug('バリデーションチェックOK!!');

    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      if($screen_flg){
        // ===== 編集画面 =====
        $sql = 'UPDATE contents SET title = :title, place = :place, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3, comment = :comment WHERE user_id = :user_id AND content_id = :c_id AND delete_flg = 0';
        $data = array(':title' => $title,
                      ':place' => $place,
                      ':pic1' => $pic1,
                      ':pic2' => $pic2,
                      ':pic3' => $pic3,
                      ':comment' => $comment,
                      ':user_id' => $_SESSION['user_id'],
                      ':c_id' => $get_param
                     );
      }else{
      // ===== 新規登録画面 =====
      $sql = 'INSERT INTO contents (title, place, pic1, pic2, pic3, comment, user_id, create_date) VALUES (:title, :place, :pic1, :pic2, :pic3, :comment, :user_id, :create_date)';
      $data = array(':title' => $title,
                    ':place' => $place,
                    ':pic1' => $pic1, // TODO: 今は未設定なのであとで設定する必要あり
                    ':pic2' => $pic2,
                    ':pic3' => $pic3,
                    ':comment' => $comment,
                    ':user_id' => $_SESSION['user_id'],
                    ':create_date' => date('Y-m-d H:i:s')
                   );
      }
      // SQL文実行
      $stmt = queryPost($dbh, $sql, $data);
      // SQL文実行結果
      if($stmt){
        debug('クエリ成功！！');
        // メッセージを格納
        if($screen_flg){
          // ===== 編集画面 =====
          $_SESSION['msg_success'] = SUC04;
        }else{
          // ===== 新規登録画面 =====
          $_SESSION['msg_success'] = SUC05;
        }
        debug('マイページへ遷移します。');
        header("Location:mypage.php");

      }else{
        debug('クエリ失敗・・・。');
      }

    } catch (Exception $e) {
      error_log('エラー発生：'.$e->getMessage());
      $err_msg['common'] = ERR10;
    }
  }
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
}// POST送信用

?>

<!-- ===== head ===== -->
<?php
  $title = (!$screen_flg) ? '思い出投稿画面' : '思い出編集画面';
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
            <h2 class="title"><?php echo (!$screen_flg) ? '思い出を共有' : '思い出を編集'; ?></h2>
          </div>
          <!-- 画面 -->
          <?php // TODO: googleマップで位置を表示できるようにしたい ?>
          <div class="form-frame">
            <!-- 左側のカラム -->
            <section id="colum-2-1">
              <form class="form" action="" method="post" enctype="multipart/form-data">
                <div class="msg-form">
                  <?php ErrorMessage('common'); ?>
                </div>

                <label class="item-left">
                  タイトル
                  <?php // TODO: 「必須」の文字を横につける ?>
                  <input class="<?php if(!empty($err_msg['title'])) echo 'err'; ?>" type="text" name="title" value="<?php echo FormContentsData('title'); ?>">
                </label>
                <div class="msg-form">
                  <?php ErrorMessage('title'); ?>
                </div>

                <label class="item-left">
                  場所
                  <input class="<?php if(!empty($err_msg['place'])) echo 'err'; ?>" type="text" name="place" value="<?php echo FormContentsData('place'); ?>">
                </label>
                <div class="msg-form">
                  <?php ErrorMessage('place'); ?>
                </div>

                <label class="item-left">
                  <p style="display:block">写真(3つまで登録可能)</p>
                  <?php // TODO: 「必須」の文字を横につける ?>
                  <div class="msg-img-form">
                    <?php ErrorMessage('picture'); ?>
                  </div>
                  <?php // TODO: Ajax通信を適用してみるのもありかも ?>
                  <!-- 写真１ -->
                  <div class="picture-frame">
                    ドラッグ＆ドロップ
                    <input type="hidden" name="MAX_FILE_SIZE" value="10485760"> <!-- 画像最大サイズ指定(フォーム＋php.iniファイル) -->
                    <input class="input-photo" type="file" name="pic1">
                    <img class="photo-img" src="<?php echo FormContentsData('pic1'); ?>" style="<?php if(empty(FormContentsData('pic1'))) echo 'display:none;' ?>">
                  </div>
                  <!-- 写真２ -->
                  <div class="picture-frame">
                    ドラッグ＆ドロップ
                    <input type="hidden" name="MAX_FILE_SIZE" value="10485760"> <!-- 画像最大サイズ指定(フォーム＋php.iniファイル) -->
                    <input class="input-photo" type="file" name="pic2">
                    <img class="photo-img" src="<?php echo FormContentsData('pic2'); ?>" style="<?php if(empty(FormContentsData('pic2'))) echo 'display:none;' ?>">
                  </div>
                  <!-- 写真３ -->
                  <div class="picture-frame">
                    ドラッグ＆ドロップ
                    <input type="hidden" name="MAX_FILE_SIZE" value="10485760"> <!-- 画像最大サイズ指定(フォーム＋php.iniファイル) -->
                    <input class="input-photo" type="file" name="pic3">
                    <img class="photo-img" src="<?php echo FormContentsData('pic3'); ?>" style="<?php if(empty(FormContentsData('pic3'))) echo 'display:none;' ?>">
                  </div>
                </label>

                <label class="item-left">
                  思い出
                  <textarea class="comment" name="comment" rows=6><?php echo FormContentsData('comment'); ?></textarea>
                </label>
                <div class="msg-form">
                  <?php ErrorMessage('comment'); ?>
                </div>

                <label class="submit-frame">
                  <input type="submit" name="submit" value="<?php echo (!$screen_flg) ? '投稿' : '編集'; ?>">
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
