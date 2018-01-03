<?php
include '../aliwrap.php';

$keywords = $_GET['q'];
$page = $_GET['p'];

$fields = [
  'keywords' => $keywords
  ];
$titles = array();

$ret = array();
$retItems = array();

for($count = $page; $count <= $page; $count ++){
  $items = get($fields, $count);
  $ret = array_merge($ret, $items);
  foreach($items['result']['products'] as $value){
    $titles[] = strip_tags($value['productTitle']);
    $retItems[] = $value;
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

header("Content-Type: application/json; charset=utf-8");
echo json_encode($ret);
