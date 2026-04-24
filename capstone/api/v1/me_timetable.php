<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

function json_response(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}


if (!isset($_SESSION["student_IdNum"])) {
    json_response(["ok" => false, "error" => "Not logged in"], 401);
}

require_once __DIR__ . "/../../Database/db_conn.php";
require_once __DIR__ . "/../../src/Repositories/TimetableRepository.php";

try {
    $repo = new TimetableRepository($conn);
    $items = $repo->getByStudentId((int)$_SESSION["student_IdNum"]);
    json_response(["ok" => true, "data" => $items]);
} catch (Throwable $e) {
    json_response(["ok" => false, "error" => "Timetable failed", "details" => $e->getMessage()], 500);
}