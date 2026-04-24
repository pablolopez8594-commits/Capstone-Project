<?php
session_start();
require "Database/db_conn.php";

$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$firstname = trim($_POST['first_name']);
$lastname = trim($_POST['last_name']);
$password = trim($_POST['password']);
$passwordmatch = trim($_POST['passwordmatch']);
$course = trim($_POST['program']);

$target_dir = "uploads/";
$target_file = $target_dir . $email . "-" . time() ;
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
if(isset($_FILES["profilepic"]["tmp_name"]) && $_FILES["profilepic"]["tmp_name"] !== "") {
  $check = getimagesize($_FILES["profilepic"]["tmp_name"]);
  if($check !== false) {
    if (file_exists($target_file)) {
      header("Location: register.php?error=IMPROPER IMAGE FILE NAME CHANGE AND RETRY");
    } else {
      if (move_uploaded_file($_FILES["profilepic"]["tmp_name"], $target_file)) {
        $uploadOk = 1;
        if($password == $passwordmatch){
          $emailcheck = $conn->prepare("SELECT student_IdNum FROM students WHERE student_email = :email");
          $emailcheck->execute([':email' => $email]);

          if ($emailcheck->rowCount() > 0) {
              header("Location: register.php?error=EMAIL ALREADY EXISTS");
              exit();
          } else {
            $hashedpassword = password_hash($password, PASSWORD_DEFAULT);
            
            $insert = "INSERT INTO students (student_first_name, student_last_name, student_email, student_password, student_photo, student_program) VALUES (:firstname, :lastname, :email, :hashedpassword, :target_file, :course)";
            $stmt = $conn->prepare($insert);
            // Bind and Execute
            $stmt->execute([':firstname' => $firstname, ':lastname' => $lastname, ':email' => $email,  ':hashedpassword' => $hashedpassword, ':target_file' => $target_file, ':course' => $course]);
            $_SESSION["student_IdNum"] = (int)$conn->lastInsertId();
            header("Location: index.php");
          } 
        } else {
          header("Location: register.php?error=PASSWORDS DO NOT MATCH TRY AGAIN");
        }
      }
    }
  }  
} else {
  header("Location: register.php?error=IMPROPER IMAGE TRY AGAIN");
}

?>