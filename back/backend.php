<?php
ini_set('display_errors', 1); // PHPがエラーを吐いたら表示する

require './vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->load(); // .envを使用する

// データベースに接続するための情報を用意する関数
function connectMysql() {
  $dbname = getenv("DB_DSN");
  $userName = getenv("DB_USER");
  $pass = getenv("DB_PASSWORD");

  $pdo = new PDO(
      $dbname,
      $userName,
      $pass,
      [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]
  );
  return $pdo;
}


// DBの中身を取得する関数
function getMnsn($pass_encrypt){
    $pdo = connectMysql(); // DBとの接続開始
    $stmt = $pdo->prepare("SELECT * FROM mnsn_sheet where :pass_hash = pass_hash ORDER BY id DESC");
    $stmt->bindValue(':pass_hash', $pass_encrypt, PDO::PARAM_STR); //bindValueメソッドでパラメータをセット
    $stmt->execute();
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC); //全件取得
    // ============= ここまでDBからの取得 =============

    // $job_sleepQuality = $all[0]["job_sleepQuality"]; //睡眠の質_仕事の日

    $mnsn = $all[0]; // 問診票の内容をすべて入れる

    return $mnsn;
}

?>
