<?php
// ぐるなびアクセスキー
const GNAVI_ACCESS_KEY = "180222565e51c56e7198ca6d82202b6b";
const LINE_CHANNEL_ID = "1640679778";
const LINE_CHANNEL_SECRET = "16da3b99d9594f2cb12abd3c9693c36d";
const LINE_MID = "Ua725114b1412232ec50cf52093d7d394";

$request = file_get_contents('php://input');
$jsonObj = json_decode($request);
$to = $jsonObj->{"result"}[0]->{"content"}->{"from"};
$contentType = $jsonObj->{"result"}[0]->{"content"}->{"contentType"};
$opType = $jsonObj->{"result"}[0]->{"content"}->{"opType"};

// 友達追加時に送信するメッセージ
if ($opType !== null && $opType === 4) {
    $response_format_text = ['contentType'=>1,"toType"=>1,"text"=>"ご登録ありがとうございます！"];
    send_message_to_user($to,$response_format_text);
    return;
}

if ($contentType !== 7) {
    $response_format_text = ['contentType'=>1,"toType"=>1,"text"=>"位置情報を送って下さいね〜"];
    send_message_to_user($to,$response_format_text);
} else {
    $ramen_info = get_ramen_info($jsonObj);
    $response_format_text = ['contentType'=>1,"toType"=>1,"text"=>$ramen_info];
    send_message_to_user($to,$response_format_text);    
}

function send_message_to_user($to,$response_format_text){
    $post_data = ["to"=>[$to],"toChannel"=>"1383378250","eventType"=>"138311608800106203","content"=>$response_format_text];
    $ch = curl_init("https://trialbot-api.line.me/v1/events");
    curl_setopt($ch, CURLOPT_POST,true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, create_http_header());
    $result = curl_exec($ch);
    curl_close($ch);
}

function create_http_header(){
    $content_type = 'Content-Type: application/json; charser=UTF-8';
    $channel_id = 'X-Line-ChannelID: '.LINE_CHANNEL_ID;
    $channel_secret = 'X-Line-ChannelSecret: '.LINE_CHANNEL_SECRET;
    $mid = 'X-Line-Trusted-User-With-ACL: '.LINE_MID;
    $channelAccessToken = '<8v7Q2Wosv/C0jdZmzylOycv4TIKF6LBFSzfHloY+MDBw+guQzbyfvJEYXSU8oaVnYNvNC9absNvD+Zi7dfmIuvzdu7wDkKLnRCtxT/3XU3Yme50fFVaFndkDng6b6/PDPUGvHysgDyZhnLQJpW0TGwdB04t89/1O/w1cDnyilFU=>';
    return array($content_type,$channel_id,$channel_secret,$mid);
}

// ぐるなびWebサービスを利用した検索
function get_ramen_info($jsonObj){
    // ぐるなびWebサービス利用するためのURLの組み立て
    $url = build_url($jsonObj);
    // API実行
    $json = file_get_contents($url);
    return parse($json);
}


function build_url($jsonObj){

    //エンドポイント
    $uri = "http://api.gnavi.co.jp/RestSearchAPI/20150630/";

    //APIアクセスキーは、ぐるなびで取得して設定します。
    $acckey = GNAVI_ACCESS_KEY;

    //返却値のフォーマットを変数に入れる
    $format= "json";

    //緯度・経度、範囲、及びカテゴリーにラーメンを設定
    $location = $jsonObj->{"result"}[0]->{"content"}->{"location"};
    $lat   = $location->latitude;
    $lon   = $location->longitude;
    $range = 3;
    // 業態がラーメン屋さんを意味するぐるなびのコード(大業態マスタ取得APIをコールして調査)
    $category_s = "RSFST08000";

    //URL組み立て
    $url  = sprintf("%s%s%s%s%s%s%s%s%s%s%s%s%s", $uri, "?format=", $format, "&keyid=", $acckey, "&latitude=", $lat,"&longitude=",$lon,"&range=",$range,"&category_s=",$category_s);

    return $url;
}

function parse($json){

    $obj  = json_decode($json);

    $result = "";

    $total_hit_count = $obj->{'total_hit_count'};

    if ($total_hit_count === null) {
        $result .= "近くにラーメン屋さんはありません。";
    }else{
        $result .= "近くにあるラーメン屋さんです。\n\n";
        foreach($obj->{'rest'} as $val){

            if (checkString($val->{'name'})) {
                $result .= $val->{'name'}."\n";
            }

            if (checkString($val->{'address'})) {
                $address = get_address_without_postal_code($val->{'address'});
                $result .= $address."\n";
            }

            if (checkString($val->{'url'})) {
                $result .= $val->{'url'}."\n";
            }

            $result.="\n"."\n";
        }
        $result.="Powered by ぐるなび";
    }
    return $result;
}

function get_address_without_postal_code($address){
    return mb_substr($address,11);
}

//文字列であるかをチェック
function checkString($input)
{
    if(isset($input) && is_string($input)) {
        return true;
    }else{
        return false;
    }
}