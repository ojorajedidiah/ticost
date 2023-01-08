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
  die($errMsg);
}

?>