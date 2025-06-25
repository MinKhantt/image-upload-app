<?php 
  $dsn = "mysql:host=localhost:3307;dbname=logindb";
  $db_user = 'root';
  $db_pass = '';

  try {
    $conn = new PDO($dsn, $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  }
  catch (PDOException $e) {
    echo "Can't connect to database!!" . $e->getMessage();
  }
?>