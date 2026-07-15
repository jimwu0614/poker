/* 
全域變數與狀態管理
*/ 
let gameData = null;      // 儲存後端回傳的完整遊戲劇本與使用者資料
let currentStage = 0;     // 0:未開始, 1:Pre-flop, 2:Flop, 3:Turn, 4:River, 5:Showdown
const stages = ['start', 'pre_flop', 'flop', 'turn', 'river', 'showdown'];

/*
核心流程控制：推進遊戲階段
*/

async function nextStep() {
    const btn = document.getElementById('action-btn');
    
    if (currentStage === 0) {
        // 第一步：初始化遊戲，從後端撈取完整劇本 (預設扣除底注 $100)
        try {
            const response = await fetch('../App/api/action.php?action=new_game');
            gameData = await response.json();
            
            console.log("【遊戲初始化成功】後端回傳資料：", gameData);

            if (gameData.status === 'success') {
                currentStage = 1;
                
                // 1. 更新畫面頂部的使用者名稱與初始籌碼 (此時後端可能尚未扣除底注，在此同步)
                document.getElementById('user-name').innerText = gameData.user_info.name;
                document.getElementById('user-chips').innerText = gameData.user_info.chips;
                
                // 2. 初始化畫面上的總底池金額 (玩家 $100 + 3位 AI 各 $100 = $400)
                if (document.getElementById('total-pot')) {
                    document.getElementById('total-pot').innerText = gameData.pot;
                }
                
                // 3. 渲染玩家手牌與暗蓋的 AI 手牌
                renderPreFlop();
                
                // 4. 切換控制按鈕區：隱藏「開始新局」，顯示「下注控制項」
                toggleButtons(true);
            } else {
                // 後端回傳錯誤（例如：資料庫找不到 admin 帳號）
                document.getElementById('msg').innerText = "錯誤：" + gameData.message;
            }
        } catch (error) {
            console.error("API 連線失敗：", error);
            document.getElementById('msg').innerText = "連線失敗，請檢查後端 API";
        }
    } else if (currentStage < 5) {
        // 正常分段發牌推進 (Flop -> Turn -> River)
        currentStage++;
        renderStage();
    } else {
        // 遊戲結束（Showdown 完畢），點擊按鈕重置頁面重新開始
        location.reload();
    }
}

/*
下注動作處理（與後端資料庫連動）
*/
async function handleAction(type) {
    if (type === 'fold') {
        alert("你選擇了棄牌 (Fold)！這局結束。");
        location.reload();
        return;
    }

    let betAmount = 0;
    if (type === 'call') {
        betAmount = 200; // 假設每輪跟注金額為 $200
    }
    // 若為 'check' (看牌)，下注金額為 0

    try {
        // 呼叫下注扣錢 API
        const response = await fetch(`../App/api/action.php?action=bet&amount=${betAmount}`);
        const result = await response.json();
        
        console.log(`【下注動作: ${type}】API 回傳結果：`, result);

        if (result.status === 'success') {
            // 1. 扣錢成功，更新前端畫面的玩家籌碼餘額
            document.getElementById('user-chips').innerText = result.new_chips;
            
            // 2. 如果是跟注，前端畫面上的總底池手動加上 $800 (1玩家 + 3AI 共同跟注)
            if (type === 'call' && document.getElementById('total-pot')) {
                let currentPot = parseInt(document.getElementById('total-pot').innerText) || 0;
                document.getElementById('total-pot').innerText = currentPot + 800;
            }
            
            // 3. 自動推進到下一個發牌階段（例如 Pre-Flop -> Flop）
            nextStep();
        } else {
            // 籌碼不足或其他後端驗證失敗，阻擋玩家繼續
            alert(result.message);
        }
    } catch (error) {
        console.error("下注 API 連線失敗：", error);
        alert("下注失敗，請檢查網路連線");
    }
}

/*
UI 介面渲染工具
*/ 

/*
控制按鈕區塊的切換
@param {boolean} showBet - true: 顯示遊戲中下注按鈕 / false: 顯示開始新局按鈕
*/
function toggleButtons(showBet) {
    const startArea = document.getElementById('start-area');
    const betArea = document.getElementById('bet-area');
    
    if (startArea && betArea) {
        startArea.style.display = showBet ? 'none' : 'block';
        betArea.style.display = showBet ? 'block' : 'none';
    }
}

/*
將卡片物件轉為帶有花色符號與樣式的 HTML 字串
*/
function createCardHTML(cardObj) {
    const suitMap = { 'Spades': '♠', 'Hearts': '♥', 'Diamonds': '♦', 'Clubs': '♣' };
    const isRed = (cardObj.suit === 'Hearts' || cardObj.suit === 'Diamonds');
    return `<div class="card-slot card ${isRed ? 'red' : ''}">${suitMap[cardObj.suit]}${cardObj.rank}</div>`;
}

/*
階段一：發放手牌 (Pre-flop)
*/
function renderPreFlop() {
    const data = gameData.stages.pre_flop;
    
    // 渲染玩家自己的兩張手牌
    document.getElementById('player-cards').innerHTML = data.player.map(c => createCardHTML(c)).join('');
    
    // 渲染三個 AI 區塊，此時先蓋牌 (顯示問號與背面樣式)
    data.opponents.forEach((bot, i) => {
        const botDiv = document.getElementById(`bot-${i}`);
        if (botDiv) {
            botDiv.innerHTML = `
                <div class="player-info"><strong>${bot.name}</strong></div>
                <div style="font-size:0.8rem; color:#bdc3c7; margin-bottom:5px;">(${bot.strategy})</div>
                <div class="card-display" style="margin:0; min-height:auto;">
                    <div class="card-slot card-back">?</div>
                    <div class="card-slot card-back">?</div>
                </div>
            `;
        }
    });
    document.getElementById('msg').innerText = "第一輪：請下注或看牌 (Pre-flop)";
}

/*
階段二~五：公牌派發與翻牌開牌
*/
function renderStage() {
    const s = stages[currentStage];
    const commDiv = document.getElementById('community-cards');

    if (s === 'flop') {
        // 翻牌階段：發出前 3 張公牌，後 2 張維持空格
        const cards = gameData.stages.flop.map(c => createCardHTML(c)).join('');
        commDiv.innerHTML = cards + '<div class="card-slot"></div><div class="card-slot"></div>';
        document.getElementById('msg').innerText = "第二輪：翻牌 (Flop)";
    } 
    else if (s === 'turn') {
        // 轉牌階段：前 3 張 + 第 4 張，最後 1 張維持空格
        const flop = gameData.stages.flop.map(c => createCardHTML(c)).join('');
        const turn = createCardHTML(gameData.stages.turn[0]);
        commDiv.innerHTML = flop + turn + '<div class="card-slot"></div>';
        document.getElementById('msg').innerText = "第三輪：轉牌 (Turn)";
    }
    else if (s === 'river') {
        // 河牌階段：發出完整 5 張公牌
        const flop = gameData.stages.flop.map(c => createCardHTML(c)).join('');
        const turn = createCardHTML(gameData.stages.turn[0]);
        const river = createCardHTML(gameData.stages.river[0]);
        commDiv.innerHTML = flop + turn + river;
        document.getElementById('msg').innerText = "第四輪：河牌 (River)，最後一輪下注！";
        
        // 提示玩家，下一輪按鈕動作就是開牌了
        const callBtn = document.getElementById('call-btn');
        if (callBtn) callBtn.innerText = "看牌 / 攤牌";
    }
    else if (s === 'showdown') {
        // 開牌攤牌階段
        const show = gameData.stages.showdown;
        
        // 1. 翻開所有對手 AI 的暗牌，並標註他們的最終名次與牌型
        show.ai_hands.forEach((hand, i) => {
            const botName = gameData.stages.pre_flop.opponents[i].name;
            const botDiv = document.getElementById(`bot-${i}`);
            
            // 尋找這個 AI 在排名榜中的資料
            const botRank = show.rankings.findIndex(r => r.name === botName) + 1;
            const botLabel = show.rankings.find(r => r.name === botName).label;

            if (botDiv) {
                botDiv.innerHTML = `
                    <div class="player-info"><strong>${botName}</strong></div>
                    <div style="color:gold; font-size:0.9rem;">第 ${botRank} 名: ${botLabel}</div>
                    <div class="card-display" style="margin:0; min-height:auto;">
                        ${hand.map(c => createCardHTML(c)).join('')}
                    </div>
                `;
            }
        });
        
        // 2. 找出玩家自己的排名與牌型描述
        const playerRank = show.rankings.findIndex(r => r.name === gameData.user_info.name) + 1;
        const playerLabel = show.rankings.find(r => r.name === gameData.user_info.name).label;

        // 3. 在中央訊息欄宣布獲勝者
        const winner = show.rankings[0];
        document.getElementById('msg').innerHTML = `
            <span style="font-size:1.4rem; color:#f1c40f;">【 遊戲結束 】</span><br>
            你的牌型是：<strong>${playerLabel}</strong> (第 ${playerRank} 名)<br>
            本局贏家：<strong style="color:#2ecc71;">${winner.name}</strong> (${winner.label})
        `;
        
        // 4. 切換按鈕回到初始狀態，文字變成「重新開始」
        toggleButtons(false);
        document.getElementById('action-btn').innerText = "再玩一局";
    }
}