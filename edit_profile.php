<?php 
session_start();
include "./include/db_config.php";

$error = "";
$success = "";

$user_id = $_SESSION["user_id"];
$editProfileID = $_GET["editProfileID"] ?? null;

// Check if user is logged in
if (!isset($user_id) || empty($user_id)) {
  header("location: login.php");
  exit();
}

// Validate editProfileID
if (!$editProfileID || !is_numeric($editProfileID)) {
  $error = "Invalid profile ID.";
  $currentUser = null;
} elseif ($editProfileID != $user_id) {
  $error = "Unauthorized access.";
  $currentUser = null;
} else {
  $currentUser = getCurrentUser($conn, $editProfileID);

  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["editBtn"])) {
    updateProfile($conn, $editProfileID);
    // Redirect to avoid form resubmission
    if (empty($error)) {
      header("Location: edit_profile.php?editProfileID=" . $editProfileID . "&updated=1");
      exit();
    }
    $currentUser = getCurrentUser($conn, $editProfileID); // refresh data
  }
}

// Show success message after redirect
if (isset($_GET['updated']) && $_GET['updated'] == 1) {
  $success = "Profile updated successfully!";
}

function getCurrentUser($conn, $id) {
  global $error;
  try {
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([":id" => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  } catch(PDOException $e) {
    $error = "Error fetching user data: " . $e->getMessage();
    return null;
  }
}

function updateProfile($conn, $id) {
  global $error;

  $fName = trim(filter_input(INPUT_POST, "fname", FILTER_SANITIZE_FULL_SPECIAL_CHARS));
  $lName = trim(filter_input(INPUT_POST, "lname", FILTER_SANITIZE_FULL_SPECIAL_CHARS));

  if (empty($fName) || empty($lName)) {
    $error = "All fields are required!";
    return;
  }

  try {
    $sql = "UPDATE users SET fName = :fName, lName = :lName WHERE id = :id";
    $stmt = $conn->prepare($sql);
    // Execute the statement
    $stmt->execute([
      ":fName" => $fName,
      ":lName" => $lName,
      ":id" => $id
    ]);
    // Success handled by redirect
    // Update session variables
    $user_name = $fName . " " . $lName;
    $_SESSION["user_name"] = $user_name;
  } catch(PDOException $e) {
    $error = "Error updating profile: " . $e->getMessage();
  }
}

$title = "Edit Profile";
include "./include/header.php";
?>

<section>

  <!-- <h2>Edit Profile</h2> -->

  <?php if ($currentUser): ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?editProfileID=" . $editProfileID; ?>" method="POST">
      <label for="fname">First Name:</label>
      <input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($currentUser['fName']); ?>">

      <label for="lname">Last Name:</label>
      <input type="text" id="lname" name="lname" value="<?php echo htmlspecialchars($currentUser['lName']); ?>" >

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" disabled >

      <p class="note">Note: Email cannot be changed.</p>

      <input type="submit" name="editBtn" value="Update Profile">

      <a href="change_password.php?userID=<?php echo $editProfileID; ?>" class="back-link">Change Password</a>

      <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>
    </form>
  <?php endif; ?>
  
</section>

<?php include "./include/footer.php"; ?>
