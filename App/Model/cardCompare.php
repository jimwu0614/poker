<?php

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
    public static function compare($hand1, $hand2) {
        // 這裡應該實現牌型比較的邏輯
        // 目前只是示範，實際上需要根據牌型來比較大小
        return rand(-1, 1); // 隨機返回 -1, 0, 或 1
    }


    // 定義點數權重，方便比較
    private static $rankMap = [
        '2'=>2, '3'=>3, '4'=>4, '5'=>5, '6'=>6, '7'=>7, '8'=>8, '9'=>9, '10'=>10, 'J'=>11, 'Q'=>12, 'K'=>13, 'A'=>14
    ];

    /**
     * 核心功能：輸入 7 張牌，回傳最強牌型資訊
     */
    public static function evaluateHand($cards) {
        // 1. 先將點數轉換為數字並排序
        // 2. 檢查同花、順子等邏輯
        // (這裡為了簡略先示範判斷「對子」與「高牌」的結構)
        
        $ranks = [];
        $suits = [];
        foreach ($cards as $card) {
            $ranks[] = self::$rankMap[$card->getRank()];
            $suits[] = $card->getSuit();
        }
        rsort($ranks); // 從大到小排序

        // 計算點數出現次數 (例如：兩個 A 就會是 [14 => 2])
        $counts = array_count_values($ranks);
        arsort($counts); // 按出現次數排序

        // 簡易判定邏輯範例
        foreach ($counts as $rank => $count) {
            if ($count === 4) return ['power' => 8, 'name' => '四條', 'high' => $rank];
            if ($count === 3 && count($counts) >= 2) {
                // 檢查是否有另一個對子組成葫蘆
                return ['power' => 7, 'name' => '葫蘆', 'high' => $rank];
            }
            if ($count === 3) return ['power' => 4, 'name' => '三條', 'high' => $rank];
            if ($count === 2) {
                // 檢查是否兩對
                $pairs = array_filter($counts, function($v) { return $v === 2; });
                if (count($pairs) >= 2) return ['power' => 3, 'name' => '兩對', 'high' => array_keys($pairs)];
                return ['power' => 2, 'name' => '對子', 'high' => $rank];
            }
        }

        return ['power' => 1, 'name' => '高牌', 'high' => $ranks[0]];
    }

    /**
     * 比較兩個玩家誰贏
     */
    public static function compareHands($handA, $handB) {
        $resA = self::evaluateHand($handA);
        $resB = self::evaluateHand($handB);

        if ($resA['power'] > $resB['power']) return 1;  // A 贏
        if ($resA['power'] < $resB['power']) return -1; // B 贏
        
        // 如果牌型一樣大，比較點數 (High Card)
        return $resA['high'] <=> $resB['high']; 
    }


}