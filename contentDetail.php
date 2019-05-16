<?php

// ========== 共通ファイル読み込み ==========
require('function.php');

// ========== 「画面処理開始時」のデバッグログ ==========
debug('=========================================================================');
debug('コンテンツ詳細画面');
debug('=========================================================================');
debugLogStart();

// ========== ログイン認証(既にログインしているユーザーがチェック) ==========
require('auth.php');

// コンテンツIDのGETパラメータ取得
$c_id = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
debug('コンテンツID：'.print_r($c_id, true));
// DBからcontentsテーブルとusersテーブルを結合
$contentDetaildata = ContentDetailData($c_id);
debug('コンテンツ内容：'.print_r($contentDetaildata, true));
if(empty($contentDetaildata)){
  header("Location:contents.php");
}

// ===============================
// メッセージ機能
// ===============================
// ===== 画面表示時にboard(掲示板)テーブル生成 =====
$boardCreate = createBoard($c_id);
// ===== boardテーブルのデータを取得 =====
$boardData = getBoardData($c_id);
// ===== boardテーブルのboard_idを取得 =====
$boardId = $boardData['board_id'];
debug('boardテーブルのboard_idを取得:'.print_r($boardData['board_id'], true));
// ===== messageテーブルのsend_date, guest_id, owner_id, messageを取得 =====
$messageData = getMeassageData($boardId);


?>
<!-- ===== head ===== -->
<?php
  $title = '投稿一覧画面';
  require('head.php');
?>
  <style media="screen">
    .fa-thumbs-up{
      color: #585655;
    }
    .fa-thumbs-up.active{
      color: #60770B;
    }
    .fa-thumbs-up:hover{
      cursor: pointer;
    }
    /* TODO: アイコンの色変える */
  </style>
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
            <h2 class="title"></h2>
          </div>
          <!-- 画面 -->
          <div class="form-frame">
            <!-- 左側のカラム -->
            <section id="colum-2-1">
              <form class="form" action="" method="post" enctype="multipart/form-data" autocomplete="off">
                <h2 style="font-size:28px; margin-bottom: 10px; text-align: center;"><?php echo $contentDetaildata['title']; ?></h2>
                <div class="" style="display:flex;  justify-content:space-between; align-items: center;">
                  <label class="item-left">
                    場所：<span><?php echo $contentDetaildata['place']; ?></span>
                  </label>
                  <label class="item-left">
                    <?php // TODO: アイコンを押したら別のアイコン(塗りつぶされたアイコンに変えたい) ?>
                    <!-- isFavorite : activeを表示したら、画面が切り替わってもそのままキープ -->
                    <i class="far fa-thumbs-up js-favorite-action <?php if(isFavorite($contentDetaildata['content_id'], $_SESSION['user_id'])){ echo 'active'; } ?>" data-contentid="<?php echo $contentDetaildata['content_id']; ?>" style="font-size:24px; float:right;"></i>
                  </label>
                </div>
                <label class="item-left">
                  <div class="slider_container">
                    <ul class="slide_img_frame">
                      <?php // TODO: 写真2,3がからの場合はスライドさせない or スライドにからの画像を入れる ?>
                      <li class="slide_img"><img class="prof-img" src="<?php echo $contentDetaildata['pic1']; ?>" alt="" style="margin-bottom: 5px;"></li>
                      <li class="slide_img"><img class="prof-img" src="<?php echo $contentDetaildata['pic2']; ?>" alt="" style="margin-bottom: 5px;"></li>
                      <li class="slide_img"><img class="prof-img" src="<?php echo $contentDetaildata['pic3']; ?>" alt="" style="margin-bottom: 5px;"></li>
                    </ul>
                  </div>
                  <div class="slider_nav_frame">
                    <i class="fas fa-angle-left slider_nav slider_prev js-slider-prev"></i>
                    <i class="fas fa-angle-right slider_nav slider_next js-slider-next"></i>
                  </div>
                </label>
                <label class="item-left">
                  思い出
                  <div class="content-edit_detail" style="padding: 10px;"><?php echo $contentDetaildata['comment']; ?></div>
                </label>
                <?php // TODO: Ajaxを使用して、送信したメッセージを更新せずに反映できるか ?>
                <?php // TODO: 相手と自分(コンテンツ投稿者)によって左右に分けたい(Ajax部分でuserテーブルを取得する必要あり？) ?>
                <?php // TODO: 入力したら入力欄の文字を消したい ?>
                <div class="item-left">
                  メッセージ
                  <div id="message-area">
                    <div class="message-frame-show js-ajax-message">
                      <?php //ここの部分にAjax通信の処理が入る ?>
                      <?php //DBのmessageテーブルにメッセージが格納されていた場合、ページを開いたときに表示する設定 ?>
                      <div class="message-frame js-message">
                        <?php if (!empty($messageData)): ?><!-- 必要か？ -->
                          <?php foreach ($messageData as $key => $val): ?>
                            <!-- messageテーブルのデータ取得 -->
                            <div class="msg-box">
                              <p class="message"><?php echo $val['message']; ?></p>
                              <p class="msg_date"><?php echo $val['send_date']; ?></p>
                            </div>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </div>
                    </div>
                    <input class="board_id" type="hidden" name="" value="<?php echo $boardId; ?>">
                    <input class="content_user" type="hidden" name="" value="<?php echo $contentDetaildata['user_id']; ?>">
                    <input class="message_entry" type="text" name="message" placeholder="メッセージを入力してください">
                    <div class="msg-form">
                      <?php ErrorMessage('msg'); ?>
                    </div>
                    <button class="submit entry_button" name="submit" type="submit" style="border-radius:3px;">送信</button>
                  </div>
                </div>
                <!-- コンテンツ一覧画面へ遷移 -->
                <!-- コンテンツ詳細画面のURLに[c_id=〇〇&page=〇〇]が付いているので、[c_id=〇〇&]部分を外す -->
                <a class="back_contents" href="contents.php<?php echo removeParam(array('page')); ?>">&gt;&gt; コンテンツ一覧に戻る</a>
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
