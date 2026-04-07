<?php

require_once __DIR__ . '/card.php';

// 同花大順 (Royal Flush) - 10
// 同花順 (Straight Flush) - 9
// 四條 (Four of a Kind) - 8
// 葫蘆 (Full House) - 7
// 同花 (Flush) - 6
// 順子 (Straight) - 5
// 三條 (Three of a Kind) - 4
// 兩對 (Two Pairs) - 3
// 對子 (One Pair) - 2
// 高牌 (High Card) - 1

class CardCompare {
    

    // 點數權重表
    // A 視為 14；2 是最小的牌，權重為 2
    
    public $rankMap = [
        '2'=>2, '3'=>3, '4'=>4, '5'=>5, '6'=>6, '7'=>7, '8'=>8, '9'=>9, '10'=>10, 
        'J'=>11, 'Q'=>12, 'K'=>13, 'A'=>14
    ];

    public function __construct() {
        // 實例化時不需要額外設定
    }

    /**
     * 【總入口】四人對決：傳入四個玩家的 7 張牌，回傳排名陣列
     */
    public function getFinalRankings($p1, $p2, $p3, $p4) {
        $players = [
            ['name' => '玩家', 'cards' => $p1],
            ['name' => 'AI_1', 'cards' => $p2],
            ['name' => 'AI_2', 'cards' => $p3],
            ['name' => 'AI_3', 'cards' => $p4]
        ];

        $finalList = [];
        foreach ($players as $p) {
            $analysis = $this->evaluateSevenCards($p['cards']);
            $finalList[] = [
                'name'  => $p['name'],
                'power' => $analysis['power'],
                'label' => $analysis['label'],
                'high'  => $analysis['high'],
                'cards' => $p['cards'] // 保留原始卡片方便前台顯示
            ];
        }

        // 排序邏輯：數字越大越前面
        usort($finalList, function($a, $b) {
            if ($a['power'] != $b['power']) {
                return $b['power'] <=> $a['power'];
            }
            return $b['high'] <=> $a['high'];
        });

        return $finalList;
    }

    /**
     * 【核心分析】輸入 7 張，回傳最強的 5 張牌型結果
     */
    public function evaluateSevenCards($sevenCards) {
        // 先檢查最強的，一旦符合就回傳，不再往下跑
        
        // 1. 同花順 (簡化版：同時符合同花與順子)
        $res = $this->checkFlushStraight($sevenCards);
        if ($res) return $res;

        // 2. 四條
        $res = $this->checkFourOfAKind($sevenCards);
        if ($res) return $res;

        // 3. 葫蘆
        $res = $this->checkFullHouse($sevenCards);
        if ($res) return $res;

        // 4. 同花
        $res = $this->checkFlush($sevenCards);
        if ($res) return $res;

        // 5. 順子
        $res = $this->checkStraight($sevenCards);
        if ($res) return $res;

        // 6. 三條
        $res = $this->checkThreeOfAKind($sevenCards);
        if ($res) return $res;

        // 7. 兩對
        $res = $this->checkTwoPairs($sevenCards);
        if ($res) return $res;

        // 8. 對子
        $res = $this->checkOnePair($sevenCards);
        if ($res) return $res;

        // 9. 高牌 (最後保底)
        return $this->checkHighCard($sevenCards);
    }

    // --- 以下為細分的檢查零件 ---

    public function checkFourOfAKind($cards) {
        $counts = $this->getRankCounts($cards);
        foreach ($counts as $rank => $num) {
            if ($num >= 4) return ['power' => 8, 'label' => '四條', 'high' => $rank];
        }
        return null;
    }

    public function checkFullHouse($cards) {
        $counts = $this->getRankCounts($cards);
        $hasThree = 0;
        $hasTwo = 0;
        foreach ($counts as $rank => $num) {
            if ($num >= 3) $hasThree = $rank;
            elseif ($num >= 2) $hasTwo = $rank;
        }
        if ($hasThree && $hasTwo) {
            return ['power' => 7, 'label' => '葫蘆', 'high' => $hasThree];
        }
        return null;
    }

    public function checkFlush($cards) {
        $suitCounts = [];
        foreach ($cards as $c) {
            $s = $c->getSuit();
            $suitCounts[$s] = ($suitCounts[$s] ?? 0) + 1;
        }
        foreach ($suitCounts as $suit => $num) {
            if ($num >= 5) {
                // 找出該花色中點數最大的
                $max = 0;
                foreach ($cards as $c) {
                    if ($c->getSuit() == $suit) {
                        $val = $this->rankMap[$c->getRank()];
                        if ($val > $max) $max = $val;
                    }
                }
                return ['power' => 6, 'label' => '同花', 'high' => $max];
            }
        }
        return null;
    }

    public function checkStraight($cards) {
        $ranks = [];
        foreach ($cards as $c) { $ranks[] = $this->rankMap[$c->getRank()]; }
        $uniqueRanks = array_unique($ranks);
        sort($uniqueRanks);

        $consecutive = 1;
        $highestInStraight = 0;
        for ($i = 0; $i < count($uniqueRanks) - 1; $i++) {
            if ($uniqueRanks[$i+1] == $uniqueRanks[$i] + 1) {
                $consecutive++;
                if ($consecutive >= 5) $highestInStraight = $uniqueRanks[$i+1];
            } else {
                $consecutive = 1;
            }
        }
        if ($highestInStraight > 0) {
            return ['power' => 5, 'label' => '順子', 'high' => $highestInStraight];
        }
        return null;
    }

    public function checkThreeOfAKind($cards) {
        $counts = $this->getRankCounts($cards);
        foreach ($counts as $rank => $num) {
            if ($num >= 3) return ['power' => 4, 'label' => '三條', 'high' => $rank];
        }
        return null;
    }

    public function checkTwoPairs($cards) {
        $counts = $this->getRankCounts($cards);
        $pairs = [];
        foreach ($counts as $rank => $num) {
            if ($num >= 2) $pairs[] = $rank;
        }
        if (count($pairs) >= 2) {
            rsort($pairs);
            return ['power' => 3, 'label' => '兩對', 'high' => $pairs[0]];
        }
        return null;
    }

    public function checkOnePair($cards) {
        $counts = $this->getRankCounts($cards);
        foreach ($counts as $rank => $num) {
            if ($num >= 2) return ['power' => 2, 'label' => '對子', 'high' => $rank];
        }
        return null;
    }

    public function checkHighCard($cards) {
        $max = 0;
        foreach ($cards as $c) {
            $val = $this->rankMap[$c->getRank()];
            if ($val > $max) $max = $val;
        }
        return ['power' => 1, 'label' => '高牌', 'high' => $max];
    }

    // 輔助工具：計算各個點數出現幾次
    public function getRankCounts($cards) {
        $ranks = [];
        foreach ($cards as $c) {
            $ranks[] = $this->rankMap[$c->getRank()];
        }
        return array_count_values($ranks);
    }

    // 同花順檢查 (簡單調用上面兩個)
    public function checkFlushStraight($cards) {
        $isFlush = $this->checkFlush($cards);
        $isStraight = $this->checkStraight($cards);
        if ($isFlush && $isStraight) {
            return ['power' => 9, 'label' => '同花順', 'high' => $isStraight['high']];
        }
        return null;
    }
}