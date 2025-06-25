<?php
session_start();
include "./include/db_config.php";

$userID = $_SESSION["user_id"] ?? null;

if(!$userID) {
  header("location: login.php");
  exit();
}

// Fetch all contents for this user
try {
  $stmt = $conn->prepare("SELECT contentDir, createdAt FROM contents WHERE userID = :userID ORDER BY createdAt DESC");
  $stmt->execute([':userID' => $userID]);
  $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
  $contents = [];
  $error = "Failed to fetch contents: " . $e->getMessage();
}

$title = "My Uploaded Contents";
include "./include/header.php";
?>
<section>
  <!-- <h2>My Uploaded Contents</h2> -->
  <?php if (!empty($error)): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <div class="gallery">
    <?php if ($contents): ?>
      <?php foreach($contents as $item): ?>
        <div class="gallery-item">
          <img src="<?php echo htmlspecialchars($item['contentDir']); ?>" alt="Content">
          <div class="date"><?php echo htmlspecialchars($item['createdAt']); ?></div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="gallery-empty">No contents uploaded yet.</p>
    <?php endif; ?>
  </div>
</section>

<?php include "./include/footer.php"; ?>