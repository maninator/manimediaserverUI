<?php
  /**
   * Cron
   *
   * @package Mani Media Manager
   * @author maninator
   * @copyright 2016
   * @version $Id: cron.php, v1.00 2016-02-05 10:12:05 gewa Exp $
   */
  define("_MANI", true);
  require_once("../init.php");
  
  Cron::Run(1);