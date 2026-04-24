<?php
session_start();

if (!isset($_SESSION["student_IdNum"])) {
    header("Location: login.php");
    exit;
}

header("Location: home.php");
exit;