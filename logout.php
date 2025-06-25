<?php 
  session_start();

  $logoutID = $_GET["logoutID"];
  $user_id = $_SESSION["user_id"];

  if(!isset($logoutID) || $logoutID != $user_id) {
    header("location: login.php");
    exit();
  }
  else {
    if(isset($_GET["action"]) && $_GET["action"] == "ok") {
      session_destroy();
      header("location: login.php");
      exit();
    }
    elseif(isset($_GET["action"]) && $_GET["action"] == "cancel") {
      header("location: index.php");
      exit();
    }
  }

  $title = "Logout";
  include "./include/header.php";

?>
<section>

  <div class="logout-container">
    <p class="logout-message">Do you really want to log out?</p>
    <div class="logout-buttons">
      <a href="logout.php?logoutID=<?php echo $user_id ?>&action=cancel" class="btn cancel-btn">
        Cancel
      </a>
      <a href="logout.php?logoutID=<?php echo $user_id ?>&action=ok" class="btn ok-btn">
        Ok
      </a>
    </div>
  </div>

</section>

<?php include "./include/footer.php"; ?>