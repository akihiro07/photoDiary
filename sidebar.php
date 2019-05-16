<!-- //ユーザー登録 -->
<?php

$userId = $_SESSION['user_id'];
$userData = TableUsers($userId);
debug('ユーザーテーブルの中身：'.print_r($userData, true));

?>

<!-- 右側のカラム -->
<section id="colum-2-2">
  <div class="img-frame">
    <?php if(!empty($userData['prof_img'])): ?>
      <img src="<?php echo $userData['prof_img']; ?>">
    <?php else: ?>
      <img src="<?php echo 'img/noimage.jpg'; ?>">
    <?php endif; ?>
  </div>
  <?php // TODO: プロフ画像挿入<img src="<?php echo showImg(sanitize($viewData['pic3']));・・・ ?>
  <ul>
    <li><a class="item-right" href="mypage.php">マイページ</a></li>
    <li><a class="item-right" href="profile.php">プロフィール編集</a></li>
    <li><a class="item-right" href="passwordChange.php">パスワード変更</a></li>
    <li><a class="item-right" href="contentRegistEdit.php">思い出を共有</a></li>
    <li><a class="item-right" href="contents.php">みんなの思い出を見る</a></li>
    <li><a class="item-right" href="unsubscribe.php">退会</a></li>
  </ul>
</section>
