<?php
// ====== ログイン認証 ======
//セッションがある(ログインをしている)場合
if(!empty($_SESSION['login_date'])){

  // ログイン有効期限内の場合
  if( ($_SESSION['login_date'] + $_SESSION['login_limit']) > time() ){
    debug('ログイン有効期限内です。');
    $_SESSION['login_date'] = time(); //ログイン日時を現在の時刻に更新
    if(basename($_SERVER['PHP_SELF']) === 'login.php'){
      debug('マイページへ遷移します。');
      header("Location:mypage.php");
    }

  // ログイン有効期限が切れていた場合
  }else{
    debug('ログイン有効期限オーバーです。');
    //セッションを削除する(ログアウト)
    session_destroy();
    debug('ログインページへ遷移します。');
    header("Location:login.php");
  }

// セッションがない(そもそも未ログイン)の場合
}else{
  debug('未ログインのユーザーです。');
  if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
    debug('ログインページへ遷移します。');
    header("Location:login.php");
  }
}
