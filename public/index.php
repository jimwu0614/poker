<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>德州撲克 Texas Hold'em - 面試展示版</title>
    <style>
        body {
            background-color: #1a472a; /* 經典撲克牌桌綠 */
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
            max-width: 950px;
            text-align: center;
            background: rgba(0, 0, 0, 0.4);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 50px rgba(0,0,0,0.6);
        }
        
        /* 玩家狀態列 */
        .user-bar {
            background: rgba(0, 0, 0, 0.6);
            padding: 8px 25px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 25px;
            border: 1px solid gold;
            font-size: 1.1rem;
        }
        .user-bar span { font-weight: bold; }
        #user-chips { color: #2ecc71; }

        /* 對手區版型優化 */
        .opponents {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 25px;
        }
        .player-area {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px;
            border-radius: 12px;
            width: 180px;
            min-height: 150px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .player-info {
            font-size: 1.05rem;
            color: #fff;
            margin-bottom: 2px;
        }

        /* 卡片顯示區塊（解決歪掉的問題） */
        .card-display {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin: 15px 0;
            min-height: 100px;
        }
        
        /* 統一卡片與卡槽尺寸 */
        .card-slot {
            width: 65px;
            height: 95px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            font-size: 22px;
            font-weight: bold;
            box-sizing: border-box;
            transition: transform 0.2s;
        }
        
        /* 實體撲克牌樣式 */
        .card {
            background: white !important;
            color: black !important;
            border: 1px solid #ccc !important;
            box-shadow: 2px 4px 8px rgba(0,0,0,0.4);
        }
        .card.red { color: #e74c3c !important; }
        
        /* 牌背蓋牌樣式 */
        .card-back {
            background: #2980b9 !important;
            color: white !important;
            border: 2px solid white !important;
            box-shadow: 2px 4px 8px rgba(0,0,0,0.4);
        }
        
        /* 公牌區獨立樣式 */
        #community-cards {
            background: rgba(0, 0, 0, 0.2);
            padding: 15px 25px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: inline-flex;
        }

        .section-title {
            font-size: 0.9rem;
            color: #b3dfc1;
            margin: 5px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* 中央訊息通知 */
        #msg {
            font-size: 1.2rem;
            margin: 20px 0;
            min-height: 50px;
            color: gold;
            display: flex;
            justify-content: center;
            align-items: center;
            line-height: 1.5;
        }

        /* 控制按鈕區與美化 */
        .controls {
            margin-top: 15px;
            min-height: 55px;
        }
        .controls button {
            padding: 12px 35px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 0 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: all 0.2s ease;
        }
        .controls button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.4);
        }
        .controls button:active {
            transform: translateY(1px);
        }
        
        /* 開始按鈕黃色系 */
        #action-btn { background: #f1c40f; color: #2c3e50; }
        #action-btn:hover { background: #f39c12; }
    </style>
</head>
<body>

<div class="container">
    <h2>Texas Hold'em Poker</h2>

    <div class="user-bar">
        玩家: <span id="user-name">載入中...</span> | 
        籌碼: $<span id="user-chips">0</span>
    </div>

    <div style="font-size: 1.2rem; margin: 10px 0; color: #f39c12; font-weight: bold;">
        當前總底池 (POT): $<span id="total-pot">400</span>
    </div>

    <div class="opponents">
        <div id="bot-0" class="player-area"><div class="player-info">對手 1</div><div style="color:#aaa;">待機中</div></div>
        <div id="bot-1" class="player-area"><div class="player-info">對手 2</div><div style="color:#aaa;">待機中</div></div>
        <div id="bot-2" class="player-area"><div class="player-info">對手 3</div><div style="color:#aaa;">待機中</div></div>
    </div>

    <div class="section-title">公牌 (Community Cards)</div>
    <div class="card-display">
        <div id="community-cards">
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
    
    <div class="controls">
        <div id="start-area">
            <button id="action-btn" onclick="nextStep()">開始新局 (底注 $100)</button>
        </div>
        
        <div id="bet-area" style="display: none;">
            <button onclick="handleAction('fold')" style="background:#e74c3c; color:white;">棄牌 (Fold)</button>
            <button onclick="handleAction('check')" style="background:#3498db; color:white;">看牌 (Check)</button>
            <button id="call-btn" onclick="handleAction('call')" style="background:#2ecc71; color:white;">跟注 $200</button>
        </div>
    </div>
</div>

<script src="js/game.js"></script>

</body>
</html>