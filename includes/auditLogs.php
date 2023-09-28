<?php 
$qry='';


if (isset($_REQUEST) && shdSaveLog()) {
  $ip = getIPAddress();
  $userN = ucwords(strtolower($_SESSION['fullname']));
  $qry = basename($_SERVER['REQUEST_URI']);
  $strW = json_encode($_REQUEST);
  $logD='Accessed by: ('.$userN .') URL: '.$qry.' [REQUEST: '.$strW.']';
  //echo 'the querystring is '.$logD;
  $dat=new DateTime();
  $dt=$dat->format('Y-m-d');
  
  try {
    $db = new connectDatabase();
    if ($db->isLastQuerySuccessful()) {
      $con = $db->connect();
      
      $sql = "INSERT INTO logs (logIP,logDate,logDescription) VALUES (:lgID,:lgD,:lgDS)";

      $stmt = $con->prepare($sql);
      $stmt->bindparam(":lgID", $ip, PDO::PARAM_STR);
      $stmt->bindparam(":lgD", $dt, PDO::PARAM_STR);
      $stmt->bindparam(":lgDS", $logD, PDO::PARAM_STR);     
      
      $row = $stmt->execute();
    } else {
      trigger_error($db->connectionError());
    }
    $db->closeConnection();
  } catch (PDOException $e) {
    trigger_error($e->getMessage());
  }
} 
// else {
//   die('e not enter condition');
// }




function shdSaveLog()
{
  $rtn=false;
  $tmp=json_encode($_REQUEST);
  
  if (strlen(strstr($tmp,'edit')) > 1 
    || strlen(strstr($tmp,'update')) > 1 
    || strlen(strstr($tmp,'disable')) > 1
    || strlen(strstr($tmp,'sendSMS')) > 1
    )
  {
    $rtn=true;
  }

  return $rtn;
}

?>