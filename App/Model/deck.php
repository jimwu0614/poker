<?php
require_once 'card.php'; // 引入卡牌物件

class Deck {
    private $cards = [];

    public function __construct() {
        $this -> resetDeck();
    }

    // 初始化資料陣列
    // 重置並生成 52 張牌

    public function resetDeck() {
        $this->cards = [];
        $suits = ['Spades', 'Hearts', 'Diamonds', 'Clubs'];
        $ranks = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

        foreach ($suits as $suit) {
            foreach ($ranks as $rank) {
                $this -> cards[] = new Card($suit, $rank);
            }
        }
    }

    // 洗牌
    public function shuffle() {
        shuffle($this -> cards); // 使用 PHP 內建的陣列洗牌函式
    }

    // 發牌 (抽走最上面一張牌)
    public function deal() {
        if (empty($this -> cards)) {
            return null; // 沒牌了
        }
        return array_pop($this -> cards); // 取出並移除陣列最後一張牌
    }

    public function getRemainingCount() {
        return count($this -> cards);
    }
}