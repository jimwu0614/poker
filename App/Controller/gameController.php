<?php
require_once __DIR__ . '/../Model/db.php';
require_once __DIR__ . '/../Model/cardCompare.php';
require_once __DIR__ . '/../Model/deck.php';
require_once __DIR__ . '/../Model/user.php';

class GameController {
    private $db;
    private $judge;
    private $deck;
    private $user;

    public function __construct() {
        $this->db = new Database();
        $this->judge = new CardCompare();
        $this->deck = new Deck();
        $this->user = new User($this->db->getConnection());
    }

    /**
     * 開始一場新對局
     */
// ... 前面引用保持不變 ...

/**
     * 開始一場新對局 (含使用者資訊與分段劇本)
     */
    public function startNewGame() {
        try {
            $this->deck->shuffle();

            // 1. 取得使用者資料 (面試點：實際應用中這裡應從 $_SESSION['user_id'] 取得)

            $testUser = $this -> user -> getUserInfo('admin');
            
            if (!$testUser) {
                return ['status' => 'error', 'message' => 'User not found'];
            }

            // 2. 從資料庫挑選 3 位隨機對手
            $pdo = $this->db->getConnection();
            $stmt = $pdo->query("SELECT * FROM bot_opponent ORDER BY RAND() LIMIT 3");
            $opponents = $stmt->fetchAll();

            $aiNames = [
                $opponents[0]['name_'] ?? 'AI_1', 
                $opponents[1]['name_'] ?? 'AI_2', 
                $opponents[2]['name_'] ?? 'AI_3'
            ];

            // 3. 執行發牌邏輯
            $playerPrivate   = $this->drawCards(2);
            $ai1Private      = $this->drawCards(2);
            $ai2Private      = $this->drawCards(2);
            $ai3Private      = $this->drawCards(2);
            $communityCards  = $this->drawCards(5);

            // 4. 運算最終比牌結果
            $results = $this->judge->getFinalRankings(
                array_merge($playerPrivate, $communityCards),
                array_merge($ai1Private, $communityCards),
                array_merge($ai2Private, $communityCards),
                array_merge($ai3Private, $communityCards)
            );

            // 修正比牌結果中的名稱，對應到資料庫抓出的對手名
            foreach ($results as &$res) {
                if ($res['name'] === 'AI_1') $res['name'] = $aiNames[0];
                if ($res['name'] === 'AI_2') $res['name'] = $aiNames[1];
                if ($res['name'] === 'AI_3') $res['name'] = $aiNames[2];
            }

            // 5. 組合最終 Response
            $response = [
                'status' => 'success',
                'user_info' => [
                    'name'  => $testUser['username'],
                    'chips' => (int)$testUser['chips'] // 確保輸出為整數
                ],
                'stages' => [
                    'pre_flop' => [
                        'player' => $playerPrivate,
                        'opponents' => [
                            ['name' => $aiNames[0], 'strategy' => $opponents[0]['strategy_']],
                            ['name' => $aiNames[1], 'strategy' => $opponents[1]['strategy_']],
                            ['name' => $aiNames[2], 'strategy' => $opponents[2]['strategy_']]
                        ]
                    ],
                    'flop'  => array_slice($communityCards, 0, 3),
                    'turn'  => [$communityCards[3]],
                    'river' => [$communityCards[4]],
                    'showdown' => [
                        'ai_hands' => [
                            $ai1Private, $ai2Private, $ai3Private
                        ],
                        'rankings' => $results
                    ]
                ]
            ];

            return $response;

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => '伺服器錯誤：' . $e->getMessage()
            ];
        }
    }

    private function drawCards($count) {
        $cards = [];
        for ($i = 0; $i < $count; $i++) {
            $cards[] = $this->deck->deal();
        }
        return $cards;
    }
}


