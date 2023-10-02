<?php
$_SESSION['msgProd'] = '';
$errMsg = '';

if (isset($_REQUEST['saveRec'])) {
  // var_dump($_REQUEST);
  $errMsg = createNewProduct();
  $_REQUEST['v'] = "update";
}



?>


<div class="content">
  <div class="container-fluid">
    <form method="post" target="">
      <div class="card card-outline card-success">
        <div class="card-header">
          <div class="row">        
            <div class="col-sm-8">
              <h5>Dikins Product Supplies</h5>
              <?php if ($errMsg != '') { echo '<span style="color:red;font-size:15px;">' . $errMsg . '</span>'; } ?>
            </div>
            <div class="col-sm-4">
              <a href="" class="btn btn-danger float-right">Clear</a>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="card-body card-success">
            <div class="card-header">
              <h3 class="card-title">Create Product Supplies</h3>
            </div>
            <?php echo buildNewForm(); ?>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>



<?php
///--------------------------------------------------
///------------- General DML functions --------------
///--------------------------------------------------

function createNewProduct()
{
  $rtn = '';
  // $cnt=0;
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();
      $expdate = date("Y-m-d", strtotime($_REQUEST['purExpDate']));
      $pdate = date("Y-m-d", strtotime($_REQUEST['purDate']));

      // insert new supplies into the cocspurchase table
      $sql = "INSERT INTO dkn_cocospurchase (purCocoID,purDate,purExpDate,purQuantity,
        purCost) VALUES (:coID,:pDate,:pExpDate,:pQtty,:pCost)";

      $stmt = $con->prepare($sql); 
      $stmt->bindparam(":coID", $_REQUEST['purCocoID'], PDO::PARAM_INT);
      $stmt->bindparam(":pDate", $pdate, PDO::PARAM_STR);
      $stmt->bindparam(":pExpDate", $expdate, PDO::PARAM_STR);
      $stmt->bindparam(":pQtty", $_REQUEST['purQuantity'], PDO::PARAM_INT);
      $stmt->bindparam(":pCost", $_REQUEST['purCost'], PDO::PARAM_STR);

      $row = $stmt->execute();
      // $cnt++;


      // update the quantity and expiration date fields in cocos table
      $sql = "UPDATE dkn_cocos SET cocoQuantity=cocoQuantity+:pQtty,
        cocoLastExpdate=:pExpDate WHERE cocoID=:coID";

      $stmt = $con->prepare($sql); 
      $stmt->bindparam(":coID", $_REQUEST['purCocoID'], PDO::PARAM_INT);
      $stmt->bindparam(":pExpDate", $expdate, PDO::PARAM_STR);
      $stmt->bindparam(":pQtty", $_REQUEST['purQuantity'], PDO::PARAM_INT);

      $row = $stmt->execute();


      if ($row) {
        $rtn = "The Product Supplies <b>[" . getSelectedProduct($_REQUEST['purCocoID']). "...]</b> has been created!";
        //trigger_error($msg, E_USER_NOTICE);
      }
    } else {
      trigger_error($db->connectionError(), E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }

  return ($rtn == '') ? 'No Product Supplies Data' : $rtn;
}

function getProducts($cl=0)
{
  $rtn = '<option value="0">None Selected</option>';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT cocoID,CONCAT(cocoName,' ',cocoUnitSize,cocoMeasure) AS cocoName FROM dkn_cocos WHERE cocoStatus='active' ORDER BY cocoID ASC";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        $d1 = $row['cocoName'];
        $rID = $row['cocoID'];

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

function getSelectedProduct($rid)
{
  $rtn = '' ;
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT cocoID,CONCAT(cocoName,' ',cocoUnitSize,cocoMeasure) AS cocoName FROM dkn_cocos WHERE cocoID=:coID";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      $stmt->bindparam(":coID", $rid, PDO::PARAM_INT);
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);

      foreach ($stmt->fetchAll() as $row) {
        $rtn = $row['cocoName'];
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


///--------------------------------------------------
///-------------- Build Form functions --------------
///--------------------------------------------------

function buildNewForm()
{
  $rtn = '<div class="row"><div class="col-sm-4"><div class="form-group">';
  $rtn .= '<label for="purCocoID">Product Name</label>';
  $rtn .= '<select class="form-control" id="purCocoID" name="purCocoID" required>'.getProducts().'</select>' ;
  $rtn .= '<label for="purDate">Product Supplied Date</label>';
  $rtn .= '<input type="date" class="form-control" name="purDate" id="purDate" value="' . getToday() . '" required></div></div>';

  $rtn .= '<div class="col-sm-4"><div class="form-group">';
  $rtn .= '<label for="purQuantity">Product Quantity</label>';
  $rtn .= '<input type="text" class="form-control" name="purQuantity" id="purQuantity" required>';
  $rtn .= '<label for="purExpDate">Product Expiration Date</label>';
  $rtn .= '<input type="date" class="form-control" name="purExpDate" id="purExpDate" value="' . getDueDate('today') . '" required></div></div>';

  $rtn .= '<div class="col-sm-4"><div class="form-group">';
  $rtn .= '<label for="purCost">Product Cost</label>';
  $rtn .= '<input type="text" class="form-control" name="purCost" id="purCost" required><br>';
  $rtn .= '<button type="submit" id="saveRec" name="saveRec" class="btn btn-success float-right">Create Supplies</button></div></div></div>';

  $rtn .= '</div></div></div>';

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
    return date('Y-m-d', strtotime($dt));
  }
}

?>