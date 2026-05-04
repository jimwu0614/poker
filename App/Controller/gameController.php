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

        // 2. 分配名字（確保跟前端顯示一致）
        $pName = "玩家";
        $ai1Name = $opponents[0]['name_'];
        $ai2Name = $opponents[1]['name_'];
        $ai3Name = $opponents[2]['name_'];

        // 3. 發牌
        $playerPrivate = $this->drawCards(2);
        $ai1Private = $this->drawCards(2);
        $ai2Private = $this->drawCards(2);
        $ai3Private = $this->drawCards(2);
        $communityCards = $this->drawCards(5);

        // 4. 合併 7 張牌並「帶上名字」送去比牌
        // 修正：假設你的 CardCompare 支援傳入自定義名稱，或是我們手動對應
        $results = $this->judge->getFinalRankings(
            ['name' => $pName, 'cards' => array_merge($playerPrivate, $communityCards)],
            ['name' => $ai1Name, 'cards' => array_merge($ai1Private, $communityCards)],
            ['name' => $ai2Name, 'cards' => array_merge($ai2Private, $communityCards)],
            ['name' => $ai3Name, 'cards' => array_merge($ai3Private, $communityCards)]
        );

        // 5. 整理回傳
        return [
            'status' => 'success',
            'community_cards' => $communityCards,
            'player' => [
                'name' => $pName,
                'private_cards' => $playerPrivate
            ],
            'opponents' => [
                ['name' => $ai1Name, 'private_cards' => $ai1Private, 'strategy' => $opponents[0]['strategy_']],
                ['name' => $ai2Name, 'private_cards' => $ai2Private, 'strategy' => $opponents[1]['strategy_']],
                ['name' => $ai3Name, 'private_cards' => $ai3Private, 'strategy' => $opponents[2]['strategy_']]
            ],
            'rankings' => $results 
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