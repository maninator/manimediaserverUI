<div class="nav"><b>pre-installation check</b> &raquo; <b>license</b> &raquo; <b>configuration</b> &raquo; completed</div>
<h2 id="install">General Configuration</h2>
<?php echo ($_SESSION['msg']) ?  "<div class=\"error\">{$_SESSION['msg']}</div>" : '';?>
<form action="setup.php?step=2" method="post">
  <p> Setting up Membership Manager Pro to run on your server involves 3 simple steps...Please enter the hostname of the server Membership Manager Pro is to be installed on. Enter the MySQL username, password and database name you wish to use with Membership Manager Pro. It's strongly recommended to install sample data. </p>
  <h3>1. MySQL database configuration:</h3>
  <table class="inner-content data">
    <tr>
      <td>MySQL Hostname:</td>
      <td><input type="text" name="dbhost" size="30" value="<?php echo isset($_POST['dbhost']) ? sanitize($_POST['dbhost']) : 'localhost'; ?>" id="t1"></td>
      <td><div class="err" id="err1">Please input correct MySQL hostname.</div></td>
    </tr>
    <tr>
      <td>MySQL User Name:</td>
      <td><input type="text" name="dbuser" size="30" value="<?php echo isset($_POST['dbuser']) ? sanitize($_POST['dbuser']) : ''; ?>" id="t2"></td>
      <td><div class="err" id="err2">Please input correct MySQL username.</div></td>
    </tr>
    <tr>
      <td>MySQL Password:</td>
      <td><input type="password" name="dbpwd" size="30" value="" /></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>MySQL Database Name:</td>
      <td><input type="text" name="dbname" size="30" value="<?php echo isset($_POST['dbname']) ? sanitize($_POST['dbname']) : ''; ?>" id="t3"/></td>
      <td><div class="err" id="err3">Please input correct database name.</div></td>
    </tr>
    <tr>
      <td>Install sample data:</td>
      <td><input type="checkbox" id="install_data" name="install_data" checked="checked"></td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <input type="hidden" name="db_action" id="db_action" value="1">
  <h3>2. Common configuration</h3>
  <p>Configure correct paths and URLs to your Membership Manager Pro.</p>
  <table class="inner-content data">
    <tr>
      <td>Install Directory:</td>
      <td><input type="text" name="site_dir" value="<?php echo str_replace("/", "", $script_path);?>" size="30" readonly></td>
    </tr>
    <tr>
      <td>Server Name:</td>
      <td><input type="text" name="company" value="Your Server Name" size="30"></td>
    </tr>
    <tr>
      <td>Site Email:</td>
      <td><input type="text" name="site_email" value="site@mail.com" size="30" id="t4">
      <div class="err" id="err4">Please input correct admin username.</div></td>
    </tr>
  </table>
  <h3>3. Administrator configuration</h3>
  <p>Please set your admin username. It will be used for loggin to your admin panel. Your temporary password is right bellow. Take a note of it, you will need to to login into your admin panel. Once logged in you can chnage it to something more secure.</p>
  <table class="inner-content data">
    <tr>
      <td>Admin Username:</td>
      <td><input type="text" name="admin_username" value="<?php echo isset($_POST['admin_username']) ? sanitize($_POST['admin_username']) : 'admin'; ?>" size="30" id="t5"></td>
      <td><div class="err" id="err5">Please input correct admin username.</div></td>
    </tr>
    <tr>
      <td>Temp Password:</td>
      <td><input type="text" name="pass" value="pass1234" size="30" disabled></td>
      <td>&nbsp;</td>
    </tr>
  </table>
  <div class="btn lgn">
    <button type="button" onclick="document.location.href='setup.php?step=1';" name="back">Back</button>
    &nbsp;&nbsp;
    <button type="submit" name="next">Next</button>
  </div>
</form>