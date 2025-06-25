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

$title = "View Content";
include "./include/header.php";
?>
<section>
  <div class="single-content-wrapper">
    
    <img src="<?php echo htmlspecialchars($content['contentDir']); ?>" alt="Content" class="single-content-image">
    <div class="single-content-date">Uploaded at: <?php echo htmlspecialchars($content['createdAt']); ?></div>
    <a href="edit_contents.php" class="back-link">&larr; Back to Contents</a>
  </div>
</section>

<?php include "./include/footer.php"; ?>