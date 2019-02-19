<?php

$accessToken = '8v7Q2Wosv/C0jdZmzylOycv4TIKF6LBFSzfHloY+MDBw+guQzbyfvJEYXSU8oaVnYNvNC9absNvD+Zi7dfmIuvzdu7wDkKLnRCtxT/3XU3Yme50fFVaFndkDng6b6/PDPUGvHysgDyZhnLQJpW0TGwdB04t89/1O/w1cDnyilFU=';

// 受信したメッセージ情報
$raw = file_get_contents('php://input');
$receive = json_decode($raw, true);

$event = $receive['events'][0];
$replyToken  = $event['replyToken'];
$messageType = $event['message']['type'];

// 送られてきたのが位置情報以外だったら応答しない
if($messageType != "location") exit;

$lat = $event['message']['latitude'];
$lon = $event['message']['longitude'];

// 送られてきた位置情報を元にぐるなびのAPIにアクセスしてケンタッキーの店舗情報を取得する
$uri    = 'https://api.gnavi.co.jp/RestSearchAPI/20150630/';
$accKey = '180222565e51c56e7198ca6d82202b6b';

$url  = $uri . '?format=json&name=ケンタッキー&range=5&keyid=' . $accKey . '&latitude=' . $lat . '&longitude=' . $lon;

$json = file_get_contents($url);
$obj  = json_decode($json);

// 店舗情報を取得
$count = 0;
$columns = array();
foreach ($obj->rest as $restaurant) {
  $columns[] = array(
    'thumbnailImageUrl' => $restaurant->image_url->shop_image1,
    'text'    => $restaurant->name,
    'actions' => array(array(
                  'type'  => 'uri',
                  'label' => '詳細を見る',
                  'uri'   => $restaurant->url
                ))
  );
  if (++$count > 5) { // 最大５店舗の情報を返す
    break;
  }
}

// LINEで返信する
$headers = array('Content-Type: application/json',
                 'Authorization: Bearer ' . $accessToken);

if ($columns) {
  $template = array('type'    => 'carousel',
                    'columns' => $columns);

  $message = array('type'     => 'template',
                   'altText'  => 'ケンタッキーの情報',
                   'template' => $template);
} else {
  $message = array('type' => 'text',
                   'text' => 'メリークリスマス。残念ですが近くにケンタッキーはありません。');
}

$body = json_encode(array('replyToken' => $replyToken,
                          'messages'   => array($message)));
$options = array(CURLOPT_URL            => 'https://api.line.me/v2/bot/message/reply',
                 CURLOPT_CUSTOMREQUEST  => 'POST',
                 CURLOPT_RETURNTRANSFER => true,
                 CURLOPT_HTTPHEADER     => $headers,
                 CURLOPT_POSTFIELDS     => $body);

$curl = curl_init();
curl_setopt_array($curl, $options);
curl_exec($curl);
curl_close($curl);
