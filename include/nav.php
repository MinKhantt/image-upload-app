<?php
if (!isset($user_id)) $user_id = $_SESSION["user_id"] ?? null;
$current = basename($_SERVER['PHP_SELF']);
?>
<nav>
    <a href="index.php" class="<?php echo $current == 'index.php' ? 'current' : '' ?>">🏠</a>
    <a href="edit_profile.php?editProfileID=<?php echo $user_id; ?>" class="<?php echo $current == 'edit_profile.php' ? 'current' : '' ?>">👤</a>
    <a href="contents.php" class="<?php echo $current == 'contents.php' ? 'current' : '' ?>">📂</a>
    <a href="edit_contents.php" class="<?php echo $current == 'edit_contents.php' ? 'current' : '' ?>">📝</a>
    <?php if ($user_id): ?>
        <a href="logout.php?logoutID=<?php echo $user_id; ?>">👋</a>
    <?php else: ?>
        <a href="login.php" class="<?php echo $current == 'login.php' ? 'current' : '' ?>">🔑</a>
    <?php endif; ?>
</nav>