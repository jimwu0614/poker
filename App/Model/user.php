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



    public function updateChips($username, $amount) {
        // 使用預處理防止 SQL 注入
        $stmt = $this->db->prepare("UPDATE users SET chips = chips + ? WHERE username = ?");
        return $stmt->execute([$amount, $username]);
    }
}


