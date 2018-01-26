<?php
  /**
   * Resend Notification
   *
   * @package Mani Media Manager
   * @author maninator
   * @copyright 2016
   * @version $Id: resendNotification.tpl.php, v1.00 2016-03-02 10:12:05 gewa Exp $
   */
  if (!defined("_MANI"))
      die('Direct access to this location is not allowed.');
	  
  if(!$this->data) : Message::invalid("ID" . Filter::$id); return; endif;
?>
<div class="wojo small form content">
  <form method="post" id="modal_form" name="modal_form">
    <div class="content-center">
      <p><i class="huge circular icon positive email"></i></p>
      <p class="half-top-padding"> <?php echo str_replace("[NAME]", '<span class="wojo bold text">' . $this->data->email  . '</span>', Lang::$word->M_INFO4);?> </p>
    </div>
  </form>
</div>