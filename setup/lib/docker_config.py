#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# @Author: Mani
# @Date:   2017-08-28 19:20:58
# @Last Modified time: 2017-09-27 12:51:57
# 
##############################################


#### REQUIRES
# docker-py

import docker, json, copy
#client = docker.from_env()



# kwargs to copy straight from run to create
RUN_CREATE_KWARGS = [
    'command',
    'detach',
    'domainname',
    'entrypoint',
    'environment',
    'healthcheck',
    'hostname',
    'image',
    'labels',
    'mac_address',
    'name',
    'network_disabled',
    'stdin_open',
    'stop_signal',
    'tty',
    'user',
    'volume_driver',
    'working_dir',
]

# kwargs to copy straight from run to host_config
RUN_HOST_CONFIG_KWARGS = [
    'auto_remove',
    'blkio_weight_device',
    'blkio_weight',
    'cap_add',
    'cap_drop',
    'cgroup_parent',
    'cpu_count',
    'cpu_percent',
    'cpu_period',
    'cpu_quota',
    'cpu_shares',
    'cpuset_cpus',
    'cpuset_mems',
    'device_read_bps',
    'device_read_iops',
    'device_write_bps',
    'device_write_iops',
    'devices',
    'dns_opt',
    'dns_search',
    'dns',
    'extra_hosts',
    'group_add',
    'init',
    'init_path',
    'ipc_mode',
    'isolation',
    'kernel_memory',
    'links',
    'log_config',
    'lxc_conf',
    'mem_limit',
    'mem_reservation',
    'mem_swappiness',
    'memswap_limit',
    'nano_cpus',
    'network_mode',
    'oom_kill_disable',
    'oom_score_adj',
    'pid_mode',
    'pids_limit',
    'privileged',
    'publish_all_ports',
    'read_only',
    'restart_policy',
    'security_opt',
    'shm_size',
    'storage_opt',
    'sysctls',
    'tmpfs',
    'ulimits',
    'userns_mode',
    'version',
    'volumes_from',
    'runtime'
]

class DockerHandler(object):
    def __init__(self, con, cache, debugging):
        self.client    = docker.Client(base_url='unix://var/run/docker.sock')
        self.con       = con
        self.cache     = cache
        self.debugging = debugging

    def find_installed_container(self, search):
        containers = self.client.containers(all=True)
        for container in containers:
            if "/%s" % search in container["Names"]:
                return container
        return

    def start_container(self, name):
        container = self.find_installed_container(name)
        if container:
            res = self.client.start(name);
            return self.find_installed_container(name);

    def stop_container(self, name):
        container = self.find_installed_container(name)
        if container:
            res = self.client.stop(name);
            return self.find_installed_container(name);

    def create(self, meta):
        name = meta[0]
        info = meta[1]
        container = self.find_installed_container(name)
        if not container:
            if 'docker_create' in info and info['docker_create']:
                config = info['docker_create']["container_config"]
                if 'name' in config:
                    name = config['name']
                    # First create host config
                    if self.debugging:
                        print('Creating Docker container',json.dumps(info["docker_create"], indent=4))
                    host_config = self.client.create_host_config(**info["docker_create"]["host_config"])
                    # Now send all args
                    self.client.create_container(**config, host_config=host_config)

    def install(self, meta):
        name = meta[0]
        info = meta[1]
        for line in self.client.pull(info["source"], tag="latest", stream=True):
            try:
                line = line.decode()
            except:
                pass
            self.cache.docker_pull_progress(json.loads(line))
            #print(json.dumps(json.loads(line), indent=4))
        self.create(meta)

    def remove(self, meta):
        name = meta[0]
        meta = meta[1]
        self.client.remove_container(meta["source"])
