<?php
ini_set('display_errors',false);
register_shutdown_function( "fatal_handler" );
set_error_handler("errorMessage");
//session_start();


function fatal_handler() 
{
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if($error !== NULL) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];
        $trace = print_r( debug_backtrace( false ), true );

        errorMessage( $errno, $errstr, $errfile, $errline,$trace);
    }
}

function errorMessage($errno, $errstr, $errfile, $errline, $trace=NULL)
{
    
  if ($errno == E_USER_NOTICE)
  {
  $errMsg='<div id="dialog" title="Data Entry Error"><p>';
    $errMsg.=$errstr;
  } else {
  $errMsg='<div id="dialog" title="Error Message"><p>';
    $errMsg.='<b>Error Message: </b>'.$errstr.'<br><b>Error Description:</b>[File] '.substr($errfile,-13).' [Line] '.$errline;
  }
	
	//$errMsg.='<br><a style="color:red;font-size:18px;text-align:right;" href="'.$_SESSION['activePage'].'">Back</a></p></div>';    
  $errMsg.='</div>';
  // log Error into DB
  if (saveLogData($errno,$errstr,$errline,$errfile)) {
    die($errMsg);
  }
}


function saveLogData($n,$s,$l,$f)
{ // create record of error in the DB
  $rtn=true;
  $ip = getIPAddress();
  $userN = getUserName();
  $qry = basename($_SERVER['REQUEST_URI']);
  $strW = (isset($_REQUEST))?json_encode($_REQUEST):'';
  $logD = 'URL: ' . $qry . ' [Error Number: ' . $n . '][Error String: '
    .$s.'][Error Line number:'.$l.']{Error FileName: '.$f.']';

  // exclude the querystring entry in the login errors
  if (!(strpos(strtolower($f),'ldap') !== false 
    || strpos(strtolower($f),'index') !== false)) { $logD .= '[QueryString: '.$strW.']';}

  $dat = new DateTime();
  $dt = $dat->format('Y-m-d');

  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();

      $sql = "INSERT INTO error_logs (errUserName,errIP,errDate,errDescription) VALUES (:erUN,:erIP,:erD,:erDS)";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":erUN", $userN, PDO::PARAM_STR);
      $stmt->bindparam(":erIP", $ip, PDO::PARAM_STR);
      $stmt->bindparam(":erD", $dt, PDO::PARAM_STR);
      $stmt->bindparam(":erDS", $logD, PDO::PARAM_STR);

      $row = $stmt->execute();
    } else {
      $rtn=false;
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    $rtn=false;
  }
  return $rtn;
}

function getUserName()
{ // get Username of application user
  $rtn='';
  //ucwords(strtolower($_SESSION['fullname']));
  if (isset($_SESSION['un']) && strlen($_SESSION['un']) > 1){
    $rtn = strtolower($_SESSION['un']);
  } elseif (isset($_REQUEST['username']) && strlen($_REQUEST['username']) > 1){
    $rtn = strtolower($_REQUEST['username']);
  } elseif (isset($_SESSION['username']) && strlen($_SESSION['username']) > 1){
    $rtn = strtolower($_SESSION['username']);
  }
  return $rtn;
}

function getIPAddress()
{
  $ipadd = '';

  try {
    //die('testing the login 2');
    if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != '' && $_SERVER['HTTP_CLIENT_IP'] != '127.0.0.1') {
      $ipadd = $_SERVER['HTTP_CLIENT_IP'];
    } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
      $ipadd = $_SERVER['REMOTE_ADDR'];
    } else if (isset($_SERVER['REMOTE_HOST']) && $_SERVER['REMOTE_HOST'] != '' && $_SERVER['REMOTE_HOST'] != '127.0.0.1') {
      $ipadd = $_SERVER['REMOTE_HOST'];
    }
  } catch (Exception $ex) {
    die('Error getIPAddress ' . $ex->getMessage());
  }

  return $ipadd; 
}

?>