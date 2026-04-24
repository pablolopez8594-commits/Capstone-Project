<?php
header("Content-Type: application/json; charset=utf-8");

function json_response(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . "/../../Database/db_conn.php";

try {
    $stmt = $conn->query("SELECT program_ID, Program_Name FROM programs ORDER BY Program_Name");
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    json_response(["ok" => true, "data" => $programs]);

} catch (Throwable $e) {
    json_response(["ok" => false, "error" => "Could not fetch programs", "details" => $e->getMessage()], 500);
}
