#!/usr/bin/env python3
# -*- coding: utf-8 -*-
#
# @Author: Mani
# @Date:   2017-08-28 19:20:58
# @Last Modified time: 2017-09-25 21:44:22
# 
##############################################


import json
import requests
import time
import codecs
import hashlib

import mani_config

mani_options = mani_config.read_config()
CONFIG = {
    'host': mani_options.get('Emby', 'host'),
    'port': mani_options.get('Emby', 'port'),
    'user': mani_options.get('Emby', 'user'),
    'pass': mani_options.get('Emby', 'pass'),
}

DEF_AUTH_HEADER = 'MediaBrowser Client="mani",Device="Mani Media Manager",DeviceId="8796757984356",Version="1.4"'

DEF_HEADERS = {
    'x-emby-authorization': DEF_AUTH_HEADER, 
    'Content-Type': 'application/json', 
    'Accept': 'application/json'
}

POLICY_DEFAULTS = {
    "IsAdministrator": False,
    "IsHidden": True,
    "IsDisabled": False,
    "BlockedTags": [],
    "EnableUserPreferenceAccess": True,
    "AccessSchedules": [],
    "BlockUnratedItems": [],
    "EnableRemoteControlOfOtherUsers": False,
    "EnableSharedDeviceControl": False,
    "EnableLiveTvManagement": False,
    "EnableLiveTvAccess": False,
    "EnableMediaPlayback": True,
    "EnableAudioPlaybackTranscoding": True,
    "EnableVideoPlaybackTranscoding": True,
    "EnablePlaybackRemuxing": True,
    "EnableContentDeletion": False,
    "EnableContentDownloading": False,
    "EnableSyncTranscoding": False,
    "EnabledDevices": [],
    "EnableAllDevices": True,
    "EnabledChannels": [],
    "EnableAllChannels": True,
    "EnabledFolders": [],
    "EnableAllFolders": False,
    "InvalidLoginAttemptCount": 0,
    "EnablePublicSharing": False,
    "RemoteClientBitrateLimit": 0,
    "BlockedChannels": None,
    "BlockedMediaFolders": None
}

ACCOUNT_DEFAULTS = {
    "Policy": POLICY_DEFAULTS,
    "Configuration": {
        "SubtitleMode": "Default", 
        "HidePlayedInLatest": True, 
        "GroupedFolders": [], 
        "DisplayCollectionsView": False, 
        "OrderedViews": [], 
        "LatestItemsExcludes": [], 
        "EnableLocalPassword": False, 
        "RememberAudioSelections": True, 
        "RememberSubtitleSelections": True, 
        "DisplayMissingEpisodes": False, 
        "PlayDefaultAudioTrack": True, 
        "EnableNextEpisodeAutoPlay": True
    }
}



class Urlfetch():
    def __init__(self,cookie_file=False,timeout=20,debug=False):
        self.timeout = timeout
        self.debug   = debug
        # Configure session and cookies
        self.http_session = requests.Session()
        self.use_cookie = False
        if cookie_file:
            self.use_cookie = True
            self.cookie_jar = cookielib.LWPCookieJar(cookie_file)
            try:
                self.cookie_jar.load(ignore_discard=True, ignore_expires=True)
            except IOError:
                pass
            self.http_session.cookies = self.cookie_jar

        def makearr(tsteps):
            global stemps
            global steps
            stemps = {}
            for step in tsteps:
                stemps[step] = { 'start': 0, 'end': 0 }
            steps = tsteps
        makearr(['init','check'])

        def starttime(typ = ""):
            for stemp in stemps:
                if typ == "":
                    stemps[stemp]['start'] = time.time()
                else:
                    stemps[stemp][typ] = time.time()
        starttime()

    def __str__(self):
        return str(self.url)

    def log(self, string):
        if self.debug:
            try:
                print ('[Urlfetch]: %s' % string)
            except UnicodeEncodeError:
                # we can't anticipate everything in unicode they might throw at
                # us, but we can handle a simple BOM
                bom = unicode(codecs.BOM_UTF8, 'utf8')
                print ('[Urlfetch]: %s' % string.replace(bom, ''))
            except:
                pass

    def make_request(self, url, method, payload=None, headers=None, allow_redirects=False):
        """Make an http request. Return the response."""
        self.log('Request URL: %s' % url)
        self.log('Headers: %s' % headers)
        self.log('Payload: %s' % payload)
        try:
            if method == 'get':
                req = self.http_session.get(url, params=payload, headers=headers, allow_redirects=allow_redirects,timeout=self.timeout)
            elif method == 'json':  # post
                req = self.http_session.post(url, json=payload, headers=headers, allow_redirects=allow_redirects,timeout=self.timeout)
            else:  # post
                req = self.http_session.post(url, data=payload, headers=headers, allow_redirects=allow_redirects,timeout=self.timeout)
            req.raise_for_status()
            self.log('Response code: %s' % req.status_code)
            self.log('Response: %s' % req.content)
            if self.use_cookie:
                self.cookie_jar.save(ignore_discard=True, ignore_expires=False)
            return req
        except requests.exceptions.HTTPError as error:
            self.log('An HTTP error occurred: %s' % error)
            return req
        except requests.exceptions.ConnectionError as error:
            self.log('Connection Error: - %s' % error.message)
            raise
        except requests.exceptions.RequestException as error:
            self.log('Error: - %s' % error.value)
            raise

def emby_hash_password(password):
    return {
        'sha1' : hashlib.sha1(str(password).encode('utf-8')).hexdigest(),
        'md5' : hashlib.md5(str(password).encode('utf-8')).hexdigest()
    }

class EmbyHandle(object):
    def __init__(self, con, cache, debugging):
        self.con       = con
        self.cache     = cache
        self.debugging = debugging
        self.emby      = None
        self.api_url   = 'http://%s:%s/emby' % (CONFIG['host'],CONFIG['port'])
        self.headers   = DEF_HEADERS
        self._session  = None
        self._token    = None

    def connection1(self, event_loop):
        conn = aiohttp.TCPConnector(verify_ssl=False)
        self._session = aiohttp.ClientSession(connector=conn, headers=self.headers, loop=event_loop)
        return self._session

    def connection(self):
        if not self._session:
            self._session = Urlfetch(debug=self.debugging)

    def check_auth(self):
        res    = {"status":False, "error":"Unknown"}
        url    = self.api_url+'/Sessions'
        result = self._session.make_request(url=url, headers=self.headers, method="get")
        if result.status_code == 200:
            return True
        return False

    def authenticate(self):
        url  = self.api_url+'/Users/AuthenticateByName'
        pas  = emby_hash_password(CONFIG['pass'])
        data = {"Password":pas["sha1"],"PasswordMd5":pas["md5"],"Username":CONFIG['user']}
        post_result = self._session.make_request(url=url, headers=self.headers, method="json", payload=data).content
        login_info  = json.loads(post_result.decode())
        # Append access token to header for future requests
        self._token = login_info["AccessToken"]
        self.headers["x-emby-authorization"] = '%s, Token="%s"' % (DEF_AUTH_HEADER, login_info["AccessToken"])
        return login_info

    def connect(self):
        self.connection();
        auth_required = True
        if self._token and self._session:
            auth_required = self.check_auth()
        if auth_required:
            self.authenticate();

    def get_result(self, result):
        if result["status"]:
            return json.loads(result["result"].content.decode())
        return False

    def add_library(self, user_info, lib_type):
        # Note - Folder must exists or will return with 400 http error.
        res = {"status":False, "error":"Unknown"}
        url = self.api_url+'/Library/VirtualFolders'
        if lib_type == "tvshows":
            lib_name = "%s-TV" % user_info['Name']
            dir_name = "TVShows"
        elif lib_type == "movies":
            lib_name = "%s-Movies" % user_info['Name']
            dir_name = "Movies"
        url_vars = '?collectionType=%s&refreshLibrary=true&name=%s' % (lib_type, lib_name);
        url += url_vars;
        data = {
            "LibraryOptions":{
                "EnableArchiveMediaFiles":False,
                "EnablePhotos":True,
                "EnableRealtimeMonitor":False,
                "ExtractChapterImagesDuringLibraryScan":False,
                "EnableChapterImageExtraction":False,
                "DownloadImagesInAdvance":False,
                "EnableInternetProviders":False,
                "ImportMissingEpisodes":False,
                "SaveLocalMetadata":True,
                "EnableAutomaticSeriesGrouping":True,
                "PreferredMetadataLanguage":"en",
                "MetadataCountryCode":"US",
                "AutomaticRefreshIntervalDays":0,
                "EnableEmbeddedTitles":False,
                "PathInfos":[
                    {"Path":"/libraries/%s/%s" % (user_info['Name'],dir_name)}
                ]
            }
        }
        result = self._session.make_request(url=url, headers=self.headers, method="json", payload=data)
        if result.status_code == 400:
            if 'The specified path does not exist' in result.content.decode():
                res["status"] = False
                res["error"]  = result.content
        elif result.status_code == 204:
            res = {"status":True, "result":result}
        return res

    def add_account(self, user_info):
        # Create a new accont. (This needs to be posted with form urlencode.)
        res    = {"status":False, "error":"Unknown"}
        url    = self.api_url+'/Users/New'
        data   = {'Name':user_info['Name']}
        head   = self.headers.copy()
        head['Content-Type'] = 'application/x-www-form-urlencoded;'
        result = self._session.make_request(url=url, headers=head, method="post", payload=data)
        if result.status_code == 200:
            res = {"status":True, "result":result}
        elif result.status_code == 400:
            print (result.content)
            res = {"status":False, "result":result}
        return res

    def get_users(self):
        res    = {"status":False, "error":"Unknown"}
        url    = self.api_url+'/users'
        result = self._session.make_request(url=url, headers=self.headers, method="get")
        if result.status_code == 200:
            res = {"status":True, "result":result}
        return res

    def get_media_locations(self):
        res    = {"status":False, "error":"Unknown"}
        url    = self.api_url+'/Library/MediaFolders'
        data   = {'IsHidden':False}
        result = self._session.make_request(url=url, headers=self.headers, method="get", payload=data)
        if result.status_code == 200:
            res = {"status":True, "result":result}
        return res

    def get_account_media_locations(self, user_info):
        locations = self.get_media_locations();
        folders = []
        if locations and locations["status"]:
            loc_data = self.get_result(locations)
            for x in loc_data["Items"]:
                name = x["Name"]
                if name.split("-")[0] == user_info["Name"]:
                    folders.append(x["Id"])
        return folders

    def get_current_account_config(self, user_info):
        # Get Current accoutn info based on user ID
        if not "Id" in user_info:
            return user_info
        url    = self.api_url+'/Users/%s' % user_info["Id"]
        result = self._session.make_request(url=url, headers=self.headers, method="get", payload=data)
        if result.status_code == 204:
            res    = {"status":True, "result":result}
            config = self.get_result(res)
            user_info.update(config)
        return user_info

    def edit_account_policy(self, user_info, policy):
        # Note - Need to get user ID and a list of enabled folder before running this. (GET from get_media_locations())
        # Apply account policy edit
        res    = {"status":False, "error":"Unknown"}
        url    = self.api_url+'/Users/%s/Policy' % user_info["Id"]
        data   = policy.copy()
        result = self._session.make_request(url=url, headers=self.headers, method="json", payload=data)
        if result.status_code == 204:
            res = {"status":True, "result":result}
        return res

    def edit_account_access(self, user_info):
        res  = {"status":False, "error":"Unknown"}
        print ("user_info")
        print (user_info)
        url  = self.api_url+'/Users/%s' % user_info["Id"]
        user_info.update(ACCOUNT_DEFAULTS)
        user_info["Policy"]["EnabledFolders"] = self.get_account_media_locations(user_info)
        data = user_info.copy()
        result = self._session.make_request(url=url, headers=self.headers, method="json", payload=data)
        if result.status_code == 204:
            result = self.edit_account_policy(user_info, user_info["Policy"]);
            res = {"status":result["status"], "result":user_info}
        return res

    def edit_account_password(self, user_info, old_password="", new_password=""):
        res  = {"status":False, "error":"Unknown"}
        url  = self.api_url+'/Users/%s/Password' % user_info["Id"]
        opas = emby_hash_password(old_password)
        npas = emby_hash_password(new_password)
        data = {
            "currentPassword" : opas["sha1"],
            "newPassword" : npas["sha1"]
        }
        headers = self.headers.copy()
        headers['Content-Type'] = 'application/x-www-form-urlencoded;'
        result  = self._session.make_request(url=url, headers=headers, method="post", payload=data)
        if result.status_code == 204:
            res = {"status":True, "result":result}
        return res



    ##################### EXTERNAL EXEC
    def rescan_user_library(self, params):
        user_info = {'Name':params["username"]}
        # First get media locations:
        self.connect();
        locations = self.get_account_media_locations(user_info)
        if locations:
            head   = self.headers.copy()
            head['Content-Type'] = 'application/x-www-form-urlencoded;'
            data   = {
                'Recursive':True,
                'ImageRefreshMode':'Default',
                'MetadataRefreshMode':'Default',
                'ReplaceAllImages':False,
                'ReplaceAllMetadata':False
            }
            for loc in locations:
                url    = self.api_url+'/Items/%s/Refresh' % loc
                result = self._session.make_request(url=url, headers=head, method="post", payload=data)
            return locations

    def create_new_user(self, params):
        user_info = {'Name':params["username"]}
        self.connect();
        # Add new library location for user
        self.add_library(user_info, "tvshows");
        # Add new account and update user info from newly create account:
        user_info = self.get_result(self.add_account(user_info));
        # Setup newly created account with Mani defaults and update user info:
        if user_info:
            res = self.edit_account_access(user_info);
            if res["status"]:
                user_info.update(res["result"]);
            # Set password for newly created account
            self.edit_account_password(user_info, "", params["password"]);
        return user_info


    def check_for_user_by_name(self, params):
        username = params["username"]
        self.connect();
        results = self.get_result(self.get_users());
        if results:
            for res in results:
                if res["Name"] == username:
                    return {"found":True, "data":res};
        return {"found":False}





######### TESTS:

def test_password_hash(password=""):
    # da39a3ee5e6b4b0d3255bfef95601890afd80709
    # {"Password":"5e91f810d83783712f8e50b8b6096121356aee34","PasswordMd5":"364e5de4ad9a7be8375e6adfeb24ee93","Username":"master"}
    print(json.dumps(emby_hash_password(password), indent=4))

def test_user_create(username, password):
    handle = EmbyHandle(None, None, True);
    handle.create_new_user({"username":username,"password":password})

def test_check_for_user(username):
    handle = EmbyHandle(None, None, True);
    res = handle.check_for_user_by_name({"username":username});
    if res:
        print(json.dumps(res, indent=4))
    else:
        print("User '%s' does not exist" % username)

def test_rescan_user_folders(username):
    handle = EmbyHandle(None, None, True);
    res = handle.rescan_user_library({"username":username});
    if res:
        print(json.dumps(res, indent=4))
    else:
        print("Librarys for user '%s' do not exist" % username)


if __name__ == '__main__':
    #test_user_create("fooo", "barr")
    #test_password_hash("myrootpassword")
    #test_check_for_user("fooo")
    test_rescan_user_folders("fooo")
