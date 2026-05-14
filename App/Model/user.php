<?php


class User {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    // 取得使用者資料
    public function getUserInfo($username) {
        $stmt = $this->db->prepare("SELECT id, username, chips FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // 更新籌碼 (這在遊戲結束時會用到)
    public function updateChips($userId, $amount) {
        $stmt = $this->db->prepare("UPDATE users SET chips = chips + ? WHERE id = ?");
        return $stmt->execute([$amount, $userId]);
    }
}