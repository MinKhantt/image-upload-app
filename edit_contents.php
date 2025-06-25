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
    $stmt = $conn->prepare("SELECT contentID, contentDir, createdAt FROM contents WHERE userID = :userID ORDER BY createdAt DESC");
    $stmt->execute([':userID' => $userID]);
    $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch(PDOException $e) {
    $contents = [];
    $error = "Failed to fetch contents: " . $e->getMessage();
  }

  $title = "Edit Your Contents";
  include "./include/header.php";
?>
<section>
  <!-- <h2>Edit/Delete Your Contents</h2> -->
  <?php if (!empty($deleteMsg)): ?>
    <div class="success"><?php echo htmlspecialchars($deleteMsg); ?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <table class="contents-table">
    <thead>
      <tr>
        <th>Image</th>
        <th>Uploaded At</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($contents): ?>
        <?php foreach($contents as $item): ?>
          <tr>
            <td><img src="<?php echo htmlspecialchars($item['contentDir']); ?>" alt="Content"></td>
            <td><?php echo htmlspecialchars($item['createdAt']); ?></td>
            <td>
              <a class="action-btn view-btn" href="each_contents.php?contentID=<?php echo $item['contentID']; ?>">👁️</a>
              <a class="action-btn edit-btn" href="edit_content_item.php?contentID=<?php echo $item['contentID']; ?>">✏️</a>
              <a class="action-btn delete-btn" href="delete_content.php?deleteID=<?php echo $item['contentID']; ?>">🗑️</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="3">No contents uploaded yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</section>

<?php include "./include/footer.php"; ?>