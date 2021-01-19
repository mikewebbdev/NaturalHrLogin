<?php

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Natural HR Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
  <style>
  #main {
    display: flex;
    flex-direction: column;
    padding-top: 1em;
  }
  #header {
    padding-bottom: 1em;
  }
  #help-links {
    padding-top: 1em;
  }
  input:valid {
    border: 1px solid #ced4da;
  }
  #password-visibility-addon {
    cursor: pointer;
  }
  </style>
</head>
<body>
  <div id="main">
    <div id="header" class="container">
      <div class="row justify-content-center">
        <div class="col-8">
          <img src="gfx/Natural-HR-logo-Aug-16-Transparent-background-LARGE.png" class="img-fluid" alt="Natural HR Logo" >
          <h1>Register for an account</h1>
        </div>
      </div>
    </div>
    <div id="form" class="container">
      <div class="row justify-content-center">
        <div class="col-8">
          <form>
            <input type="hidden" id="loginProcess" value="register">
            <div class="input-group mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
              </div>
              <input type="text" autocomplete="given-name" class="form-control" id="inputNameFirst" placeholder="First name" required>
              <input type="text" autocomplete="family-name" class="form-control" id="inputNameLast" placeholder="Surname" required>
            </div>
            <div class="input-group mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text" id="email-addon"><i class="bi bi-envelope-fill"></i></span>
              </div>
              <input type="email" autocomplete="email" class="form-control" id="inputEmail" placeholder="Email address" required>
            </div>
            <div class="input-group mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text" id="password-addon"><i class="bi bi-key-fill"></i></span>
              </div>
              <input type="password" autocomplete="new-password" class="form-control" id="inputPassword" placeholder="Passphrase" pattern=".{8,}" title="Please set a passphrase of at least 8 characters" required>
              <div class="input-group-append">
                <span class="input-group-text" id="password-visibility-addon"><i id="password-visibility-icon" class="bi bi-eye-slash-fill"></i></span>
              </div>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
          </form>
        </div>
      </div>
    </div>
    <div id="help-links" class="container">
      <div class="row justify-content-center">
        <a href="login.php">Login</a>&nbsp; • &nbsp;<a href="">Forgot password?</a>
      </div>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <script>
  // prevent form from submitting (we want to handle this ourselves) but allow html5 validation
  $("form").on('submit', function(e){
    e.preventDefault();
    validateForm();
  });
  // get form values, check type and post to handler
  function validateForm() {
    let loginProcess = $("#loginProcess").val();
    let nameFirst = $("#inputNameFirst").val();
    let nameLast = $("#inputNameLast").val();
    let email = $("#inputEmail").val();
    let pass = $("#inputPassword").val();
    fetch("db/loginHandler.php", {
      headers: {"Content-Type": "application/json; charset=utf-8"},
      method: "POST",
      body: JSON.stringify({
        loginProcess: loginProcess,
        nameFirst: nameFirst,
        nameLast: nameLast,
        email: email,
        pass: pass,
      }),
    })
    .then(response => response.json())
    .then(data => {
      if (data == 200) {
        // success, show alert prompt to user then send back to home page
        Swal.fire({
          title: "Thank you for registering!",
          text: "Please visit your inbox to verify your account",
        });
        let timeoutID = window.setTimeout("window.location.replace('login.php')", 3*1000);
      } else if (data == 601) {
        // user already exists
        Swal.fire({
          icon: "error",
          title: "User already registered",
          text: "Please check your email and try again",
          html: "If you have forgotton your password, follow the <a href=''>'Forgot Password?'</a> link to reset it",
          footer: "Contact us at support@naturalhr.net if you have any other questions"
        })
      } else {
        // unspecified error/response
        console.debug(data);
      }
    });
  }
  // allow user to turn password visibility on and off
  $("#password-visibility-addon").on("click", function(e){
    e.preventDefault();
    if ($("#inputPassword").attr("type") == "text") {
      $("#inputPassword").attr("type", "password");
      $("#password-visibility-icon").removeClass("bi-eye-fill").addClass("bi-eye-slash-fill");
    } else {
      $("#inputPassword").attr("type", "text");
      $("#password-visibility-icon").addClass("bi-eye-fill").removeClass("bi-eye-slash-fill");
    }
  });
  </script>
</body>
</html>
