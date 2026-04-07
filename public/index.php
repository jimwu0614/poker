<!DOCTYPE html>
<html lang="zh-tw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>四人德州撲克小遊戲</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .cards { display: flex; justify-content: center; margin: 10px; }
        .card { border: 1px solid #000; padding: 5px; margin: 5px; background: white; }
        .player { margin: 20px; }
        .community { margin: 20px; border-top: 1px solid #ccc; padding-top: 20px; }
        .folded { opacity: 0.5; }
    </style>
</head>
<body>
    <h1>四人德州撲克小遊戲</h1>
    <?php
    require_once '../App/Model/game.php';

    $game = new Game();
    $game->dealCards();
    echo "<h2>Preflop</h2>";
    $game->bettingRound();

    $game->dealFlop();
    echo "<h2>Flop</h2>";
    $game->bettingRound();

    $game->dealTurn();
    echo "<h2>Turn</h2>";
    $game->bettingRound();

    $game->dealRiver();
    echo "<h2>River</h2>";
    $game->bettingRound();

    $rankings = $game->getPlayerCards();
    $communityCards = $game->getCommunityCards();
    $players = $game->getPlayers();
    $pot = $game->getPot();
    ?>

    <div class="community">
        <h2>公共牌</h2>
        <div class="cards">
            <?php foreach ($communityCards as $card): ?>
                <div class="card"><?php echo $card->toString(); ?></div>
            <?php endforeach; ?>
        </div>
        <p>總獎池: <?php echo $pot; ?> 籌碼</p>
    </div>

    <h2>玩家牌與排名</h2>
    <?php foreach ($rankings as $ranking): ?>
        <div class="player <?php echo in_array($ranking['name'], array_column(array_filter($players, fn($p) => $p['folded']), 'name')) ? 'folded' : ''; ?>">
            <h3><?php echo $ranking['name']; ?> - <?php echo $ranking['label']; ?> (權重: <?php echo $ranking['power']; ?>)</h3>
            <div class="cards">
                <?php
                // 顯示玩家的底牌
                foreach ($players as $p) {
                    if ($p['name'] == $ranking['name']) {
                        foreach ($p['holeCards'] as $card) {
                            echo '<div class="card">' . $card->toString() . '</div>';
                        }
                        echo '<p>籌碼: ' . $p['chips'] . ' | ' . ($p['folded'] ? '已棄牌' : '活躍') . '</p>';
                        break;
                    }
                }
                ?>
            </div>
        </div>
    <?php endforeach; ?>

    <br>
    <button onclick="location.reload()">重新開始遊戲</button>
</body>
</html>