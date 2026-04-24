<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>德州撲克對決</title>
    <style>
        body { background-color: #1a472a; color: white; font-family: "Microsoft JhengHei", sans-serif; display: flex; flex-direction: column; align-items: center; margin: 0; padding: 20px; }
        #table { width: 90%; max-width: 1000px; background: #2d5a3f; border: 10px solid #5d4037; border-radius: 150px; position: relative; padding: 50px; min-height: 400px; display: flex; flex-direction: column; justify-content: space-between; align-items: center; box-shadow: inset 0 0 50px #000; }
        .player-row { display: flex; justify-content: space-around; width: 100%; }
        .card-slot { background: rgba(0,0,0,0.2); border: 2px dashed #fff; width: 60px; height: 90px; border-radius: 5px; display: inline-block; margin: 5px; vertical-align: top; text-align: center; line-height: 90px; font-weight: bold; font-size: 20px; }
        .card { background: white; color: black; border: 1px solid #000; position: relative; }
        .red { color: #ff0000; }
        .community-area { background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin: 20px 0; }
        .controls { margin-top: 30px; }
        button { padding: 10px 25px; font-size: 18px; cursor: pointer; background: #f1c40f; border: none; border-radius: 5px; font-weight: bold; }
        .player-info { text-align: center; margin-top: 10px; }
        .status-msg { margin-top: 10px; font-weight: bold; color: #f1c40f; }
    </style>
</head>
<body>

    <h1>Texas Hold'em Poker</h1>

    <div id="table">
        <div class="player-row" id="ai-area">
            <div id="bot-0">等待中...</div>
            <div id="bot-1">等待中...</div>
            <div id="bot-2">等待中...</div>
        </div>

        <div class="community-area">
            <h3>公牌 (Community Cards)</h3>
            <div id="community-cards">
                <div class="card-slot"></div>
                <div class="card-slot"></div>
                <div class="card-slot"></div>
                <div class="card-slot"></div>
                <div class="card-slot"></div>
            </div>
        </div>

        <div id="player-area">
            <div id="player-cards">
                <div class="card-slot"></div>
                <div class="card-slot"></div>
            </div>
            <div class="player-info"><strong>你 (You)</strong></div>
        </div>
    </div>

    <div class="status-msg" id="msg">請點擊開始遊戲</div>

    <div class="controls">
        <button onclick="startNewGame()">開始新局</button>
    </div>

    <script>
        // 渲染卡片的工具函式
        function createCardHTML(cardObj) {
            const suitMap = { 'Spades': '♠', 'Hearts': '♥', 'Diamonds': '♦', 'Clubs': '♣' };
            const isRed = (cardObj.suit === 'Hearts' || cardObj.suit === 'Diamonds');
            return `<div class="card-slot card ${isRed ? 'red' : ''}">${suitMap[cardObj.suit]}${cardObj.rank}</div>`;
        }

        async function startNewGame() {
            document.getElementById('msg').innerText = "洗牌中...";
            
            try {
                // 呼叫你的 API
                const response = await fetch('../App/Api/action.php?action=new_game');
                const data = await response.json();

                if (data.status === 'success') {
                    renderGame(data);
                    document.getElementById('msg').innerText = "發牌完成！贏家是：" + data.rankings[0].name;
                }
            } catch (error) {
                console.error("API 呼叫失敗:", error);
                document.getElementById('msg').innerText = "連線失敗，請檢查 API 路徑";
            }
        }

        function renderGame(data) {
            // 1. 渲染公牌
            const communityDiv = document.getElementById('community-cards');
            communityDiv.innerHTML = data.community_cards.map(c => createCardHTML(c)).join('');

            // 2. 渲染玩家私有牌
            const playerDiv = document.getElementById('player-cards');
            playerDiv.innerHTML = data.player.private_cards.map(c => createCardHTML(c)).join('');

            // 3. 渲染 AI 對手
            data.opponents.forEach((bot, index) => {
                const botDiv = document.getElementById('bot-' + index);
                botDiv.innerHTML = `
                    <div class="player-info"><strong>${bot.name}</strong> (${bot.strategy})</div>
                    <div>${bot.private_cards.map(c => createCardHTML(c)).join('')}</div>
                `;
            });
        }
    </script>
</body>
</html>