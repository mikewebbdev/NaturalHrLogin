<?php
// check to see if login cookie has been set
if(isset($_COOKIE["userID"])) {
  // logged in, check user details
  require_once("conn.php");
  try {
    $dbh = new PDO("mysql:dbname=naturalhr;host=127.0.0.1", $user, $password);
  } catch (PDOException $e) {
    echo 'Connection failed: '.$e->getMessage();
    exit();
  }
  $userID = array_key_first($_COOKIE["userID"]);
  $stmt = $dbh->prepare("SELECT userRecovery FROM nhrusers WHERE userID = :userID");
  $stmt->bindParam(":userID", $userID, PDO::PARAM_STR);
  $stmt->execute();
  $userRecovery = $stmt->fetchColumn();
  $cookieRecovery = array_values($_COOKIE["userID"]);
  if ($userRecovery == $cookieRecovery[0]) {
    // match, safe to proceed
    $stmt = $dbh->prepare("SELECT userFirst, userSecond FROM nhrUsers WHERE userID = :userID");
    $stmt->bindParam(":userID", $userID, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
    // no match, user has been breached, log them out
    // unset cookies
    if (isset($_SERVER['HTTP_COOKIE'])) {
      $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
      foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time()-1000);
        setcookie($name, '', time()-1000, '/');
      }
    }
    header("Location: login.php");
  }
} else {
  // not logged in, redirect to login page
  header("Location: login.php");
  exit();
}
// get uploaded files to user to view
if (is_dir("uploads/".$userID)){
  // we remove the directory references to create a cleaner array
  $fileList = array_diff(scandir("uploads/".$userID), array("..", "."));
} else {
  $fileList = null;
}

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>NaturalHR</title>
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
    /* Page styling */
    body {
    font-size: .875rem;
    }
    .feather {
      width: 16px;
      height: 16px;
      vertical-align: text-bottom;
    }
    /* Sidebar */
    .sidebar {
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      z-index: 100; /* Behind the navbar */
      padding: 0;
      box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
    }
    .sidebar-sticky {
      position: -webkit-sticky;
      position: sticky;
      top: 48px; /* Height of navbar */
      height: calc(100vh - 48px);
      padding-top: .5rem;
      overflow-x: hidden;
      overflow-y: auto; /* Scrollable contents if viewport is shorter than content. */
    }
    .sidebar .nav-link {
      font-weight: 500;
      color: #333;
    }
    .sidebar .nav-link .feather {
      margin-right: 4px;
      color: #999;
    }
    .sidebar .nav-link.active {
      color: #007bff;
    }
    .sidebar .nav-link:hover .feather,
    .sidebar .nav-link.active .feather {
      color: inherit;
    }
    .sidebar-heading {
      font-size: .75rem;
      text-transform: uppercase;
    }
    /* Navbar */
    .navbar-brand {
      padding-top: .75rem;
      padding-bottom: .75rem;
      font-size: 1rem;
      background-color: rgba(0, 0, 0, .25);
      box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
    }
    .navbar .form-control {
      padding: .75rem 1rem;
      border-width: 0;
      border-radius: 0;
    }
    .form-control-dark {
      color: #fff;
      background-color: rgba(255, 255, 255, .1);
      border-color: rgba(255, 255, 255, .1);
    }
    .form-control-dark:focus {
      border-color: transparent;
      box-shadow: 0 0 0 3px rgba(255, 255, 255, .25);
    }
     /* Utilities */
    .border-top { border-top: 1px solid #e5e5e5; }
    .border-bottom { border-bottom: 1px solid #e5e5e5; }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
      <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="#"><img class="img-fluid" src="gfx/Natural-HR-logo-Aug-16-Transparent-background-LARGE.png" /></a>
      <ul class="navbar-nav px-3">
        <li class="nav-item text-nowrap">
          <a class="nav-link" href="logout.php">Sign out</a>
        </li>
      </ul>
    </nav>
    <div class="container-fluid">
      <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
          <div class="sidebar-sticky">
            <ul class="nav flex-column">
              <li class="nav-item">
                <a class="nav-link active" href="#">
                  <span data-feather="home"></span>
                  Dashboard <span class="sr-only">(current)</span>
                </a>
              </li>
            </ul>
          </div>
        </nav>
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
            <h1 class="h2">Dashboard</h1>
          </div>
          <h2>Welcome back, <?php echo $user["userFirst"]; ?>.</h2>
          <br />
          <h4><?php if($fileList == null) {echo "Upload your first file below";} else { echo "View your uploaded files below";}?></h4>
          <div class="container">
            <div class="row">
              <ul class="list-group">
                <!-- @TODO after database link added, convert to table with info such as type/size/desc/preview -->
                <!-- @TODO add controls to edit file info and deletions -->
                <?php foreach ($fileList as $file => $name) : ?>
                <li class="list-group-item">
                  <a href="uploads/<?php echo $userID; ?>/<?php echo $name; ?>" target="_blank"><?php echo $name; ?></a>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
          <div class="container mt-3">
            <div class="row">
              <form>
                <div class="input-group">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="inputGroupFile">
                    <label class="custom-file-label" for="inputGroupFile">Choose file</label>
                  </div>
                  <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit">Upload</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </main>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script>
    // allows file input to show selected file
    bsCustomFileInput.init()
    // prevent form from submitting (we want to handle this ourselves)
    $("form").on('submit', function(e){
      e.preventDefault();
      uploadFile();
    });
    function uploadFile() {
      let file = document.getElementById("inputGroupFile").files[0];
      const formData = new FormData();
      formData.append('file', file);
      const options = {
        method: "POST",
        body: formData,
      }
      if (options && options.headers) {
        delete options.headers['Content-Type'];
      }
      fetch("db/uploadFile.php", options).then(response => response.json())
      .then(data => {
        if (data == 200) {
          // success, show success prompt and reload page
          Swal.fire({
            title: "Upload Success!",
            icon: 'success',
          });
          let timeoutID = window.setTimeout("window.location.replace('index.php')", 3*1000, );
        } else if (data == 604 || data == 605) {
          // failure, show failure prompt
          Swal.fire({
            icon: "error",
            title: "File did not upload",
            text: "Please check your file and try again, you may need to reduce the size if it is a large file.",
            footer: "Contact us at support@naturalhr.net if you have any other questions"
          })
        } else {
          console.debug(data);
        }
      });
    }
    </script>
  </body>
</html>
