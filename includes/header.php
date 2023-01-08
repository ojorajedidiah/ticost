<?php
  session_start();
  include('error_handler.php');
  include('models/databaseConnection.class.php');
  include('includes/auditLogs.php');
  // $_SESSION['activePage']=basename($_SERVER['REQUEST_URI']);
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Titilivate Couture & Style</title>
    <link rel="stylesheet" href="assets/ticost.css">
    <link rel="stylesheet" href="assets/css/fontawesome-css/all.min.css">
    <link rel="stylesheet" href="assets/css/adminlte.min.css">
    
    <!-- <link rel="stylesheet" href="assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">     -->
    <link rel="stylesheet" href="assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    
    <link rel="stylesheet" href="assets/css/jquery-ui.css">
    <script src="assets/js/jquery-3.6.0.js"></script>
    <script src="assets/js/jquery-ui.js"></script>
    <script>
        $(function() {
            $("#dialog").dialog();
        });
    </script>
    
</head>