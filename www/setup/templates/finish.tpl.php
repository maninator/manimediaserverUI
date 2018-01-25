<div class="nav"><b>pre-installation check &raquo; license &raquo; configuration &raquo; completed</b></div>
<h2 id="install">Installation completed</h2>
<h3>Installation log:</h3>
<table class="inner-content data">
  <tr>
    <td>Database Installation</td>
    <td><?php if($_SESSION['msg']):?>
      <span class="no">Error during MySQL queries execution:</span><br>
      <?php echo $_SESSION['msg']; ?>
      <?php else:?>
      <span class="yes">OK</span>
      <?php endif;?></td>
  </tr>
  <tr>
    <td>Configuration File</td>
    <td>Available for download<br />
      If there was a problem creating config file, you MUST save config.inc.php file to your local PC and then upload to Mani Media Manager <strong>/lib/</strong> directory. <a href="javascript:void(0);" onclick="if (document.getElementById('file_content').style.display=='block') { document.getElementById('file_content').style.display='none';} else {document.getElementById('file_content').style.display='block'}">Click here</a> to view the content of config.ini.php file.<br />
      <div style="margin: 10px 0; text-align: center;">
        <input type="button" onclick="document.location.href='safe_config.php?h=<?php echo $_POST['dbhost'].'&u='.$_POST['dbuser'].'&p='.$_POST['dbpwd'].'&n='.$_POST['dbname'].'&k='.sessionKey();?>';" value="Download config.ini.php" />
      </div></td>
  </tr>
  <tr>
    <td colspan="2"><div style="display:none;border: 1px solid #777;height: 400px; background-color: #fff; padding:10px;overflow:auto;" id="file_content">
        <?php if (is_callable("highlight_string")):?>
        <?php $param = array("host" => $_POST['dbhost'], "user" => $_POST['dbuser'], "pass" => $_POST['dbpwd'], "name" => $_POST['dbname'], "key" => sessionKey());?>
        <?php highlight_string(writeConfigFile($param, true));?>
        <?php endif;?>
      </div></td>
  </tr>
  <tr>
    <td colspan="2"><div class="remove_install">Now you MUST completely remove 'setup' directory from your server.</div>
      <br />
      <div class="remove_install">Please for security reasons chmod your /<b>lib</b>/ directory to 0755.</div></td>
  </tr>
</table>
<div class="btn lgn">
  <button type="button" onclick="history.go(-1);" name="check">Back</button>
  &nbsp;&nbsp;
  <button type="button" onclick="document.location.href='../admin/';" name="next" tabindex="3">Admin</button>
</div>
