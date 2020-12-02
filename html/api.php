<?php
include '../ali.php';

$keywords = $_GET['q'];
$page = $_GET['p'];
$sort = 0;

if(isset($_GET['s'])){
  $sort = $_GET['s'];
}

$titles = array();

$ret = array();
$retItems = array();

for($count = $page; $count <= $page; $count ++){
  if(!empty(getenv("DEBUG"))){
    $items = get($keywords, $count, $sort);
  }else{
    $items = cachedGet($keywords, $count, $sort);
  }

  if($items !== NULL){
    foreach($items as $value){
      $titles[] = strip_tags($value['product_title']);
      $retItems[] = $value;
    }
  }
}

$words = array();
foreach($titles as $title){
  foreach(explode(' ', $title) as $word){
    if(isset($words[$word])){
      $words[$word] ++;
    }else{
      $words[$word] = 1;
    }
  }
}
uasort($words, function($a, $b){return ($a > $b ? -1 : 1);});

$keys = array();
foreach($words as $word => $weight){
  $keys[] = array('weight' => $weight, 'key' => $word);
}

$ret = array(
  'items' => $retItems,
  'words' => $keys
);

$cv = '';
if(isset($_GET['callback'])){
  $cv = $_GET['callback'];
}

header("Content-Type: application/json; charset=utf-8");
if(empty($cv)){
  echo json_encode($ret);
}else{
  echo $cv . '(' . json_encode($ret) . ')';
}

