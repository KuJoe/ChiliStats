<?php
define('ChiliAllowed', TRUE);
session_start();
require_once('config.php');

$errorMessage = "";

if (isset($_POST['username']) && isset($_POST['password'])) {

  $username = $_POST['username'];
  $password = $_POST['password'];

  try {
    $sql = "SELECT * FROM staff WHERE staff_username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
      if (password_verify($password, $user['staff_password'])) {
        $_SESSION['staff_id'] = $user['staff_id'];
        header("Location: stats.php");
        exit;
      } else {
        $errorMessage = "Invalid username or password";
      }
    } else {
      $errorMessage = "Invalid username or password";
    }
  } catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
  }
  
  $stmt = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <div class="container">
	<?php if ($errorMessage) { ?>
		<p style="color: red;"><?php echo $errorMessage; ?></p>
	<?php } ?>
    <h1>Login</h1>
    <form action="" method="post"> <label for="username">Username:</label>
      <input type="text" name="username" id="username" required>
      <label for="password">Password:</label>
      <input type="password" name="password" id="password" required>
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>