<?php 
session_start();
include "./include/db_config.php";

$user_id = $_SESSION["user_id"] ?? null;
$user_name = "";

if ($user_id) {
    // Always get the latest user info from the database
    try {
        $stmt = $conn->prepare("SELECT fName, lName FROM users WHERE id = :id");
        $stmt->execute([":id" => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_name = $user ? $user['fName'] . ' ' . $user['lName'] : "User";
    } catch(PDOException $e) {
        $user_name = "User";
    }
}

define('MAX_FILE_SIZE', 5000000); // 5MB

$upload_error = "";
$upload_success = "";
$upload_image = "";

if ($user_id && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $original_name = basename($_FILES['file']['name']);
    $file_type = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $safe_name = uniqid('img_', true) . '.' . $file_type;
    $target_file = $target_dir . $safe_name;

    if ($_FILES['file']['size'] > MAX_FILE_SIZE) {
        $upload_error = "File size is too large (max " . (MAX_FILE_SIZE/1000000) . "MB)";
    } else {
        $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
        if (in_array($file_type, $allowed_types)) {
            try {
                if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                    $upload_success = "File uploaded successfully";
                    $upload_image =  "<img src='$target_file' alt='uploaded image' style='max-width:200px;'>";
                    uploadContent($conn, $user_id, $target_file);
                } else {
                    $upload_error = "Failed to move uploaded file.";
                }
            } catch(Exception $e){
                $upload_error = $e->getMessage();
            }
        } else {
            $upload_error = "File type not allowed";
        }
    }
}

function uploadContent($conn, $user_id, $contentDir) {
    global $upload_error;
    try {
        $sql = "INSERT INTO contents (userID, contentDir) VALUES (:userID, :contentDir)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':userID' => $user_id, ':contentDir' => $contentDir]);
        return true;
    } catch(PDOException $e) {
        $upload_error = "Failed to upload content: " . $e->getMessage();
        return false;
    }
}

$title = "Home - File Upload System";
include "./include/header.php";
?>
<section>
  <?php if ($user_id): ?>
    <h1>Welcome <?php echo htmlspecialchars($user_name); ?></h1>
    <div class="upload-wrapper">
      <form class="upload-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <label for="fileUpload" class="upload-label">Choose your file</label>
        <input type="file" id="fileUpload" name="file" class="upload-input" required>
        <br><br>
        <input type="submit" name="submit" value="Upload" class="upload-btn">
        <div class="form-messages">
          <?php 
            if($upload_error) {
              echo "<p class='error'>" . htmlspecialchars($upload_error) . "</p>";
            } 
            else if($upload_success) {
              echo "<p class='success'>" . htmlspecialchars($upload_success) . "</p>";
              echo $upload_image;
            }
          ?>
        </div>
      </form>
    </div>
  <?php else: ?>
    <h1>Welcome to My Website!</h1>
    <p>
      Please <a href="login.php" class="back-link">log in</a> to upload files and access your content.
    </p>
  <?php endif; ?>

  <!-- Cookie Consent -->
  <?php if(!isset($_COOKIE['cookie_consent'])) { ?>

    <div class="cookie-consent" id="cookieConsent">
      <p>This website uses cookies to enhance your experience. By continuing to use this site, you agree to our <a href="privacy_policy.php">Privacy Policy</a>.</p>
      <button onclick='declineCookies()'>Decline</button>
      <button onclick='acceptCookies()'>Accept</button>
    </div>

    <script>
      function acceptCookies() {

        // Set a cookie to remember the user's consent
        const date = new Date();
        date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000)); // 30 days in milliseconds
        // date.setTime(date.getTime() + (60 * 1000)); // 1 minute
        const ecpires = "expires=" + date.toUTCString();
        document.cookie = "cookie_consent=1; " + ecpires + "; path=/";

        // Hide the cookie consent banner
        document.getElementById("cookieConsent").style.display = "none";
      }

      function declineCookies() {
        // Set a cookie to remember the user's choice
        const date = new Date();
        date.setTime(date.getTime() + (60 * 1000)); // 1 minute
        const expires = "expires=" + date.toUTCString();
        document.cookie = "cookie_consent=0; " + expires + "; path=/";

        // Hide the cookie consent banner
        document.getElementById("cookieConsent").style.display = "none";
      }


    </script>

  <?php } ?>

</section>
<?php include "./include/footer.php"; ?>