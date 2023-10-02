<?php 
$_SESSION['msgProd'] = '';
$errMsg = '';

if (isset($_REQUEST['saveRec'])) {
  // var_dump($_REQUEST);
  $errMsg = createNewProduct();
  $_REQUEST['v'] = "update";
}

if (isset($_POST['updateRec'])) {
  
  if (canSaveEdit()) {
    $errMsg = updateProduct();
    $_REQUEST['v'] = "update";
  } else {
    $errMsg = $_SESSION['msgProd'];
    $_REQUEST['v'] = "edit";
  }
}

if (isset($_POST['statusRec'])) {
  $errMsg = updateStatus();
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
              <?php echo buildDisableForm($_REQUEST['rid']) ?>
            </form>
          </div>
        </div>
      <?php } else if (isset($_REQUEST['v']) && $_REQUEST['v'] == 'edit') { ?>
        <div class="row">
          <div class="card-body card-success">
            <div class="card-header">
              <h3 class="card-title">Edit Product</h3>
            </div>
            <form method="post" target="">
              <?php echo buildEditForm($_REQUEST['rid']); ?>
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
      $measure=getProductMeasure();
      
      $sql = "INSERT INTO dkn_cocos (cocoName,cocoMeasure,cocoQuantity,
        cocoUnitSize,cocoReOrderLevel,cocoDescription,cocoLastExpDate) 
        VALUES (:coName,:coMeasure,:coQtty,:coUnit,:coReorder,:coDescription,:coLastExpDate)";

      $stmt = $con->prepare($sql);//date("Y-m-d", strtotime($_REQUEST['clientCreatedDate']))
      $stmt->bindparam(":coName", $_REQUEST['cocoName'], PDO::PARAM_STR);
      $stmt->bindparam(":coMeasure", $measure, PDO::PARAM_STR);
      $stmt->bindparam(":coQtty", $_REQUEST['cocoQuantity'], PDO::PARAM_INT);
      $stmt->bindparam(":coUnit", $_REQUEST['cocoUnitSize'], PDO::PARAM_INT);
      $stmt->bindparam(":coReorder", $_REQUEST['cocoReOrderLevel'], PDO::PARAM_INT);
      $stmt->bindparam(":coDescription", $_REQUEST['cocoDescription'], PDO::PARAM_STR);
      $stmt->bindparam(":coLastExpDate", $expdate, PDO::PARAM_STR);
      
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The Product <b>[" . $_REQUEST['cocoName'] . "...]</b> has been created!";
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

      $sql = "UPDATE dkn_cocos SET cocoStatus=:coStatus WHERE cocoID=:recID";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":recID", $_REQUEST['rid'], PDO::PARAM_INT);
      $stmt->bindparam(":coStatus", $_REQUEST['cocoStatus'], PDO::PARAM_STR);
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The status of the Product <b>[" . substr($_REQUEST['cocoDescription'],0,15). "...]</b> has been updated";
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

function updateProduct()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $cdate=date("Y-m-d", strtotime($_REQUEST['cocoLastExpDate']));
      $measure=getProductMeasure();

      //cocoName,cocoMeasure,cocoQuantity,      cocoUnitSize,cocoReOrderLevel,cocoDescription,cocoLastExpDate

      $sql = "UPDATE dkn_cocos SET cocoName=:coName,cocoMeasure=:coMeasure,cocoQuantity=:coQuantity,
        cocoUnitSize=:coUnitSize,cocoReOrderLevel=:coReOrderLevel,cocoDescription=:coDescription,
        cocoLastExpDate=:coLastExpDate WHERE cocoID=:recID";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":recID", $_REQUEST['rid'], PDO::PARAM_INT);
      $stmt->bindparam(":coName", $_REQUEST['cocoName'], PDO::PARAM_STR);
      $stmt->bindparam(":coMeasure", $measure, PDO::PARAM_STR);
      $stmt->bindparam(":coQuantity", $_REQUEST['cocoQuantity'], PDO::PARAM_INT);
      $stmt->bindparam(":coUnitSize", $_REQUEST['cocoUnitSize'], PDO::PARAM_INT);
      $stmt->bindparam(":coReOrderLevel", $_REQUEST['cocoReOrderLevel'], PDO::PARAM_INT);
      $stmt->bindparam(":coLastExpDate", $cdate, PDO::PARAM_STR);
      $stmt->bindparam(":coDescription", $_REQUEST['cocoDescription'], PDO::PARAM_STR);
      $row = $stmt->execute();

      if ($row) {
        $rtn = "The Product <b>[" . substr($_REQUEST['cocoDescription'],0,15). "...]</b> has been updated";
        //trigger_error($msg, E_USER_NOTICE);
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }

  return ($rtn == '') ? 'No Product Data' : $rtn;
}

function getProductRecords()
{
  $rtn = '';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT cocoID,cocoName,cocoMeasure,cocoUnitSize,cocoQuantity,cocoDescription
        FROM dkn_cocos WHERE cocoStatus ='active' ORDER BY cocoID ASC";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        $rID = $row['cocoID'];
        
        $rtn .= '<tr><td>' . $row['cocoName'] . '</td><td style="text-align:right;">' . $row['cocoUnitSize'] 
          . ' '.$row['cocoMeasure'] .'</td>'
          . '<td style="text-align:right;">' . $row['cocoQuantity']  . '</td><td>' . $row['cocoDescription']  . '</td>'
          . '<td style="text-align:center;"><span class="badge"><a href="home.php?p=dikins_list&v=status&rid=' . $rID . '">'
          . '<i class="nav-icon fas fa-window-restore" title="Delist Product" style="color:red;"></i>'
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

function getSpecificProduct($rec)
{
  $rtn = array();
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT cocoID,cocoName,cocoMeasure,cocoQuantity,cocoUnitSize,cocoReOrderLevel,
        cocoDescription,cocoLastExpDate FROM dkn_cocos WHERE cocoID=:id";
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

function getProdStatus($did = '')
{
  $rtn = '';
  $arr = array('active', 'not active');

  for ($cnt = 0; $cnt < count($arr); $cnt++) {
    if (isset($did) && $arr[$cnt] == $did) {
      $rtn .= '<option selected value="' . $arr[$cnt] . '">' . $arr[$cnt] . '</option>';
    } else {
      $rtn .= '<option value="' . $arr[$cnt] . '">' . $arr[$cnt] . '</option>';
    }
  }
  return ($rtn == '') ? '<option value="0">Empty Status List</option>' : $rtn;
}


function getProductMeasure()
{
  return ($_REQUEST['measure']=='ml') ? 'ml':'kg';
}


///--------------------------------------------------
///-------------- Build Form functions --------------
///--------------------------------------------------
function buildEditForm($id)
{
  $prod = getSpecificProduct($id);
  if (is_array($prod) && count($prod) >= 1) {
    $cdat = new DateTime($prod['cocoLastExpDate']);

    $rtn = '<div class="row"><div class="col-sm-4"><div class="form-group">';
    $rtn .= '<label for="cocoName">Product Name</label>';
    $rtn .= '<input type="text" class="form-control" name="cocoName" id="cocoName" value="'.$prod['cocoName'].'" required>';
    if ($prod['cocoMeasure']=='kg') {
      $rtn .= '<input type="radio" id="kilo" name="measure" value="kg" checked><label for="kilo">&nbsp;By Kilograms </label> &nbsp;&nbsp;';
      $rtn .= '<input type="radio" id="litre" name="measure" value="ml"><label for="litre">&nbsp;By Litres </label>';
    } else {
      $rtn .= '<input type="radio" id="kilo" name="measure" value="kg"><label for="kilo">&nbsp;By Kilograms </label> &nbsp;&nbsp;';
      $rtn .= '<input type="radio" id="litre" name="measure" value="ml" checked><label for="litre">&nbsp;By Litres </label>';
    }
    $rtn .= '<label for="cocoUnitSize">Product Unit (kg/litres)</label>';
    $rtn .= '<input type="text" class="form-control" name="cocoUnitSize" id="cocoUnitSize" value="'.$prod['cocoUnitSize'].'" required></div></div>';

    $rtn .= '<div class="col-sm-4"><div class="form-group">';
    $rtn .= '<label for="cocoQuantity">Product Quantity (Carton)</label>';
    $rtn .= '<input type="text" class="form-control" name="cocoQuantity" id="cocoQuantity" value="'.$prod['cocoQuantity'].'" required>';
    $rtn .= '<label for="cocoReOrderLevel">Re-order Level (Carton)</label>';
    $rtn .= '<input type="text" class="form-control" name="cocoReOrderLevel" id="cocoReOrderLevel" value="'.$prod['cocoReOrderLevel'].'" required >';
    $rtn .= '<label for="cocoLastExpDate">Product Expiration Date</label>';
    $rtn .= '<input type="date" class="form-control" name="cocoLastExpDate" id="cocoLastExpDate" value="' . getDueDate($prod['cocoLastExpDate']) . '"></div></div>';

    $rtn .= '<div class="col-sm-4"><div class="form-group">';
    $rtn .= '<label for="cocoDescription">Product Description</label>';
    $rtn .= '<textarea class="form-control" rows="4" name="cocoDescription" id="cocoDescription" spellcheck="true" required>'.$prod['cocoDescription'].'</textarea><br>';
    $rtn .= '<button type="submit" id="updateRec" name="updateRec" class="btn btn-success float-right">Update Product</button></div></div></div>';

    $rtn .= '</div></div></div>';

    $_SESSION['oldRec'] = $prod;
  }

  return $rtn;
}

function buildNewForm()
{
  $rtn = '<div class="row"><div class="col-sm-4"><div class="form-group">';
  $rtn .= '<label for="cocoName">Product Name</label>';
  $rtn .= '<input type="text" class="form-control" name="cocoName" id="cocoName" required>';
  $rtn .= '<input type="radio" id="kilo" name="measure" value="kg" checked><label for="kilo">&nbsp;By Kilograms </label> &nbsp;&nbsp;';
  $rtn .= '<input type="radio" id="litre" name="measure" value="ml"><label for="litre">&nbsp;By Litres </label>';
  $rtn .= '<label for="cocoUnitSize">Product Unit (kg/litres)</label>';
  $rtn .= '<input type="text" class="form-control" name="cocoUnitSize" id="cocoUnitSize" required></div></div>';

  $rtn .= '<div class="col-sm-4"><div class="form-group">';
  $rtn .= '<label for="cocoQuantity">Product Quantity (Carton)</label>';
  $rtn .= '<input type="text" class="form-control" name="cocoQuantity" id="cocoQuantity" required>';
  $rtn .= '<label for="cocoReOrderLevel">Re-order Level (Carton)</label>';
  $rtn .= '<input type="text" class="form-control" name="cocoReOrderLevel" id="cocoReOrderLevel" required >';
  $rtn .= '<label for="cocoLastExpDate">Product Expiration Date</label>';
  $rtn .= '<input type="date" class="form-control" name="cocoLastExpDate" id="cocoLastExpDate" value="'.getDueDate("today").'"></div></div>';
  
  $rtn .= '<div class="col-sm-4"><div class="form-group">';
  $rtn .= '<label for="cocoDescription">Product Description</label>';
  $rtn .= '<textarea class="form-control" rows="4" name="cocoDescription" id="cocoDescription" spellcheck="true" required></textarea><br>';
  $rtn .= '<button type="submit" id="saveRec" name="saveRec" class="btn btn-success float-right">Create Product</button></div></div></div>';

  $rtn .= '</div></div></div>';

  return $rtn;
}

function buildDisableForm($id)
{
  $prod = getSpecificProduct($id);
  if (is_array($prod) && count($prod) >= 1) {
    $cdat = new DateTime($prod['cocoLastExpDate']);

    $rtn = '<div class="row"><div class="col-sm-4"><div class="form-group">';
    $rtn .= '<label for="cocoName">Product Name</label>';
    $rtn .= '<input type="text" class="form-control" name="cocoName" id="cocoName" value="'.$prod['cocoName'].'" readonly>';
    if ($prod['cocoMeasure']=='kg') {
      $rtn .= '<input type="radio" id="kilo" name="measure" value="kg" checked disabled><label for="kilo">&nbsp;By Kilograms </label> &nbsp;&nbsp;';
      $rtn .= '<input type="radio" id="litre" name="measure" value="ml" disabled><label for="litre">&nbsp;By Litres </label>';
    } else {
      $rtn .= '<input type="radio" id="kilo" name="measure" value="kg" disabled><label for="kilo">&nbsp;By Kilograms </label> &nbsp;&nbsp;';
      $rtn .= '<input type="radio" id="litre" name="measure" value="ml" checked disabled><label for="litre">&nbsp;By Litres </label>';
    }
    $rtn .= '<label for="cocoUnitSize">Product Unit (kg/litres)</label>';
    $rtn .= '<input type="text" class="form-control" name="cocoUnitSize" id="cocoUnitSize" value="'.$prod['cocoUnitSize'].'" readonly></div></div>';

    $rtn .= '<div class="col-sm-4"><div class="form-group">';
    $rtn .= '<label for="cocoQuantity">Product Quantity (Carton)</label>';
    $rtn .= '<input type="text" class="form-control" name="cocoQuantity" id="cocoQuantity" value="'.$prod['cocoQuantity'].'" readonly>';
    $rtn .= '<label for="cocoReOrderLevel">Re-order Level (Carton)</label>';
    $rtn .= '<input type="text" class="form-control" name="cocoReOrderLevel" id="cocoReOrderLevel" value="'.$prod['cocoReOrderLevel'].'" readonly >';
    $rtn .= '<label for="cocoLastExpDate">Product Expiration Date</label>';
    $rtn .= '<input type="date" class="form-control" name="cocoLastExpDate" id="cocoLastExpDate" value="' . getDueDate($prod['cocoLastExpDate']) . '" readonly></div></div>';

    $rtn .= '<div class="col-sm-4"><div class="form-group">';
    $rtn .= '<label for="cocoDescription">Product Description</label>';
    $rtn .= '<textarea class="form-control" rows="2" name="cocoDescription" id="cocoDescription" spellcheck="true" readonly>'.$prod['cocoDescription'].'</textarea><br>';
    $rtn .= '<label for="cocoStatus">Current Status</label>';
    $rtn .= '<select class="form-control" id="cocoStatus" name="cocoStatus" required>'.getProdStatus($id).'</select></div>';
    $rtn .= '<div class="form-group"><button type="submit" id="statusRec" name="statusRec" class="btn btn-secondary float-right">Update Status</button></div>';
    // $rtn .= '<button type="submit" id="updateRec" name="updateRec" class="btn btn-success float-right">Update Product</button></div></div></div>';

    $rtn .= '</div></div></div>';

    $_SESSION['oldRec'] = $prod;
  }

  return $rtn;
}


///--------------------------------------------------
///---------- Data Verification functions -----------
///--------------------------------------------------
function canSaveEdit()
{
  $rtn = false;
  $oldRec = $_SESSION['oldRec'];

  if ($oldRec['cocoID'] != intval($_REQUEST['rid'])) {$rtn=true;};
  if ($oldRec['cocoName'] != $_REQUEST['cocoName']) {$rtn=true;};
  if ($oldRec['cocoMeasure'] != $_REQUEST['measure']) {$rtn=true;};
  if ($oldRec['cocoQuantity'] != intval($_REQUEST['cocoQuantity'])){$rtn=true;};
  if ($oldRec['cocoUnitSize'] != intval($_REQUEST['cocoUnitSize'])){$rtn=true;};
  if ($oldRec['cocoReOrderLevel'] != intval($_REQUEST['cocoReOrderLevel'])){$rtn=true;};
  if ($oldRec['cocoDescription'] != $_REQUEST['cocoDescription']){$rtn=true;};
  if ($oldRec['cocoLastExpDate'] != $_REQUEST['cocoLastExpDate']){$rtn=true;};

  if ($rtn == false) {
    $_SESSION['msgProd'] = 'No new data to update!';
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

function getDueDate($dt)
{
  if ($dt =='today'){
    $dt = new DateTime('now');
    $dt->add(new DateInterval('P1Y'));
    return $dt->format('Y-m-d');
  } else {
    return date('Y-m-d',strtotime ($dt));
  }
    
  
}

?>