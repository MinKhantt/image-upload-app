<?php 
session_start();
include("include/db_config.php");
$error = "";

if (!isset($_SESSION['fail_attempts'])) {
  $_SESSION['fail_attempts'] = 0;
}

$max_attempts = 3; // Maximum login attempts

// Check if locked out and show countdown
if (isset($_SESSION['locked_until']) && time() < $_SESSION['locked_until']) {
  $remaining = $_SESSION['locked_until'] - time();
  $minutes = floor($remaining / 60);
  $seconds = $remaining % 60;
  $error = "Too many failed attempts. Try again after {$minutes}m {$seconds}s.";
} 
// If lockout expired, reset attempts and lock
elseif (isset($_SESSION['locked_until']) && time() >= $_SESSION['locked_until']) {
  unset($_SESSION['locked_until']);
  $_SESSION['fail_attempts'] = 0;
}

// Only process login if not locked out
if (!isset($_SESSION['locked_until']) && $_SERVER["REQUEST_METHOD"] == "POST") {
  $email = filter_input(INPUT_POST,"email", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  $password = filter_input(INPUT_POST,"password", FILTER_SANITIZE_FULL_SPECIAL_CHARS);

  if(empty($email)) {
    $error =  "Enter an email!";
  }
  elseif(empty($password)) {
    $error =  "Enter a password!";
  }
  else {
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($sql);
    try {
      $stmt->execute([ ":email" => $email ]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      if($result && password_verify($password, $result["password"])) {
        // Success: reset fail counter
        $_SESSION['fail_attempts'] = 0;
        unset($_SESSION['locked_until']);
        $_SESSION["user_id"] = $result["id"];
        $_SESSION["user_name"] = $result["fName"] . " " . $result["lName"];
        $_SESSION["user_email"] = $result["email"];
        header("Location: index.php");
        exit();
      } else {
        // Fail: increment counter
        $_SESSION['fail_attempts']++;
        if ($_SESSION['fail_attempts'] >= $max_attempts) {
          $_SESSION['locked_until'] = time() + 3 * 60; // 3 minutes lock
          $error = "Too many failed attempts. Locked for 3 minutes.";
        } else {
          $error = "Invalid email or password! Attempt {$_SESSION['fail_attempts']} of $max_attempts.";
        }
      }
    }
    catch(PDOException $e) {
      $error = "Login failed! " . $e->getMessage();
    } 
  }
}

$title = "Login";
include("include/header.php");
?>

<section>
  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <label for="email">Email: </label>
    <input type="email" id="email" name="email" placeholder="email" <?php if (isset($_SESSION['locked_until']) && time() < $_SESSION['locked_until']) echo 'disabled'; ?>>
    <label for="password">Password: </label>
    <input type="password" id="password" name="password" placeholder="password" <?php if (isset($_SESSION['locked_until']) && time() < $_SESSION['locked_until']) echo 'disabled'; ?>>
    <input type="submit" name="login" value="Login" <?php if (isset($_SESSION['locked_until']) && time() < $_SESSION['locked_until']) echo 'disabled'; ?>>
    <p>
      Don't have an account 👉
      <a href="register.php" class="register">Register</a>
    </p>

    <!-- 🔻 Error message at the bottom of the form -->
    <?php if (!empty($error)) : ?>
      <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

  </form>
</section>
<?php include("include/footer.php"); ?>