<?php
session_start();

// 判斷登入狀態（可依需求改為你的 Session key）
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>德州撲克 Texas Hold'em</title>
    <!-- 引入自訂樣式表 -->
    <link rel="stylesheet" href="css/style.css">
    <!-- 引入 SweetAlert2 的 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>

    <!-- 空的 Container，由後端根據狀態動態渲染 HTML 畫面 -->
    <div class="container">
        <?php 
        if ($isLoggedIn) {
            include __DIR__ . '/../App/Views/game_view.php';
        } else {
            include __DIR__ . '/../App/Views/login_view.php';
        }
        ?>
    </div>

    <!-- 引入 SweetAlert2 的套件腳本 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- 根據登入狀態引入對應的 JS 功能模組 -->
    <?php if ($isLoggedIn): ?>
        <script src="js/game.js"></script>
    <?php else: ?>
        <script src="js/login.js"></script>
    <?php endif; ?>

</body>
</html>