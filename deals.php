<?php
//session_start();
$_SESSION['msgErr'] = '';
$errMsg = '';
if (isset($_REQUEST['saveRec'])) {
  // if (canSave()) {
    $errMsg = createNewMsg();
    $_REQUEST['v'] = "update";
  // } else {
  //   $errMsg = $_SESSION['msgErr'];
  //   $_REQUEST['v'] = "new";
  // }
}

if (isset($_POST['updateRec'])) {
  if (canSaveEdit()) {
    $errMsg = UpdateMsg();
    $_REQUEST['v'] = "update";
  } else {
    $errMsg = $_SESSION['msgErr'];
    $_REQUEST['v'] = "edit";
  }
}

if (isset($_POST['deleteRec'])) {
  // if (canSaveEdit()) {
  //   $errMsg = UpdateMsg();
  //   $_REQUEST['v'] = "update";
  // }else {
  //   $errMsg=$_SESSION['msgErr'];
  //   $_REQUEST['v'] = "edit";
  // }  
}
?>


<div class="content">
  <div class="container-fluid" style="width:70%;">
    <div class="card card-outline card-success">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-8">
            <h5>Client Deals</h5>
            <?php if ($errMsg != '') {
              echo '<span style="color:red;font-size:15px;">' . $errMsg . '</span>';
            } ?>
          </div>
          <div class="col-sm-4">
            <?php if (isset($_REQUEST['v']) && ($_REQUEST['v'] == 'new' || $_REQUEST['v'] == 'edit' || $_REQUEST['v'] == 'disable')) { ?>
              <a href="home.php?p=deals" class="btn btn-danger float-right">Back</a>
            <?php } else { ?>
              <a href="home.php?p=deals&v=new" class="btn btn-secondary float-right">Create New Deal</a>
            <?php } ?>
          </div>
        </div>
      </div>
      <?php if (isset($_REQUEST['v']) && $_REQUEST['v'] == 'new') { ?>
        <div class="row">
          <div class="card-body card-success">
            <div class="card-header">
              <h3 class="card-title">Create New Deal</h3>
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
              <h3 class="card-title">Disable Deal</h3>
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
              <h3 class="card-title">Edit Deal</h3>
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
                  <th>Created Date</th>
                  <th>Due Date</th>
                  <th>COD</th>
                  <th>Profit</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php echo getDealRecords(); ?>
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

function createNewMsg()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();
      
      $sql = "INSERT INTO deals (msgBody,msgCategory,msgSpecialDate) VALUES (:msgBd,:msgCat,:msgSD)";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":msgBd", $_REQUEST['msgBody'], PDO::PARAM_STR);
      $stmt->bindparam(":msgCat", $_REQUEST['msgCategory'], PDO::PARAM_STR);
      $stmt->bindparam(":msgSD", $_REQUEST['msgSpecialDate'], PDO::PARAM_STR);     
      
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The Message Template <b>[" . substr($_REQUEST['msgBody'],0,25) . "...]</b> has been created!";
        //trigger_error($msg, E_USER_NOTICE);
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }

  return ($rtn == '') ? 'No Message Data' : $rtn;
}

function UpdateMsg()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();
      $sql = "UPDATE deals SET msgBody= :msgB, msgCategory=:msgC, msgSpecialDate=:msgSD WHERE msgID=:recID";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":recID", $_REQUEST['rid'], PDO::PARAM_INT);
      $stmt->bindparam(":msgB", $_REQUEST['msgBody'], PDO::PARAM_STR);
      $stmt->bindparam(":msgC", $_REQUEST['msgCategory'], PDO::PARAM_STR);
      $stmt->bindparam(":msgSD", $_REQUEST['msgSpecialDate'], PDO::PARAM_STR);
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The SMS Message <b>[" . substr($_REQUEST['msgBody'],0,25). "...]</b> has been updated";
        //trigger_error($msg, E_USER_NOTICE);
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }

  return ($rtn == '') ? 'No Message Data' : $rtn;
}

function getDealRecords()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT dealID,clientName,dealCreatedDate,dealDueDate,dealCOD,dealProfit 
        FROM deals d LEFT JOIN clients c ON d.clientID=c.clientID 
        ORDER BY dealID ASC";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        $d1=$row['clientName'];
        $d2=date('d-M-Y', strtotime($row['dealCreatedDate']));
        $d3=date('d-M-Y', strtotime($row['dealDueDate']));
        $d4=number_format(floatval($row['dealCOD']),2);
        $d5=number_format(floatval($row['dealProfit']),2);///'N '.
        $rID = $row['dealID'];
        
        $rtn .= '<tr><td>' . $d1 . '</td><td>' . $d2 . '</td>'
          . '<td>' . $d3 . '</td><td>N ' . $d4 . '</td><td>N ' . $d5 . '</td>'
          . '<td><span class="badge badge-complete"><a href="home.php?p=deals&v=disable&rid=' . $rID . '">'
          . '<i class="nav-icon fas fa-user-lock" title="Disable Message" style="color:red;"></i>'
          . '</a></span><span class="badge badge-edit"><a href="home.php?p=deals&v=edit&rid=' . $rID . '">'
          . '<i class="nav-icon fas fa-edit" title="Edit Message" style="color:blue;"></i></a></span></td></tr>';
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (Exception $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }
  return ($rtn == '') ? '<tr><td colspan="6" style="color:red;text-align:center;"><b>No Deals Available</b></td></tr>' : $rtn;
}

function getSpecificDeal($rec)
{
  $rtn = array();
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT msgID,msgBody,msgCategory,msgSpecialDate FROM deals WHERE msgID = :id";
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

function getClients()
{
  $rtn = '<option value="0">None Selected</option>';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT clientID,clientName FROM clients WHERE clientStatus='active' ORDER BY clientID ASC";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        $d1=$row['clientName'];
        $rID = $row['clientID'];
        
        $rtn .= '<option value="'.$rID.'">' . $d1 . '</option>';
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (Exception $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }
  return ($rtn == '') ? '<option value="0">Empty Client List</option>' : $rtn;
}


///--------------------------------------------------
///-------------- Build Form functions --------------
///--------------------------------------------------
function buildEditForm($id)
{
  $rtn = '';
  $msg = array();
  $msg = getSpecificDeal($id);
  if (is_array($msg) && count($msg) >= 1) {
    $dat=new DateTime($msg['msgSpecialDate']);
    $rtn = '<div class="row"><div class="col-sm-6"><label for="msgCategory">Message Category</label><div class="form-group">';
    $rtn .= '<select class="form-control" id="msgCategory" name="msgCategory" required>';
    $rtn.=($msg['msgCategory'] == "send")? '<option value="send" selected>Ready to Send</option>': '<option value="send">Ready to Send</option>';
    $rtn.=($msg['msgCategory'] == "do not send")? '<option value="do not send" selected>Not Ready to be Sent</option>': '<option value="do not send">Not Ready to be Sent</option>';
    $rtn.=($msg['msgCategory'] == "already sent")? '<option value="already sent" selected>Already Sent</option>': '<option value="already sent">Already Sent</option>';
    $rtn .= '</select></div>';

    $rtn .= '<div class="form-group"><div class="form-group"><label for="msgScheduleDate">Scheduled SMS Date</label>';
    $rtn .= '<input type="date" class="form-control" name="msgScheduleDate" id="msgScheduleDate" value="'.$dat->format('D d F, Y').'"></div></div></div>';

    $rtn .= '<div class="col-sm-6"><div class="form-group"><label for="msgBody">SMS Template</label>';
    $rtn .= '<textarea class="form-control" rows="6" name="msgBody" id="msgBody" spellcheck="true" required>'.$msg['msgBody'].'</textarea></div>';

    $rtn .= '<div class="form-group"><div id="count" class="float-left"><span id="current">0</span><span id="maximum">/120</span></div>';
    $rtn .= '<button type="submit" id="updateRec" name="updateRec" class="btn btn-success float-right">Update Message</button></div></div></div>';
  }

  // die('the value is '.$rtn);
  $_SESSION['oldRec'] = $msg;
  return $rtn;
}

function buildNewForm()
{
  $rtn = '<div class="row"><div class="col-sm-4"><div class="form-group"><label for="clientID">Client Name</label>';
  $rtn .= '<select class="form-control" id="clientID" name="clientID">'.getClients().'</select>';
  $rtn .= '<label for="dealCreatedDate">Deal Date</label>';
  $rtn .= '<input type="date" class="form-control" name="dealCreatedDate" id="dealCreatedDate" onchange="javascript:updateDueDate();" value="'.getToday().'">';
  $rtn .= '<label for="dealDueDate">Deal Delivery Date</label>'; 
  $rtn .= '<input type="date" class="form-control" name="dealDueDate" id="dealDueDate" value="'.getDueDate().'"></div></div>';

  $rtn .= '<div class="col-sm-4"><div class="form-group"><label for="dealDesign">Design Cost</label>';
  $rtn .= '<input type="text" class="form-control" name="dealDesign" id="dealDesign" required>';
  $rtn .= '<label for="dealSewing">Sewing Cost</label>';
  $rtn .= '<input type="text" class="form-control" name="dealSewing" id="dealSewing" required>';
  $rtn .= '<label for="dealMaterial">Material Cost</label>';
  $rtn .= '<input type="text" class="form-control" name="dealMaterial" id="dealMaterial" required></div></div>';

  $rtn .= '<div class="col-sm-4"><div class="form-group"><label for="dealMaterial">Manufacturing Cost</label>';
  $rtn .= '<input type="text" class="form-control" name="dealMenu" id="dealMenu" required>';
  $rtn .= '<label for="dealAmount">Amount Charged</label>';
  $rtn .= '<input type="text" class="form-control" name="dealAmount" id="dealAmount" required>';
  $rtn .= '<label for="dealDescription">Deal Description</label>';
  $rtn .= '<textarea class="form-control" rows="1" name="dealDescription" id="dealDescription" spellcheck="true" required></textarea></div>';

  $rtn .= '<div class="form-group"><div id="profit" class="float-left"></div>';
  $rtn .= '<button type="submit" id="saveRec" name="saveRec" class="btn btn-success float-right">Create Deal</button></div></div></div>';

  return $rtn;
}

function buildDisableForm($id)
{
  $rtn = '';
  $gst = array();
  $msg = getSpecificDeal($id);
  // die('the value is '.$gst['guestVisitDate']);
  if (is_array($msg) && count($msg) >= 1) {
    $rtn = '<div class="row"><div class="col-sm-6"><label for="msgCategory">Message Category</label><div class="form-group">';
    $rtn .= '<select class="form-control" id="msgCategory" name="msgCategory" readonly>';
    $rtn.=($msg['msgCategory'] == "send")? '<option value="send" selected>Ready to Send</option>': '<option value="send">Ready to Send</option>';
    $rtn.=($msg['msgCategory'] == "do not send")? '<option value="do not send" selected>Not Ready to be Sent</option>': '<option value="do not send">Not Ready to be Sent</option>';
    $rtn .= '</select></div>';

    $rtn .= '<div class="form-group"><div class="form-group"><label for="msgSpecialDate">Scheduled SMS Date</label>';
    $rtn .= '<input type="date" readonly class="form-control" name="msgSpecialDate" id="msgSpecialDate" value="'.$msg['msgSpecialDate'].'"></div></div></div>';

    $rtn .= '<div class="col-sm-6"><div class="form-group"><label for="msgBody">SMS Template</label>';
    $rtn .= '<textarea class="form-control" rows="6" name="msgBody" id="msgBody" spellcheck="true" readonly>'.$msg['msgBody'].'</textarea></div>';

    $rtn .= '<div class="form-group"><div id="count" class="float-left"><span id="current">0</span><span id="maximum">/120</span></div>';
    $rtn .= '<button type="submit" id="disableRec" name="disableRec" class="btn btn-success float-right">Disable Message</button></div></div></div>';
    
  }

  // die('the value is '.$rtn);
  $_SESSION['oldRec'] = $gst;
  return $rtn;
}


///--------------------------------------------------
///---------- Data Verification functions -----------
///--------------------------------------------------
function canSave()
{
  $rtn = true;
  try {
    $msgC = $_REQUEST['msgCategory'];
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();
      $sql = "SELECT * FROM deals WHERE msgCategory = '$msgC'";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        $rtn = false;
        trigger_error("This Message already exist in the Database!", E_USER_NOTICE);
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

  $cse['msgBody'] = $_REQUEST['msgBody'];
  $cse['msgScheduleDate'] = $_REQUEST['msgScheduleDate'];
  $cse['msgCategory'] = $_REQUEST['msgCategory'];

  if (count(array_diff($oldRec, $cse)) >= 1) {
    $rtn = true;
  } else {
    $_SESSION['msgErr'] = 'No new data to update!';
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

function getDueDate()
{
  $dt = new DateTime('now');
  $dt->add(new DateInterval('P3W'));
  return $dt->format('Y-m-d');
}

?>