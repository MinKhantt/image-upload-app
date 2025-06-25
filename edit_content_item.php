<?php
  session_start();
  include "./include/db_config.php";

  $userID = $_SESSION["user_id"] ?? null;
  if(!$userID) {
    header("location: login.php");
    exit();
  }

  $contentID = $_GET['contentID'] ?? null;
  if(!$contentID || !is_numeric($contentID)) {
    die("Invalid content ID.");
  }

  // Fetch the content item and check ownership
  try {
    $stmt = $conn->prepare("SELECT * FROM contents WHERE contentID = :contentID AND userID = :userID");
    $stmt->execute([':contentID' => $contentID, ':userID' => $userID]);
    $content = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$content) {
      die("Content not found or you do not have permission.");
    }
  } catch(PDOException $e) {
    die("Error: " . $e->getMessage());
  }

  $success = "";
  $error = "";

  // Handle update
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
      mkdir($target_dir, 0755, true);
    }
    $original_name = basename($_FILES['file']['name']);
    $file_type = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $safe_name = uniqid('img_', true) . '.' . $file_type;
    $target_file = $target_dir . $safe_name;
    $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];

    if ($_FILES['file']['size'] > 5000000) {
      $error = "File size is too large (max 5MB)";
    } elseif (!in_array($file_type, $allowed_types)) {
      $error = "File type not allowed";
    } else {
      if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        // Optionally: unlink($content['contentDir']); // delete old file
        try {
          $stmt = $conn->prepare("UPDATE contents SET contentDir = :contentDir WHERE contentID = :contentID AND userID = :userID");
          $stmt->execute([
            ':contentDir' => $target_file,
            ':contentID' => $contentID,
            ':userID' => $userID
          ]);
          $success = "Content updated successfully!";
          // Refresh content info
          $content['contentDir'] = $target_file;
        } catch(PDOException $e) {
          $error = "Update failed: " . $e->getMessage();
        }
      } else {
        $error = "Failed to upload new file.";
      }
    }
  }

  $title = "Edit Content Item";
  include "./include/header.php";
?>
<section>
  <div class="edit-content-wrapper">
    <?php if ($error): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <div class="edit-content-flex">
      <div class="current-image-block">
        <div class="current-image-label">Current Image:</div>
        <img src="<?php echo htmlspecialchars($content['contentDir']); ?>" alt="Current Content" class="current-image-preview">
      </div>
      <form action="" method="post" enctype="multipart/form-data" class="edit-content-form">
        <label for="file" class="edit-content-label">Change Image</label>
        <input type="file" name="file" id="file" class="edit-content-input" required>
        <input type="submit" value="Update" class="edit-content-btn">
        <a href="edit_contents.php" class="edit-content-back">&larr; Back to Contents</a>
      </form>
    </div>
  </div>
</section>

<?php include "./include/footer.php"; ?>