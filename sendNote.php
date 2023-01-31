<?php

//session_start();
$_SESSION['msgSend'] = '';
$errMsg = '';
// var_dump($_REQUEST);

if (isset($_POST['updateRec'])) {
  if (canSaveEdit()){
    $errMsg = updateDeal();
    $_REQUEST['v'] = "update";
  } else {
    $errMsg = $_SESSION['msgSend'];
    $_REQUEST['v'] = "edit";
  }
  
}
?>


<div class="content">
  <div class="container-fluid" style="width:70%;">
    <div class="card card-outline card-success">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-6">
            <h5>Deal Notifications</h5>
            <?php if ($errMsg != '') {
              echo '<span style="color:red;font-size:15px;">' . $errMsg . '</span>';
            } ?>
          </div>
          <div class="col-sm-6">
            <?php if (isset($_REQUEST['v']) && $_REQUEST['v'] == 'send') { ?>
              <span style="color:red;font-size:15px;font-weight:bold;"><?php echo sendNote($_REQUEST['rid']); ?></span>
            <?php } elseif (isset($_REQUEST['v']) && $_REQUEST['v'] == 'edit')  { ?>
              <a href="home.php?p=sendNote" class="btn btn-danger float-right">Back</a>
            <?php } ?>
          </div>
        </div>
      </div>
      <?php if (isset($_REQUEST['v']) && $_REQUEST['v'] == 'edit') { ?>
        <div class="row">
          <div class="card-body card-success">
            <div class="card-header">
              <h3 class="card-title">Update Deal</h3>
            </div>
            <form method="post" target="">
              <?php echo buildUpdateForm($_REQUEST['rid']); ?>
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

function updateDeal()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "UPDATE deals SET dealDescription=:dlDesc WHERE dealID=:recID";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":recID", $_REQUEST['rid'], PDO::PARAM_INT);
      $stmt->bindparam(":dlDesc", $_REQUEST['dealDescription'], PDO::PARAM_STR);
      // $stmt->bindparam(":dlStatus", $_REQUEST['dealStatus'], PDO::PARAM_STR);
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The deal <b>[" . substr($_REQUEST['dealDescription'],0,15). "...]</b> has been updated";
        //trigger_error($msg, E_USER_NOTICE);
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }

  return ($rtn == '') ? 'No Deal Data' : $rtn;
}

function getDealRecords()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT dealID,clientName,dealCreatedDate,dealDueDate,dealCOD,dealProfit 
        FROM deals d LEFT JOIN clients c ON d.clientID=c.clientID WHERE dealStatus='qc passed'
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
          . '<td>' . $d3 . '</td><td>&#8358; ' . $d4 . '</td><td>&#8358; ' . $d5 . '</td>'
          . '<td style="text-align:center;"><span class="badge"><a href="home.php?p=sendNote&v=send&rid=' . $rID . '">'
          . '<i class="nav-icon fas fa-share-square" title="Send Email" style="color:green;"></i>'
          . '</a></span><span class="badge"><a href="home.php?p=sendNote&v=edit&rid=' . $rID . '">'
          . '<i class="nav-icon fas fa-edit" title="Update Deal" style="color:blue;"></i></a></span>'
          . '</td></tr>';
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

      $sql = "SELECT dealID,c.clientID,dealCreatedDate,dealDueDate,
        dealDescription,dealDesign,dealSewing,dealMaterial,dealManu,dealCOD,dealAmount
        FROM deals d LEFT JOIN clients c ON d.clientID=c.clientID WHERE dealID=:id";
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

function getClients($cl=0)
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
        $d1 = $row['clientName'];
        $rID = $row['clientID'];

        if (isset($cl) && $cl == $rID) {
          $rtn .= '<option selected value="' . $rID . '">' . $d1 . '</option>';
        } else {
          $rtn .= '<option value="' . $rID . '">' . $d1 . '</option>';
        }
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

function getDealStatus($did=0)
{
  $rtn = '';
  $arr=array('created','agreed','active','completed','deleted','qc passed','notified');
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT dealStatus FROM deals WHERE dealID=:id";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));

      $stmt->bindparam(":id", $did, PDO::PARAM_INT);      
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute();

      $row=$stmt->fetch();
      $d1 = $row['dealStatus'];

      for ($cnt=0; $cnt<count($arr);$cnt++) {
        if (isset($did) && $arr[$cnt] == $d1) {
          $rtn .= '<option selected value="' . $arr[$cnt] . '">' . $arr[$cnt] . '</option>';
        } else {
          $rtn .= '<option value="' . $arr[$cnt] . '">' . $arr[$cnt] . '</option>';
        }
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (Exception $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }
  return ($rtn == '') ? '<option value="0">Empty Status List</option>' : $rtn;
}

function sendNote($id=0)
{
  $rtn='SMS sent to sampling SMS';
  if ($id >= 0){
    $notf=getRecipient($id);
    try
    {
      $to=$notf['clientEmail'];
      $subj='Your dress is ready for Pickup/Delivery';
      $msg=getEmailBody('pickup');
      $hdrs='From:titilivate@gmail.com';

      mail($to, $subj, $msg, $hdrs);
      $rtn="Email sent successfully to ". $notf['clientName'];

    } catch(Exception $e){
      trigger_error($e->getMessage(), E_USER_NOTICE);
    }
  }
  return $rtn;
}

function getRecipient($id=0)
{
  $rtn = array();
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT clientName,clientWhatsAppNum,clientEmail 
        FROM deals d LEFT JOIN clients c ON d.clientID=c.clientID WHERE dealID=:id";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->bindparam(":id", $id, PDO::PARAM_INT);
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        $rtn['clientWhatsAppNum']=$row['clientWhatsAppNum'];
        $rtn['clientName']=$row['clientName']; 
        $rtn['clientEmail']=$row['clientEmail'];     
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (Exception $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }
  return $rtn;
}

function getEmailBody($ky='')
{
  $rtn='';
  if ($ky=='pickup'){
    $rtn='We are excited to inform you that your dress is ready for pickup. 
    <br><br>We have painstakingly crafted a beautiful piece for you, nitted with lots of love and care. 
    We were careful in detailing every curves and pieces as defined in your sizing. 
    <br><br>It will be our pleasure to have your in our office for sizing and any other 
    top-ups you may required to fine-fune this gorgeous dress.<br><br>See you soon.
    <br><b>Please reply to this Whatsapp Number: 08085719632</b>
    <br><br><br>Yours sincerely,<br><b>Titilivate Courture & style</b>';
  }
  return $rtn;
}

///--------------------------------------------------
///-------------- Build Form functions --------------
///--------------------------------------------------

function buildUpdateForm($id)
{
  $rtn = '';
  $deal = array();
  $deal = getSpecificDeal($id);
  // die('the value is '.$gst['guestVisitDate']);
  if (is_array($deal) && count($deal) >= 1) {

    $rtn = '<div class="row"><div class="col-sm-4"><div class="form-group"><label for="clientID">Client Name</label>';
    $rtn .= '<select class="form-control" id="clientID" name="clientID" disabled>'.getClients($id).'</select>';
    $rtn .= '<label for="dealCreatedDate">Deal Date</label>';
    $rtn .= '<input type="date" class="form-control" readonly value="'.$deal['dealCreatedDate'].'">';
    $rtn .= '<label for="dealDueDate">Deal Delivery Date</label>'; 
    $rtn .= '<input type="date" class="form-control" name="dealDueDate" readonly id="dealDueDate" value="'.$deal['dealDueDate'].'"></div></div>';

    $rtn .= '<div class="col-sm-4"><div class="form-group"><label for="dealDesign">Total Cost of Design</label>';
    $rtn .= '<input type="text" class="form-control" style="text-align:right;" readonly value="'.number_format($deal['dealCOD'],2).'">';
    $rtn .= '<label for="dealAmount">Amount Charged</label>';
    $rtn .= '<input type="text" class="form-control" style="text-align:right;" readonly value="'.number_format($deal['dealAmount'],2).'">';
    $rtn .= '<label for="dealMaterial">Profit/Margin</label>';
    $rtn .= '<input type="text" class="form-control" style="text-align:right; font-weight:bold" 
      readonly value="'.number_format(($deal['dealAmount']-$deal['dealCOD']),2).'"></div></div>';

    $rtn .= '<div class="col-sm-4"><div class="form-group"><label for="dealDescription">Deal Description</label>';
    $rtn .= '<textarea class="form-control" rows="2" name="dealDescription" id="dealDescription" spellcheck="true" required>'.$deal['dealDescription'].'</textarea>';
    $rtn .= '<label for="dealMaterial">Current Status</label>';
    $rtn .= '<select class="form-control" id="dealStatus" name="dealStatus" disabled>'.getDealStatus($id).'</select></div>';
    $rtn .= '<div class="form-group"><button type="submit" id="updateRec" name="updateRec" class="btn btn-dark float-right">Update Deal</button></div>';
   
    $rtn .= '</div></div>';
    $_SESSION['oldRec'] = $deal;
  }

  return $rtn;
}

///--------------------------------------------------
///---------- Data Verification functions -----------
///--------------------------------------------------

function canSaveEdit()
{
  $rtn = false;
  $cse = array();
  $oldRec = $_SESSION['oldRec'];

  $cse['dealID'] = (int)$_REQUEST['rid'];
  $cse['dealDescription'] = $_REQUEST['dealDescription'];

  if ($oldRec['dealDescription'] !== $cse['dealDescription']) {
    $rtn = true;
  } else {
    $_SESSION['msgSend'] = 'No new data to update!';
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