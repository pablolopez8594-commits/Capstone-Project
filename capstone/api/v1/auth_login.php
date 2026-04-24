<?php
declare(strict_types=1);

session_start();
header("Content-Type: application/json; charset=utf-8");

function json_response(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST") {
    json_response(["ok" => false, "error" => "POST required"], 405);
}

/**
 * LEE EL BODY UNA SOLA VEZ
 */
$raw  = file_get_contents("php://input") ?: "";
$body = json_decode($raw, true);

if (!is_array($body)) {
    // fallback por si algún día mandas form-data
    $body = $_POST;
}

$email    = trim((string)($body["email"] ?? ""));
$password = (string)($body["password"] ?? ""); // NO se “pierde” aquí

if ($email === "" || $password === "") {
    json_response(["ok" => false, "error" => "email and password are required"], 400);
}

require_once __DIR__ . "/../../Database/db_conn.php";
require_once __DIR__ . "/../../src/Repositories/StudentRepository.php";

try {
    $repo = new StudentRepository($conn);
    $student = $repo->findByEmail($email);

    if (!$student) {
        json_response(["ok" => false, "error" => "estudiante no encontrado"], 401);
    }
///////
$hash = (string)($student["student_password"] ?? "");

if ($hash === "") {
  json_response([
    "ok" => false,
    "error" => "student_password missing from repo result",
    "debug" => [
      "keys" => array_keys($student),
    ]
  ], 500);
}

if (!password_verify($password, $hash)) {
  json_response([
    "ok" => false,
    "error" => "password incorrecta",
    "debug" => [
      "pwd_len" => strlen($password),
      "pwd_hex" => bin2hex($password),
      "expected_hex_for_Pass123!" => "5061737331323321",
      "hash_prefix" => substr($hash, 0, 15),
      "hash_len" => strlen($hash)
    ]
  ], 401);
}
//////
    session_regenerate_id(true);
    $_SESSION["student_IdNum"] = (int)$student["student_IdNum"];
    $_SESSION["student_email"] = $student["student_email"];

    json_response([
        "ok" => true,
        "data" => [
            "student_IdNum" => (int)$student["student_IdNum"],
            "student_first_name" => $student["student_first_name"],
            "student_last_name" => $student["student_last_name"],
            "student_email" => $student["student_email"],
            "student_photo" => $student["student_photo"]
        ]
    ], 200);

} catch (Throwable $e) {
    error_log("LOGIN ERROR: " . $e->getMessage());
    json_response(["ok" => false, "error" => "Login failed"], 500);
}