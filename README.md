# Mani Media Server

[![N|Mani Media Server](https://raw.githubusercontent.com/maninator/manimediaserver/master/www/uploads/logo.png)](https://github.com/maninator/manimediaserver/)

Mani comes in 2 parts.
  - Mani Service - A CLI for interfacing directly with running docker services and provides an api for Emby
  - Mani UI - A content management system for managing your users subscriptions to content on your media server

# How it works

**TL;DR**
Mani is installed as a WebuUI with Nginx, PHP and MySQL. It will also install a service that will run as root to provide a sucure gateway to configure your docker containers from the WebUI.
Once you have installed Mani ont your machine, you can install [Emby Medai Server](https://emby.media/) and [Ombi](https://github.com/tidusjar/Ombi) to provide user access to media content and a means to report issues and request new content.
Mani WebUI gives you the ability to provide subscribers to your media server their own individual librarys and a dashboard to subscibe to (or remove) content the want (or dont want) in their library.

**Details**
Mani WebUI provides users with a dashboard for selecting or removing content from their library.
It does this by scanning your folders for content. When a user selects that item, it symlnks it to their own content folder where they have their own library access to in Emby Media Server.
When a user logs into Mani's WebUI for the first time, it will auto generate their own personal library. A folder is created for them on the system and mani then generates a unique library on Emby Media Server associated with thier email address and a user account then with sole access to that library. 
The login credentials for your Mani WebUI dashboard are the same for the user login for Emby and Ombi.


# Installation:

### Requirements:
 - Ubuntu 17.04 (not tested on any other OS)
 - git
```sh
sudo apt install git
```

### Steps:

**1) Clone this repo to any folder (recommend /opt).**
```sh
cd /opt
git clone https://github.com/maninator/manimediaserver.git
```

**2) Run the install script.**
```sh
cd manimediaserver
sudo ./install.sh all
sudo ./install.sh env
```
**NB.**
When prompted to setup mysql, if this is a server already running a mysql database, then you will need to configure it manually. Otherwise it is save to let this install script set it up for you by follow the prompts.

**3) Start the services:**
```sh
sudo service mani restart
sudo service nginx restart
```

# Supported services
Mani uses a number of open source projects to work properly:

* [Emby Medai Server](https://emby.media/) - Media server
* [Ombi](https://github.com/tidusjar/Ombi) - Media requests and issue tracking
* [SickRage]
* [Transmission]

# Using Mani

### Command Line:
Once your mani service is running, you can use it to install and manage your service.
For example:

**To install Emby:**
```sh
mani install embyserver
```

**To start Emby:**
```sh
mani start embyserver
```

**To stop Emby:**
```sh
mani stop embyserver
```

**Ombi:**
```sh
mani install ombi
mani start ombi
mani stop ombi
```

**Sickrage:**
```sh
mani install sickrage
mani start sickrage
mani stop sickrage
```

You can also use mani to get the status of your installed services
```sh
mani service_status embyserver
```
