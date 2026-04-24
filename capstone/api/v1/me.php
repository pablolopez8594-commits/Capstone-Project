<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

function json_response(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// Si no hay sesión, no hay datos
if (!isset($_SESSION["student_IdNum"])) {
    json_response(["ok" => false, "error" => "Not logged in"], 401);
}

require_once __DIR__ . "/../../Database/db_conn.php";
require_once __DIR__ . "/../../src/Repositories/StudentRepository.php";

try {
    $repo = new StudentRepository($conn);
    $student = $repo->findById((int)$_SESSION["student_IdNum"]);

    if (!$student) {
        json_response(["ok" => false, "error" => "Student not found"], 404);
    }

    json_response([
        "ok" => true,
        "data" => [
            "student_IdNum"      => (int)$student["student_IdNum"],
            "student_first_name" => $student["student_first_name"],
            "student_last_name"  => $student["student_last_name"],
            "student_email"      => $student["student_email"],
            "student_photo"      => $student["student_photo"],
        ]
    ]);

} catch (Throwable $e) {
    json_response(["ok" => false, "error" => "Could not fetch profile", "details" => $e->getMessage()], 500);
}
