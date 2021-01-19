<?php
if(isset($_COOKIE["userID"])) {
  $userID = array_key_first($_COOKIE["userID"]);
}

if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
  //upload ok
  // check to see if target directory exists
  $targetDir = "../uploads/".$userID;
  if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777);
  }
  $tmpName = $_FILES['file']['tmp_name'];
  $targetName = basename($_FILES['file']['name']);
  $target = $targetDir ."/". $targetName;
  if (move_uploaded_file($tmpName, $target)) {
    // move success
    // @TODO add database link to enable more information to be stored, and better management
    echo json_encode("200");
  } else {
    // move failed
    echo json_encode("605");
  }
} else {
  // upload failed
  echo json_encode("604");
}


?>
