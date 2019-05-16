$(function(){
  // ========== footerを画面の一番下に固定 ===========
  var $ftr = $('#footer');
  if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
    $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) + 'px; width:100%;'});
  }
  // =========== スライドトグル(一度だけ出てくるスライド) ===========
  var $sucMsg = $('#js-suc-msg');
  var msgText = $sucMsg.text();
  if(msgText.replace(/^\s+|\s+$/g, '').length){ // 空文字("")はfalse判定
    $sucMsg.slideToggle('slow');
    setTimeout(function(){$sucMsg.slideToggle('slow'); }, 3000);
  }

  // =========== 画像のライブプレビュー ===========
  var $pictureFrame = $('.picture-frame');
  // 画像の情報が入ってくる箇所
  var $picture = $('.input-photo');
  // ライブプレビューの処理
  $pictureFrame.on('dragover', function(e){
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', '3px dotted #60770B');
  });
  $pictureFrame.on('dragleave', function(e){
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', 'none');
  });
  $picture.on('change', function(){ //画像の情報が入ってきたら
    $pictureFrame.css('border', 'none');
    var file = this.files[0], //files配列にファイルが入っている($pictureのファイルの情報)
        $img = $(this).siblings('.photo-img'), //兄弟要素を取得
        fileReader = new FileReader(); //ファイルを読み込むためのオブジェクト
  //読み込みが完了した時のイベントハンドラ(imgタグのsrcにデータをセット)
  fileReader.onload = function(event){ //eventに画像の情報が入る
    $img.attr('src', event.target.result).show();
  };
  // 画像ファイルをDataURLに変換(DataURL : 画像を文字列として扱うもの)
  fileReader.readAsDataURL(file);
  });

  // =========== 画像スライダー ===========
  // TODO: 画像が１枚の場合はスライド画像非表示
  var currentImageNum = 1;
  var $sliderFrame = $('.slide_img_frame'); // スライドの括り
  var slideImage = $('.slide_img').length; // スライドの総個数(今回は最大3個)
  var slideImageWidth = $('.slide_img').innerWidth(); // 1つのスライドの横幅
  var slideTotalWidth = slideImageWidth * slideImage; // 全てのスライドの合計した横幅
  // 全ての横幅を横一列に並べる(スライドを括っているものの横幅を調整)
  $sliderFrame.attr('style', 'width:' + slideTotalWidth + 'px');
  // 次へ進むスライダー
  $('.js-slider-next').click(function(){
    if(currentImageNum < slideImage){
      $sliderFrame.animate({'left':'-='+slideImageWidth+'px'}, 'slow');
      currentImageNum++;
    }
  });
  // 前へ戻るスライダー
  $('.js-slider-prev').click(function(){
    if(currentImageNum > 1){
      $sliderFrame.animate({'left':'+='+slideImageWidth+'px'}, 'slow');
      currentImageNum--;
    }
  });

  // =========== お気に入り登録・削除 ===========
  var $favorite = $('.js-favorite-action');
  var favContentId = $favorite.data('contentid');
  $favorite.click(function(){
    var $this = $(this);
    // Ajax通信開始
    $.ajax({
      type: "POST",
      url: "favoriteAjax.php",
      data: {contentId : favContentId} //favContentIdをcontentIdに入れて送る
    // Ajax通信が成功した場合
    }).done(function(){
      $this.toggleClass('active');
    // Ajax通信が失敗した場合
    }).fail(function(){
    });
  });

    // =========== メッセージ機能 ===========
    var $button = $('.entry_button');
    $button.click(function(e){
      e.preventDefault();
      var $this = $(this);
      var message = $('.message_entry').val(); //送信メッセージ内容
      var contentOwnerUser = $('.content_user').val(); //投稿者のID
      var boardId = $('.board_id').val(); //bordのID
      console.log('メッセージ：' + message);
      console.log('投稿者ID：' + contentOwnerUser);
      console.log('boardID：' + boardId);
      $.ajax({
        type: "POST",
        url: "messageAjax.php",
        dataType: "json",
        data: {message: message,
               ownerUser: contentOwnerUser,
               boardId: boardId}
      // Ajax通信が成功した場合・・・
      // 画面にメッセージを一時的に表示＋入力内容をクリア
      }).done(function(data){
        var messageDataArray = data.messageData;
        $('.js-ajax-message').prepend("<div class='msg-box'>"+"<p class='message'>"+messageDataArray.message+"</p>"
                                     + "<p class='msg_date'>"+messageDataArray.send_date+"</p>"+"</div>")
                             .siblings('.message_entry').val('');
        console.log(sample);
        // Ajax通信が失敗した場合
      }).fail(function(){

      });
    });

    // =========== 画面スクロール ===========
    var $message = $('.js-message');
    $('.js-message').click(function(){
      var msgBottom = $message.offset().top + $message.height();
      $('.js-message').scrollTop(500);
      console.log(msgBottom);
    });

});
