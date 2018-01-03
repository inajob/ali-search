<?php
require 'vendor/autoload.php';

use AliexApi\Configuration\GenericConfiguration;
use AliexApi\AliexIO;
use AliexApi\Operations\ListProducts;
use AliexApi\Operations\GetLinks;

$secretRaw = file_get_contents("/var/www/app-secret/static.json");
$secret = json_decode($secretRaw, true);
$ALI_API_KEY = $secret['ali-api-key'];
$ALI_API_TRACKING_KEY = $secret['ali-api-tracking-key'];
$ALI_API_SIGN = $secret['ali-api-sign'];


function getLinks($out){
  global $ALI_API_KEY;
  global $ALI_API_TRACKING_KEY
  global $ALI_API_SIGN

  $urls = array();
  foreach($out['result']['products'] as $value){
    $url = $value['productUrl'];
    $urls[] = $url;
  }
  $conf = new GenericConfiguration();
  $conf
    ->setApiKey($ALI_API_KEY)
    ->setTrackingKey($ALI_API_TRACKING_KEY)
    ->setDigitalSign($ALI_API_SIGN);
  $aliexIO = new AliexIO($conf);
  $getLinks = new GetLinks();
  $getLinks->setFields('url,promotionUrl');
  $getLinks->setTrackingId($ALI_API_TRACKING_KEY);
  $getLinks->setUrls(implode(',', $urls));

  $formattedResponse = $aliexIO->runOperation($getLinks);
  $out = json_decode($formattedResponse,true);
  $ret = array();
  foreach($out['result']['promotionUrls'] as $value){
    $ret[$value['url']] = $value['promotionUrl'];
  }
  return $ret;
}

function get($fields, $page) {
  global $ALI_API_KEY;
  global $ALI_API_TRACKING_KEY
  global $ALI_API_SIGN

  $conf = new GenericConfiguration();
  $conf
    ->setApiKey($ALI_API_KEY)
    ->setTrackingKey($ALI_API_TRACKING_KEY)
    ->setDigitalSign($ALI_API_SIGN);
  $aliexIO = new AliexIO($conf);
  $listproducts = new ListProducts();
  $listproducts->setFields('productId,productTitle,productUrl,imageUrl,originalPrice,salePrice,localPrice,allImageUrls');
  $listproducts->setKeywords($fields);
  $listproducts->setLocalCurrency('JPY');
  $listproducts->setPageSize(40);
  $listproducts->setPageNo($page);
  
  #$listproducts->setSort('orignalPriceUp');
  #$listproducts->setSort('validTimeUp');
  #$listproducts->setSort('validTimeDown');
  
  #$listproducts->setOriginalPriceFrom('10'); // dollor
  
  $formattedResponse = $aliexIO->runOperation($listproducts);
  $out = json_decode($formattedResponse, true);
  #error_log("===start==");
  #error_log(var_export($out, true));
  #error_log("===end==");
  error_log($ALI_API_KEY);
  error_log($ALI_API_TRACKING_KEY);
  error_log($ALI_API_SIGN);
  error_log(var_export($secretRaw, true));
  error_log(var_export($secret, true));
  
  $urlAssoc = getLinks($out);

  foreach($out['result']['products'] as &$value){
    $value['promotionUrl'] = $urlAssoc[$value['productUrl']];
    $value['allImageUrls'] = explode(',', $value['allImageUrls']);
  }

  return $out;
}

function cachedGet($fields, $page){
  $key = $fields['keywords'] .':'. $page;
  $obj = apc_fetch($key);

  if($obj === FALSE){
    $obj = get($fields, $page);
    apc_store($key, $obj, 60 * 60); // 60 min cache
  }else{
    error_log("use cache");
  }
  return $obj;
}
?>
