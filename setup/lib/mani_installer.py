#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# @Author: Mani
# @Date:   2017-08-28 19:20:58
# @Last Modified time: 2017-09-10 20:21:03
# 
##############################################

import mani_config


class Installer(object):
    def __init__(self, con=None, cache=None, debugging=None):
        self.con       = con
        self.cache     = cache
        self.debugging = debugging
        self.services  = mani_config.Services()

    def install_handle(self,meta):
        if meta[1]["type"] == "docker":
            import docker_config
            doc = docker_config.DockerHandler(self.con,self.cache,self.debugging)
            return doc.install(meta)

    def install(self,command):
        res  = False
        meta = self.services.find_service_meta(command)
        if meta: # We know how to handle this command
            res = self.install_handle(meta)
        return res