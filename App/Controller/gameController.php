<?php
require_once __DIR__ . '/../Model/db.php';
require_once __DIR__ . '/../Model/cardCompare.php';
require_once __DIR__ . '/../Model/deck.php';

class GameController {
    private $db;
    private $judge;
    private $deck;

    public function __construct() {
        $this->db = new Database();
        $this->judge = new CardCompare();
        $this->deck = new Deck();
    }

    /**
     * 開始一場新對局
     */
// ... 前面引用保持不變 ...

    public function startNewGame() {
        $this->deck->shuffle();

        $pdo = $this->db->getConnection();
        $stmt = $pdo->query("SELECT * FROM bot_opponent ORDER BY RAND() LIMIT 3");
        $opponents = $stmt->fetchAll();

        $pName = "玩家";
        $aiNames = [$opponents[0]['name_'], $opponents[1]['name_'], $opponents[2]['name_']];

        // 發牌
        $playerPrivate = $this->drawCards(2);
        $ai1Private = $this->drawCards(2);
        $ai2Private = $this->drawCards(2);
        $ai3Private = $this->drawCards(2);
        $communityCards = $this->drawCards(5);

        // 比牌 (這裡沿用你修正後的 CardCompare)
        $results = $this->judge->getFinalRankings(
            array_merge($playerPrivate, $communityCards),
            array_merge($ai1Private, $communityCards),
            array_merge($ai2Private, $communityCards),
            array_merge($ai3Private, $communityCards)
        );

        // 重新命名結果 (匹配資料庫名字)
        foreach ($results as &$res) {
            if ($res['name'] === 'AI_1') $res['name'] = $aiNames[0];
            if ($res['name'] === 'AI_2') $res['name'] = $aiNames[1];
            if ($res['name'] === 'AI_3') $res['name'] = $aiNames[2];
        }

        // --- 關鍵修改：分段打包資料 ---
        return [
            'status' => 'success',
            'stages' => [
                'pre_flop' => [
                    'player' => $playerPrivate,
                    'opponents' => [
                        ['name' => $aiNames[0], 'strategy' => $opponents[0]['strategy_']],
                        ['name' => $aiNames[1], 'strategy' => $opponents[1]['strategy_']],
                        ['name' => $aiNames[2], 'strategy' => $opponents[2]['strategy_']]
                    ]
                ],
                'flop'  => array_slice($communityCards, 0, 3), // 前 3 張
                'turn'  => [$communityCards[3]],               // 第 4 張
                'river' => [$communityCards[4]],               // 第 5 張
                'showdown' => [
                    'ai_hands' => [
                        $ai1Private, $ai2Private, $ai3Private
                    ],
                    'rankings' => $results
                ]
            ]
        ];
    }

    private function drawCards($count) {
        $cards = [];
        for ($i = 0; $i < $count; $i++) {
            $cards[] = $this->deck->deal();
        }
        return $cards;
    }
}


