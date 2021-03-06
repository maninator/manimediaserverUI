<?php
  /**
   * Membership Manager
   *
   * @package Mani Media Manager
   * @author maninator
   * @copyright 2016
   * @version $Id: _memberships_new.tpl.php, v1.00 2016-07-08 10:12:05 gewa Exp $
   */
  if (!defined("_MANI"))
      die('Direct access to this location is not allowed.');
?>
<h3><?php echo Lang::$word->META_T8;?></h3>
<form method="post" id="wojo_form" name="wojo_form">
  <div class="wojo basic segment form">
    <div class="row">
      <div class="columns screen-70 tablet-60 mobile-100 phone-100 padding">
        <div class="wojo fields align-middle">
          <div class="field four wide labeled">
            <label class="content-right mobile-content-left"><?php echo Lang::$word->NAME;?> <i class="icon asterisk"></i></label>
          </div>
          <div class="field">
            <input type="text" placeholder="<?php echo Lang::$word->NAME;?>" name="title">
          </div>
        </div>
        <div class="wojo fields align-middle">
          <div class="field four wide labeled">
            <label class="content-right mobile-content-left"><?php echo Lang::$word->MEM_PRICE;?> <i class="icon asterisk"></i></label>
          </div>
          <div class="field">
            <div class="wojo labeled input">
              <div class="wojo label"><?php echo Utility::currencySymbol();?></div>
              <input type="text" placeholder="<?php echo Lang::$word->MEM_PRICE;?>" name="price">
            </div>
          </div>
        </div>
        <div class="wojo fields align-middle">
          <div class="field four wide labeled">
            <label class="content-right mobile-content-left"><?php echo Lang::$word->MEM_DAYS;?> <i class="icon asterisk"></i></label>
          </div>
          <div class="field">
            <div class="wojo action input">
              <input type="text" placeholder="<?php echo Lang::$word->MEM_DAYS;?>" name="days">
              <select class="wojo compact selection dropdown" name="period">
                <?php echo Utility::loopOptionsSimpleAlt(Date::getMembershipPeriod());?>
              </select>
            </div>
          </div>
        </div>
        <div class="wojo fields align-middle">
          <div class="field four wide labeled">
            <label class="content-right mobile-content-left"><?php echo Lang::$word->MEM_PRIVATE;?></label>
          </div>
          <div class="field">
            <div class="wojo inline fields">
              <div class="wojo checkbox small radio slider field">
                <input name="private" type="radio" value="1">
                <label><?php echo Lang::$word->YES;?></label>
              </div>
              <div class="wojo checkbox small radio slider field">
                <input name="private" type="radio" value="0" checked="checked">
                <label><?php echo Lang::$word->NO;?></label>
              </div>
            </div>
          </div>
        </div>
        <div class="wojo fields align-middle">
          <div class="field four wide labeled">
            <label class="content-right mobile-content-left"><?php echo Lang::$word->MEM_REC;?></label>
          </div>
          <div class="field">
            <div class="wojo inline fields">
              <div class="wojo checkbox small radio slider field">
                <input name="recurring" type="radio" value="1" checked="checked">
                <label><?php echo Lang::$word->YES;?></label>
              </div>
              <div class="wojo checkbox small radio slider field">
                <input name="recurring" type="radio" value="0">
                <label><?php echo Lang::$word->NO;?></label>
              </div>
            </div>
          </div>
        </div>
        <div class="wojo fields align-middle">
          <div class="field four wide labeled">
            <label class="content-right mobile-content-left"><?php echo Lang::$word->PUBLISHED;?></label>
          </div>
          <div class="field">
            <div class="wojo inline fields">
              <div class="wojo checkbox small radio slider field">
                <input name="active" type="radio" value="1" checked="checked">
                <label><?php echo Lang::$word->YES;?></label>
              </div>
              <div class="wojo checkbox small radio slider field">
                <input name="active" type="radio" value="0">
                <label><?php echo Lang::$word->NO;?></label>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="columns screen-30 tablet-40 mobile-100 phone-100 screen-left-divider tablet-left-divider wojo secondary bg padding">
        <input type="file" name="thumb" data-type="image" accept="image/png, image/jpeg">
        <div class="wojo space divider"></div>
        <div class="field">
          <label><?php echo Lang::$word->DESCRIPTION;?></label>
          <textarea placehoder="<?php echo Lang::$word->DESCRIPTION;?>" name="description"></textarea>
        </div>
      </div>
    </div>
  </div>
  <div class="content-center"> <a href="<?php echo Url::url("/admin/memberships");?>" class="wojo button"><?php echo Lang::$word->CANCEL;?></a>
    <button type="button" data-action="processMembership" name="dosubmit" class="wojo secondary button"><?php echo Lang::$word->MEM_SUB1;?></button>
  </div>
</form>