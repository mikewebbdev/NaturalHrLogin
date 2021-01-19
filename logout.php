<?php
// establish dbh object
require_once("conn.php");
try {
  $dbh = new PDO("mysql:dbname=naturalhr;host=127.0.0.1", $user, $password);
} catch (PDOException $e) {
  echo 'Connection failed: '.$e->getMessage();
  exit();
}
// unset cookies
$userID = array_key_first($_COOKIE["userID"]);
if (isset($_SERVER['HTTP_COOKIE'])) {
  $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
  foreach($cookies as $cookie) {
    $parts = explode('=', $cookie);
    $name = trim($parts[0]);
    setcookie($name, '', time()-1000);
    setcookie($name, '', time()-1000, '/');
  }
}
// update user recovery
$stmt = $dbh->prepare("UPDATE nhrusers SET userRecovery = 0 WHERE userID = :userID");
$stmt->bindParam(":userID", $userID, PDO::PARAM_STR);
$stmt->execute();
header("Location: login.php");
?>
