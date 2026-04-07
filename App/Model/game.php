<?php
require_once __DIR__ . '/deck.php';
require_once __DIR__ . '/cardCompare.php';

class Game {
    private $deck;
    private $players;
    private $communityCards;
    private $stage; // preflop, flop, turn, river, showdown
    private $pot;
    private $currentBet;
    private $db;

    public function __construct() {
        $this->deck = new Deck();
        $this->deck->shuffle();
        $this->players = [
            ['name' => '玩家', 'holeCards' => [], 'chips' => 1000, 'bet' => 0, 'folded' => false, 'isAI' => false],
            ['name' => 'AI_1', 'holeCards' => [], 'chips' => 1000, 'bet' => 0, 'folded' => false, 'isAI' => true],
            ['name' => 'AI_2', 'holeCards' => [], 'chips' => 1000, 'bet' => 0, 'folded' => false, 'isAI' => true],
            ['name' => 'AI_3', 'holeCards' => [], 'chips' => 1000, 'bet' => 0, 'folded' => false, 'isAI' => true]
        ];
        $this->communityCards = [];
        $this->stage = 'preflop';
        $this->pot = 0;
        $this->currentBet = 0;
        $this->db = new PDO('sqlite:../strategies.db');
    }

    public function dealCards() {
        // 發每人2張底牌
        foreach ($this->players as &$player) {
            $player['holeCards'] = [$this->deck->deal(), $this->deck->deal()];
        }
    }

    public function dealFlop() {
        // 燒一張，發3張
        $this->deck->deal(); // burn
        for ($i = 0; $i < 3; $i++) {
            $this->communityCards[] = $this->deck->deal();
        }
        $this->stage = 'flop';
    }

    public function dealTurn() {
        $this->deck->deal(); // burn
        $this->communityCards[] = $this->deck->deal();
        $this->stage = 'turn';
    }

    public function dealRiver() {
        $this->deck->deal(); // burn
        $this->communityCards[] = $this->deck->deal();
        $this->stage = 'river';
    }

    public function getHandStrength($playerIndex) {
        $cards = array_merge($this->players[$playerIndex]['holeCards'], $this->communityCards);
        $cardCompare = new CardCompare();
        $analysis = $cardCompare->evaluateSevenCards($cards);
        return $analysis['power'];
    }

    public function aiDecideAction($playerIndex) {
        $aiName = $this->players[$playerIndex]['name'];
        $stage = $this->stage;
        $strength = $this->getHandStrength($playerIndex);

        // 查詢資料庫
        $stmt = $this->db->prepare("SELECT action, probability FROM ai_strategies WHERE ai_name = ? AND stage = ? AND hand_strength = ? ORDER BY probability DESC");
        $stmt->execute([$aiName, $stage, $strength]);
        $actions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($actions)) {
            return 'fold'; // 默認棄牌
        }

        // 根據機率選擇動作
        $rand = mt_rand() / mt_getrandmax();
        $cumulative = 0;
        foreach ($actions as $act) {
            $cumulative += $act['probability'];
            if ($rand <= $cumulative) {
                return $act['action'];
            }
        }
        return 'fold';
    }

    // 簡化投注邏輯：假設小盲注等，但這裡簡化
    public function bettingRound() {
        // 重置bets
        foreach ($this->players as &$p) {
            $p['bet'] = 0;
        }
        $this->currentBet = 0;

        // 對於AI，自動決定
        foreach ($this->players as $i => &$p) {
            if ($p['folded']) continue;
            if ($p['isAI']) {
                $action = $this->aiDecideAction($i);
                if ($action == 'fold') {
                    $p['folded'] = true;
                } elseif ($action == 'call') {
                    $bet = min($this->currentBet, $p['chips']);
                    $p['bet'] = $bet;
                    $p['chips'] -= $bet;
                    $this->pot += $bet;
                } elseif ($action == 'raise') {
                    $raise = 50; // 簡化，加注50
                    $totalBet = $this->currentBet + $raise;
                    $bet = min($totalBet, $p['chips']);
                    $p['bet'] = $bet;
                    $p['chips'] -= $bet;
                    $this->pot += $bet;
                    $this->currentBet = $bet;
                }
            } else {
                // 對於玩家，手動，但這裡簡化為call
                $bet = min($this->currentBet, $p['chips']);
                $p['bet'] = $bet;
                $p['chips'] -= $bet;
                $this->pot += $bet;
            }
        }
    }

    public function getPlayerCards() {
        $cardCompare = new CardCompare();
        $validCards = [];
        $validNames = [];
        foreach ($this->players as $p) {
            if (!$p['folded']) {
                $validCards[] = array_merge($p['holeCards'], $this->communityCards);
                $validNames[] = $p['name'];
            }
        }
        if (count($validCards) == 0) return []; // 所有人都棄牌
        // 對於少於4人，getFinalRankings需要修改，但這裡簡化，假設至少一人
        $rankings = $cardCompare->getFinalRankings($validCards[0] ?? [], $validCards[1] ?? [], $validCards[2] ?? [], $validCards[3] ?? []);
        // 添加name
        for ($i = 0; $i < count($rankings); $i++) {
            $rankings[$i]['name'] = $validNames[$i] ?? 'Unknown';
        }
        return $rankings;
    }

    public function getCommunityCards() {
        return $this->communityCards;
    }

    public function getPlayers() {
        return $this->players;
    }

    public function getStage() {
        return $this->stage;
    }

    public function getPot() {
        return $this->pot;
    }
}
?>