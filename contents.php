<?php

// ========== 共通ファイル読み込み ==========
require('function.php');

// ========== 「画面処理開始時」のデバッグログ ==========
debug('=========================================================================');
debug('コンテンツ一覧画面');
debug('=========================================================================');
debugLogStart();

// ========== ログイン認証(既にログインしているユーザーがチェック) ==========
require('auth.php');

// ========== 画面表示用のデータを取得 ==========
// 「現在のページ」のGETパラメータを取得(URLから)
$currentPageNum = (!empty($_GET['page'])) ? $_GET['page'] : 1; // デフォルトは１ページ目
// 「ソート順」のGETパラメータを取得
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';
debug('現在のページ：'.print_r($currentPageNum, true));
// パラメータが改ざんされていないかチェック(改ざんがあったら、再度コンテンツ一覧ページを読み込み)
if(!is_int((int)$currentPageNum)){ //// TODO: なぜ(int)が必要なの？
  debug('エラー発生：指定ページに不正な値が入りました。');
  header("Location:contents.php");
}

// 「１ページ中の表示項目数」
$PageShow = 10;
// 現在の表示レコードの先頭
$currentMinNum = (($currentPageNum - 1) * $PageShow);
// DBからコンテンツデータを取得（content_id）
// 変数 $result内にデータを格納(「総レコード数」=>['total_record']、「総ページ数」=>['total_page']、「全レコードのデータ」=>['records_data'])
$ContentDataEdit = TableContentsEdit($currentMinNum, $sort);

debug('コンテンツデータの中身：'.print_r($ContentDataEdit['total_page'], true));
debug('コンテンツデータの中身：'.print_r($ContentDataEdit, true));
debug('画面表示処理終了 ====================================================');

?>
<!-- ===== head ===== -->
<?php
  $title = 'HOME画面';
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
            <h2 class="title"></h2>
          </div>
          <!-- 画面 -->
          <div class="form-frame">
            <!-- 左側のカラム -->
            <section id="colum-2-1">
              <!-- formの上部表示 -->
              <div class="form-top">
                <div class="data-num-frame">
                  <?php // TODO: なぜ、count($ContentDataEdit['records_data'])で1ページ１０件のみ表示できるのか？(全レコード数表示しそうだけど？) ?>
                  <span><?php echo $currentMinNum + 1; ?></span>~<span><?php echo $currentMinNum + count($ContentDataEdit['records_data']); ?></span>/<span><?php echo $ContentDataEdit['total_record']; ?></span>件中
                </div>
                <form class="data-search-frame" method="get">
                  <!-- 表示順 -->
                  <div class="order-frame">
                    <span class="order-title">表示順</span>
                    <select class="order" name="sort">
                      <option value="0" <?php if(TextKeep('sort', false) == 0){echo 'selected';} ?>>--選択して下さい--</option>
                      <option value="1" <?php if(TextKeep('sort', false) == 1){echo 'selected';} ?>>新着順</option>
                      <option value="2" <?php if(TextKeep('sort', false) == 2){echo 'selected';} ?>>投稿順</option>
                    </select>
                  </div>
                  <!-- キーワード検索 -->
                  <div class="keyword-research-frame">
                    <?php // TODO: キーワード検索機能も追加する ?>
                    <input class="keyword-research-enter" type="text" class="keyword-research" name="keyword-research" placeholder="キーワード検索">
                    <input class="keyword-research-submit" type="submit" value="検索">
                  </div>
                </form>
              </div>
              <!-- 投稿コンテンツ -->
              <form class="form contents-form" action="" method="post">
                <div class="form-main">
                  <!-- 投稿コンテンツ -->
                  <?php foreach ($ContentDataEdit['records_data'] as $key => $val): ?>
                    <!-- コンテンツ詳細画面からコンテンツ一覧画面に戻る際に(page=〇〇)が必要 -->
                    <?php // TODO: URLに検索機能を使用した時のURLをくっつけてあげたい ?>
                    <a href="contentDetail.php?c_id=<?php echo $val['content_id'].'&page='.$currentPageNum; ?>" class="upload-content">
                      <div class="content-head">
                        <img class="upload-content_img" src="<?php echo $val['pic1']; ?>" alt="<?php echo $val['title']; ?>" style="height:160px; border-radius: 3px;">
                      </div>
                      <div class="content-body">
                        <span class="upload-content_text"><?php echo $val['title']; ?></span>
                      </div>
                    </a>
                  <?php endforeach; ?>
                </div>
              </form>
              <!-- ページネーション -->
              <div class="form-bottom">
                <ul>
                  <?php
                    //「ページネーションのカラム数」
                    $PagenationNum = 5;
                    //「総ページ数」
                    $TotalPage = $ContentDataEdit['total_page'];
                    // ページネーションのカラム表示設定
                    if($currentPageNum == $TotalPage && $TotalPage >= $PagenationNum){
                      $PagenatoinMinNum = $currentPageNum - 4;
                      $PagenatoinMaxNum = $currentPageNum ;
                    }elseif($currentPageNum == ($TotalPage - 1) && $TotalPage >= $PagenationNum){
                      $PagenatoinMinNum = $currentPageNum - 3;
                      $PagenatoinMaxNum = $currentPageNum + 1;
                    }elseif($currentPageNum == 2 && $TotalPage >= $PagenationNum){
                      $PagenatoinMinNum = $currentPageNum - 1;
                      $PagenatoinMaxNum = $currentPageNum + 3;
                    }elseif($currentPageNum == 1 && $TotalPage >= $PagenationNum){
                      $PagenatoinMinNum = $currentPageNum;
                      $PagenatoinMaxNum = $currentPageNum + 4;
                    // ページネーションのカラムより総ページ数が少ない場合(1~総ページ数)
                    }elseif($TotalPage < $PagenationNum){
                      $PagenatoinMinNum = 1;
                      $PagenatoinMaxNum = $TotalPage;
                    }else{
                      $PagenatoinMinNum = $currentPageNum - 2;
                      $PagenatoinMaxNum = $currentPageNum + 2;
                    }
                  ?>
                  <!-- 最初のページへ戻る -->
                  <?php if($currentPageNum != 1): ?>
                    <li class="list-item"><a href="?page=1">&lt;</a></li>
                  <?php endif; ?>
                  <?php for($i = $PagenatoinMinNum; $i <= $PagenatoinMaxNum; $i++): ?>
                    <?php // TODO: ページネーションをしても、検索機能を使用できる設定にする ?>
                    <li class="list-item <?php if($i == $currentPageNum) echo 'now-page'; ?>"><a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                  <?php endfor; ?>
                  <!-- 最後のページへ進む -->
                  <?php if($currentPageNum != $TotalPage): ?>
                    <li class="list-item"><a href="?page=<?php echo $TotalPage; ?>">&gt;</a></li>
                  <?php endif; ?>
                </ul>
              </div>

            </section>

            <!-- 右側のカラム -->
            <?php require('sidebar.php'); ?>

          </div>
        </div>
      </div>
    </main>

    <!-- ③footer -->
    <?php require('footer.php'); ?>
