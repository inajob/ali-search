
window.addEventListener('load', function(){

// GET request
function xhr(url, f, ef){
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.open("GET", url);
  xmlhttp.onreadystatechange = function(){
    if(xmlhttp.readyState == 4){
      if(xmlhttp.status == 200){
        try{
          var obj = JSON.parse(xmlhttp.responseText);
          if(f){f(obj)}
        }catch(e){
          if(ef){ef()}
        }
      }
    }
  };
  xmlhttp.send();
}

var keyword = "atmega32u4";

var container = new Vue({
  el: "#container",
  data:{
    items:[
    ],
    keyword: keyword,
    sortMode: "0",
    recommends: [
      "atmega32u4",
      "display spi",
      "oled",
      "tft spi",
      "orange pi board",
      "3d printer reprap auto leveling",
    ],
    page: 1,
    moreStyle:"display:block;",
    errorStyle:"display:none;",
    nomoreStyle:"display:none;",
    disableSearch: false,
    loadingStyle:"display:none;",
  },
  methods: {
    searchBy: function(key){
      this.keyword = key;
      this.page = 1;
      this.items = [];
      this.moreStyle = "display:block;";
      this.nomoreStyle = "display:none;";
      this.disableSearch = true;
      this.loadingStyle = "display:block;";
      history.pushState(null, null, "?q=" + encodeURIComponent(this.keyword) + "&s=" + this.sortMode);
      this.load(this.keyword, this.page, this.sortMode);
    },
    search: function(){
      this.page = 1;
      this.items = [];
      this.moreStyle = "display:block;";
      this.nomoreStyle = "display:none;";
      this.disableSearch = true;
      this.loadingStyle = "display:block;";
      history.pushState(null, null, "?q=" + encodeURIComponent(this.keyword) + "&s=" + this.sortMode);
      this.load(this.keyword, this.page, this.sortMode);
    },
    more: function(){
      this.disableSearch = true;
      this.loadingStyle = "display:block;";
      this.page += 1;
      this.load(this.keyword, this.page, this.sortMode);
    },
    load: function(q, page, sortMode){
      var that = this;
      that.errorStyle = "display:none;";
      console.log("load");
      xhr('./api.php?p='+page+'&q=' + encodeURIComponent(q) + '&s=' + sortMode, function(obj){
        that.disableSearch = false;
        that.loadingStyle = "display:none;";
        console.log(obj);
        if(obj.items.length == 0){
          that.nomoreStyle = "display:block;";
          that.moreStyle = "display:none;";
        }
        if(obj.items.length < 40){ // 40 is max
          that.moreStyle = "display:none;";
        }
        obj.items.forEach(function(v){
          v.thumbUrl = v["product_main_image_url"] + "";
          v.blog = vanishTags(v["product_title"]) + "[![image](" +v["product_main_image_url"] + ")](" + v["promotion_link"] + ")";
          v.thumbStyle = "background-image: url('" + v["product_main_image_url"] + "');";
          that.items.push(v);
        });
        let count = 0;
        that.recommends = [];
        obj.words.forEach(function(v,i){
          if(count > 10)return;
          that.recommends.push(v.key);
          count ++;
        });
      }, function(){
        that.errorStyle = "display:block;";
        that.loadingStyle = "display:none;";
        that.moreStyle = "display:none;";
        that.disableSearch = false;
      })
    },
  }
});

function vanishTags(s){
  return s.replace(/<[^>]*>/g, "");
}

function init(){
  var search = document.location.search;
  if(search.length > 0){
    search = search.substring(1);
    var list = search.split('&');
    var options = {};
    list.forEach(function(v){
      var kv = v.split("=")
      options[kv[0]] = decodeURIComponent(kv[1]);
    });
    container.keyword = options['q'];
    if(options['s']){
      container.sortMode = options['s'];
    }
  }
  
  container.disableSearch = true;
  container.loadingStyle = "display:block;";
  container.load(container.keyword, 1, container.sortMode);
}
init();

window.addEventListener('popstate', function(e){
  init();
});

});
