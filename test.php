<?php
require_once 'App/Model/card.php';
require_once 'App/Model/deck.php';

$deck = new Deck();
$deck -> shuffle(); // 洗牌

echo "--- 抽 5 張牌測試 ---\n";
for ($i = 0; $i < 5; $i++) {
    $card = $deck -> deal();
    echo $card -> toString() . " ";
}
echo "\n剩下牌數: " . $deck -> getRemainingCount() . " 張\n";