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

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}