<?php

include "TopSdk.php";

$appkey = getenv("ALI_API_KEY");
$secret = getenv("ALI_API_SECRET");
$trackingId = getenv("ALI_API_TRACKING_KEY");


function simpleXmlToArray($xmlObject)
{
  $array = [];
  $c=0;
  foreach ($xmlObject->children() as $node) {
       // Here the new if: check if children don't have a node name keeping them in the new structure as not-associative nodes
      if($node->children()->count() > 0)
        {
          $array[$node->getName()][] = simpleXmlToArray($node);
        }
      else {
          $array[$node->getName()] = (string) $node;
      }
  }
  return $array;
}

function get($keywords, $page, $sort){
  global $appkey;
  global $secret;
  global $trackingId;
  $c = new TopClient;
  $c->appkey = $appkey;
  $c->secretKey = $secret;
  $req = new AliexpressAffiliateProductQueryRequest;
  $req->setAppSignature("alisearch");
  # $req->setCategoryIds("111,222,333");
  $req->setFields("commission_rate,sale_price");
  $req->setKeywords($keywords);
  # $req->setMaxSalePrice("100");
  # $req->setMinSalePrice("15");
  $req->setPageNo($page);
  $req->setPageSize("50");
  $req->setPlatformProductType("ALL");
  error_log("sort $sort");
  switch($sort){
  case 0:
    $req->setSort("SALE_PRICE_ASC");
    break;
  case 1:
    $req->setSort("SALE_PRICE_DESC");
    break;
  case 2:
    break;
  }
  $req->setTargetCurrency("JPY");
  $req->setTargetLanguage("EN");
  $req->setTrackingId($trackingId);
  # $req->setShipToCountry("ES");
  # $req->setDeliveryDays("3");
  $resp = $c->execute($req);
  
  #var_dump($resp);
  $ret = simpleXmlToArray($resp);
  #var_dump($ret);
  $ret = $ret["resp_result"][0]["result"][0]["products"][0]["product"];
  #var_dump($ret);
  return $ret;
}

function cachedGet($keywords, $page, $sort){
  $key = urlencode($keywords) .':'. $page . ':' . $sort;

  $memcache = new Memcached();
  $memcache->addServer('memcached', 11211);
  $obj = $memcache->get($key);

  if($obj === FALSE){
    error_log("cache false " . $key);
    $obj = get($keywords, $page, $sort);
    $memcache->set($key, $obj, 60 * 60); // 60 min cache
  }else{
    error_log("use cache". $key);
  }
  return $obj;
}


#get("m5stack", 1);
