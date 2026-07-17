<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>德州撲克 Texas Hold'em - 面試展示版</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <h2>Texas Hold'em Poker</h2>

    <div class="user-bar">
        玩家: <span id="user-name" class="username-highlight">載入中...</span> | 
        籌碼: $<span id="user-chips" class="chips-highlight">0</span>
    </div>

    <div class="pot-bar">
        當前總底池 (POT): $<span id="total-pot">400</span>
    </div>

    <div class="opponents">
        <div id="bot-0" class="player-area">
            <div class="player-info">對手 1</div>
            <div class="waiting-status">待機中</div>
        </div>
        <div id="bot-1" class="player-area">
            <div class="player-info">對手 2</div>
            <div class="waiting-status">待機中</div>
        </div>
        <div id="bot-2" class="player-area">
            <div class="player-info">對手 3</div>
            <div class="waiting-status">待機中</div>
        </div>
    </div>

    <div class="section-title">公牌 (Community Cards)</div>
    <div class="card-display">
        <div id="community-cards" class="community-cards-container">
            <div class="card-slot"></div>
            <div class="card-slot"></div>
            <div class="card-slot"></div>
            <div class="card-slot"></div>
            <div class="card-slot"></div>
        </div>
    </div>
    
    <div class="section-title">你的手牌 (Your Hand)</div>
    <div id="player-cards" class="card-display">
        <div class="card-slot"></div>
        <div class="card-slot"></div>
    </div>
    
    <div id="msg">按下下方按鈕開始對局</div>
    
    <div id="showdown-board" class="showdown-board hide">
        <h3 class="showdown-title">【 遊戲結束・攤牌結算 】</h3>
        <p>你的牌型：<strong id="showdown-player-label">---</strong> (<span id="showdown-player-rank">---</span>)</p>
        <p>本局贏家：<strong id="showdown-winner-name" class="showdown-winner-name">---</strong> (<span id="showdown-winner-label">---</span>)</p>
        
        <div id="showdown-win-msg" class="showdown-win-msg hide">
            🎉 恭喜贏家！你抱走了底池全部獎金 $<span id="showdown-prize-amount">0</span>！籌碼已入帳！
        </div>
        <div id="showdown-lose-msg" class="showdown-lose-msg hide">
            這局由 <strong id="showdown-lose-winner" class="showdown-lose-winner-name">---</strong> 贏得底池，再接再厲！
        </div>
    </div>
    
    <div class="controls">
        <div id="start-area">
            <button id="action-btn" class="btn-start" onclick="nextStep()">開始新局 (底注 $100)</button>
        </div>
        
        <div id="bet-area" class="hide">
            <button class="btn-fold" onclick="handleAction('fold')">棄牌 (Fold)</button>
            <button class="btn-check" onclick="handleAction('check')">看牌 (Check)</button>
            <button id="call-btn" class="btn-call" onclick="handleAction('call')">跟注 $200</button>
        </div>
    </div>
</div>

<script src="js/game.js"></script>

</body>
</html>