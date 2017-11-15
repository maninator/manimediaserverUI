#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# @Author: Mani
# @Date:   2017-08-28 19:20:58
# @Last Modified time: 2017-09-27 08:30:50
# 
##############################################

import mani_controller

class Cache(object):
    def __init__(self):
        self._docker_pull_progress     = {"status": None,"progressDetail": None,"id": None,"progress": None}
        self._docker_containers_status = {}
    
    def docker_pull_progress(self, data):
        if "id" in data:
            self._docker_pull_progress["id"] = data["id"]
        if "status" in data:
            self._docker_pull_progress["status"] = data["status"]
        if "progress" in data:
            self._docker_pull_progress["progress"] = data["progress"]
        if "progressDetail" in data:
            self._docker_pull_progress["progressDetail"] = data["progressDetail"]
    
    def docker_start_progress(self, data):
    	self._docker_containers_status = data
