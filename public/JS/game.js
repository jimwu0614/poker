// 全域變數儲存遊戲狀態
let gameData = null; 
let currentStage = 0; 
const stages = ['start', 'pre_flop', 'flop', 'turn', 'river', 'showdown'];

/**
 * 核心流程控制：推進遊戲階段
 */
async function nextStep() {
    const btn = document.getElementById('action-btn');
    
    if (currentStage === 0) {
        // 第一步：初始化並抓取完整 API 資料
        try {
            const response = await fetch('../App/api/action.php?action=new_game');
            gameData = await response.json();
            
            if (gameData.status === 'success') {
                currentStage = 1;
                renderPreFlop();
                btn.innerText = "下一輪";
            }
        } catch (error) {
            console.error("API Error:", error);
            document.getElementById('msg').innerText = "連線失敗，請檢查 API";
        }
    } else if (currentStage < 5) {
        currentStage++;
        renderStage();
        
        // 更新按鈕文字
        if (currentStage === 2) btn.innerText = "下一輪";
        if (currentStage === 3) btn.innerText = "下一輪";
        if (currentStage === 4) btn.innerText = "開牌";
        if (currentStage === 5) btn.innerText = "重新開始";
    } else {
        // 遊戲結束，重置頁面
        location.reload();
    }
}

/**
 * 渲染卡片 HTML 的工具函式
 */
function createCardHTML(cardObj) {
    const suitMap = { 'Spades': '♠', 'Hearts': '♥', 'Diamonds': '♦', 'Clubs': '♣' };
    const isRed = (cardObj.suit === 'Hearts' || cardObj.suit === 'Diamonds');
    return `<div class="card-slot card ${isRed ? 'red' : ''}">${suitMap[cardObj.suit]}${cardObj.rank}</div>`;
}

/**
 * 階段一：發放私有牌 (Pre-flop)
 */
function renderPreFlop() {
    const data = gameData.stages.pre_flop;
    
    // 渲染玩家牌
    document.getElementById('player-cards').innerHTML = data.player.map(c => createCardHTML(c)).join('');
    
    // 渲染 AI 區塊 (先顯示背面圖示)
    data.opponents.forEach((bot, i) => {
        const botDiv = document.getElementById(`bot-${i}`);
        botDiv.innerHTML = `
            <div class="player-info"><strong>${bot.name}</strong> (${bot.strategy})</div>
            <div class="card-slot card-back">?</div><div class="card-slot card-back">?</div>
        `;
    });
    document.getElementById('msg').innerText = "第一輪：請下注 (Pre-flop)";
}

/**
 * 階段二~五：公牌與開牌
 */
function renderStage() {
    const s = stages[currentStage];
    const commDiv = document.getElementById('community-cards');

    if (s === 'flop') {
        const cards = gameData.stages.flop.map(c => createCardHTML(c)).join('');
        commDiv.innerHTML = cards + '<div class="card-slot"></div><div class="card-slot"></div>';
        document.getElementById('msg').innerText = "第二輪：Flop";
    } 
    else if (s === 'turn') {
        const flop = gameData.stages.flop.map(c => createCardHTML(c)).join('');
        const turn = createCardHTML(gameData.stages.turn[0]);
        commDiv.innerHTML = flop + turn + '<div class="card-slot"></div>';
        document.getElementById('msg').innerText = "第三輪：Turn";
    }
    else if (s === 'river') {
        const flop = gameData.stages.flop.map(c => createCardHTML(c)).join('');
        const turn = createCardHTML(gameData.stages.turn[0]);
        const river = createCardHTML(gameData.stages.river[0]);
        commDiv.innerHTML = flop + turn + river;
        document.getElementById('msg').innerText = "第四輪：River";
    }
    else if (s === 'showdown') {
        const show = gameData.stages.showdown;
        // 翻開所有 AI 的手牌
        show.ai_hands.forEach((hand, i) => {
            const botDiv = document.getElementById(`bot-${i}`);
            botDiv.innerHTML = `
                <div class="player-info"><strong>${gameData.stages.pre_flop.opponents[i].name}</strong></div>
                <div>${hand.map(c => createCardHTML(c)).join('')}</div>
            `;
        });
        document.getElementById('msg').innerText = "開牌！第一名是：" + show.rankings[0].name + " (" + show.rankings[0].label + ")";
    }
}