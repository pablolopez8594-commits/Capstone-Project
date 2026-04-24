<?php
require_once __DIR__ . "/../../Database/db_conn.php";
class StudentRepository {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function findByEmail(string $email): ?array {
        $sql = "SELECT * FROM students WHERE student_email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":email" => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
    public function findById(int $id): ?array {
    $sql = "SELECT * FROM students WHERE student_IdNum = :id LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([":id" => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}
}


?>