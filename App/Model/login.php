<?php
// 啟動 Session 紀錄登入狀態
session_start();

// 引入資料庫類別檔案
require_once 'db.php';

header('Content-Type: application/json');

// 實例化資料庫物件並取得連線
$db = new Database();
$pdo = $db->getConnection();

// 讀取前端傳來的 JSON 資料
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? '';

if ($action === 'register') {
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => '帳號或密碼不能為空']);
        exit;
    }

    // 檢查帳號是否已存在
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => '此帳號已被註冊']);
        exit;
    }

    // 註冊時直接儲存明文密碼（不加密）
    $stmt = $pdo->prepare("INSERT INTO users (username, password, chips) VALUES (?, ?, 1000)");
    if ($stmt->execute([$username, $password])) {
        echo json_encode(['success' => true, 'message' => '註冊成功！請進行登入']);
    } else {
        echo json_encode(['success' => false, 'message' => '註冊失敗，請重試']);
    }
} 
elseif ($action === 'login') {
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // 直接比對明文密碼
        if ($password === $user['password']) {
            
            // 登入成功，將資訊寫入 Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            echo json_encode([
                'success' => true,
                'message' => '登入成功',
                'data' => [
                    'username' => $user['username'],
                    'chips' => (int)$user['chips']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => '密碼錯誤']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => '帳號不存在']);
    }
} 
else {
    echo json_encode(['success' => false, 'message' => '未知的操作']);
}