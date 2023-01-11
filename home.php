<?php
date_default_timezone_set("Africa/Lagos");
include('includes/header.php');
if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] == 1) {

?>

<script>
  $(function() {
    $("#grids").DataTable({
      "paging": true,
      "lengthChange": false,
      "ordering": true,
      "searching": false,
      "info": true,
      "autoWidth": false,
      "responsive": true,
      // "buttons": ["excel", "pdf", "colvis"]
    }); //.buttons().container().appendTo('#grids_wrapper .col-md-6:eq(0)');
  });

</script>


  <body class="hold-transition layout-top-nav">
    <div id="app">
      <div class="wrapper">
        <?php include('includes/top_menu.php'); ?>
        <div class="content-wrapper">
          <div class="content">
            <div class="container">
              <div class="content-header">
                <div class="container">
                  <div class="row mb-2">
                    <div class="col-sm-6">
                      <h1 class="m-0" style="font-family: 'Lucy Said Ok', Courier, monospace; font-size:xxx-large;">Titilivate Couture & Style</h1>
                      <!-- <h3 class="card-title" style="color:cadetblue;"><?php //echo userDetails(); ?></h3> -->
                    </div>
                    <div class="col-sm-6">
                      <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="">Home</a></li>
                        <li class="breadcrumb-item acive"> <?php echo namePage(); ?> </li>
                      </ol>
                    </div>
                  </div>
                </div>
              </div>

              <?php if (isset($_REQUEST['p'])) {
                grantAccess();
              } else { ?>
                <div class="content">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-3 col-sm-6 col-12">
                        <div class="small-box bg-info">
                          <div class="inner">
                            <h3><?php echo '3'; ?></h3>
                            <p style="font-size: smaller;">Total No. of Deals (This Week)</p>
                          </div>
                          <!-- <div class="icon"><i class="fas fa-user-secret"></i></div> -->
                        </div>
                      </div>
                      <div class="col-md-3 col-sm-6 col-12">
                        <div class="small-box bg-red">
                          <div class="inner">
                            <h3><?php echo '3'; ?></h3>
                            <p style="font-size: smaller;">Total Undelivered Deals (This Week)</p>
                          </div>
                          <!-- <div class="icon"><i class="fas fa-user-secret"></i></div> -->
                        </div>
                      </div>
                      <div class="col-md-3 col-sm-6 col-12">
                        <div class="small-box bg-success">
                          <div class="inner">
                            <h3><?php echo '3'; ?></h3>
                            <p style="font-size: smaller;">Total No. of Deals (This Month)</p>
                          </div>
                          <!-- <div class="icon"><i class="fas fa-user-shield"></i></div> -->
                        </div>
                      </div>
                      <div class="col-md-3 col-sm-6 col-12">
                        <div class="small-box bg-red">
                          <div class="inner">
                            <h3><?php echo '3'; ?></h3>
                            <p style="font-size: smaller;">Total Undelivered Deals (This month)</p>
                          </div>
                          <!-- <div class="icon"><i class="fas fa-user-tie"></i></div> -->
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>


      <footer class="main-footer" style="font-weight: bold;background-color: azure;">
        <div class="float-right d-none d-sm-inline">
          Powered by <strong><a href="https://github.com/ojorajedidiah" target="new">ojorajedidiah</a></strong>
        </div>
        Copyright &copy <span id="copy"><?php echo date('Y'); ?></span>
      </footer>
    </div>

  <?php } else {
  die('<head><script LANGUAGE="JavaScript">window.location="index.php";</script></head>');
} ?>

  <script src="assets/js/jquery.min.js"></script>
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/adminlte.min.js"></script>
  <!-- <script src="assets/js/jquery.knob.min.js"></script> -->

  <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
  <script src="assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
  <script src="assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
  <script src="assets/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
  <script src="assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
  <!-- <script src="assets/plugins/jszip/jszip.min.js"></script> -->
  <script src="assets/plugins/pdfmake/pdfmake.min.js"></script>
  <script src="assets/plugins/pdfmake/vfs_fonts.js"></script>
  <script src="assets/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
  <script src="assets/plugins/datatables-buttons/js/buttons.print.min.js"></script>
  <script src="assets/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
  <!-- <script src="assets/plugins/flot/jquery.flot.js"></script>
  <script src="assets/plugins/flot/plugins/jquery.flot.resize.js"></script>
  <script src="assets/plugins/flot/plugins/jquery.flot.pie.js"></script> -->

  </body>

</html>


<?php

function getBarChartData()
{
  // $rtn='[[1,10], [2,8], [3,4], [4,13]]';
  // $rtnTitle='[[1,'January'], [2,'February'], [3,'March'], [4,'April']]';
  $rtn='[';$cnt=1;$rtnTitle='[';
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT COUNT(*) FROM bar_chart";
      $res = $con->query($sql);
      $r_count = $res->fetchColumn();

      $sql = "SELECT chtTitle,chtValue FROM bar_chart ORDER BY chtTitle DESC";
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      
      foreach ($stmt->fetchAll() as $row) 
      {
        $stmt->rowCount();
        if ($cnt>1 && $cnt<=$r_count){$rtn.=', ';$rtnTitle.=', ';}
        $val=$row['chtValue'];
        $tit=$row['chtTitle'];
        $rtn.='['.$cnt.','.$val.']';
        $rtnTitle.="[".$cnt.",'".date('j M Y',strtotime($tit))."']";
        $cnt++;
      }
      $rtn.=']';$rtnTitle.="]";
    } else {
      trigger_error($db->connectionError());
    }
    $db->closeConnection();
  } catch (Exception $e) {
    trigger_error($db->connectionError());
  }
  $_SESSION['titles']=$rtnTitle;
  return $rtn;
}

function namePage()
{
  $rtn = '';
  if (isset($_REQUEST['p']) && $_REQUEST['p'] != '') {
    $rtn = ucwords($_REQUEST['p']);
  } else {
    $rtn = 'Dashboard';
  }
  return $rtn;
}

function getWeekCount()
{
  $cnt = 0;
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT COUNT(eleSID) as cnt FROM ele_sms WHERE ele_sent_status LIKE 'Message sent%' AND ele_sent_date BETWEEN ".getDateRange('W');
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $tmp=$stmt->fetch();
      $cnt=(int) $tmp['cnt'];
      die('i enter rent');

    } else {
      trigger_error($db->connectionError(),E_USER_NOTICE);
    }
    $db->closeConnection();
  } catch (Exception $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
  }
  return $cnt;
}

function getMonthCount()
{
  $cnt = 0;
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT COUNT(eleSID) as cnt FROM ele_sms WHERE ele_sent_status LIKE 'Message sent%' AND ele_sent_date BETWEEN ".getDateRange('M');
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $tmp=$stmt->fetch();
      $cnt=(int) $tmp['cnt'];

    } else {
      trigger_error($db->connectionError());
    }
    $db->closeConnection();
  } catch (Exception $e) {
    trigger_error($db->connectionError());
  }
  return $cnt;
}

function getQuarterCount()
{
  $cnt = 0;
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "SELECT COUNT(eleSID) as cnt FROM ele_sms WHERE ele_sent_status LIKE 'Message sent%' AND ele_sent_date BETWEEN ".getDateRange('Q');
      $stmt = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
      
      $stmt->execute();
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $tmp=$stmt->fetch();
      $cnt=(int) $tmp['cnt'];

    } else {
      trigger_error($db->connectionError());
    }
    $db->closeConnection();
  } catch (Exception $e) {
    trigger_error($db->connectionError());
  }
  return $cnt;
}

function getTotalNumTPM()
{
  return 20;
}

function userDetails()
{
  $rtn = '';
  if (isset($_SESSION['fullname'])) {
    $rtn = ucwords($_SESSION['fullname']);
  } else {
    $rtn = 'No User Details';
  }

  return $rtn;
}

function grantAccess()
{
  $errormsg = ''; ///, $usersOnline, $staffOnline, $allowed
  global $pagePriviledge;

  if (isset($_GET["p"])) {
    $pageRequested = $_GET["p"];
    
    include($pageRequested . '.php');
  }
  if ($errormsg != '') {
    echo '<center><br><span style="color:red; font-size:20pt;">' . $errormsg . '</span></center>';
  }
}

function getDateRange($intv)
{
  $rg='';
  $rge=new DateTime();
  if ($intv == 'W'){
    $rgs=new DateTime();
    $tp='-'.date('w').' days';
    $rgs->modify($tp);
    $rg=" '".$rgs->format('Y-m-d')."' AND '".$rge->format('Y-m-d')."' ";
  } else if ($intv=='M'){
    $rg=" '".$rge->format('Y-m')."-01' AND '".$rge->format('Y-m-d')."' ";
  } else if ($intv=='Q'){
      $m=$rge->format('m');
      switch($m)
      {
      case 1: case 2: case 3:
        $rg=" '".$rge->format('Y')."-01-01' AND '".$rge->format('Y')."-03-31' ";
        break;
      case 4: case 5: case 6:
        $rg=" '".$rge->format('Y')."-04-01' AND '".$rge->format('Y')."-06-30' ";
        break;
      case 7: case 8: case 9:
        $rg=" '".$rge->format('Y')."-07-01' AND '".$rge->format('Y')."-09-30' ";
        break;
      case 10: case 11: case 12:
        $rg=" '".$rge->format('Y')."-10-01' AND '".$rge->format('Y')."-12-31' ";
        break;
      }
  }    
  return $rg;
}

?>