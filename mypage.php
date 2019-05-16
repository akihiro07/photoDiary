<?php

// ========== 共通ファイル読み込み ==========
require('function.php');

// ========== 「画面処理開始時」のデバッグログ ==========
debug('=========================================================================');
debug('マイページ画面');
debug('=========================================================================');
debugLogStart();

// ========== ログイン認証(既にログインしているユーザーがチェック) ==========
require('auth.php');

// ========== DBから各テーブルのデータを取得 ==========
$userId = $_SESSION['user_id'];
$ContentsData = getMyContents($userId);
$FavoritesData = getMyFavorites($userId);
$messageData = getMessage($userId);
debug('マイコンテンツの中身：'.print_r($ContentsData, true));
debug('お気に入りの中身：'.print_r($FavoritesData, true));
debug('メッセージの中身：'.print_r($messageData, true));


?>
<!-- ===== head ===== -->
<?php
  $title = 'マイページ';
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
        <div id="colum-2">
          <!-- 画面タイトル -->
          <div class="title-frame">
            <h2 class="title">マイページ</h2>
          </div>
          <!-- 画面 -->
          <div class="form-frame">
            <!-- 左側のカラム -->
            <section id="colum-2-1">
              <form class="form mypage-form" action="" method="post" enctype="multipart/form-data">
                <label class="item-left">
                  <h3 class="mypage-h3">&#9654; 投稿一覧</h3>
                  <div class="up-content">
                    <?php if(!empty($ContentsData)): ?>
                      <?php foreach ($ContentsData as $key => $val): ?>
                        <a class="mycontent-frame" href="contentRegistEdit.php<?php echo '?c_id='.$val['content_id']; ?>">
                          <div class="mycontent-head">
                            <img class="content-img" src="<?php echo showImg($val['pic1']); ?>">
                          </div>
                          <div class="mycontent-body">
                            <p><?php echo $val['title']; ?></p>
                          </div>
                        </a>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </label>

                <label class="item-left">
                  <h3 class="mypage-h3">&#9654; お気に入り一覧</h3>
                  <div class="favorite-content">
                    <?php if(!empty($FavoritesData)): ?>
                      <?php foreach ($FavoritesData as $key => $val): ?>
                        <a class="myfavorite-frame" href="contentDetail.php<?php echo '?c_id='.$val['content_id']; ?>">
                          <div class="myfavorite-head">
                            <img class="favorite-img" src="<?php echo showImg($val['pic1']); ?>">
                          </div>
                          <div class="myfavorite-body">
                            <p><?php echo $val['title']; ?></p>
                          </div>
                        </a>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>
                </label>

                <label class="item-left">
                  <h3 class="mypage-h3">&#9654; メッセージ</h3>
                  <div class="latest-message-frame">
                    <table class="latest-message">
                          <thead>
                            <tr>
                              <th class="th-left">最新送信日時</th>
                              <th class="th-center">相手</th>
                              <th class="th-right">メッセージ</th>
                            </tr>
                          </thead>
                      <?php if(!empty($messageData)): ?>
                        <?php foreach ($messageData as $key => $val): ?>
                          <tbody>
                            <tr>
                              <td class="td-left"><?php echo $val['create_date']; ?></td>
                              <td class="td-center"><?php echo $val['name']; ?></td>
                              <td class="td-right"><?php echo $val['message']; ?></td>
                            </tr>
                          </tbody>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </table>
                  </div>
                </label>
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
