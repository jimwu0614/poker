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
    public function startNewGame() {
        $this->deck->shuffle();

        // 1. 挑選對手
        $pdo = $this->db->getConnection();
        $stmt = $pdo->query("SELECT * FROM bot_opponent ORDER BY RAND() LIMIT 3");
        $opponents = $stmt->fetchAll();

        // 2. 發私有牌 (每人 2 張)
        $playerPrivate = $this->drawCards(2);
        $ai1Private = $this->drawCards(2);
        $ai2Private = $this->drawCards(2);
        $ai3Private = $this->drawCards(2);

        // 3. 發公牌 (5 張)
        $communityCards = $this->drawCards(5);

        // 4. 合併 7 張牌進行運算 (私有牌 + 公牌)
        $results = $this->judge->getFinalRankings(
            array_merge($playerPrivate, $communityCards),
            array_merge($ai1Private, $communityCards),
            array_merge($ai2Private, $communityCards),
            array_merge($ai3Private, $communityCards)
        );

        // 5. 整理回傳給前端的資料格式
        return [
            'status' => 'success',
            'community_cards' => $communityCards,
            'player' => [
                'name' => 'You',
                'private_cards' => $playerPrivate
            ],
            'opponents' => [
                ['name' => $opponents[0]['name_'], 'private_cards' => $ai1Private, 'strategy' => $opponents[0]['strategy_']],
                ['name' => $opponents[1]['name_'], 'private_cards' => $ai2Private, 'strategy' => $opponents[1]['strategy_']],
                ['name' => $opponents[2]['name_'], 'private_cards' => $ai3Private, 'strategy' => $opponents[2]['strategy_']]
            ],
            'rankings' => $results // 比牌結果
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