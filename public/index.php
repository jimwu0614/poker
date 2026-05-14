<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>德州撲克 Texas Hold'em - 面試展示版</title>
    <style>
        body {
            background-color: #1a472a; /* 牌桌綠 */
            color: white;
            font-family: "Microsoft JhengHei", Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            width: 90%;
            max-width: 1000px;
            text-align: center;
            background: rgba(0, 0, 0, 0.3);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 50px rgba(0,0,0,0.5);
        }
        .user-bar {
            background: rgba(0, 0, 0, 0.6);
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 20px;
            border: 1px solid gold;
        }
        .user-bar span { font-weight: bold; }
        #user-chips { color: #2ecc71; }

        .opponents {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
        }
        .player-area {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: 10px;
            width: 150px;
            min-height: 120px;
        }
        .card-display {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
            min-height: 110px;
        }
        /* 卡片樣式 */
        .card-slot {
            width: 70px;
            height: 100px;
            background: white;
            color: black;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
        }
        .card.red { color: #e74c3c; }
        .card-back {
            background: #2980b9;
            color: white;
            border: 2px solid white;
        }
        
        #community-cards {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 15px;
            border: 2px dashed rgba(255, 255, 255, 0.2);
        }

        #msg {
            font-size: 1.2rem;
            margin: 20px 0;
            height: 1.5em;
            color: gold;
        }
        .controls button {
            padding: 12px 40px;
            font-size: 18px;
            cursor: pointer;
            background: #f1c40f;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            transition: 0.3s;
        }
        .controls button:hover {
            background: #f39c12;
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Texas Hold'em</h1>

    <div class="user-bar">
        玩家: <span id="user-name">載入中...</span> | 
        籌碼: $<span id="user-chips">0</span>
    </div>

    <div class="opponents">
        <div id="bot-0" class="player-area">待機中</div>
        <div id="bot-1" class="player-area">待機中</div>
        <div id="bot-2" class="player-area">待機中</div>
    </div>

    <hr style="opacity: 0.2;">

    <p>公牌 (Community Cards)</p>
    <div id="community-cards" class="card-display">
        <div class="card-slot"></div>
        <div class="card-slot"></div>
        <div class="card-slot"></div>
        <div class="card-slot"></div>
        <div class="card-slot"></div>
    </div>
    
    <p>你的手牌 (Your Hand)</p>
    <div id="player-cards" class="card-display"></div>
    
    <div id="msg">按下「開始新局」來挑戰 AI</div>
    
    <div class="controls">
        <button id="action-btn" onclick="nextStep()">開始新局</button>
    </div>
</div>

<script src="js/game.js"></script>

</body>
</html>