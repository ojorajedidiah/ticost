<?php

#**** Database Connection Class 
#***Author of the script
#***Name: Adeleke Ojora
#***Email : ojorajedidiah@gmail.com
#***Date created: 17/09/2021
#***Date modified: 


#   The Database Connection class is used establish connection 
#   to a data source. 

class connectDatabase
{
  //declaring connection  variable
  protected $dbConnect;
  public $connection_status;
  protected $connectionError = '';

  //activating  connection
  function __construct()
  {
    /** 
     * @param string conType Connection Type ('mysql' or 'mssql')
     * @param array conParameters Connection Parameters 
     *      For Example Connection Parameter could be
     *      array("host" => "myHost","username" => "myUserName", 
     *      "password" => "myPassword", "dbname" =>"myDatabase")
     * 
     *  param has been adjust with passed parameter set inside the class.
     **/

    $conParameters = array("host" => "localhost", "username" => "ticost", "password" => "titilivate01!", "dbname" => "ticost");
    $conType = 'mysql';

    if (is_array($conParameters)) {
      if (strtolower($conType) == 'mssql') {
        $msSQLExtras = array("ReturnDatesAsStrings" => true);
        $dbHost = array_shift($conParameters);
        $dbString = array_push($conParameters, $msSQLExtras);
        $this->connection_status = $this->connectMSSQL($dbHost, $conParameters);
      } elseif (strtolower($conType) == 'mysql') {
        $dbHost = array_shift($conParameters);
        $this->connection_status = $this->connectMySQL($dbHost, $conParameters);
      } else {
        $this->connectionError = "Invalid Connection Parameters passed!";
        $this->connection_status=false;
      }
    } else {
      $this->connectionError = "Invalid Connection Parameters passed!";
      $this->connection_status=false;
    }
  }

  // connect to mySQL Data source
  protected function connectMySQL($host, $parameters)
  {
    $serverName = "mysql:host=$host;dbname=" . $parameters['dbname'];
    $user = $parameters['username'];
    $pwd = $parameters['password']; 
    try {
      //initializing  connection
      $this->dbConnect = new PDO("$serverName", "$user", "$pwd");
      $this->dbConnect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $connected =  true;
    } catch (PDOException $e) {
      $this->connectionError = "Unable to connect to mySQL database: " . $e->getMessage();
      $connected =  false;
    }
    return $connected;
  }

  // connect to MSSQL Data source
  protected function connectMSSQL($host, $parameters)
  {
    $serverName = "sqlsrv:server=$host; Database=" . $parameters['dbname']; 
    $user = $parameters['username'];
    $pwd = $parameters['password'];
    try {
      $this->dbConnect = new PDO("$serverName", "$user", "$pwd");
      $this->dbConnect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $connected = true;
    } catch (PDOException $e) {
      $this->connectionError = "Unable to connect to MSSQL database: " . $e->getMessage();
      $connected = false;
    }
    return $connected;
  }

  //this captures the current db connection
  function connect()
  {
    return $this->dbConnect;
  }

  function closeConnection()
  {
    $this->dbConnect = null;
    return true;
  }

  function getErrorMessage()
  {
    return $this->connectionError;
  }

  //this returns the current connection Error if there exist any
  function connectionError()
  {
    return $this->connectionError;
  }

  function isLastQuerySuccessful()
  {
    return $this->connection_status;
  }
}
