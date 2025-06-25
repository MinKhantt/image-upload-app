<?php 
session_start();
include "./include/db_config.php";

// $userID = $_GET['userID'] ?? ($_SESSION["user_id"] ?? null);

$userID = $_SESSION["user_id"] ?? null;
if(!$userID) {
    header("location: login.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/";

    // Fetch current hashed password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = :userID");
    $stmt->execute([':userID' => $userID]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($old_password, $user['password'])) {
        $error = "Old password is incorrect.";
    } 
    elseif ($old_password === $new_password) {
        $error = "New password cannot be the same as old password.";
    }
    else if(!preg_match($pattern, $new_password)) {
        $error = "Password must be at least 8 characters and include uppercase, lowercase, number, and special char.";
    } 
    elseif ($new_password !== $confirm_password) {
      $error = "New passwords do not match.";
    } 
    else {
        // Update password
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :userID");
        $stmt->execute([':password' => $hashed, ':userID' => $userID]);
        $success = "Password changed successfully!";

        // Optionally, you can redirect to a different page after success
        header("Location: login.php?password_changed=1");
        session_destroy(); // Log out the user after password change
        exit();
    }
}

$title = "Change Password";
include "./include/header.php";
?>
<section>
    <form method="post" class="change-password-form">
        <h2>Change Password</h2>
        
        <label for="old_password">Old Password</label>
        <input type="password" name="old_password" id="old_password" >

        <label for="new_password">New Password</label>
        <input type="password" name="new_password" id="new_password" >

        <label for="confirm_password">Confirm New Password</label>
        <input type="password" name="confirm_password" id="confirm_password" >

        <input type="submit" value="Change Password" class="upload-btn">

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
    </form>
</section>

<?php include "./include/footer.php"; ?>