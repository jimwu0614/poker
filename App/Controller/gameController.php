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

            $testUser = $this->user->getUserInfo('admin');
            if (!$testUser) {
                return ['status' => 'error', 'message' => 'User not found'];
            }

            // 1. 玩家扣除底注 $100 (前一步驟你已實作或由前端發動)
            // 這裡我們在後端計算初始底池：1 玩家 + 3 AI = 400 元
            $initialPot = 400; 

            $pdo = $this->db->getConnection();
            $stmt = $pdo->query("SELECT * FROM bot_opponent ORDER BY RAND() LIMIT 3");
            $opponents = $stmt->fetchAll();

            $aiNames = [
                $opponents[0]['name_'] ?? 'AI_1', 
                $opponents[1]['name_'] ?? 'AI_2', 
                $opponents[2]['name_'] ?? 'AI_3'
            ];

            $playerPrivate   = $this->drawCards(2);
            $ai1Private      = $this->drawCards(2);
            $ai2Private      = $this->drawCards(2);
            $ai3Private      = $this->drawCards(2);
            $communityCards  = $this->drawCards(5);

            $results = $this->judge->getFinalRankings(
                array_merge($playerPrivate, $communityCards),
                array_merge($ai1Private, $communityCards),
                array_merge($ai2Private, $communityCards),
                array_merge($ai3Private, $communityCards)
            );

            foreach ($results as &$res) {
                if ($res['name'] === 'AI_1') $res['name'] = $aiNames[0];
                if ($res['name'] === 'AI_2') $res['name'] = $aiNames[1];
                if ($res['name'] === 'AI_3') $res['name'] = $aiNames[2];
                
                // 如果後端比牌判定回傳的名字是 "玩家"，就自動置換成實際的帳號名稱 (如 admin)
                if ($res['name'] === '玩家') {
                    $res['name'] = $testUser['username'];
                }
            }

            // 2. 核心功能：如果贏家是玩家 (admin)，直接在這裡把錢加回去！
            // 總獎金計算公式：初始底池 400 + 之後的三輪下注 (每輪玩家200 + 3個AI各200) = 共 2800 元
            $totalWinnerPrize = 2800; 
            $playerWon = ($results[0]['name'] === $testUser['username']);
            
            if ($playerWon) {
                // 贏了！把整池的錢儲存回資料庫
                $this->user->updateChips($testUser['username'], $totalWinnerPrize);
                // 重新取得最新籌碼餘額
                $testUser = $this->user->getUserInfo('admin');
            }

            $response = [
                'status' => 'success',
                'user_info' => [
                    'name'  => $testUser['username'],
                    'chips' => (int)$testUser['chips'],
                    'player_won' => $playerWon // 讓前端知道玩家贏了沒
                ],
                'pot' => $initialPot, // 傳給前端初始底池
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
                        'rankings' => $results,
                        'prize' => $totalWinnerPrize // 告訴前端這局總獎金是多少
                    ]
                ]
            ];

            return $response;

        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function drawCards($count) {
        $cards = [];
        for ($i = 0; $i < $count; $i++) {
            $cards[] = $this->deck->deal();
        }
        return $cards;
    }


    public function handleBet($username, $amount) {
        $userInfo = $this->user->getUserInfo($username);
        
        // 如果是扣錢 (下注)，檢查餘額夠不夠
        if ($amount < 0 && $userInfo['chips'] < abs($amount)) {
            return ['status' => 'error', 'message' => '籌碼不足！'];
        }

        if ($this->user->updateChips($username, $amount)) {
            $newInfo = $this->user->getUserInfo($username); // 取得更新後的餘額
            return ['status' => 'success', 'new_chips' => $newInfo['chips']];
        }
        
        return ['status' => 'error', 'message' => '更新失敗'];
    }

}


