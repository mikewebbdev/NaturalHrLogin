<?php
// establish dbh object
require_once("../conn.php");
try {
  $dbh = new PDO("mysql:dbname=naturalhr;host=127.0.0.1", $user, $password);
} catch (PDOException $e) {
  echo 'Connection failed: '.$e->getMessage();
  exit();
}

// function for generating Unique IDs (we don't want to just use sequential numbers)
function v4UUID() {
	return sprintf('%04x%04x%04x',

	// 32 bits for "time_low"
	mt_rand(0, 0xffff), mt_rand(0, 0xffff),

	// 16 bits for "time_mid"
	mt_rand(0, 0xffff),

	// 16 bits for "time_hi_and_version",
	// four most significant bits holds version number 4
	mt_rand(0, 0x0fff) | 0x4000,

	// 16 bits, 8 bits for "clk_seq_hi_res",
	// 8 bits for "clk_seq_low",
	// two most significant bits holds zero and one for variant DCE1.1
	mt_rand(0, 0x3fff) | 0x8000,

	// 48 bits for "node"
	mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
	);
}

// get the post object, we have sent it as json so will need to convert to an array
$json = file_get_contents("php://input");
$data = json_decode($json, true);
// we sanitize the user input
$data = filter_var_array($data, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// get the process
$loginProcess = $data["loginProcess"];
// run the required process
switch($loginProcess) {
  case "register":
    register($data, $dbh);
    break;
  case "login":
    login($data, $dbh);
    break;
  case "reset":
    break;
  case "logout":
    logout();
    break;
}

// registration function
function register($data, $dbh) {
  $email = $data["email"];
  // check to see if the email already exists
  $stmt = $dbh->prepare("SELECT userID FROM nhrusers WHERE userEmail = :userEmail LIMIT 1");
  $stmt->bindParam(":userEmail", $email, PDO::PARAM_STR);
  if ($stmt->execute()) {
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($result)) {
      // new user, hash password and insert into table
      $userID = v4UUID();
      // @TODO add function for generating UUID with step for database check to ensure unique value
      // @TODO explore 'snowflake generator' options as an alternative
      // @TODO add encryption layer for user info
      $pass = password_hash($data["pass"], PASSWORD_DEFAULT);
      $stmt = $dbh->prepare("INSERT INTO nhrusers (userID, userFirst, userSecond, userEmail, userPass, userStatus, userRole, userRecovery) VALUES (:userID, :userFirst, :userLast, :userEmail, :userPass, 1, 0, 0)");
      $stmt->bindParam(":userID", $userID, PDO::PARAM_STR);
      $stmt->bindParam(":userFirst", $data["nameFirst"], PDO::PARAM_STR);
      $stmt->bindParam(":userLast", $data["nameLast"], PDO::PARAM_STR);
      $stmt->bindParam(":userEmail", $email, PDO::PARAM_STR);
      $stmt->bindParam(":userPass", $pass, PDO::PARAM_STR);
      if ($stmt->execute()) {
        // user successfully registered, send mail to verify account
        // @TODO add mailout, with unique code embedded in link
        // @TODO once link is clicked by user, account status will change to 1 (active)
        // @TODO once mailout implemented, change db insert to userStatus = 0
        /*
        Suggested format:
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

        require 'path/to/PHPMailer/src/Exception.php';
        require 'path/to/PHPMailer/src/PHPMailer.php';
        require 'path/to/PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = 'smtp.example.com';                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'user@example.com';                     // SMTP username
            $mail->Password   = 'secret';                               // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom('noreply@naturalhr.net', 'Mailer');
            $mail->addAddress('$email', $data["nameFirst"]." ".$data["nameLast"]);     // Add a recipient
            $mail->addReplyTo('info@naturalhr.net', 'Information');

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Thank you for registing';
            $mail->Body    = 'Thank you for registering for an account. Please follow the link below to verify your account';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
        */
        // send success code back to page
        echo json_encode("200");
        exit();
      } else {
        echo "\nPDO::errorInfo():\n";
        print_r($stmt->errorInfo());
        exit();
      }
    } else {
      // user already exists
      echo json_encode("601");
      exit();
    }
    die();
  } else {
    echo "\nPDO::errorInfo():\n";
    print_r($stmt->errorInfo());
    exit();
  }

}

// login function
function login($data, $dbh) {
  $email = $data["email"];
  // check to see if the email already exists
  $stmt = $dbh->prepare("SELECT userID FROM nhrusers WHERE userEmail = :userEmail LIMIT 1");
  $stmt->bindParam(":userEmail", $email, PDO::PARAM_STR);
  if ($stmt->execute()) {
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($result)) {
      // user not in system
      echo json_encode("602");
      exit();
    } else {
      // user found, check password
      $stmt = $dbh->prepare("SELECT userPass FROM nhrusers WHERE userID = :userID LIMIT 1");
      $stmt->bindParam(":userID", $result['userID'], PDO::PARAM_STR);
      if ($stmt->execute()) {
        $hash = $stmt->fetchColumn();
        if (password_verify($data["pass"], $hash)) {
          // password match, log user in
          $bytes = bin2hex(random_bytes(5));
          setcookie("userID[".$result['userID']."]", $bytes, time()+3600*24*30, "/");
          $stmt = $dbh->prepare("UPDATE nhrusers SET userRecovery = :bytes WHERE userID = :userID");
          $stmt->bindParam(":userID", $result["userID"], PDO::PARAM_STR);
          $stmt->bindParam(":bytes", $bytes, PDO::PARAM_STR);
          if ($stmt->execute()) {
            echo json_encode("200");
            exit();
          } else {
            echo "\nPDO::errorInfo():\n";
            print_r($stmt->errorInfo());
            exit();
          }
        } else {
          // password fail
          echo json_encode("603");
          exit();
        }
      } else {
        echo "\nPDO::errorInfo():\n";
        print_r($stmt->errorInfo());
        exit();
      }
    }
  } else {
    echo "\nPDO::errorInfo():\n";
    print_r($stmt->errorInfo());
    exit();
  }
}

?>
