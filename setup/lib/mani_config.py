#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# @Author: Mani
# @Date:   2017-08-28 19:20:58
# @Last Modified time: 2017-09-27 13:23:38
# 
##############################################


import os, configparser

def read_config():
    config_file = os.path.join(os.path.dirname(__file__), "..", "config", "config.ini");
    Config = configparser.ConfigParser()
    Config.read(config_file);
    return Config

CONFIG           = read_config();
# Get your timezone
DEFAULT_TZ       = CONFIG.get('Global', 'timezone')
# Get the base location where all configs will be stored
BASE_INSTALL_DIR = CONFIG.get('Global', 'base_dir')
# Get the location where all media will be written 
BASE_MEDIA_DIR   = CONFIG.get('Global', 'media_dir')
# Get the location where user libraries are created
LIBRARIES_DIR    = CONFIG.get('Global', 'libraries')
# Get the location where downloads are actioned
DOWNLOADS_DIR    = CONFIG.get('Global', 'downloads_dir')

DOCKER = {
    'embyserver': {
        'name': "Emby Server",
        'description': "Emby Server",
        'type': "docker",
        'install_command': "docker ps -a",
        'setup_command': "docker ps",
        'source': "emby/embyserver",
        'docker_create': {
            'container_config' : {
                'name' : 'embyserver',
                'image' : 'emby/embyserver:latest',
                'environment':{
                    'TZ': DEFAULT_TZ
                }, 
                'volumes' : [
                    '/libraries',
                    '/media',
                    '/config'
                ]
            },
            'host_config': {
                'network_mode':'host',
                'binds' : {
                    LIBRARIES_DIR: {
                        'bind': '/libraries',
                        'mode': 'ro',
                    },
                    BASE_MEDIA_DIR: {
                        'bind': '/media',
                        'mode': 'rw',
                    },
                    BASE_INSTALL_DIR+'/config/emby/config': {
                        'bind': '/config',
                        'mode': 'rw',
                    }
                }
            }
        }
    },
    'ombi': {
        'name': "Ombi",
        'description': "Ombi",
        'type': "docker",
        'source': "linuxserver/ombi",
        'docker_create': {
            'container_config' : {
                'name' : 'ombi',
                'image' : 'linuxserver/ombi:latest',
                'environment':{
                    'TZ': DEFAULT_TZ
                }, 
                'volumes' : [
                    '/etc/localtime',
                    '/config'
                ]
            },
            'host_config': {
                'network_mode':'host',
                'binds' : {
                    '/etc/localtime': {
                        'bind': '/etc/localtime',
                        'mode': 'ro',
                    },
                    BASE_INSTALL_DIR+'/config/ombi/config': {
                        'bind': '/config',
                        'mode': 'rw',
                    }
                }
            }
        }
    },
    'sickrage': {
        'name': "SickRage",
        'description': "SickRage",
        'type': "docker",
        'source': "linuxserver/sickrage",
        'docker_create': {
            'container_config' : {
                'name' : 'sickrage',
                'image' : 'linuxserver/sickrage:latest',
                'environment':{
                    'TZ': DEFAULT_TZ
                }, 
                'volumes' : [
                    '/downloads',
                    '/config',
                    '/media'
                ]
            },
            'host_config': {
                'network_mode':'host',
                'binds' : {
                    DOWNLOADS_DIR: {
                        'bind': '/downloads',
                        'mode': 'rw',
                    },
                    BASE_INSTALL_DIR+'/config/sickrage/config': {
                        'bind': '/config',
                        'mode': 'rw',
                    },
                    BASE_MEDIA_DIR: {
                        'bind': '/media',
                        'mode': 'rw',
                    },
                }
            }
        }
    },
    'couchpotato': {
        'name': "CouchPotato",
        'description': "CouchPotato",
        'type': "docker",
        'source': "linuxserver/couchpotato",
        'docker_create': {
            'container_config' : {
                'name' : 'couchpotato',
                'image' : 'linuxserver/couchpotato:latest',
                'environment':{
                    'TZ': DEFAULT_TZ
                }, 
                'volumes' : [
                    '/downloads',
                    '/config',
                    '/media'
                ]
            },
            'host_config': {
                'network_mode':'host',
                'binds' : {
                    DOWNLOADS_DIR: {
                        'bind': '/downloads',
                        'mode': 'rw',
                    },
                    BASE_INSTALL_DIR+'/config/couchpotato/config': {
                        'bind': '/config',
                        'mode': 'rw',
                    },
                    BASE_MEDIA_DIR: {
                        'bind': '/media',
                        'mode': 'rw',
                    },
                }
            }
        }
    },
    'transmission': {
        'name': "Transmission",
        'description': "Transmission",
        'type': "docker",
        'source': "linuxserver/transmission",
        'docker_create': {
            'container_config' : {
                'name' : 'transmission',
                'image' : 'linuxserver/transmission:latest',
                'environment':{
                    'TZ': DEFAULT_TZ
                }, 
                'volumes' : [
                    '/downloads',
                    '/config',
                    '/watch'
                ]
            },
            'host_config': {
                'network_mode':'host',
                'binds' : {
                    DOWNLOADS_DIR: {
                        'bind': '/downloads',
                        'mode': 'rw',
                    },
                    BASE_INSTALL_DIR+'/config/transmission/config': {
                        'bind': '/config',
                        'mode': 'rw',
                    },
                    DOWNLOADS_DIR+'/watch': {
                        'bind': '/watch',
                        'mode': 'rw',
                    },
                }
            }
        }
    },
    'sabnzbd': {
        'name': "SABnzbd",
        'description': "SABnzbd",
        'type': "docker",
        'source': "linuxserver/sabnzbd",
        'docker_create': {
            'container_config' : {
                'name' : 'sabnzbd',
                'image' : 'linuxserver/sabnzbd:latest',
                'environment':{
                    'TZ': DEFAULT_TZ
                }, 
                'volumes' : [
                    '/downloads',
                    '/config',
                    '/incomplete-downloads'
                ]
            },
            'host_config': {
                'network_mode':'host',
                'binds' : {
                    DOWNLOADS_DIR: {
                        'bind': '/downloads',
                        'mode': 'rw',
                    },
                    BASE_INSTALL_DIR+'/config/sabnzbd/config': {
                        'bind': '/config',
                        'mode': 'rw',
                    },
                    DOWNLOADS_DIR+'/nzbincomplete': {
                        'bind': '/incomplete-downloads',
                        'mode': 'rw',
                    }
                }
            }
        }
    },
    'radarr': {
        'name': "Radarr",
        'description': "Radarr",
        'type': "docker",
        'source': "linuxserver/radarr",
        'docker_create': {
            'container_config' : {
                'name' : 'radarr',
                'image' : 'linuxserver/radarr:latest',
                'environment':{
                    'TZ': DEFAULT_TZ
                }, 
                'volumes' : [
                    '/downloads',
                    '/config',
                    '/media'
                ]
            },
            'host_config': {
                'network_mode':'host',
                'binds' : {
                    DOWNLOADS_DIR: {
                        'bind': '/downloads',
                        'mode': 'rw',
                    },
                    BASE_INSTALL_DIR+'/config/radarr/config': {
                        'bind': '/config',
                        'mode': 'rw',
                    },
                    BASE_MEDIA_DIR: {
                        'bind': '/media',
                        'mode': 'rw',
                    }
                }
            }
        }
    }
}


class Services(object):
    def __init__(self):
        self.AllServices = self.getEvents()
    
    def getEvents(self):
        SERVICES_LIST = [DOCKER]
        allservices   = {}
        for s in SERVICES_LIST:
            allservices.update(s)
        return allservices

    def find_service_meta(self, service):
        meta      = []
        try:
            items = self.AllServices.items();
        except:
            items = self.AllServices.iteritems();
        for key, value in items:
            if key == service:
                meta.append(key)
                meta.append(value)
        return meta


def test_config_read():
    config = read_config();
    main_dict = {}
    for section in config.sections():
        main_dict[section] = {}
        for key in config[section]:
            main_dict[section][key] = config.get(section, key)
    import json
    print(json.dumps(main_dict, indent=4))

if __name__ == '__main__':
    # Test config file
    test_config_read();
