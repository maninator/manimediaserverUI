<?php
  /**
   * Activation
   *
   * @package Mani Media Manager
   * @author maninator
   * @copyright 2016
   * @version $Id: activation.tpl.php, v1.00 2016-01-08 10:12:05 gewa Exp $
   */
  if (!defined("_MANI"))
      die('Direct access to this location is not allowed.');
?>
<?php if(Validator::get('done')):?>
<?php Message::msgOk(Lang::$word->M_INFO9 . '<a href="' . SITEURL . '">' . Lang::$word->M_INFO9_1 . '</a>');?>
<?php else:?>
<?php echo Message::msgError(Lang::$word->M_INFO10);?>
<?php endif;?>