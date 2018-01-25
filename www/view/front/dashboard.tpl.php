<?php
  /**
   * Dashboard
   *
   * @package Mani Media Manager
   * @author maninator
   * @copyright 2016
   * @version $Id: dashboard.tpl.php, v1.00 2016-01-08 10:12:05 gewa Exp $
   */
  if (!defined("_MANI"))
      die('Direct access to this location is not allowed.');
?>
<div class="content-center"><img src="<?php echo UPLOADURL;?>/avatars/<?php echo (App::Auth()->avatar) ? App::Auth()->avatar : "blank.png";?>" alt="" class="avatar"></div>
<div class="wojo big space divider"></div>
<div class="clearfix" id="tabs-alt"> <a class="active"> <?php echo Lang::$word->ADM_MEMBS;?></a> <a class="static" href="<?php echo Url::url("/dashboard/history");?>"><?php echo Lang::$word->HISTORY;?></a> <a class="static" href="<?php echo Url::url("/dashboard/profile");?>"><?php echo Lang::$word->M_SUB18;?></a> </div>
<div class="login-form login-form-new">
<div class="row screen-block-3 tablet-block-2 mobile-block-1 phone-block-1 double-gutters align-center">
  <?php if(App::Auth()->is_User()):?>
    <?php global $MANI_CONFIG; ?>
    <div class="menu-new">
      <div class="menu-sec">
      <a class="subs dashboard yellow display" href="<?php echo Url::url("/dashboard");?>"></a>        
           <span class="menu-title">Dashboard</span>
      </div>
      <div class="menu-sec">
        <a href="<?php echo $MANI_CONFIG["emby_url"]; ?>" class="subs black display">
          <?php echo '<img class="imgs" src="' . SITEURL . '/uploads/' . App::Core()->logo . '">'; ?>
        </a>        
          <span class="menu-title">Clickfix</span>
      </div>
      <div class="menu-sec">
      <a class="subs find display black" href="<?php echo $MANI_CONFIG["ombi_url"]; ?>"></a>        
           <span class="menu-title">Requests</span>
      </div><div class="menu-sec">
      <a class="subs red tv display" href="<?php echo Url::url("/dashboard/tvshows");?>"></a>        
           <span class="menu-title">TV Subscriptions</span>
      </div>
    </div>
    <div class="menu-new">
      <div class="menu-sec">
      <a class="subs movies red display" href="<?php echo Url::url("/dashboard/movies");?>"></a>        
           <span class="menu-title">Movie Subscriptions</span>
      </div>
      <div class="menu-sec">
      <a class="subs black news display" href=""></a>        
           <span class="menu-title">News</span>
      </div>
      <div class="menu-sec">
      <a class="subs packages yellow display" href="<?php echo Url::url("/packages");?>"></a>        
           <span class="menu-title">Menu</span>
      </div><div class="menu-sec">
      <a class="subs yellow  logout display" href="<?php echo Url::url("/logout");?>"></a>        
           <span class="menu-title">Logout</span>
      </div>
    </div>

    <?php endif;?>
  <?php unset($row);?>
</div>
<div id="mResult"></div>
<style>
.imgs {
    margin-top: 15px;
    width: 43px;
}
.subs {
    
    height: 4em;
    width: 4em;
    border-radius: 50%;
    margin-top: .6em;
    margin-left: .5em;
    opacity: 0;
    position: relative;
    z-index: 1;
    display: block;
    visibility: hidden;
}
.subs.yellow{
  background-color: #E68D27;
}
.subs.black{
  background-color: #000;
}
.subs.red{
  background-color: #D32F2F;
}
.login-form.login-form-new .menu-sec .subs::before {
    height: 2em;
    width: 2em;
    font-family: 'WojoIcons';
    position: absolute;
    color: #FFF;
    text-align: center;
    line-height: 55px;
    top: 50%;
    left: 50%;
    transform: translateX(-50%) translateY(-50%);
    font-size: 30px;
}
*, ::before, ::after {
    box-sizing: inherit;
}
.subs{
    visibility: visible;
}
.subs.dashboard::before {
    content: '\e077';
}
.subs.play::before {
    content: "\e0fc";
}
.subs.find::before {
    content: "\e09b";
}
.subs.tv::before {
    content: "\e148";
}
.subs.movies::before {
    content: '\e0e0';
}.subs.news::before {
    content: '\e0d9';

}.subs.packages::before {
    content: '\e01d';

}.subs.logout::before {
    content: '\e102';

}
.login-form.login-form-new .row.screen-block-3 {
    margin-left: 0px;
}
.login-form.login-form-new .menu-new {
    width: 100%;
    margin-bottom: 25px;
}
.login-form.login-form-new .menu-sec {
    width: 25%;
    float: left;
    text-align: center;
}
.login-form.login-form-new .menu-sec .subs.display {
    opacity: 1;
    visibility: visible;
    display: block;
    margin: 0px auto;
}
.login-form.login-form-new .menu-sec .menu-title {
    color: #444;
    margin-top: 10px;
    display: block;
}
.login-form.login-form-new .menu-sec:hover .subs{
  border:1px solid #fff;
}
@media screen and (max-width: 680px){
  .login-form.login-form-new .menu-sec {
    width: 100%;
    margin-bottom: 20px;
}
}
</style>