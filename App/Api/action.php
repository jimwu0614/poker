<?php
// 設定回傳格式為 JSON
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Controller/gameController.php';

$action = $_GET['action'] ?? '';
$controller = new GameController();

switch ($action) {
    case 'new_game':
        // 開始新遊戲並取得結果
        $data = $controller->startNewGame();
        echo json_encode($data);
        break;

    case 'get_config':
        // 也可以放一些初始設定 (如籌碼)
        echo json_encode(['initial_chips' => 1000]);
        break;

    case 'bet':
        $username = 'admin'; // 暫時寫死
        $amount = -(int)$_GET['amount']; // 傳入正數代表下注，所以這裡轉負數扣錢
        
        require_once __DIR__ . '/../controller/gameController.php';
        $controller = new GameController();
        $result = $controller->handleBet($username, $amount);
        echo json_encode($result);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}