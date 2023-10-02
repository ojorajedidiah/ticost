<?php
#   Author of the script
#   Name: Adeleke Ojora
#   Email : ojorajedidiah@gmail.com
#   Modified Date: 07-01-2022
#	  Modified by: Adeleke Ojora

session_start();
date_default_timezone_set("Africa/Lagos");
include('models/databaseConnection.class.php');
$ip = $_SERVER['REMOTE_ADDR'];

// var_dump($_REQUEST);
// var_dump($_SESSION);

$msg = '';

if (isset($_REQUEST['login']) && (isset($_REQUEST['vw']) && $_REQUEST['vw'] == 'psswd')) {
  //die('na here e dey');      
  if (!(empty($_SESSION['un'])) && !(empty($_REQUEST['password']))) {

    $db = new connectDatabase(); //    
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      try {
        $dn = $_SESSION['un'];
        $pwd = md5($_REQUEST['password']);

        $sql = "SELECT shID,sh_userName,sh_fullName,canSendSMS,shStatus,firstTimer FROM sh_sec WHERE sh_userName = '$dn' AND sh_password = '$pwd'";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $row = $stmt->fetch();

        if ($row) {
          if ($row['shStatus'] !== 'active') {
            $msg = 'Account deactivated, please contact your Admin';
            $_REQUEST['vw'] = 'error';
          } else {
            $_SESSION['fullname'] = $row['sh_fullName'];
            $_SESSION['expiryTime'] = time() + (3 * 60); //set up session to expire within 1 min
            $_SESSION['username'] = $dn;
            $_SESSION['canSendSMS'] = $row['canSendSMS'];
            $_SESSION['firstTimer'] = $row['firstTimer'];
            $_SESSION['loggedIn'] = 1;
            $_SESSION['pwd'] = $pwd;

            //// Perform insert for login action and insert into logs table
            $action = $_SESSION['fullname'] . ' Logged into TICOST Application';
            $data = [
              'logIP' => $ip,
              'logDate' => date('Y-m-d'),
              'logDescription' => $action,
            ];
            $sql = "INSERT INTO logs (logIP,logDate,logDescription) VALUES (:logIP, :logDate, :logDescription)";
            $stmt = $con->prepare($sql);
            $stmt->execute($data);
            $db->closeConnection();
            //die('i enter here');
            if ($_SESSION['firstTimer'] == 0) {
              die('<head><script language="javascript">window.location="home.php";</script></head>');
            }
          }
        } else {
          $msg = 'Wrong username and password combination!';
          $_REQUEST['vw'] = 'error';
        }
      } catch (PDOException $er) {
        $msg = $er->getMessage() . '<br>Please contact TICOST Team!';
        $_REQUEST['vw'] = 'error';
      }
    } else {
      $msg = $db->getErrorMessage();
      $_REQUEST['vw'] = 'error';
    }
  }
} else if (isset($_REQUEST['chgpwd']) && isset($_REQUEST['pass1']) && isset($_REQUEST['pass2']) && $_REQUEST['vw'] == 'chgpwd') {
  if ($_REQUEST['pass1'] === $_REQUEST['pass2']) {
    $db = new connectDatabase(); //    
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      try {
        $dn = $_SESSION['un'];
        $pwd = $_SESSION['pwd'];
        $npwd = md5($_REQUEST['pass1']);

        $sql = "UPDATE sh_sec SET sh_password=:npwd, firstTimer=0 WHERE sh_userName = '$dn' AND sh_password = '$pwd'";
        $stmt = $con->prepare($sql);
        $stmt->bindparam(":npwd", $npwd, PDO::PARAM_STR);
        $row = $stmt->execute();

        if ($row) {
          $msg .= "The login details has been updated! Login again";
          die('<head><script language="javascript">window.location="home.php";</script></head>');
          // $_SESSION=array();
        }
      } catch (PDOException $er) {
        $msg = $er->getMessage() . '<br>Please contact TICOST Team!';
        $_REQUEST['vw'] = 'error';
      }
      $db->closeConnection();
    } else {
      $msg = ' Passwords are not the same value!';
      $_REQUEST['vw'] = 'error';
    }
  }
} else {
  $_SESSION['un'] = (isset($_REQUEST['username'])) ? $_REQUEST['username'] : '';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Titilivate Couture & Style | Log in</title>

  <link rel="stylesheet" href="assets/css/all.min.css">
  <link rel="stylesheet" href="assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/adminlte.min.css">
</head>

<body class="hold-transition login-page"  style="background-color: azure;">
  <div class="login-box">
    <div class="login-logo">
      <a class="navbar-brand" href=""><img src="assets/img/logo.png" alt="Logo" width="165" height="75" /></a>
      <a href="">
        <h5><b>Staff | Log in</b></h5>
      </a>
    </div>
    <?php if (isset($_SESSION['firstTimer']) && $_SESSION['firstTimer'] == 1) { ?>
      <div class="card card-primary card-outline">
        <div class="card-body login-card-body">
          <p class="login-box-msg" style="font-weight:bold;color:green;">This is your first login, please create a new password</p>
          <span class="badge badge-danger"><?php echo $msg; ?></span>
          <form action="" method="post">
            <div class="input-group mb-3">
              <input type="hidden" name="vw" id="vw" value="chgpwd">
              <input type="password" name="pass1" class="form-control" required placeholder="Enter new password">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-lock"></span>
                </div>
              </div>
            </div>
            <div class="input-group mb-3">
              <input type="password" name="pass2" class="form-control" required placeholder="Retype new password">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-lock"></span>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-8">
              </div>
              <?php if (strlen($msg) > 1) { ?>
                <div class="col-4">
                  <button type="submit" name="chgpwdTA" class="btn btn-danger btn-block">Try Again</button>
                </div>
              <?php } else { ?>
                <div class="col-4">
                  <button type="submit" name="chgpwd" class="btn btn-danger btn-block">Save</button>
                </div>
              <?php } ?>
            </div>
        </div>
        </form>

        <div class="lockscreen-footer text-center">
          <span style="font-size: 8pt;">Powered by <b><a href="https://github.com/ojorajedidiah" target="new">ojorajedidiah</a></b></span>
        </div>
      </div>
  </div>
<?php } else { ?>
  <div class="card card-success card-outline">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Sign in to start your session</p>
      <span class="badge badge-danger"><?php echo $msg; ?></span>

      <form action="" method="post">
        <?php if (!isset($_REQUEST['vw']) || strlen($msg) > 1) { ?>
          <div class="input-group mb-3">
            <input type="hidden" name="vw" id="vw" value="uname">
            <input type="username" name="username" class="form-control" required placeholder="Enter username">
            <!-- <div class="input-group-append">
              <div class="input-group-text">
                <span class="fa fa-user-check"></span>
              </div>
            </div> -->
          </div>
        <?php } else if (isset($_REQUEST['vw']) && $_REQUEST['vw'] == 'uname') { ?>
          <div class="input-group mb-3">
            <input type="hidden" name="vw" id="vw" value="psswd">
            <input type="password" name="password" class="form-control" required placeholder="Password">
            <!-- <div class="input-group-append">
              <div class="input-group-text">
                <span class="fa fa-lock"></span>
              </div>
            </div> -->
          </div>
        <?php } ?>
        <div class="row">
          <div class="col-8">
          </div>
          <?php if (!isset($_REQUEST['vw']) || strlen($msg) > 1) { ?>
            <div class="col-4">
              <button type="submit" name="nxt" class="btn btn-danger btn-block">Next</button>
            </div>
          <?php } elseif (isset($_REQUEST['vw']) && isset($_REQUEST['vw']) == 'uname') { ?>
            <div class="col-4">
              <button type="submit" name="login" class="btn btn-danger btn-block">Sign In</button>
            </div>
          <?php } elseif (strlen($msg) > 1) { ?>
            <div class="col-4">
              <button type="submit" name="tryAgain" class="btn btn-danger btn-block">Try Again</button>
            </div>
          <?php } ?>
        </div>
      </form>

      <div class="lockscreen-footer text-center">
        <span style="font-size: 8pt;">Powered by <b><a href="https://github.com/ojorajedidiah" target="new">ojorajedidiah</a></b></span>
      </div>
    </div>
  </div>
<?php } ?>
</div>


<script src="assets/js/jquery-3.6.0.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/adminlte.min.js"></script>
</body>

</html>