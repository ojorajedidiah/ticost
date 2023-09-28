<?php 
$errMsg='';


?>

<div class="content">
  <div class="container-fluid" style="width:70%;">
    <div class="card card-outline card-success">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-8">
            <h5>Client Deals (WIPs)</h5>
            <?php if ($errMsg != '') {
              echo '<span style="color:red;font-size:15px;">' . $errMsg . '</span>';
            } ?>
          </div>
          <div class="col-sm-4">
            <!--0//&& ($_REQUEST['v'] == 'new' || $_REQUEST['v'] == 'edit' || $_REQUEST['v'] == 'disable' || $_REQUEST['v'] == 'status')-->
            <?php if (isset($_REQUEST['v'])) { ?>
              <a href="home.php?p=" class="btn btn-danger float-right">Back</a>
            <?php } else { ?>
              <a href="home.php?p=&v=new" class="btn btn-info float-right">Create New Deal</a>
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
              <?php //echo buildNewForm(); ?>
            </form>
          </div>
        </div>
      <?php } else if (isset($_REQUEST['v']) && ($_REQUEST['v'] == 'disable' || $_REQUEST['v'] == 'status')) { ?>
        <div class="row">
          <div class="card-body card-success">
            <div class="card-header">
              <?php if ($_REQUEST['v'] == 'disable') { ?>
                <h3 class="card-title">Disable Deal</h3>
              <?php } else { ?>
                <h3 class="card-title">Update Deal Status</h3>
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
                <?php //echo getDealRecords(); ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

