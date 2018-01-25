<?php
  /**
   * Command Class
   *
   * @package Mani Media Manager
   * @author maninator
   * @copyright 2016
   * @version $Id: command.class.php, v1.00 2016-04-20 18:20:24 gewa Exp $
   */

	if (!defined("_MANI"))
		die('Direct access to this location is not allowed.');

  
/**
*  include_once(BASEPATH . "lib/command.class.php");
*  $res = Command::querySocket("");
*  die();
*/

class Command
{
	public static function querySocket($query, $host='127.0.0.1', $port='3626')
	{
		$hello_world = '{"name":"Status", "type":"request", "request":{"type":"status","query":{"type":"hello_world"}}}';
		if (empty($query)) {
			$query = $hello_world;
		}
		$result = array();
		$fp = stream_socket_client("tcp://".$host.":".$port, $errno, $errstr, 30);
		if (!$fp) {
		    echo "$errstr ($errno)<br />\n";
		} else {
			
		    fwrite($fp, $query);
		    while (!feof($fp)) {
		        $result[] = fgets($fp, (10 * 1024));
		    }
		    fclose($fp);
		}
		return $result;
	}

	static function emby_check_user($user_data)
	{
		$query = array(
			"name" => "Configure",
			"type" => "request",
			"request" => array(
				"type" => "status",
				"query" => array(
					"type" => "service_configure",
					"service" => "embyserver",
					"function" => "check_for_user_by_name",
					"params" => array(
						"email" 	=> $user_data["email"],
						"username" 	=> $user_data["username"]
					)
				)
			)
		);
		$res = Command::querySocket(json_encode($query));
		return $res;
	}

	static function emby_create_user($user_data)
	{
		$query = array(
			"name" => "Configure",
			"type" => "request",
			"request" => array(
				"type" => "status",
				"query" => array(
					"type" => "service_configure",
					"service" => "embyserver",
					"function" => "create_new_user",
					"params" => array(
						"email" 	=> $user_data["email"],
						"username" 	=> $user_data["username"],
						"password" 	=> $user_data["password"],
						"categories"=> array("Movies-All","TVShows-All")
					)
				)
			)
		);
		$res = Command::querySocket(json_encode($query));
		return $res;
	}

	static function emby_rescan_user_library($user_data)
	{
		$query = array(
			"name" => "Configure",
			"type" => "request",
			"request" => array(
				"type" => "status",
				"query" => array(
					"type" => "service_configure",
					"service" => "embyserver",
					"function" => "rescan_user_library",
					"params" => array(
						"username" => $user_data["username"]
					)
				)
			)
		);
		$res = Command::querySocket(json_encode($query));
		return $res;
	}

	static function emby_get_user_session($user_data)
	{
		$query = array(
			"name" => "Configure",
			"type" => "request",
			"request" => array(
				"type" => "status",
				"query" => array(
					"type" => "service_configure",
					"service" => "embyserver",
					"function" => "get_user_session",
					"params" => array(
						"username" 	=> $user_data["email"],
						"password" 	=> $user_data["password"]
					)
				)
			)
		);
		$res = Command::querySocket(json_encode($query));
		return $res;
	}
}