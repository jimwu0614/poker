<?php
require_once 'Backend/Models/database.php';
require_once 'Backend/Models/cardCompare.php';
// 假設你有一個發牌類別 Deck
require_once 'Backend/Models/deck.php'; 

// 1. 初始化
$db = new Database();
$pdo = $db->getConnection();
$judge = new CardCompare();
$deck = new Deck();

// 2. 隨機選 3 位 AI 對手
$opponents = $db->getConnection()->query("SELECT * FROM bot_opponent ORDER BY RAND() LIMIT 3")->fetchAll();

// 3. 發牌給玩家和 AI (7 張牌)
$playerHand = $deck->draw(7);
$ai1Hand = $deck->draw(7);
$ai2Hand = $deck->draw(7);
$ai3Hand = $deck->draw(7);

// 4. 呼叫你的比牌引擎
$results = $judge->getFinalRankings($playerHand, $ai1Hand, $ai2Hand, $ai3Hand);

// 5. 印出結果
echo "<h2>遊戲開牌結果</h2>";
foreach ($results as $index => $res) {
    echo "第 " . ($index + 1) . " 名: " . $res['name'] . " - 牌型: " . $res['label'] . " (高牌: " . $res['high'] . ")<br>";
}