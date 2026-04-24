<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../../Database/db_conn.php";

try {
  $port = $conn->query("SELECT @@port")->fetchColumn();
  $db   = $conn->query("SELECT DATABASE()")->fetchColumn();

  $stmt = $conn->prepare("SELECT student_email, student_password, LENGTH(student_password) AS len
                          FROM students WHERE student_email=?");
  $stmt->execute(["pablo@example.com"]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  echo json_encode([
    "connected_db" => $db,
    "mysql_port" => $port,
    "pablo_exists" => (bool)$row,
    "hash_prefix" => $row ? substr($row["student_password"], 0, 15) : null,
    "hash_len" => $row ? (int)$row["len"] : null,
  ], JSON_PRETTY_PRINT);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}