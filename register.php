<?php 
  include("include/db_config.php");
  $error = "";

  if($_SERVER["REQUEST_METHOD"] == "POST") {
        $fName = trim(filter_input(INPUT_POST,"fName", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $lName = trim(filter_input(INPUT_POST,"lName", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $email = trim(filter_input(INPUT_POST,"email", FILTER_SANITIZE_EMAIL));
        $password = trim($_POST["password"] ?? "");
        $confirmPsw = trim($_POST["conPassword"] ?? "");

        $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/";

        if(empty($fName)) {
          $error =  "First name is required!";
          return;
        }
        elseif(empty($lName)) {
          $error =  "Last name is required!";
          return;
        }
        elseif(empty($email)) {
          $error =  "Email is required!";
          return;
        }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $error = "Invalid email format!";
          return;
        }
        elseif(empty($password)) {
          $error = "Password is required!";
          return;
        }
        else if(!preg_match($pattern, $password)) {
          $error = "Password must be at least 8 characters and include uppercase, lowercase, number, and special char.";
          return;
        }
        else if($password != $confirmPsw) {
          $error =  "Confirm Password does not match";
          return;
        }
        else {
          $hash = password_hash($password, PASSWORD_DEFAULT);

          
          // Use prepared statement to prevent SQL injection
          $sql = "INSERT INTO users (fName, lName, email, password) VALUES (:fName, :lName, :email, :password)";
          $stmt = $conn->prepare($sql);
            
          try {
            $stmt->execute([
              ':fName' => $fName,
              ':lName' => $lName,
              ':email' => $email,
              ':password' => $hash
            ]);
            
            header("Location: login.php");
            exit();
          } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
              $error = "That email is already taken!";
            } else {
              $error = "Database error: " . $e->getMessage();
            }
          }
        
        }
      }

$title = "Register";
include("include/header.php");
  
?>
<section>
  
  <!-- <h2>Register</h2> -->

  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <label for="fName">First Name: </label>
    <input type="text" id="fName" name="fName" placeholder="first name">
    <label for="lName">Last Name: </label>
    <input type="text" id="lName" name="lName" placeholder="last name">
    <label for="email">Email: </label>
    <input type="email" id="email" name="email" placeholder="email">
    <label for="password">Password: </label>
    <input type="password" id="password" name="password" placeholder="password">
    <label for="conPassword">Confirm Password: </label>
    <input type="password" id="conPassword" name="conPassword" placeholder="confirm password">
    <input type="submit" name="register" value="Register">
    <p>
      Have an account 👉
      <a href="login.php" class="login">Login</a>
    </p>

    <!-- 🔻 Error message at the bottom of the form -->
    <?php if (!empty($error)) : ?>
      <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
  </form>
</section>

<?php include("include/footer.php"); ?>


