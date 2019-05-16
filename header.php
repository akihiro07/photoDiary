<body>

  <!-- ①header -->
  <header>
    <!-- 横幅を調整 -->
    <div class="site-width">
      <h1 class="mainTitle"><a href="contents.php">PhotoDiary</a></h1>
      <nav class="nav-frame">
        <ul>
          <!-- ==========「ユーザーID」の有無をチェック ========== -->
          <!-- ユーザーIDがある場合 -->
          <?php if(!empty($_SESSION['user_id'])){ ?>
          <li><a href="mypage.php" class="nav nav1">マイページ</a></li>
          <li><a href="logout.php" class="nav nav2">ログアウト</a></li>
          <!-- ユーザーIDがない場合 -->
        <?php }else{ ?>
          <li><a href="registration.php" class="nav nav1">ユーザー登録</a></li>
          <li><a href="login.php" class="nav nav2">ログイン</a></li>
        <?php } ?>
        </ul>
      </nav>
    </div>
  </header>
