<?php 
  session_start();
  include "./include/db_config.php";

  $userID = $_SESSION["user_id"] ?? null;
  if(!$userID) {
    header("location: login.php");
    exit();
  }

  $deleteMsg = "";

  // Only delete if action=ok and deleteID is set
  if (isset($_GET['deleteID'], $_GET['action']) && $_GET['action'] === 'ok') {
    $deleteID = intval($_GET['deleteID']);
    try {
      // 1. Get the file path
      $stmt = $conn->prepare("SELECT contentDir FROM contents WHERE contentID = :contentID AND userID = :userID");
      $stmt->execute([':contentID' => $deleteID, ':userID' => $userID]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($row && file_exists($row['contentDir'])) {
        unlink($row['contentDir']); // 2. Delete the file
      }

      // 3. Delete the database record
      $stmt = $conn->prepare("DELETE FROM contents WHERE contentID = :contentID AND userID = :userID");
      $stmt->execute([':contentID' => $deleteID, ':userID' => $userID]);
      $deleteMsg = "Content deleted successfully.";
      header("Location: edit_contents.php");
      exit();
    } catch(PDOException $e) {
      $deleteMsg = "Delete failed: " . $e->getMessage();
    }
  } elseif (isset($_GET['deleteID'], $_GET['action']) && $_GET['action'] === 'cancel') {
    // Redirect or show cancel message
    header("Location: edit_contents.php");
    exit();
  }

  $title = "Delete Content";
  include 'include/header.php';
?>

<section>
  <div class="delete-container">
    
      <p class="delete-message">Do you really want to delete content?</p>
      <div class="delete-buttons">
        <a href="delete_content.php?deleteID=<?php echo intval($_GET['deleteID']); ?>&action=cancel" class="btn cancel-btn">
          Cancel
        </a>
        <a href="delete_content.php?deleteID=<?php echo intval($_GET['deleteID']); ?>&action=ok" class="btn ok-btn">
          Ok
        </a>
      </div>

  </div>
</section>

<?php  include 'include/footer.php' ?>