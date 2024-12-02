<?php
use GuzzleHttp\Client;

// OpenAI APIにリクエストを送信してGPTから応答を取得する関数
function createCompletion($request)
{
  // urlを指定
  $apiUrl = 'https://api.openai.com/v1/chat/completions';
  // リクエストヘッダー
  $headers = array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . getenv("OPENAI_API_KEY")
  );
  
  // cURLセッションを初期化
  $ch = curl_init($apiUrl);
  
  // cURLオプションを設定
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  
  // cURLリクエストを実行
  $gptresponse = curl_exec($ch);

  // エラーチェック
  if ($gptresponse === false) {
    error_log(print_r(curl_error($ch), true) . "\n", 3, dirname(__FILE__).'/debugA.log');
    throw new ErrorException("curlによる失敗");
  }

  // APIからのレスポンスを取得する
  $result = json_decode($gptresponse, true);

  // 生成されたテキストを取得する
  $generatedText = $result['choices'][0]['message']['content'];

  return $generatedText;
}

function getGptResponse($event)
{
  // 初期メッセージを格納
  $generatedText = "すいません，よくわかりませんでした🤔";
  // 自動回答判定フラグ
  $autoreply_flag = false;

  // GPTによる質問のジャンル分け
  $data = [
    'model' => 'gpt-4',
    'messages' => [
      ['role' => 'system', 'content' =>
      "あなたは教師です\
      小学生の質問に答える気持ちで回答してください\
      ",
      ],
      ['role' => 'assistant', 'content' => $event->{"message"}->{"text"}],
    ],
    'max_tokens' => 500,
  ];

  try {
    $generatedText = createCompletion($data);
  }catch (Exception $e){
    error_log(print_r($e, true) . "\n", 3, dirname(__FILE__).'/debug.log');
  }

  return $generatedText;
}

?>
