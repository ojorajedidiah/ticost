<?php
//session_start();
$_SESSION['clnErr'] = '';
$errMsg = '';
if (isset($_REQUEST['saveRec'])) {
  if (canSave()) {
    $errMsg = createNewClient();
    $_REQUEST['v'] = "update";
  } else {
    $errMsg = $_SESSION['clnErr'];
    $_REQUEST['v'] = "new";
  }
}

if (isset($_POST['updateRec'])) {
  if (canSaveEdit()) {
    $errMsg = updateClient();
    $_REQUEST['v'] = "update";
  } else {
    $errMsg = $_SESSION['clnErr'];
    $_REQUEST['v'] = "edit";
  }
}

if (isset($_POST['deleteRec'])) {
  if (canDisable()) {
    $errMsg = disableClient();
    $_REQUEST['v'] = "update";
  }else {
    $errMsg=$_SESSION['clnErr'];
    $_REQUEST['v'] = "disable";
  }  
}
?>


<div class="content">
  <div class="container-fluid" style="width:70%;">
    <div class="card card-outline card-success">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-8">
            <h5>Clients</h5>
            <?php if ($errMsg != '') {
              echo '<span style="color:red;font-size:15px;font-weight:bold;">' . $errMsg . '</span>';
            } ?>
          </div>
          <div class="col-sm-4">
            <?php if (isset($_REQUEST['v']) && ($_REQUEST['v'] == 'new' || $_REQUEST['v'] == 'edit' || $_REQUEST['v'] == 'disable')) { ?>
              <a href="home.php?p=clients" class="btn btn-danger float-right">Back</a>
            <?php } else { ?>
              <a href="home.php?p=clients&v=new" class="btn btn-info float-right">Create New</a>
            <?php } ?>
          </div>
        </div>
      </div>
      <?php if (isset($_REQUEST['v']) && $_REQUEST['v'] == 'new') { ?>
        <div class="row">
          <div class="card-body card-success">
            <div class="card-header">
              <h3 class="card-title">Create New Client</h3>
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
              <h3 class="card-title">Disable Client</h3>
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
              <h3 class="card-title">Edit Clients</h3>
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
                  <th>Client Name</th>
                  <th>Client Number</th>
                  <th>Client email</th>
                  <th>Client Whatsapp</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php echo getClientRecords(); ?>
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

function createNewClient()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();
      $dat=date("Y-m-d");
      
      $sql = "INSERT INTO clients (clientName,clientNumber,clientEmail,clientDetails,clientWhatsAppNum,clientCreatedDate) 
      VALUES (:clnName,:clnNum,:clnEmail,:clnDetails,:clnWANum,:clnCreatedDate)";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":clnName", $_REQUEST['clientName'], PDO::PARAM_STR);
      $stmt->bindparam(":clnNum", $_REQUEST['clientNumber'], PDO::PARAM_STR);
      $stmt->bindparam(":clnEmail", $_REQUEST['clientEmail'], PDO::PARAM_STR); 
      $stmt->bindparam(":clnDetails", $_REQUEST['clientDetails'], PDO::PARAM_STR);
      $stmt->bindparam(":clnWANum", $_REQUEST['clientWhatsAppNum'], PDO::PARAM_STR);
      $stmt->bindparam(":clnCreatedDate", $dat, PDO::PARAM_STR);   
      
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The new client <b>" . $_REQUEST['clientName'] . "</b> has been created!";
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }

  return ($rtn == '') ? 'No Client Data' : $rtn;
}

function updateClient()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();
      $sql = "UPDATE clients SET clientName=:clnName, clientNumber=:clnNum, clientEmail=:clnEmail, 
        clientDetails=:clnDetails, clientWhatsAppNum=:clnWANum 
        WHERE clientID=:recID";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":recID", $_REQUEST['rid'], PDO::PARAM_INT);
      $stmt->bindparam(":clnName", $_REQUEST['clientName'], PDO::PARAM_STR);
      $stmt->bindparam(":clnNum", $_REQUEST['clientNumber'], PDO::PARAM_STR);
      $stmt->bindparam(":clnEmail", $_REQUEST['clientEmail'], PDO::PARAM_STR); 
      $stmt->bindparam(":clnDetails", $_REQUEST['clientDetails'], PDO::PARAM_STR);
      $stmt->bindparam(":clnWANum", $_REQUEST['clientWhatsAppNum'], PDO::PARAM_STR);
      // $stmt->bindparam(":clnCreatedDate", date("Y-m-d", strtotime($_REQUEST['clientCreatedDate'])), PDO::PARAM_STR); 
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The Client <b>" . $_REQUEST['clientName']. "</b> has been updated";
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }

  return ($rtn == '') ? 'No client data to update' : $rtn;
}

function getClientRecords()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT clientID,clientName,clientNumber,clientEmail,clientWhatsAppNum FROM clients 
        WHERE clientStatus='active' ORDER BY clientID ASC";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        $rID = $row['clientID'];
        
        $rtn .= '<tr><td>' . $row['clientName'] . '</td><td>' . $row['clientNumber'] . '</td>'
          . '<td>' . $row['clientEmail'] . '</td><td>' . $row['clientWhatsAppNum'] . '</td>'
          . '<td><span class="badge badge-complete"><a href="home.php?p=clients&v=disable&rid=' . $rID . '">'
          . '<i class="nav-icon fas fa-user-lock" title="Disable" style="color:red;"></i>'
          . '</a></span><span class="badge badge-edit"><a href="home.php?p=clients&v=edit&rid=' . $rID . '">'
          . '<i class="nav-icon fas fa-edit" title="Edit" style="color:green;"></i></a></span></td></tr>';
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (Exception $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }
  return ($rtn == '') ? '<tr><td colspan="6" style="color:red;text-align:center;"><b>No Clients Available</b></td></tr>' : $rtn;
}

function getSpecificClient($rec)
{
  $rtn = array();
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT clientID,clientName,clientNumber,clientEmail,clientDetails,clientWhatsAppNum,clientCreatedDate 
        FROM clients WHERE clientID=:id";
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

function loadClientStatus($rec)
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT clientID,clientStatus FROM clients WHERE clientID=:id";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->bindparam(":id", $rec, PDO::PARAM_INT);
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        if ($row['clientStatus'] == 'active') {
          $rtn .= '<option selected value="active">Active</option>';
          $rtn .= '<option value="not active">Not Active</option>';
        } else {
          $rtn .= '<option value="active">Active</option>';
          $rtn .= '<option selected value="not active">Not Active</option>';
        }
        $_SESSION['stat']=$row['clientStatus'];
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

function disableClient()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();
      $sql = "UPDATE clients SET clientStatus=:clnStatus WHERE clientID=:recID";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":recID", $_REQUEST['rid'], PDO::PARAM_INT);
      $stmt->bindparam(":clnStatus", $_REQUEST['clientStatus'], PDO::PARAM_STR);
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The Client <b>" . $_REQUEST['clientName']. "</b> has been updated";
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }

  return ($rtn == '') ? 'No client data to updated' : $rtn;
}


///--------------------------------------------------
///-------------- Build Form functions --------------
///--------------------------------------------------
function buildEditForm($id)
{
  $rtn = '';
  $cln = getSpecificClient($id);
  if (is_array($cln) && count($cln) >= 1) {
    $dat=date('Y-m-d',strtotime($cln['clientCreatedDate']));
    $rID=$cln['clientID'];
    
    $rtn = '<div class="row"><div class="col-sm-6"><div class="form-group"><label for="clientName">Client Name</label>';
    $rtn .= '<input type="text" class="form-control" name="clientName" id="clientName" required value="'.$cln['clientName'].'">';
    $rtn .= '<label for="clientNumber">Client Number</label>';
    $rtn .= '<input type="text" class="form-control" name="clientNumber" id="clientNumber" readonly value="'.$cln['clientNumber'].'">';
    $rtn .= '<label for="clientEmail">Client Email</label>';
    $rtn .= '<input type="text" class="form-control" name="clientEmail" id="clientEmail" value="'.$cln['clientEmail'].'">';
    $rtn .= '</div></div>';

    $rtn .= '<div class="col-sm-6"><div class="form-group"><label for="clientWhatsAppNum">Client\'s WhatsAppNum</label>';
    $rtn .= '<input type="text" class="form-control" name="clientWhatsAppNum" id="clientWhatsAppNum" required value="'.$cln['clientWhatsAppNum'].'">';
    $rtn .= '<label for="clientDetails">Client\'s Measurement</label>';
    $rtn .= '<textarea class="form-control" rows="4" name="clientDetails" id="clientDetails" spellcheck="true" required>'.$cln['clientDetails'];
    $rtn .= '</textarea></div></div>';

    $rtn .= '<div class="col-sm-12"><input type="hidden" name="clientCreatedDate" id="clientCreatedDate" value="'.$dat.'">';
    $rtn .= '<input type="hidden" name="clientID" id="clientID" value="'.$rID.'">';
    $rtn .= '<button type="submit" id="updateRec" name="updateRec" class="btn btn-success float-right">Update Client</button></div></div>';    
  }

  // die('the value is '.$rtn);
  $_SESSION['oldRec'] = $cln;
  return $rtn;
}

function buildNewForm()
{
  $rtn = '<div class="row"><div class="col-sm-6"><div class="form-group"><label for="clientName">Client Name</label>';
  $rtn .= '<input type="text" class="form-control" name="clientName" id="clientName" required>';
  $rtn .= '<label for="clientNumber">Client Number</label>';
  $rtn .= '<input type="text" class="form-control" name="clientNumber" id="clientNumber" required>';
  $rtn .= '<label for="clientEmail">Client Email</label>';
  $rtn .= '<input type="text" class="form-control" name="clientEmail" id="clientEmail"></div></div>';

  $rtn .= '<div class="col-sm-6"><div class="form-group"><label for="clientWhatsAppNum">Client\'s WhatsAppNum</label>';
  $rtn .= '<input type="text" class="form-control" name="clientWhatsAppNum" id="clientWhatsAppNum" required>';
  $rtn .= '<label for="clientDetails">Client\'s Measurement</label>';
  $rtn .= '<textarea class="form-control" rows="4" name="clientDetails" id="clientDetails" spellcheck="true" required>';
  $rtn .= '</textarea></div></div>';

  $rtn .= '<div class="col-sm-12">';
  $rtn .= '<button type="submit" id="saveRec" name="saveRec" class="btn btn-success float-right">Create Client</button></div></div>';

  return $rtn;
}

function buildDisableForm($id)
{
  $rtn = '';
  $cln = getSpecificClient($id);
  if (is_array($cln) && count($cln) >= 1) {
    $dat=date('Y-m-d',strtotime($cln['clientCreatedDate']));
    $rID=$cln['clientID'];
    
    $rtn = '<div class="row"><div class="col-sm-4"><div class="form-group"><label for="clientName">Client Name</label>';
    $rtn .= '<input type="text" class="form-control" name="clientName" id="clientName" readonly value="'.$cln['clientName'].'">';
    $rtn .= '<label for="clientNumber">Client Number</label>';
    $rtn .= '<input type="text" class="form-control" name="clientNumber" id="clientNumber" readonly value="'.$cln['clientNumber'].'">';
    $rtn .= '</div></div>';

    $rtn .= '<div class="col-sm-4"><div class="form-group"><label for="clientWhatsAppNum">Client\'s WhatsAppNum</label>';
    $rtn .= '<input type="text" class="form-control" name="clientWhatsAppNum" id="clientWhatsAppNum" readonly value="'.$cln['clientWhatsAppNum'].'">';
    $rtn .= '<label for="clientEmail">Client Email</label>';
    $rtn .= '<input type="text" class="form-control" name="clientEmail" id="clientEmail" readonly value="'.$cln['clientEmail'].'">';
    $rtn .= '</textarea></div></div>';
    
    $rtn .= '<div class="col-sm-4"><div class="form-group">';
    $rtn .= '<input type="hidden" name="clientCreatedDate" id="clientCreatedDate" value="'.$dat.'">';
    $rtn .= '<input type="hidden" name="clientID" id="clientID" value="'.$rID.'">';
    $rtn .= '<label for="clientStatus">Client Status</label><select class="form-control" id="clientStatus" name="clientStatus" required>';
    $rtn .= loadClientStatus($rID). '</select></div>';
    $rtn .= '<div class="form-group"><button type="submit" id="deleteRec" name="deleteRec" class="btn btn-success float-right">Update Client</button></div></div><div>';    
  }

  // die('the value is '.$rtn);
  $_SESSION['oldRec'] = $_SESSION['stat'];
  return $rtn;
}


///--------------------------------------------------
///---------- Data Verification functions -----------
///--------------------------------------------------
function canSave()
{
  $rtn = true;
  try {
    $clnN = $_REQUEST['clientNumber'];
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();
      $sql = "SELECT * FROM clients WHERE clientNumber = '$clnN'";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        $rtn = false;
        trigger_error("This client phone number already exist in the Database!", E_USER_NOTICE);
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

  $cse['clientID'] = $_REQUEST['clientID'];
  $cse['clientName'] = $_REQUEST['clientName'];
  $cse['clientEmail'] = $_REQUEST['clientEmail'];  
  $cse['clientDetails'] = $_REQUEST['clientDetails'];
  $cse['clientWhatsAppNum'] = $_REQUEST['clientWhatsAppNum'];
  $cse['clientCreatedDate'] = $_REQUEST['clientCreatedDate'];
  

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