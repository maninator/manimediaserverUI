<?php
    /**
    * EMBY Class
    *
    * @package Mani Media Manager
    * @author maninator
    * @copyright 2016
    * @version $Id: emby.class.php, v1.00 2016-04-20 18:20:24 gewa Exp $
    */

    /*if (!defined("_MANI"))
        die('Direct access to this location is not allowed.');*/


$debugging = False;

define('EMBY_HOST', 'easyuse.tv');
define('EMBY_PORT', '8096');
define('EMBY_USER', 'master');
define('EMBY_PASS', 'mymasterpassword');
define('EMBY_LIBRARY_DIR', '/media/libraries'); // Folder where user favorite media content links are created


class Emby
{
    private $DEF_AUTH_HEADER    = 'MediaBrowser Client="mani",Device="Mani Media Manager",DeviceId="8796757984356",Version="1.4"';
    private $API_URL            = '';
    private $DEF_HEADERS        = array();
    private $POLICY_DEFAULTS    = array();
    private $ACCOUNT_DEFAULTS   = array();

    private $emby               = NULL;
    private $_token             = NULL;
    private $_authed            = NULL;
    private $_cookies           = NULL;
    private $_session           = array();
    private $_authheadder       = NULL;

    public function __construct($params=array()) {
        $this->con          = @$params['con'];
        $this->cache        = @$params['cache'];
        $this->debugging    = @$params['debugging'];

        $this->_authed      = False;
        $this->_authheader  = $this->DEF_AUTH_HEADER;
        $this->API_URL      = 'http://' . EMBY_HOST . ':' . EMBY_PORT . '/emby';

        $this->POLICY_DEFAULTS = array(
            "IsAdministrator" => False,
            "IsHidden" => True,
            "IsDisabled" => False,
            "BlockedTags" => array(),
            "EnableUserPreferenceAccess" => True,
            "AccessSchedules" => array(),
            "BlockUnratedItems" => array(),
            "EnableRemoteControlOfOtherUsers" => False,
            "EnableSharedDeviceControl" => False,
            "EnableLiveTvManagement" => False,
            "EnableLiveTvAccess" => False,
            "EnableMediaPlayback" => True,
            "EnableAudioPlaybackTranscoding" => True,
            "EnableVideoPlaybackTranscoding" => True,
            "EnablePlaybackRemuxing" => True,
            "EnableContentDeletion" => False,
            "EnableContentDownloading" => False,
            "EnableSyncTranscoding" => False,
            "EnabledDevices" => array(),
            "EnableAllDevices" => True,
            "EnabledChannels" => array(),
            "EnableAllChannels" => True,
            "EnabledFolders" => array(),
            "EnableAllFolders" => False,
            "InvalidLoginAttemptCount" => 0,
            "EnablePublicSharing" => False,
            "RemoteClientBitrateLimit" => 0,
            "BlockedChannels" => NULL,
            "BlockedMediaFolders" => NULL
        );
        $this->ACCOUNT_DEFAULTS = array(
            "Policy" => $this->POLICY_DEFAULTS,
            "Configuration" => array(
                "SubtitleMode" => "Default", 
                "HidePlayedInLatest" => True, 
                "GroupedFolders" => array(), 
                "DisplayCollectionsView" => False, 
                "OrderedViews" => array(), 
                "LatestItemsExcludes" => array(), 
                "EnableLocalPassword" => False, 
                "RememberAudioSelections" => True, 
                "RememberSubtitleSelections" => True, 
                "DisplayMissingEpisodes" => False, 
                "PlayDefaultAudioTrack" => True, 
                "EnableNextEpisodeAutoPlay" => True
            )
        );
    }

    function logError($text, $file, $showMessage = False) {
        global $debugging;
        // TODO: Make this function global
        $fp = fopen("../error.log", "a");
        if ($fp!==false) {
            $date = date("d/m/Y H:i", time());
            fwrite($fp, $date." - ".$file." - ".$text."\n");
            fclose($fp);
        }
        if ($showMessage) {
            // TODO ADD MESSAGE TO MANI DEBUGGER
            //addMessage('An error has been logged at '.date("jS M H:i", time()).' please contact support or try again.');
            if ($debugging) {
                echo("\n[".date("jS M H:i", time())."] ERROR - " . $text . "\n\n");
            } 
        }
    }

    function curl_post($url, $method = 'GET', array $data = NULL, $headers=NULL, array $options = array()) { 
        global $debugging;
        //global $cookieFileLocation;
        if ($debugging){
            echo("\nREAD REMOTE LOCATION - URL: #".$url."#\n");
        }
        $defaults = array( 
            CURLOPT_URL => $url, 
            CURLOPT_FRESH_CONNECT => 1, 
            CURLOPT_RETURNTRANSFER => 1, 
            CURLOPT_FORBID_REUSE => 1, 
            CURLOPT_TIMEOUT => 20, 
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        );

        if (!$headers) {
            $headers[] = 'x-emby-authorization: ' . $this->_authheader;
            $headers[] = 'Accept: application/json';
        }

        if ($method == 'JSON') {
            $headers[] = 'Content-Type: application/json';
        }

        if ($headers) {
            $defaults[CURLOPT_HTTPHEADER] = $headers;
        }

        if (!empty($data)) {
            if ($method == 'JSON') {
                $defaults[CURLOPT_POSTFIELDS] = json_encode($data);
            } else if ($method == 'GET') {
                $defaults[CURLOPT_URL] .= '?' . http_build_query($data);
            } else {
                $defaults[CURLOPT_POST] = 1;
                $defaults[CURLOPT_POSTFIELDS] = http_build_query($data);
            }
        }

        $ch = curl_init();
        $opt_array =  $options + $defaults;
        if ($debugging){
            print_r($opt_array);
        }
        curl_setopt_array($ch, ($options + $defaults)); 
        if( ! $result = curl_exec($ch)) 
        { 
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpcode != 204){
                $error = curl_error($ch);
                trigger_error($error);
                $this->logError($error, __FILE__ . ', LINE #' . __LINE__);
            }
        } 
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($debugging){
            print_r($httpcode);
        }

        return array(
            "result" => $result,
            "status_code" => $httpcode
        );
    }

    function check_auth() {
        $res    = array("status" => False, "error" => "Unknown");
        $url    = $this->API_URL . '/Sessions';
        $result = $this->curl_post($url);
        if ($result['status_code'] == 200) {
            $this->_authed = True;
            return True; // Is authed
        }
        return False;
    }

    function get_user_session($params) {
        $url  = $this->API_URL . '/Users/AuthenticateByName';
        $pas  = $this->emby_hash_password($params["password"]);
        if (isset($params["username"])) {
            $username = $params["username"];
        } else if (isset($params["email"])) {
            $username = $params["email"];
        }
        $data = array(
            'Password' => $pas["sha1"],
            'PasswordMd5' => $pas["md5"],
            'Username' => $username
        );
        $result = $this->curl_post($url, "JSON", $data);
        if ($result['status_code'] != 200) {
            return array("found" => False);
        }
        $login_info  = json_decode($result['result'], True);
        return array("found" => True, "data" => $login_info);
    }

    function authenticate() {
        $params = array(
            'username'  => EMBY_USER,
            'password'  => EMBY_PASS
        );
        $res = $this->get_user_session($params);
        if ($res['found']) {
            $login_info  = $res['data'];
            // Append access token to header for future requests
            $this->_token = $login_info['AccessToken'];
            $this->_authheader = $this->DEF_AUTH_HEADER . ', Token="' . $login_info['AccessToken'] . '"';
            return $login_info;
        } else {
            $this->logError('Unable to login', __FILE__ . ', LINE #' . __LINE__);
        }
    }


    function connect() {
        if ($this->_token) {
            if ($this->_authed) {
                return;
            } else {
                $this->_authed = $this->check_auth();
            }
        }
        if (!$this->_authed) {
            $this->authenticate();
        }
    }

    function emby_hash_password($password) {
        return array(
            'sha1'  => sha1(utf8_encode($password)), //hashlib.sha1(str(password).encode('utf-8')).hexdigest(),
            'md5'   =>  md5(utf8_encode($password))
        );
    }


    // @MAIN FUNCTIONS:
    function get_users() {
        $this->connect();
        $res    = array("status"=>False, "error"=>"Unknown");
        $url    = $this->API_URL . '/users';
        $result = $this->curl_post($url, "GET");
        if ($result['status_code'] == 200) {
            $res = array("status" => True, "result" => $result["result"]);
        } else {
            $this->logError('Unable to retrieve users', __FILE__ . ', LINE #' . __LINE__);
        }
        return $res;
    }

    function get_media_locations() {
        $res    = array("status"=>False, "error"=>"Unknown");
        $url    = $this->API_URL . '/Library/MediaFolders';
        $data   = array('IsHidden'=> False);
        $result = $this->curl_post($url, "GET", $data);
        if ($result['status_code'] == 200) {
            $res = array("status" => True, "result" => $result["result"]);
        } else {
            $this->logError('Unable to get media locations', __FILE__ . ', LINE #' . __LINE__);
        }
        return $res;
    }

    function get_account_media_locations($username, $additional_categories=array()) {
        $folders    = array();
        $res        = $this->get_media_locations();
        if ($res["status"]) {
            $locations  = json_decode($res["result"], True);
            foreach ($locations['Items'] as $value) {
                $name = $value['Name'];
                if (in_array($username, explode("-", $name))) {
                    $folders[] = $value['Id'];
                }
                if (in_array($name, $additional_categories)) {
                    $folders[] = $value['Id'];
                }
            }
        }
        return $folders;
    }

    function add_library($username, $lib_type) {
        # Note - Folder must exists or will return with 400 http error.
        $res    = array("status"=>False, "error"=>"Unknown");
        $url = $this->API_URL . '/Library/VirtualFolders';
        if ($lib_type == "tvshows") {
            $lib_name = "TVShows-" . $username;
            $dir_name = "TVShows";
        } else if ($lib_type == "movies") {
            $lib_name = "Movies-" . $username;
            $dir_name = "Movies";
        }

        $url_vars = '?collectionType=' . $lib_type . '&refreshLibrary=true&name=' . $lib_name;
        $url .= $url_vars;
        $data = array(
            "LibraryOptions"                            => array(
                "EnableArchiveMediaFiles"               => False,
                "EnablePhotos"                          => True,
                "EnableRealtimeMonitor"                 => False,
                "ExtractChapterImagesDuringLibraryScan" => False,
                "EnableChapterImageExtraction"          => False,
                "DownloadImagesInAdvance"               => False,
                "EnableInternetProviders"               => False,
                "ImportMissingEpisodes"                 => False,
                "SaveLocalMetadata"                     => True,
                "EnableAutomaticSeriesGrouping"         => True,
                "PreferredMetadataLanguage"             => "en",
                "MetadataCountryCode"                   => "US",
                "AutomaticRefreshIntervalDays"          => 0,
                "EnableEmbeddedTitles"                  => False,
                "PathInfos"                             => array(
                    array(
                            "Path" => EMBY_LIBRARY_DIR . '/'. $username . '/' . $dir_name
                    )
                )
            )
        );
        $result = $this->curl_post($url, "JSON", $data);
        if ($result['status_code'] == 400) {
            if (strpos($result['result'], 'The specified path does not exist') !== false) {
                $res["error"]  = $result['result'];
                $this->logError($result['result'], __FILE__ . ', LINE #' . __LINE__);
            }
        } else if ($result['status_code'] == 204) {
            $res = array("status"=>True, "result"=>$result);

        } else {
            $this->logError('Unable to add folder to library', __FILE__ . ', LINE #' . __LINE__);
        }
        return $res;
    }


    function check_for_user_by_name($params) {
        $this->connect();
        $results = $this->get_users();
        if ($results["status"]) {
            $results  = json_decode($results["result"], True);
            foreach ($results as $value) {
                if ($value['Name'] == $params['email']) {
                    return array("found" => True, "data" => $value);
                }
            }
        }
        return array("found" => False);
    }

    function add_account($name) {
        # Create a new accont. (This needs to be posted with x-www-form-urlencode.)
        $res    = array("status"=>False, "error"=>"Unknown");
        $url    = $this->API_URL . '/Users/New';
        $data   = array('Name' => $name);

        $headers = array(
            'x-emby-authorization: ' . $this->_authheader,
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded;'
        );
        $result = $this->curl_post($url, "POST", $data, $headers);
        if ($result['status_code'] == 200) {
            $res = array("status" => True, "result" => $result["result"]);
        } else if ($result['status_code'] == 400) {
            $res = array("status" => False, "result" => $result["result"]);
            $this->logError($result["result"], __FILE__ . ', LINE #' . __LINE__);
        } else {
            $this->logError('Unable to add a new account for ' . $name, __FILE__ . ', LINE #' . __LINE__);
        }
        return $res;
    }

    function edit_account_policy($user_info, $policy) {
        # Note - Need to get user ID and a list of enabled folder before running this. (GET from get_media_locations())
        # Apply account policy edit
        $res    = array("status"=>False, "error"=>"Unknown");
        $url    = $this->API_URL . '/Users/' . $user_info['Id'] . '/Policy';
        $result = $this->curl_post($url, "JSON", $policy);
        if ($result['status_code'] == 204) {
            $res = array("status" => True, "result" => $result["result"]);
        } else {
            $this->logError('Unable to edit user account policy settings for ' . $user_info['Id'], __FILE__ . ', LINE #' . __LINE__);
        }
        return $res;
    }

    function edit_account_access($user_info, $username, $categories) {
        $res    = array("status"=>False, "error"=>"Unknown");
        $url    = $this->API_URL . '/Users/' . $user_info['Id'];
        // Update Policy
        $policy = $user_info['Policy'];
        foreach ($this->POLICY_DEFAULTS as $key => $value) {
            $policy[$key] = $value;
        }
        $user_info['Policy'] = $policy;
        // Get Account Folders
        $enabled_folders = $this->get_account_media_locations($username, $categories);
        $user_info['Policy']['EnabledFolders'] = $enabled_folders;
        // Set User Config
        $result = $this->curl_post($url, "JSON", $user_info);
        if ($result['status_code'] == 204) {
            // Set User Policy
            $result = $this->edit_account_policy($user_info, $user_info['Policy']);
            $res = array("status" => $result["status"], "result" => $user_info);
        } else {
            $this->logError('Unable to edit user account access settings for ' . $user_info['Id'], __FILE__ . ', LINE #' . __LINE__);
        }
        return $res;
    }

    function edit_account_password($user_info, $old_password="", $new_password="") {
        # Edit accont. (This needs to be posted with x-www-form-urlencode.)
        $res  = array("status"=>False, "error"=>"Unknown");
        $url  = $this->API_URL . '/Users/' . $user_info['Id'] . '/Password';
        $opas = $this->emby_hash_password($old_password);
        $npas = $this->emby_hash_password($new_password);
        $headers = array(
            'x-emby-authorization: ' . $this->_authheader,
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded;'
        );
        # First reset the password:
        $data = array(
            "resetPassword" => True
        );
        $result = $this->curl_post($url, "POST", $data, $headers);
        if ($result['status_code'] == 204) {
            // The password reset was successful, now apply new password
            $data = array(
                "currentPassword"   => $opas["sha1"],
                "newPassword"       => $npas["sha1"]
            );
            $result = $this->curl_post($url, "POST", $data, $headers);
            if ($result['status_code'] == 204) {
                $res = array("status" => True, "result" => $result["result"]);
            } else {
                $res["error"] = "Unable to set new password";
                $this->logError('Unable to set new password for ' . $user_info['Id'], __FILE__ . ', LINE #' . __LINE__);
            }
        } else {
            $res["error"] = "Unable to reset password";
            $this->logError('Unable to reset password for ' . $user_info['Id'], __FILE__ . ', LINE #' . __LINE__);
        }
        return $res;
    }

    function ensure_user($params) {
        $this->connect();
        // Add new library location for user
        $add_library    = array("tvshows", "movies");
        $res      = $this->get_media_locations();
        if ($res["status"]) {
            $locations  = json_decode($res["result"], True);
            foreach ($locations['Items'] as $value) {
                $name = $value['Name'];
                if (in_array($params["username"], explode("-", $name))) {
                    if (($key = array_search($value['CollectionType'], $add_library)) !== false) {
                        unset($add_library[$key]);
                    }
                }
            }
            foreach ($add_library as $value) {
                $lib_add_res = $this->add_library($params["username"], $value);
            }
        }

        // Add new account and update user info from newly create account:
        $user_exists = $this->check_for_user_by_name($params);
        if ($user_exists['found']) {
            $user_info = $user_exists["data"];
        } else {
            $res = $this->add_account($params["email"]);
            if ($res["status"]) {
                $user_info  = json_decode($res["result"], True);
            }
        }

        // Setup newly created account with Mani defaults and update user info:
        if ($user_info) {
            // Setup account access settings
            $res = $this->edit_account_access($user_info, $params["username"], $params["categories"]);
            if ($res["status"]) {
                array_merge($user_info, $res["result"]);
            }
            // Set password for newly created account
            $this->edit_account_password($user_info, "", $params["password"]);
        }
        return $user_info;
    }

    function rescan_user_library($params) {
        // First get media locations:
        $this->connect();
        $locations = $this->get_account_media_locations($params["username"]);
        if ($locations) {
            $headers = array(
                'x-emby-authorization: ' . $this->_authheader,
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded;'
            );
            # First reset the password:
            $data = array(
                'Recursive' => True,
                'ImageRefreshMode' => 'Default',
                'MetadataRefreshMode' => 'Default',
                'ReplaceAllImages' => False,
                'ReplaceAllMetadata' => False
            );
            foreach ($locations as $value) {
                $url    = $this->API_URL . '/Items/' . $value . '/Refresh';
                $result = $this->curl_post($url, "POST", $data, $headers);
            }
        }
        return $locations;
    }
}



// ######### TESTS:

function test_user_create($email, $username, $password) {
    $emby = new Emby(array());
    $params = array(
        "email"     => $email,
        "username"  => $username,
        "password"  => $password,
        "categories"  => array(
            "Movies-All",
            "TVShows-All"
        )

    );
    $user_info = $emby->ensure_user($params);
    echo "\n\n\nUSER INFO:\n\n";
    print_r($user_info);
}

function test_rescan_user_library($email, $username, $password) {
    $emby = new Emby(array());
    $params = array(
        "email"     => $email,
        "username"  => $username
    );
    $user_info = $emby->rescan_user_library($params);
    echo "\n\n\nUSER INFO:\n\n";
    print_r($user_info);
}


//test_rescan_user_library("manimediamanager@gmail.com", "T0CwxuYF", "password");
