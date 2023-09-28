<?php
//session_start();
// die(var_dump($_REQUEST));
$_SESSION['clnErr'] = '';
$errMsg = '';
if (isset($_REQUEST['saveRec'])) {
  if (canSave()) {
    $errMsg = createNewUser();
    $_REQUEST['v'] = "update";
  } else {
    $errMsg = $_SESSION['clnErr'];
    $_REQUEST['v'] = "new";
  }
}

if (isset($_POST['updateRec'])) {
  if (canSaveEdit()) {
    $errMsg = updateUser();
    $_REQUEST['v'] = "update";
  } else {
    $errMsg = $_SESSION['clnErr'];
    $_REQUEST['v'] = "edit";
  }
}

if (isset($_POST['deleteRec'])) {
  if (canDisable()) {
    $errMsg = disableUser();
    $_REQUEST['v'] = "update";
  }else {
    $errMsg=$_SESSION['clnErr'];
    $_REQUEST['v'] = "disable";
  }  
}
?>


<div class="content">
  <div class="container-fluid" style="width:50%;">
    <div class="card card-outline card-success">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-8">
            <h5>Users</h5>
            <?php if ($errMsg != '') {
              echo '<span style="color:red;font-size:15px;font-weight:bold;">' . $errMsg . '</span>';
            } ?>
          </div>
          <div class="col-sm-4">
            <?php if (isset($_REQUEST['v']) && ($_REQUEST['v'] == 'new' || $_REQUEST['v'] == 'edit' || $_REQUEST['v'] == 'disable')) { ?>
              <a href="home.php?p=users" class="btn btn-danger float-right">Back</a>
            <?php } else { ?>
              <a href="home.php?p=users&v=new" class="btn btn-info float-right">Create New</a>
            <?php } ?>
          </div>
        </div>
      </div>
      <?php if (isset($_REQUEST['v']) && $_REQUEST['v'] == 'new') { ?>
        <div class="row">
          <div class="card-body card-success">
            <div class="card-header">
              <h3 class="card-title">Create New User</h3>
            </div>
            <form method="post" target="">
              <?php echo buildNewForm(); ?>
            </form>
          </div>
        </div>
      <?php } else if (isset($_REQUEST['v']) && $_REQUEST['v'] == 'disable') { ?>
        <div class="row">
          <div class="card-body card-success">
            <div class="card-header">
              <h3 class="card-title">Disable User</h3>
            </div>
            <form method="post" target="">
              <?php echo buildDisableForm($_REQUEST['rid']) ?>
            </form>
          </div>
        </div>
      <?php } else if (isset($_REQUEST['v']) && $_REQUEST['v'] == 'edit') { ?>
        <div class="row">
          <div class="card-body card-success">
            <div class="card-header">
              <h3 class="card-title">Edit User</h3>
            </div>
            <form method="post" target="">
              <?php echo buildEditForm($_REQUEST['rid']); ?>
            </form>
          </div>
        </div>
      <?php } else { ?>
        <div class="row">
          <div class="card-body">
            <table id="grids" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>User Name</th>
                  <th>FullName</th>
                  <th>Expiry Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php echo getUserRecords(); ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

<?php
///--------------------------------------------------
///------------- General DML functions --------------
///--------------------------------------------------

function createNewUser()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();
      $dtt= new DateTime(strval($_REQUEST['expiredDate']));
      $dat=date("Y-m-d"); $expDate=$dtt->format('Y-m-d');
      $pd=md5($_REQUEST['userPassword']);
      
      $sql = "INSERT INTO sh_sec (sh_username,sh_password,sh_fullname,shCreatedDate,shExpiredDate) 
      VALUES (:usrName,:usrPwd,:usrFName,:usrCDate,:usrEDate)";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":usrName", $_REQUEST['userName'], PDO::PARAM_STR);
      $stmt->bindparam(":usrPwd", $pd, PDO::PARAM_STR);
      $stmt->bindparam(":usrFName", $_REQUEST['userFullName'], PDO::PARAM_STR);
      $stmt->bindparam(":usrEDate", $expDate, PDO::PARAM_STR);
      $stmt->bindparam(":usrCDate", $dat, PDO::PARAM_STR);   
      
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The new user <b>" . $_REQUEST['userFullName'] . "</b> has been created!";
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }

  return ($rtn == '') ? 'No User Data' : $rtn;
}

function updateUser()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();
      $dttt=date("Y-m-d", strtotime($_REQUEST['expiredDate']));
      
      $sql = "UPDATE sh_sec SET shExpiredDate=:expDate, canSendSMS=:usrSend WHERE shID=:recID";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":recID", $_REQUEST['rid'], PDO::PARAM_INT);
      $stmt->bindparam(":expDate", $dttt, PDO::PARAM_STR);
      $stmt->bindparam(":usrSend", $_REQUEST['canSend'], PDO::PARAM_INT);
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The User <b>" . $_REQUEST['userFullName']. "</b> has been updated";
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }

  return ($rtn == '') ? 'No user data to update' : $rtn;
}

function getUserRecords()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT shID,sh_username,sh_fullname,shExpiredDate FROM sh_sec ORDER BY shID ASC";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        $rID = $row['shID'];
        
        $rtn .= '<tr><td>' . $row['sh_username'] . '</td><td>' . $row['sh_fullname'] . '</td>'
          . '<td>' . $row['shExpiredDate'] . '</td>'
          . '<td><span class="badge badge-complete"><a href="home.php?p=users&v=disable&rid=' . $rID . '">'
          . '<i class="nav-icon fas fa-user-lock" title="Disable" style="color:red;"></i>'
          . '</a></span><span class="badge badge-edit"><a href="home.php?p=users&v=edit&rid=' . $rID . '">'
          . '<i class="nav-icon fas fa-edit" title="Edit" style="color:green;"></i></a></span></td></tr>';
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (Exception $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }
  return ($rtn == '') ? '<tr><td colspan="6" style="color:red;text-align:center;"><b>No Users Available</b></td></tr>' : $rtn;
}

function getSpecificUser($rec)
{
  $rtn = array();
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT shID,sh_username,sh_fullname,shExpiredDate,shStatus,canSendSMS FROM sh_sec WHERE shID=:id";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->bindparam(":id", $rec, PDO::PARAM_INT);
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        $rtn = $row;
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }
  return $rtn;
}

function loadUserStatus($rec)
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT shID,shStatus FROM sh_sec WHERE shID=:id";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->bindparam(":id", $rec, PDO::PARAM_INT);
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        if ($row['shStatus'] == 'active') {
          $rtn .= '<option selected value="active">Active</option>';
          $rtn .= '<option value="not active">Not Active</option>';
        } else {
          $rtn .= '<option value="active">Active</option>';
          $rtn .= '<option selected value="not active">Not Active</option>';
        }
        $_SESSION['stat']=$row['shStatus'];
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }
  return $rtn;
}

function disableUser()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();
      $sql = "UPDATE sh_sec SET shStatus=:usrStatus WHERE shID=:recID";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":recID", $_REQUEST['rid'], PDO::PARAM_INT);
      $stmt->bindparam(":usrStatus", $_REQUEST['userStatus'], PDO::PARAM_STR);
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The User <b>" . $_REQUEST['userName']. "</b> has been updated";
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }

  return ($rtn == '') ? 'No user data to updated' : $rtn;
}


///--------------------------------------------------
///-------------- Build Form functions --------------
///--------------------------------------------------
function buildEditForm($id)
{
  $rtn = '';
  $usr = getSpecificUser($id);
  if (is_array($usr) && count($usr) >= 1) {
    $dat=date('Y-m-d',strtotime($usr['shExpiredDate']));
    $snd=($usr['canSendSMS'] == 1)?"allowed":"not allowed";
    //sh_username,sh_password,sh_fullname,shCreatedDate,shExpiredDate,canSendSMS
    // class="form-check-input"
    $rID=$usr['shID'];
    
    $rtn = '<div class="row"><div class="col-sm-6"><div class="form-group"><label for="userName">User Name</label>';
    $rtn .= '<input type="text" class="form-control" name="userName" id="userName" readonly value="'.$usr['sh_username'].'">';
    $rtn .= '<label for="userFullName">User FullName</label>';
    $rtn .= '<input type="text" class="form-control" name="userFullName" id="userFullName" readonly value="'.$usr['sh_fullname'].'">';
    $rtn .= '</div></div>';

    $rtn .= '<div class="col-sm-6"><div class="form-group"><label for="expiredDate">Expiration Date</label>';
    $rtn .= '<input type="date" class="form-control" name="expiredDate" id="expiredDate" required value="'.$usr['shExpiredDate'].'">';
    $rtn .= '<label for="canSend">Can send Notifications</label>';
    $rtn .= '<select name="canSend" id="canSend" class="custom-select">'.buildSendNotification($snd).'</select>';
    $rtn .= '</div></div>';

    $rtn .= '<div class="col-sm-12"><input type="hidden" name="userStatus" id="userStatus" value="'.$usr['shStatus'].'">';
    $rtn .= '<input type="hidden" name="shID" id="shID" value="'.$rID.'">';
    $rtn .= '<button type="submit" id="updateRec" name="updateRec" class="btn btn-success float-right">Update User</button></div></div>';
  }

  // die('the value is '.$rtn);
  $_SESSION['oldRec'] = $usr;
  return $rtn;
}

function buildNewForm()
{
  //sh_username,sh_password,sh_fullname,shCreatedDate,shExpiredDate
  $rtn = '<div class="row"><div class="col-sm-6"><div class="form-group"><label for="userName">New User Name</label>';
  $rtn .= '<input type="text" class="form-control" name="userName" id="userName" required>';  
  $rtn .= '<label for="userFullName">User FullName</label>';
  $rtn .= '<input type="text" class="form-control" name="userFullName" id="userFullName"></div></div>';

  $rtn .= '<div class="col-sm-6"><div class="form-group"><label for="userPassword">User Password</label>';
  $rtn .= '<input type="password" class="form-control" name="userPassword" id="userPassword" required>';
  $rtn .= '<label for="expiredDate">Expiration Date</label>';
  $rtn .= '<input type="date" class="form-control" name="expiredDate" id="expiredDate" required>';
  $rtn .= '</textarea></div></div>';

  $rtn .= '<div class="col-sm-12">';
  $rtn .= '<button type="submit" id="saveRec" name="saveRec" class="btn btn-success float-right">Create User</button></div></div>';

  return $rtn;
}

function buildDisableForm($id)
{
  $rtn = '';
  $usr = getSpecificUser($id);
  if (is_array($usr) && count($usr) >= 1) {
    $dat=date('Y-m-d',strtotime($usr['shExpiredDate']));
    //sh_username,sh_password,sh_fullname,shCreatedDate,shExpiredDate,canSendSMS
    $rID=$usr['shID'];
    
    $rtn = '<div class="row"><div class="col-sm-6"><div class="form-group"><label for="userName">User Name</label>';
    $rtn .= '<input type="text" class="form-control" name="userName" id="userName" readonly value="'.$usr['sh_username'].'">';
    $rtn .= '<label for="userFullName">User FullName</label>';
    $rtn .= '<input type="text" class="form-control" name="userFullName" id="userFullName" readonly value="'.$usr['sh_fullname'].'">';
    $rtn .= '</div></div>';

    $rtn .= '<div class="col-sm-6"><div class="form-group"><label for="expiredDate">Expiration Date</label>';
    $rtn .= '<input type="text" class="form-control" name="expiredDate" id="expiredDate" readonly value="'.$usr['shExpiredDate'].'">';
    $rtn .= '<label for="clientStatus">Client Status</label><select class="form-control" id="userStatus" name="userStatus" required>';
    $rtn .= loadClientStatus($rID). '</select></div></div>';

    $rtn .= '<div class="col-sm-12"><input type="hidden" name="clientCreatedDate" id="clientCreatedDate" value="'.$dat.'">';
    
    $rtn .= '<button type="submit" id="updateRec" name="updateRec" class="btn btn-success float-right">Update User</button></div></div>';
    
    $rtn .= '<div class="col-sm-4"><div class="form-group">';
    $rtn .= '<input type="hidden" name="clientCreatedDate" id="clientCreatedDate" value="'.$dat.'">';
    $rtn .= '<input type="hidden" name="shID" id="shID" value="'.$rID.'">';
    $rtn .= '</div>';



    $rtn .= '<div class="form-group"><button type="submit" id="deleteRec" name="deleteRec" class="btn btn-success float-right">Update Client</button></div></div><div>';    
  }

  // die('the value is '.$rtn);
  $_SESSION['oldRec'] = $_SESSION['stat'];
  return $rtn;
}

function buildSendNotification($can)
{
  $rtn='';
  if ($can == 'allowed') {
    $rtn='<option value="1" selected>Authorised</option><option value="0">Disallowed</option>';
  } else {
    $rtn='<option value="1">Authorised</option><option value="0" selected>Disallowed</option>';
  }  
  return $rtn;
}


///--------------------------------------------------
///---------- Data Verification functions -----------
///--------------------------------------------------
function canSave()
{
  $rtn = true;
  try {
    $clnN = $_REQUEST['userFullName'];
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();
      $sql = "SELECT * FROM sh_sec WHERE sh_fullname = '$clnN'";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        $rtn = false;
        trigger_error("This user already exist in the Database!", E_USER_NOTICE);
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }

  return $rtn;
}

function canSaveEdit()
{
  $rtn = false;
  $cse = array();
  $oldRec = $_SESSION['oldRec'];
  // var_dump($oldRec);
  // var_dump($_REQUEST);
  // die();

  $cse['shID'] = $_REQUEST['shID'];
  $cse['sh_username'] = $_REQUEST['userName'];
  $cse['sh_fullname'] = $_REQUEST['userFullName'];  
  $cse['shStatus'] = $_REQUEST['userStatus'];
  $cse['canSendSMS'] = $_REQUEST['canSend'];
  $cse['shExpiredDate'] = $_REQUEST['expiredDate'];
  

  if (count(array_diff($oldRec, $cse)) >= 1) {
    $rtn = true;
  } else {
    $_SESSION['clnErr'] = 'No new data to update!';
    $rtn = false;
  }
  return $rtn;
}

function canDisable()
{
  $rtn = false;
  $oldRec = $_SESSION['oldRec'];  

  if ($oldRec != $_REQUEST['clientStatus']) {
    $rtn = true;
  } else {
    $_SESSION['clnErr'] = 'No new data to update!';
    $rtn = false;
  }
  return $rtn;
}

///--------------------------------------------------
///------------ general-purpose functions -----------
///--------------------------------------------------

function getToday()
{
  $dt = new DateTime('now');
  return $dt->format('Y-m-d');
}

?>