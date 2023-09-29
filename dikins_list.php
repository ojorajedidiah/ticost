<?php 
$_SESSION['msgDeal'] = '';
$errMsg = '';
// var_dump($_REQUEST);
if (isset($_REQUEST['saveRec'])) {
  $errMsg = createNewProduct();
  $_REQUEST['v'] = "update";
}


?>


<div class="content">
  <div class="container-fluid">
    <div class="card card-outline card-success">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-8">
            <h5>Dikins Product Listing</h5>
            <?php if ($errMsg != '') {
              echo '<span style="color:red;font-size:15px;">' . $errMsg . '</span>';
            } ?>
          </div>
          <div class="col-sm-4">
            <!--0//&& ($_REQUEST['v'] == 'new' || $_REQUEST['v'] == 'edit' || $_REQUEST['v'] == 'disable' || $_REQUEST['v'] == 'status')-->
            <?php if (isset($_REQUEST['v'])) { ?>
              <a href="home.php?p=dikins_list" class="btn btn-danger float-right">Back</a>
            <?php } else { ?>
              <a href="home.php?p=dikins_list&v=new" class="btn btn-info float-right">Create New Product</a>
            <?php } ?>
          </div>
        </div>
      </div>
      <?php if (isset($_REQUEST['v']) && $_REQUEST['v'] == 'new') { ?>
        <div class="row">
          <div class="card-body card-success">
            <div class="card-header">
              <h3 class="card-title">Create New Product</h3>
            </div>
            <form method="post" target="">
              <?php echo buildNewForm(); ?>
            </form>
          </div>
        </div>
      <?php } else if (isset($_REQUEST['v']) && ($_REQUEST['v'] == 'disable' || $_REQUEST['v'] == 'status')) { ?>
        <div class="row">
          <div class="card-body card-success">
            <div class="card-header">
              <?php if ($_REQUEST['v'] == 'disable') { ?>
                <h3 class="card-title">Disable Product</h3>
              <?php } else { ?>
                <h3 class="card-title">Update Product Status</h3>
              <?php } ?>
            </div>
            <form method="post" target="">
              <?php //echo buildDisableForm($_REQUEST['rid']) ?>
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
              <?php //echo buildEditForm($_REQUEST['rid']); ?>
            </form>
          </div>
        </div>
      <?php } else { ?>
        <div class="row">
          <div class="card-body">
            <table id="dikins" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th width="30%">Product Name</th>
                  <th width="10%">Unit</th>
                  <th width="10%">Quantity</th>
                  <th width="40%">Description</th>
                  <th width="10%">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php echo getProductRecords();?>
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

function createNewProduct()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();
      $expdate=date("Y-m-d", strtotime($_REQUEST['cocoLastExpDate']));
      
      $sql = "INSERT INTO dkn_cocos (cocoName,cocoQuantity,
        cocoUnitSize,cocoReOrderLevel,cocoDescription,cocoLastExpDate) 
        VALUES (:coName,:coQtty,:coUnit,:coReorder,:coDescription,:coLastExpDate)";

      $stmt = $con->prepare($sql);//date("Y-m-d", strtotime($_REQUEST['clientCreatedDate']))
      $stmt->bindparam(":coName", $_REQUEST['cocoName'], PDO::PARAM_STR);
      $stmt->bindparam(":coQtty", $_REQUEST['cocoQuantity'], PDO::PARAM_INT);
      $stmt->bindparam(":coUnit", $_REQUEST['cocoUnitSize'], PDO::PARAM_INT);
      $stmt->bindparam(":coReorder", $_REQUEST['cocoReOrderLevel'], PDO::PARAM_INT);
      $stmt->bindparam(":coDescription", $_REQUEST['cocoDescription'], PDO::PARAM_STR);
      $stmt->bindparam(":coLastExpDate", $expdate, PDO::PARAM_STR);
      
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The Prodcut <b>[" . $_REQUEST['cocoName'] . "...]</b> has been created!";
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

function disableDeal()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "UPDATE tcs_deals SET dealDescription=:dlDesc,dealStatus='deleted' WHERE dealID=:recID";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":recID", $_REQUEST['rid'], PDO::PARAM_INT);
      $stmt->bindparam(":dlDesc", $_REQUEST['dealDescription'], PDO::PARAM_STR);
      // $stmt->bindparam(":dlStatus", $_REQUEST['dealStatus'], PDO::PARAM_STR);
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The deal <b>[" . substr($_REQUEST['dealDescription'],0,15). "...]</b> has been deleted";
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

function updateStatus()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "UPDATE tcs_deals SET dealDescription=:dlDesc,dealStatus=:dlStatus WHERE dealID=:recID";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":recID", $_REQUEST['rid'], PDO::PARAM_INT);
      $stmt->bindparam(":dlDesc", $_REQUEST['dealDescription'], PDO::PARAM_STR);
      $stmt->bindparam(":dlStatus", $_REQUEST['dealStatus'], PDO::PARAM_STR);
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The status of the deal <b>[" . substr($_REQUEST['dealDescription'],0,15). "...]</b> has been updated";
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

function UpdateDeal()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $cdate=date("Y-m-d", strtotime($_REQUEST['dealCreatedDate']));
      $ddate=date("Y-m-d", strtotime($_REQUEST['dealDueDate']));
      $dldgn=convertNumber($_REQUEST['dealDesign']);
      $dlsew=convertNumber($_REQUEST['dealSewing']);
      $dlmat=convertNumber($_REQUEST['dealMaterial']);
      $dlmnu=convertNumber($_REQUEST['dealManu']);
      $dlcod=getCOD();
      $dlamo=convertNumber($_REQUEST['dealAmount']);
      $dlpro=getProfit($_REQUEST['dealAmount']);

      $sql = "UPDATE tcs_deals SET clientID=:dlClient,dealDescription=:dlDesc,dealCreatedDate=:dlCreatDate,
        dealDueDate=:dlDueDate,dealDesign=:dlDesign,dealSewing=:dlSewing,dealMaterial=:dlMaterial,dealManu=:dlManu,
        dealCOD=:dlCOD,dealAmount=:dlAmount,dealProfit=:dlProfit 
        WHERE dealID=:recID";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":recID", $_REQUEST['rid'], PDO::PARAM_INT);
      $stmt->bindparam(":dlClient", $_REQUEST['clientID'], PDO::PARAM_INT);
      $stmt->bindparam(":dlDesc", $_REQUEST['dealDescription'], PDO::PARAM_STR);
      $stmt->bindparam(":dlCreatDate", $cdate, PDO::PARAM_STR);
      $stmt->bindparam(":dlDueDate", $ddate, PDO::PARAM_STR);
      $stmt->bindparam(":dlDesign", $dldgn, PDO::PARAM_STR);
      $stmt->bindparam(":dlSewing", $dlsew, PDO::PARAM_STR);
      $stmt->bindparam(":dlMaterial", $dlmat, PDO::PARAM_STR);
      $stmt->bindparam(":dlManu", $dlmnu, PDO::PARAM_STR);
      $stmt->bindparam(":dlCOD", $dlcod, PDO::PARAM_STR);
      $stmt->bindparam(":dlAmount", $dlamo, PDO::PARAM_STR);
      $stmt->bindparam(":dlProfit", $dlpro, PDO::PARAM_STR);
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

function getProductRecords()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT cocoID,cocoName,cocoUnitSize,cocoQuantity,cocoDescription
        FROM dkn_cocos WHERE cocoStatus ='active' ORDER BY cocoID ASC";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        $rID = $row['cocoID'];
        
        $rtn .= '<tr><td>' . $row['cocoName'] . '</td><td style="text-align:right;">' . $row['cocoUnitSize'] . ' <b>Kg</b></td>'
          . '<td style="text-align:right;">' . $row['cocoQuantity']  . '</td><td>' . $row['cocoDescription']  . '</td>'
          . '<td style="text-align:center;"><span class="badge"><a href="home.php?p=dikins_list&v=disable&rid=' . $rID . '">'
          . '<i class="nav-icon fas fa-window-restore" title="Delete Product" style="color:red;"></i>'
          . '</a></span><span class="badge"><a href="home.php?p=dikins_list&v=edit&rid=' . $rID . '">'
          . '<i class="nav-icon fas fa-edit" title="Update Product" style="color:blue;"></i></a></span></td></tr>';
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (Exception $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }
  return ($rtn == '') ? '<tr><td colspan="6" style="color:red;text-align:center;"><b>No Products Available</b></td></tr>' : $rtn;
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
        FROM tcs_deals d LEFT JOIN tcs_clients c ON d.clientID=c.clientID WHERE dealID=:id";
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

      $sql = "SELECT clientID,clientName FROM tcs_clients WHERE clientStatus='active' ORDER BY clientID ASC";
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
  $arr=array('created','agreed','active','completed','qc passed');
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT dealStatus FROM tcs_deals WHERE dealID=:id";
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


///--------------------------------------------------
///-------------- Build Form functions --------------
///--------------------------------------------------
function buildEditForm($id)
{
  $deal = getSpecificDeal($id);
  if (is_array($deal) && count($deal) >= 1) {
    $cdat=new DateTime($deal['dealCreatedDate']);
    $ddat=new DateTime($deal['dealDueDate']);

    $rtn = '<div class="row"><div class="col-sm-4"><div class="form-group"><label for="clientID">Client Name</label>';
    $rtn .= '<select class="form-control" id="clientID" name="clientID">'.getClients($id).'</select>';
    $rtn .= '<label for="dealCreatedDate">Deal Date</label>';
    $rtn .= '<input type="date" class="form-control" name="dealCreatedDate" id="dealCreatedDate" onchange="updateDueDate();" value="'.$deal['dealCreatedDate'].'">';
    $rtn .= '<label for="dealDueDate">Deal Delivery Date</label>'; 
    $rtn .= '<input type="date" class="form-control" name="dealDueDate" id="dealDueDate" value="'.$deal['dealDueDate'].'"></div></div>';

    $rtn .= '<div class="col-sm-4"><div class="form-group"><label for="dealDesign">Design Cost</label>';
    $rtn .= '<input type="text" class="form-control" style="text-align:right;" name="dealDesign" id="dealDesign" 
      required onchange="formatCurrency(\'dealDesign\');" value="'.number_format($deal['dealDesign'],2).'">';
    $rtn .= '<label for="dealSewing">Sewing Cost</label>';
    $rtn .= '<input type="text" class="form-control" style="text-align:right;" name="dealSewing" id="dealSewing" 
      required onchange="formatCurrency(\'dealSewing\');" value="'.number_format($deal['dealSewing'],2).'">';
    $rtn .= '<label for="dealMaterial">Material Cost</label>';
    $rtn .= '<input type="text" class="form-control" style="text-align:right;" name="dealMaterial" id="dealMaterial"  
      required onchange="formatCurrency(\'dealMaterial\');" value="'.number_format($deal['dealMaterial'],2).'"></div></div>';

    $rtn .= '<div class="col-sm-4"><div class="form-group"><label for="dealMaterial">Manufacturing Cost</label>';
    $rtn .= '<input type="text" class="form-control" style="text-align:right;" name="dealManu" id="dealManu" 
      required onchange="formatCurrency(\'dealManu\');" value="'.number_format($deal['dealManu'],2).'">';
    $rtn .= '<label for="dealAmount">Amount Charged</label>';
    $rtn .= '<input type="text" class="form-control" style="text-align:right;" name="dealAmount" id="dealAmount" 
      required onchange="updateMargin();" value="'.number_format($deal['dealAmount'],2).'">';
    $rtn .= '<label for="dealDescription">Deal Description</label>';
    $rtn .= '<textarea class="form-control" rows="1" name="dealDescription" id="dealDescription" spellcheck="true" 
      required>'.$deal['dealDescription'].'</textarea></div></div>';

    $rtn .= '<div class="col-sm-12"><div class="form-group"><div id="profit" name="profit" class="float-left" style="font-weight:bold;color:red;"></div>';
    $rtn .= '<button type="submit" id="updateRec" name="updateRec" class="btn btn-success float-right">Update Deal</button></div></div></div>';
    $_SESSION['oldRec'] = $deal;
  }
  
  return $rtn;
}

function buildNewForm()
{
  $rtn = '<div class="row"><div class="col-sm-4"><div class="form-group">';
  $rtn .= '<label for="cocoName">Product Name</label>';
  $rtn .= '<input type="text" class="form-control" name="cocoName" id="cocoName" required>';
  $rtn .= '<label for="cocoUnitSize">Product Unit (KG)</label>';
  $rtn .= '<input type="text" class="form-control" name="cocoUnitSize" id="cocoUnitSize" required>';
  $rtn .= '<label for="cocoQuantity">Product Quantity (Carton)</label>';
  $rtn .= '<input type="text" class="form-control" name="cocoQuantity" id="cocoQuantity" required></div></div>';

  $rtn .= '<div class="col-sm-4"><div class="form-group">';
  $rtn .= '<label for="cocoReOrderLevel">Re-order Level (Carton)</label>';
  $rtn .= '<input type="text" class="form-control" name="cocoReOrderLevel" id="cocoReOrderLevel" required >';
  $rtn .= '<label for="cocoLastExpDate">Product Expiration Date</label>';
  $rtn .= '<input type="date" class="form-control" name="cocoLastExpDate" id="cocoLastExpDate" value="'.getDueDate().'"></div></div>';
  
  $rtn .= '<div class="col-sm-4"><div class="form-group">';
  $rtn .= '<label for="cocoDescription">Product Description</label>';
  $rtn .= '<textarea class="form-control" rows="4" name="cocoDescription" id="cocoDescription" spellcheck="true" required></textarea><br>';
  $rtn .= '<button type="submit" id="saveRec" name="saveRec" class="btn btn-success float-right">Create Product</button></div></div></div>';

  $rtn .= '</div></div></div>';

  return $rtn;
}

function buildDisableForm($id)
{
  $rtn = '';
  $deal = array();
  $deal = getSpecificDeal($id);
  // die('the value is '.$gst['guestVisitDate']);
  if (is_array($deal) && count($deal) >= 1) {
    $cdat=new DateTime($deal['dealCreatedDate']);
    $ddat=new DateTime($deal['dealDueDate']);

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
    if ($_REQUEST['v'] == 'disable') {
      $rtn .= '<select class="form-control" id="dealStatus" name="dealStatus" disabled>'.getDealStatus($id).'</select></div>';
      $rtn .= '<div class="form-group"><button type="submit" id="deleteRec" name="deleteRec" class="btn btn-dark float-right">Delete Deal</button></div>';
    } else {
      $rtn .= '<select class="form-control" id="dealStatus" name="dealStatus" required>'.getDealStatus($id).'</select></div>';
      $rtn .= '<div class="form-group"><button type="submit" id="statusRec" name="statusRec" class="btn btn-secondary float-right">Update Status</button></div>';
    }    
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
  $cse['clientID'] = ($_REQUEST['clientID']=="0")?NULL:(int)$_REQUEST['clientID'];
  $cse['dealCreatedDate'] = $_REQUEST['dealCreatedDate'];
  $cse['dealDueDate'] = $_REQUEST['dealDueDate'];
  $cse['dealDescription'] = $_REQUEST['dealDescription'];
  $cse['dealDesign'] = str_replace(',','',$_REQUEST['dealDesign']);
  $cse['dealSewing'] = str_replace(',','',$_REQUEST['dealSewing']);
  $cse['dealMaterial'] = str_replace(',','',$_REQUEST['dealMaterial']);
  $cse['dealManu'] = str_replace(',','',$_REQUEST['dealManu']);
  $cse['dealCOD'] = str_replace(',','',number_format(getCOD(),2));
  $cse['dealAmount'] = str_replace(',','',$_REQUEST['dealAmount']);

  if (count(array_diff($oldRec, $cse)) >= 1) {
    $rtn = true;
  } else {
    $_SESSION['msgDeal'] = 'No new data to update!';
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
  $dt->add(new DateInterval('P1Y'));
  return $dt->format('Y-m-d');
}

function convertNumber($num)
{
  return intval(str_replace(',','',$num));
}

function getCOD()
{
  return floatval(convertNumber($_REQUEST['dealDesign'])+convertNumber($_REQUEST['dealSewing'])
    +convertNumber($_REQUEST['dealMaterial'])+convertNumber($_REQUEST['dealManu']));
}

function getProfit($amt)
{
  return floatval(convertNumber($amt)-getCOD());
}
?>