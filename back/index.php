<?php

require('./vendor/autoload.php');
require_once('gpt_response.php');
/* 環境変数の読み込み */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
/* ***** */

function sendMessage($post_data)
{
  // LINEBOT用の関数．いじらなくてOK
  $accessToken = getenv('LINE_ACCESS_TOKEN');
  // error_log(print_r($post_data, true), 3, dirname(__FILE__).'/debug.log');
  $ch = curl_init("https://api.line.me/v2/bot/message/reply");
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charset=UTF-8',
    'Authorization: Bearer ' . $accessToken
  ));

  $result = curl_exec($ch);
  if ($result === false) {
    error_log('Error in sending message: ' . curl_error($ch));
  }
  curl_close($ch);
}

function callApi($url)
{
  // 外部APIを呼び出すときに使える関数
  $ch = curl_init(); //開始

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // 証明書の検証を行わない
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // curl_execの結果を文字列で返す

  $response =  curl_exec($ch);
  $result = json_decode($response, true);

  curl_close($ch); //終了

  return $result;
}

function main()
{
  /* イベント（ユーザからの何らかのアクション）を取得．特にいじらなくてOK． */
  $json_string = file_get_contents('php://input');
  $jsonObj = json_decode($json_string);
  $events = $jsonObj->{"events"};
  /* ***** */

  // ユーザから来たメッセージを1件ずつ処理
  foreach ($events as $event) {
    $replyToken = $event->{"replyToken"}; // メッセージを返すのに必要
    $type = $event->{"message"}->{"type"}; // メッセージタイプ
    $messages = [];

    if ($type == "text") { // メッセージがテキストのとき

      // GPTにユーザーのメッセージを送信し、その応答を受け取る
      $gptResponse = getGptResponse($event);  // この関数はgenerate_gpt_response.phpで定義

      // GPTからの応答をLINEに送信
      $messages[] = ["type" => "text", "text" => $gptResponse];

    } else if ($type == "sticker") { // メッセージがスタンプのとき
      // メッセージを配列に追加（直接配列に追加）
      $messages[] = ["type" => "sticker", "packageId" => "446", "stickerId" => "1988"]; // 適当なステッカーを返す

    } else { // その他は無視．必要に応じて追加．
      return;
    }

    // ここで sendMessage を呼び出して、LINE API へ返信
    sendMessage([
      "replyToken" => $replyToken,
      "messages" => $messages
    ]);

  }
}

main();
