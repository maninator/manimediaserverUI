<?php
  /**
   * Ajax Class
   *
   * @package Mani Media Manager
   * @author maninator
   * @copyright 2016
   * @version $Id: Ajax.class.php, v1.00 2016-04-20 18:20:24 gewa Exp $
   */

	if (!defined("_MANI"))
		die('Direct access to this location is not allowed.');

  


class Ajax
{
	public static function query()
	{
		global $MANI_CONFIG;
		$auth = App::Auth();
		$do   = $_GET["do"];
		if($do=="hello_world")
		{
			$ret = Command::querySocket("");
		}
		if($do=="image")
		{
			Ajax::get_images();
		}
		if($do=="selectItem")
		{
			/*var_dump($_SESSION);
			die();*/
			$ret = array();
			if (isset($_SESSION["MMP_username"])) {
				$userid         = @$_SESSION["userid"];
				$username       = @$_SESSION["MMP_username"];
				$membership_id  = @$_SESSION["membership_id"];
				$item_id        = @$_GET["id"];
				$item_type      = @$_GET["type"];

				$cust_dir = $MANI_CONFIG['libraries'].'/'.$username;
				Ajax::ensure_dir($cust_dir);
				if ($item_type == "TV") {
					$working_cust_dir = $cust_dir.'/TVShows';
				}
				if ($item_type == "MOVIE") {
					$working_cust_dir = $cust_dir.'/Movies';
				}
				$data = Ajax::get_data($working_cust_dir, $item_id, $item_type);

				if (isset($_GET["select"])) {
					// Try creating a symlink
					if (!empty($data["folder"]) && !empty($data["location"])) {
						$link   = $working_cust_dir.'/'.$data["folder"];
						$target = $data["location"].'/'.$data["folder"];
						if (file_exists($link) || is_link($link)) {
							unlink($link);
						}
						if (!file_exists($link) || is_link($link)) {
							symlink($target, $link);
						}
					}
					$ret[] = "selected";
				} else if (isset($_GET["remove"])) {
					// Try Removing the symlink
					$ret[] = "removed";
					if (!empty($data["folder"]) && !empty($data["location"])) {
						$link   = $working_cust_dir.'/'.$data["folder"];
						if (file_exists($link) || is_link($link)) {
							unlink($link);
						}
					}
				}
				$user_data = array(
					"username" => $username
				);
				$emby   = new Emby();
				$result = $emby->rescan_user_library($user_data);
			}
			echo json_encode(array("result" => $ret));
			return;
		}
		if($do=="check_user")
		{
			$ret = array();
			if (isset($_SESSION["MMP_username"])) {
				$user_data = array(
					"username" => @$_SESSION["MMP_username"]
				);
				$emby   = new Emby();
				$result = $emby->check_for_user_by_name($user_data);
				echo json_encode(array("result" => $result));
				return;
			}
		}
		if($do=="test_check_user")
		{
			$ret = array();
			$user_data = array(
				"username" => @$_GET["username"]
			);
			$emby   = new Emby();
			$result = $emby->check_for_user_by_name($user_data);
			echo json_encode(array("result" => $result));
			return;
		}
		if($do=="test_add_user")
		{
			$ret = array();
			$data = array();
			$data["userid"]      = 3;
			$data["email"]       = "manimediamanager@gmail.com";
			$data["password"]    = "password";
			$data["username"]    = "odZg3YQX";
			$result = Ajax::create_emby_user_if_not_exists($data);
			echo json_encode(array("result" => $result));
			return;
		}
		if($do=="test_read_media_content")
		{
			if (isset($_GET["type"]) && $_GET["type"] == "tv") {
				$type = "TV";
			} else {
				$type = "MOVIE";
			}
			$result = Stats::userContent(App::Auth(), true, true, $type);
			echo json_encode(array("result" => $result));
			return;
		}
	}

	static function get_data($working_cust_dir, $item_id, $media_type = "TV") {
		Ajax::ensure_dir($working_cust_dir);
		$ret      = array();
		$data     = Stats::userContent(App::Auth(), true, false, $media_type);
		$folder   = "";
		$location = "";
		$found    = false;
		if (isset($data["available"])) {
			foreach ($data["available"] as $locations) {
				$location = $locations["location"];
				foreach ($locations["content"] as $content) {
					if ($content["id"] == $item_id) {
						$folder = $content["folder"];
						$found = true;
					}
					if ($found) break;
				}
				if ($found) break;
			}
		}
		$ret["location"] = $location;
		$ret["folder"]   = $folder;
		return $ret;
	}

	static function ensure_dir($dir) {
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}
	}

	static function create_emby_user_if_not_exists($data) {
		global $MANI_CONFIG;
		$create_new = false;
		$return_arr = array("error"=>false);
		$user_data  = array(
			"email"    => $data["email"],
			"username" => $data["username"],
			"password" => $data["password"]
		);
		// TODO: Check if user wants all drives
		$user_data['categories'] = array(
			"Movies-All",
			"TVShows-All"
		);
		// Create a new user with email address and password
		$cust_dir = $MANI_CONFIG['libraries'].'/'.$user_data["username"];
		Ajax::ensure_dir($cust_dir);
		Ajax::ensure_dir($cust_dir.'/TVShows');
		Ajax::ensure_dir($cust_dir.'/Movies');
		$emby = new Emby();
		return $emby->ensure_user($user_data);
	}

	static function get_images() {
		global $MEDIA_TV, $MEDIA_MOVIE, $MANI_CONFIG;
		$location = isset($_GET["loc"])   ? $_GET["loc"]   : NULL;
		$type     = isset($_GET["type"])  ? $_GET["type"]  : NULL;
		$media    = isset($_GET["media"]) ? $_GET["media"] : NULL;
		$full_file_name = "";
		if ($type == "TV"){
			$full_file_name = $MANI_CONFIG['media_dir'].'/'.$MEDIA_TV[$location]."/".urldecode($media);
		}
		if ($type == "MOVIE"){
			$full_file_name = $MANI_CONFIG['media_dir'].'/'.$MEDIA_MOVIE[$location]."/".urldecode($media);
		}
		if ($type == "network"){
			$full_file_name = BASEPATH . 'view/front/images/network/' . $media;
			if (!file_exists($full_file_name)) {
				$full_file_name = BASEPATH . 'view/front/images/network/logo.png';
			}
		}
		if (!empty($full_file_name)) {
			$full_file_name = escapeshellarg($full_file_name);
			header("Content-type: image/png");
			passthru("cat $full_file_name");
		}
	}
}