#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# @Author: Mani
# @Date:   2017-08-28 19:20:58
# @Last Modified time: 2017-09-27 12:54:55
# 
##############################################

import mani_config
import docker_config
import emby_config


SERVICE_CONFIG_OPTIONS = {
    "embyserver" : {
        "module":"emby_config"
    }
}


class Controller(object):
    def __init__(self, con=None, cache=None, debugging=None):
        self.con         = con
        self.cache       = cache
        self.debugging   = debugging
        self.services    = mani_config.Services()

        self.emby_handle = emby_config.EmbyHandle(con, cache, debugging);
        self.doc         = docker_config.DockerHandler(self.con,self.cache,self.debugging)

    def start_stop_handle(self,meta,start=False,stop=False):
        if meta[1]["type"] == "docker":
            if start:
                return self.doc.start_container(meta[0])
            elif stop:
                return self.doc.stop_container(meta[0])

    def start_service(self,command):
        res  = False
        meta = self.services.find_service_meta(command)
        if meta: # We know how to handle this command
            res = self.start_stop_handle(meta, start=True)
        return res

    def stop_service(self,command):
        res  = False
        meta = self.services.find_service_meta(command)
        if meta:
            res = self.start_stop_handle(meta, stop=True)
        return res

    def service_status(self,command):
        meta = self.services.find_service_meta(command)
        if meta:
            if meta[1]["type"] == "docker":
                data = self.doc.find_installed_container(meta[0])
                if self.cache:
                    self.cache.docker_start_progress(data)
                    return True
        return False

    def configure(self,command):
        result  = {"error":True}
        # Run the function to handle this command
        _object = SERVICE_CONFIG_OPTIONS[command["service"]]["module"]
        return getattr(self, _object)(command=command);

    def emby_config(self,command):
        result  = {"error":True}
        res = getattr(self.emby_handle, command["function"])(params=command["params"]);
        try:
            if res:
                result["error"] = False
                result["result"] = res
        except:
            result["result"] = "SOMETHING BAD HAPPENED"
        return result
