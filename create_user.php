<?php

$filename = 'LOCKED';
if (file_exists($filename)) {
    die("The directory is locked. Please delete the LOCKED file if you are sure you need to run the create_user.php file (this might overwrite existing data in the database if it exists).");
}

require_once('config.php');

function generateRandomString($length = 16) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

$username = "admin";
$password = generateRandomString();

try {
  $sql = "DROP TABLE IF EXISTS staff";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $sql = "CREATE TABLE IF NOT EXISTS staff (
            staff_id INTEGER PRIMARY KEY AUTOINCREMENT,
			staff_username TEXT NOT NULL DEFAULT '',
			staff_email TEXT NOT NULL DEFAULT '',
			staff_password TEXT NOT NULL DEFAULT 0,
			staff_active INTEGER NOT NULL DEFAULT 0,
			staff_rememberme_token TEXT NOT NULL DEFAULT 0,
			seckey TEXT NOT NULL DEFAULT '',
			staff_ip TEXT NOT NULL DEFAULT 0,
			staff_lastlogin TEXT NULL DEFAULT NULL,
			staff_failed_logins INTEGER NOT NULL DEFAULT 0,
			staff_locked DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
			unique_token TEXT NULL DEFAULT NULL
          )";
  $stmt = $conn->prepare($sql);
  $stmt->execute();

  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  $sql = "INSERT INTO staff (staff_username, staff_password) VALUES (:username, :password)";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(':username', $username);
  $stmt->bindParam(':password', $hashedPassword);
  $stmt->execute();

  echo "User created successfully! <br />";
  echo "Username: " . $username . "<br />";
  echo "Password: " . $password . "<br />";
} catch (PDOException $e) {
  echo "Error creating user: " . $e->getMessage() . "<br />";
}

$stmt = null;

$file = fopen('LOCKED', 'w');
if ($file == false) {
    echo "<font color=\"#CC0000\">- Unable to lock the directory to prevent the create_user.php script from being run again. Either manually create a file named <strong>LOCKED</strong> in this directory or delete the create_user.php to be safe.</font><br>";
} else {
    echo "<font color=\"#00CC00\">- Lock file created to prevent the create_user.php file from being run again. You can delete the create_user.php file just to safe.<br>";
    fclose($file);
}

?>