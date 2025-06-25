<?php 

  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  include "./include/db_config.php";
  $user_id = $_SESSION["user_id"];

  if(!isset($user_id) || empty($user_id)) {
    // If user is not logged in, redirect to login page
    header("location: login.php");
    exit();
  }

  define('MAX_FILE_SIZE', 5000000); // 5MB

  $upload_error = "";
  $upload_success = "";
  $upload_image = "";
  
  if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {

    $target_dir = "uploads/"; //__DIR__. is a constant that returns the directory of the current file 
    // Ensure uploads directory exists
    if (!is_dir($target_dir)) {
      mkdir($target_dir, 0755, true);
    }

    // Sanitize and make filename unique
    $original_name = basename($_FILES['file']['name']);
    $file_type = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $safe_name = uniqid('img_', true) . '.' . $file_type;
    $target_file = $target_dir . $safe_name;

    // Check if file size is too large
    if($_FILES['file']['size'] > MAX_FILE_SIZE){
      $upload_error = "File size is too large (max " . (MAX_FILE_SIZE/1000000) . "MB)";
    }
    // Only proceed if there are no errors
    else {
      $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
      if(in_array($file_type, $allowed_types)){
        try {
          if(move_uploaded_file($_FILES['file']['tmp_name'], $target_file)){
            $upload_success = "File uploaded successfully";
            $upload_image =  "<img src='$target_file' alt='uploaded image' style='max-width:200px;'>";
            // Save upload info to DB
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

  // upload contents
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>File Upload</title>
  <link rel="stylesheet" href="css/form.css?v=<?php echo time(); ?>">
</head>
<body>
  
  <div class="upload-wrapper">
    
      <form class="upload-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <label for="fileUpload" class="upload-label">Choose your file</label>
        <input type="file" id="fileUpload" name="file" class="upload-input" required>
        <br><br>
        <input type="submit" name="submit" value="Upload" class="upload-btn">
        <!-- Show messages just below the button -->
        <div class="form-messages">
          <?php 
            if($upload_error) {
              echo "<p class='error'>" . htmlspecialchars($upload_error) . "</p>";
            } 
            else if($upload_success) {
              echo "<p class='success'>" . htmlspecialchars($upload_success) . "</p>";
            }
          ?>
        </div>
      </form>
  </div>
</body>
</html>