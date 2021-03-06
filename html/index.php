<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=640">
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- ogp -->
<meta name="description" content="AliExpressのシンプルな商品検索サービスです。">
<meta property="og:description" content="AliExpressのシンプルな商品検索サービスです。">
<meta property="og:url"         content="http://web.inajob.tk/ali-search/">
<?php
include '../ali.php';
# <meta property="og:image"       content="http://web.inajob.tk/ali-search/imgs/cover.png">

$img = "";
$enableOGP = false;
$title = "";

if(strpos($_SERVER['HTTP_USER_AGENT'], "Twitterbot") === 0){
  $enableOGP = true;
}
if(!isset($_GET['q'])){
  $enableOGP = false;
}

$img = "http://web.inajob.tk/ali-search/imgs/cover.png";
if($enableOGP){
  if(!empty(getenv("DEBUG"))){
    $img = "OGP";
  }else{
      $keywords = $_GET['q'];
      $title = ' - ' . htmlspecialchars($_GET['q']) . "の検索結果";
      $page = 1;
      $sort = 0;
      if(isset($_GET['s'])){
        $sort = $_GET['s'];
      }


    $items = cachedGet($keywords, $page, $sort);
    if($items !== NULL){
      $img = $items[0]['product_main_image_url'];
    }
  }
}

echo '<title>アリサーチ'.$title.'</title>';
echo '<meta property="og:title"       content="アリサーチ'. $title .'">';
echo '<meta property="og:image" content="' . $img . '">';

?>
<meta name="twitter:card"       content="summary_large_image">
<meta name="twitter:site:id"    content="@ina_ani">
<meta name="twitter:creator"    content="@ina_ani">
<!-- /ogp -->

<!--[if IE]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<style>
  article, aside, dialog, figure, footer, header,
  hgroup, menu, nav, section { display: block; }
  body,html{
    padding:0px;
    margin:0px;
  }
</style>
<link rel="stylesheet" type="text/css" href="css/style.css"></link>
<script src="js/vue.js"></script>
<script src="js/script.js"></script>
</head>
<body>
  <script src="../mh/mh.js"></script>
  <h1><a href=""><img src="imgs/cover.png"></a></h1>
  <div>aliexpressのシンプルな商品検索サービスです</div>
  <div id="container">
    <div class="control">
      <form v-on:submit.prevent="search">
        <input class="query" v-model="keyword" type="text" />
        <select v-model="sortMode">
          <option value="0">安い順</option>
          <option value="1">高い順</option>
          <option value="2">おススメ順</option>
        </select>
        <input v-on:click="search" v-bind:disabled="disableSearch" type="button" value="検索" />
      </form>

      関連ワード
      <div class="clearfix">
        <div v-for="recommend in recommends" class="recommend">
          <a v-on:click="searchBy(recommend)">{{recommend}}</a>
        </div>
      </div>
    </div>
    <div class="clearfix">
      <div v-for="item in items" class="piece">
        <div class="price">\{{item["target_original_price"]}}</div>
        <a target="_blank" :href=item["promotion_link"]>
          <div class="img" :title=item["product_title"] :style=item.thumbStyle /></div>
        </a>
      </div>
    </div>
    <div class="bottom-control">
      <div :style=nomoreStyle>該当なし</div>
      <div :style=errorStyle>エラー</div>
      <div class="spinner" :style=loadingStyle>読み込み中...</div>
      <button v-on:click="more" v-bind:disabled="disableSearch" :style=moreStyle>もっと見る</button>
    </div>
  </div>
  <hr />
  <div class="footer">つくったひと：<a href="http://twitter.com/ina_ani" target="_blank">@ina_ani</a></div>


</body>
</html>
