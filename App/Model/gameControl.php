<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/cardCompare.php';
require_once __DIR__ . '/deck.php'; 

// 1. 初始化
$db = new Database();
$pdo = $db->getConnection();
$judge = new CardCompare();
$deck = new Deck();

// 發牌前一定要洗牌
$deck->shuffle();

// 2. 從資料庫隨機選出 3 位 AI 對手
// 這裡直接從你剛剛匯入的 bot_opponent 表抓取
$stmt = $pdo->query("SELECT * FROM bot_opponent ORDER BY RAND() LIMIT 3");
$opponentsData = $stmt->fetchAll();

// 3. 準備發牌
// 定義一個發牌的小工具，避免重複寫迴圈
function getSevenCards($deck) {
    $hand = [];
    for ($i = 0; $i < 7; $i++) {
        $hand[] = $deck->deal();
    }
    return $hand;
}

// 發給玩家
$playerHand = getSevenCards($deck);

// 分別發給三位 AI
$ai1Hand = getSevenCards($deck);
$ai2Hand = getSevenCards($deck);
$ai3Hand = getSevenCards($deck);

// 4. 準備比牌數據
// 我們將四個人的「名字」與「7張牌」打包成陣列丟給比牌引擎
// 這裡我們把資料庫抓到的 AI 名字放進去
$p1Name = "玩家 (你)";
$ai1Name = $opponentsData[0]['name_'] ?? "AI_1";
$ai2Name = $opponentsData[1]['name_'] ?? "AI_2";
$ai3Name = $opponentsData[2]['name_'] ?? "AI_3";

// 呼叫 CardCompare 裡的 getFinalRankings
$results = $judge->getFinalRankings(
    $playerHand, 
    $ai1Hand, 
    $ai2Hand, 
    $ai3Hand
);

// 修正：因為 getFinalRankings 內部預設的名字是 AI_1, AI_2... 
// 如果你想顯示資料庫抓到的真實姓名，我們在這裡稍微對應一下
// (這步驟是為了讓結果跟資料庫的人物對上)
// 定義一個簡單的卡片渲染函式
function renderCard($card) {
    $suitIcons = [
        'Spades'   => '<span style="color:black">♠</span>',
        'Hearts'   => '<span style="color:#ff4d4d">♥</span>',
        'Diamonds' => '<span style="color:#1e90ff">♦</span>',
        'Clubs'    => '<span style="color:#2ecc71">♣</span>'
    ];
    $suit = $card->getSuit();
    $rank = $card->getRank();
    
    return "<span style='background:white; color:black; padding:2px 5px; border-radius:3px; margin-right:3px; font-weight:bold; border:1px solid #ccc;'>" 
           . ($suitIcons[$suit] ?? $suit) . " " . $rank . "</span>";
}
echo "<style>
    body { font-family: sans-serif; line-height: 1.6; background: #2c3e50; color: white; padding: 20px; }
    .card-res { background: #34495e; padding: 10px; margin-bottom: 5px; border-radius: 5px; border-left: 5px solid #27ae60; }
    .winner { border-left-color: #f1c40f; background: #3d566e; }
</style>";
echo "<h1>🏆 德州撲克 - 四人對決開牌測試</h1>";
echo "<p>對陣名單：" . $ai1Name . " vs " . $ai2Name . " vs " . $ai3Name . "</p>";
echo "<hr>";

foreach ($results as $index => $res) {
    $isWinner = ($index === 0) ? "winner" : "";
    echo "<div class='card-res $isWinner' style='margin-bottom: 20px;'>";
    
    // 顯示排名與姓名
    echo "<strong>第 " . ($index + 1) . " 名：</strong> " . $res['name'] . " <br>";
    
    // 顯示這名玩家拿到的 7 張牌
    echo "🃏 牌組：";
    foreach ($res['cards'] as $card) {
        echo renderCard($card);
    }
    
    echo "<br>👉 牌型：<span style='color:#f1c40f'>" . $res['label'] . "</span>";
    echo "</div>";
}

echo "<br><button onclick='location.reload()' style='padding:10px 20px; cursor:pointer;'>重新發牌</button>";