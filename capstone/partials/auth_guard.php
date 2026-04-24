<?php
session_start();

if (!isset($_SESSION["student_IdNum"])) {
    header("Location: /capstone/login.php");
    exit;
}