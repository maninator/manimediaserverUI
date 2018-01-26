<?php
  /**
   * Header
   *
   * @package Mani Media Manager
   * @author maninator
   * @copyright 2016
   * @version $Id: header.tpl.php, v1.00 2015-10-05 10:12:05 gewa Exp $
   */
  if (!defined("_MANI"))
      die('Direct access to this location is not allowed.');
 ?>
<!DOCTYPE html>
<head>
<meta charset="utf-8">
<title><?php echo $this->title;?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="apple-mobile-web-app-capable" content="yes">
<script type="text/javascript" src="<?php echo SITEURL;?>/assets/jquery.js"></script>
<script type="text/javascript" src="<?php echo SITEURL;?>/assets/global.js"></script>
<script type="text/javascript" src="<?php echo SITEURL;?>/assets/subscribe.js"></script>
<link href="<?php echo FRONTVIEW . '/cache/' . Cache::cssCache(array('base.css','transition.css','dropdown.css','menu.css','label.css','message.css','list.css','table.css','form.css','input.css','icon.css','button.css','segment.css','divider.css','dimmer.css','modal.css','utility.css','popup.css','style.css'), FRONTBASE);?>" rel="stylesheet" type="text/css" />
<link rel="shortcut icon" type="image/png" href="<?php echo SITEURL . '/uploads/' . App::Core()->logo ?> "/>
<?php if(App::Auth()->is_User()):?>
  <link href="<?php echo FRONTVIEW . '/css/tvshows.css' ?>" rel="stylesheet" type="text/css" />
  <link href="<?php echo FRONTVIEW . '/css/tvshows-dark.css' ?>" rel="stylesheet" type="text/css" />
<?php endif;?>
</head>
<body>
<div id="menu">
<div class="actionButton"></div>
  <?php if(App::Auth()->is_User()):?>
    <?php global $MANI_CONFIG; ?>
    <a href="<?php echo Url::url("/dashboard");?>" class="sub dashboard" data-content="<?php echo Lang::$word->ADM_DASH;?>" data-position="left center"></a>
    <a href="<?php echo $MANI_CONFIG["emby_url"]; ?>" class="sub dark emby" data-content="<?php echo Lang::$word->ADM_EMBY;?>" data-position="left center">
    <?php echo '<img class="imgsh" src="' . SITEURL . '/uploads/' . App::Core()->logo . '">'; ?>
    </a>
    <a href="<?php echo $MANI_CONFIG["ombi_url"]; ?>" class="sub dark ombi" data-content="<?php echo Lang::$word->ADM_OMBI;?>" data-position="left center"><img src="<?php echo FRONTVIEW . '/images/ombi.png'; ?>"></a>
    <a href="<?php echo Url::url("/dashboard/tvshows");?>" class="sub red tvshows" data-content="<?php echo Lang::$word->ADM_TV_SUBS;?>" data-position="left center"></a>
    <a href="<?php echo Url::url("/dashboard/movies");?>" class="sub red movies" data-content="<?php echo Lang::$word->ADM_MOVIE_SUBS;?>" data-position="left center"></a>
    <a class="sub dark news" data-content="<?php echo Lang::$word->ADM_NEWS;?>" data-position="left center"></a>
    <?php else:?>
    <a href="<?php echo Url::url('');?>" class="sub login" data-content="<?php echo Lang::$word->M_SUB16;?>" data-position="left center"></a>  
    <a href="<?php echo Url::url("/register");?>" class="sub register" data-content="<?php echo Lang::$word->M_SUB17;?>" data-position="left center"></a>  
    <?php endif;?>
    <a href="<?php echo Url::url("/packages");?>" class="sub packages" data-content="<?php echo Lang::$word->ADM_MEMBS;?>" data-position="left center"></a>  
    <?php if(false):?>
    <a href="<?php echo Url::url("/contact");?>" class="sub contact" data-content="<?php echo Lang::$word->CONTACT;?>" data-position="left center"></a> 
    <?php endif;?>
    <?php if(App::Auth()->is_User()):?>
    <a href="<?php echo Url::url("/logout");?>" class="sub logout" data-content="<?php echo Lang::$word->LOGOUT;?>" data-position="left center"></a> 
    <?php endif;?>
</div>
<div class="wojo-grid">
<div id="logo"><a href="<?php echo SITEURL;?>/" class="logo"><?php echo (App::Core()->logo) ? '<img src="' . SITEURL . '/uploads/' . App::Core()->logo . '" alt="'.App::Core()->company . '">': App::Core()->company;?></a></div>
<style>
.imgsh {
    margin-top: 4px;
    width: 25px;
    margin-left: 3px;
}
</style>