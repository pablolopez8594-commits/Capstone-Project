<?php
header("Content-Type: application/json; charset=utf-8");

function json_response(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . "/../../Database/db_conn.php";
require_once __DIR__ . "/../../src/Repositories/StaffRepository.php";

$q = trim($_GET["q"] ?? "");

try {
    $repo = new StaffRepository($conn);
    $staff = $repo->search($q);

    json_response([
        "ok" => true,
        "data" => $staff
    ]);
} catch (Throwable $e) {
    // ✅ Si hay error en SQL/PDO, JAMÁS devolvemos HTML; devolvemos JSON
    json_response([
        "ok" => false,
        "error" => "Server error in staff endpoint",
        "details" => $e->getMessage() // si no quieres exponerlo, quítalo
    ], 500);
}