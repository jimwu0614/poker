<?php

class Card {
        public $suit; // 花色: Spades, Hearts, Diamonds, Clubs
        public $rank; // 點數: 2-10, J, Q, K, A

        public function __construct($suit, $rank) {
            $this -> suit = $suit;
            $this -> rank = $rank;
        }

        public function getSuit() {
            return $this -> suit;
        }

        public function getRank() {
            return $this -> rank;
        }

        // 方便印出卡牌的文字 (例如: ♠A, ♥10)
        public function toString() {
            $symbols = [
                'Spades' => '♠',
                'Hearts' => '♥',
                'Diamonds' => '♦',
                'Clubs' => '♣'
            ];
            return $symbols [$this -> suit] . $this -> rank;
        }
    }
?>