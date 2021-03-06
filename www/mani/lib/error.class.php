<?php
  /**
   * Error Class
   *
   * @package Mani Media Manager
   * @author maninator
   * @copyright 2016
   * @version $Id: error.class.php, v1.00 2016-04-20 18:20:24 gewa Exp $
   */

  if (!defined("_MANI"))
      die('Direct access to this location is not allowed.');

  class wError
  {

      private $errorConstants = array(
          1 => 'Error',
          2 => 'Warning',
          4 => 'Parse error',
          8 => 'Notice',
          16 => 'Core Error',
          32 => 'Core Warning',
          256 => 'User Error',
          512 => 'User Warning',
          1024 => 'User Notice',
          2048 => 'Strict',
          4096 => 'Recoverable Error',
          8192 => 'Deprecated',
          16384 => 'User Deprecated',
          32767 => 'All');


      /**
       * wError::__construct()
       * 
       * @return
       */
      public function __construct()
      {
          set_error_handler(array($this, 'errorHandler'));
          register_shutdown_function(array($this, 'fatalErrorShutdownHandler'));
          set_exception_handler(array($this, 'exceptionHandler'));

      }

      /**
       * wError::run()
       * 
       * @return
       */
      public static function run()
      {
          return new self();
      }

	  
      /**
       * wError::exceptionHandler()
       * 
       * @param mixed $exception
       * @return
       */
      public function exceptionHandler($exception)
      {
          $message = $exception->getMessage() . ' [code: ' . $exception->getCode() . ']';
		  
		  Message::msgSingleError($message);
		  Debug::AddMessage("warnings", '<i>Exception</i>', $message, "session");
				  
      }
	  
      /**
       * wError::errorHandler()
       * 
       * @param mixed $errno
       * @param mixed $errstr
       * @param mixed $errfile
       * @param mixed $errline
       * @return
       */
      public function errorHandler($errno, $errstr, $errfile, $errline)
      {
          $errString = (array_key_exists($errno, $this->errorConstants)) ? $this->errorConstants[$errno] : $errno;

          switch ($errno) {
              case 512:
			  case 1:
			  case 4:
			  case 16:
			  case 32:
			  case 4096:
				  Message::msgSingleError($errString . ': ' . $errstr . '' . $errno);
                  Debug::AddMessage("errors", '<i>ERROR</i>', $errString . ' [' . $errno . ']: ' . $errstr . ' in ' . $errfile . ' on line ' . $errline, "session");
                  break;
				  
              default:
                  Message::msgSingleError($errString . ': ' . $errstr . '' . $errno);
                  Debug::AddMessage("warnings", '<i>NOTICE</i>', $errString . ' [' . $errno . ']: ' . $errstr . ' in ' . $errfile . ' on line ' . $errline, "session");
                  break;
			  
				  
          }

      }

      /**
       * wError::fatalErrorShutdownHandler()
       * 
       * @return
       */
      function fatalErrorShutdownHandler()
      {
          $last_error = error_get_last();
          if ($last_error['type'] === E_ERROR) {
              Debug::AddMessage("errors", '<i>FATAL</i>', $last_error['message'] . ' in ' . $last_error['file'] . ' on line ' . $last_error['line'], "session");

          }
      }


      /**
       * wError::logError()
       * 
       * @param mixed $text
       * @param mixed $file
       * @param mixed $showMessage
       * @return
       */
      public static function logError($text, $file, $showMessage = False) {
          $fp = fopen(BASEPATH."/error.log", "a");
          if ($fp!==false) {
              $date = date("d/m/Y H:i", time());
              fwrite($fp, $date." - ".$file." - ".$text."\n");
              fclose($fp);
          }
          if ($showMessage) {
              Message::msgSingleError('An error has been logged at '.date("jS M H:i", time()).' please contact support or try again.'); 
          }
      }

  }