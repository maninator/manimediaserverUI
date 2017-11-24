#!/usr/bin/env python3
# -*- coding: utf-8 -*-
#
# @Author: Mani
# @Date:   2017-08-28 19:20:58
# @Last Modified time: 2017-09-27 13:19:37
# 
##############################################

__version__ = '1.0.0'



import os, time, threading, socket, sys


#Add lib folder to py path
BASE_RESOURCE_PATH = os.path.join( os.path.dirname(os.path.realpath(__file__)), '..', 'share', 'mani', 'lib' )
sys.path.append(BASE_RESOURCE_PATH)

try:
    import json
except ImportError:
    try:
        import simplejson as json
    except ImportError:
        json = None


try:
    basestring
except NameError:
    basestring = str


try:
    from urllib2 import urlopen, Request, HTTPError, URLError
except ImportError:
    from urllib.request import urlopen, Request, HTTPError, URLError


try:
    from Queue import Queue
except ImportError:
    from queue import Queue

try:
    from hashlib import md5
except ImportError:
    from md5 import md5


try:
    from argparse import ArgumentParser as ArgParser
    from argparse import SUPPRESS as ARG_SUPPRESS
    PARSER_TYPE_INT = int
    PARSER_TYPE_STR = str
except ImportError:
    from optparse import OptionParser as ArgParser
    from optparse import SUPPRESS_HELP as ARG_SUPPRESS
    PARSER_TYPE_INT = 'int'


try:
    import __builtin__
except ImportError:
    import builtins
    from io import TextIOWrapper, FileIO

    class _Py3Utf8Stdout(TextIOWrapper):
        """UTF-8 encoded wrapper around stdout for py3, to override
        ASCII stdout
        """
        def __init__(self, **kwargs):
            buf = FileIO(sys.stdout.fileno(), 'w')
            super(_Py3Utf8Stdout, self).__init__(
                buf,
                encoding='utf8',
                errors='strict'
            )

        def write(self, s):
            super(_Py3Utf8Stdout, self).write(s)
            self.flush()

    _py3_print = getattr(builtins, 'print')
    _py3_utf8_stdout = _Py3Utf8Stdout()

    def to_utf8(v):
        """No-op encode to utf-8 for py3"""
        return v

    def print_(*args, **kwargs):
        """Wrapper function for py3 to print, with a utf-8 encoded stdout"""
        kwargs['file'] = _py3_utf8_stdout
        _py3_print(*args, **kwargs)
else:
    del __builtin__

    def to_utf8(v):
        """Encode value to utf-8 if possible for py2"""
        try:
            return v.encode('utf8', 'strict')
        except AttributeError:
            return v

    def print_(*args, **kwargs):
        """The new-style print function for Python 2.4 and 2.5.
        Taken from https://pypi.python.org/pypi/six/
        Modified to set encoding to UTF-8 always
        """
        fp = kwargs.pop("file", sys.stdout)
        if fp is None:
            return

        def write(data):
            if not isinstance(data, basestring):
                data = str(data)
            # If the file has an encoding, encode unicode with it.
            encoding = 'utf8'  # Always trust UTF-8 for output
            if (isinstance(fp, file) and
                    isinstance(data, unicode) and
                    encoding is not None):
                errors = getattr(fp, "errors", None)
                if errors is None:
                    errors = "strict"
                data = data.encode(encoding, errors)
            fp.write(data)
        want_unicode = False
        sep = kwargs.pop("sep", None)
        if sep is not None:
            if isinstance(sep, unicode):
                want_unicode = True
            elif not isinstance(sep, str):
                raise TypeError("sep must be None or a string")
        end = kwargs.pop("end", None)
        if end is not None:
            if isinstance(end, unicode):
                want_unicode = True
            elif not isinstance(end, str):
                raise TypeError("end must be None or a string")
        if kwargs:
            raise TypeError("invalid keyword arguments to print()")
        if not want_unicode:
            for arg in args:
                if isinstance(arg, unicode):
                    want_unicode = True
                    break
        if want_unicode:
            newline = unicode("\n")
            space = unicode(" ")
        else:
            newline = "\n"
            space = " "
        if sep is None:
            sep = space
        if end is None:
            end = newline
        for i, arg in enumerate(args):
            if i:
                write(sep)
            write(arg)
        write(end)

import mani_config, mani_cache, mani_controller


####################################################
## 
##    Configure:
## 
####################################################

SERVICE_SOCKET_HOST  = "127.0.0.1"
SERVICE_SOCKET_PORT  = int(mani_config.read_config().get('Service', 'service_port'))
SERVICE_SOCKET_FILE  = "/tmp/mani.sock"
SERVICE_SETTING_FILE = "/tmp/mani.json"


####################################################
## 
##    Main Program start:
## 
####################################################


def version():
    """Print the version"""
    print_(__version__)
    sys.exit(0)


def printer(string, quiet=False, debug=False, **kwargs):
    """Helper function to print a string only when not quiet"""

    if debug and not DEBUG:
        return

    if debug:
        out = '\033[1;30mDEBUG: %s\033[0m' % string
    else:
        out = string

    if not quiet:
        print_(out, **kwargs)


def _logger(message, message2="", forceprint=False):
    message  = str(message)
    if message2:
        # Message2 can support other objects:
        if isinstance(message2, basestring):
            message = "%s - %s" % (message,str(message2))
        elif isinstance(message2, dict) or isinstance(message2, list):
            import pprint
            message2 = pprint.pformat(message2, indent=1)
            message = "%s \n%s" % (message,str(message2))
        else:
            message = "%s - %s" % (message,str(message2))
    if not forceprint:
        try:
            printer("### [%s]: %s" % ("Mani",message))
        except UnicodeEncodeError:
            printer("### [%s]: %s" % ("Mani",message.encode("utf-8", "ignore")))
        except:
            printer("### [%s]: %s" % ("Mani",'ERROR DEBUG'))
    else:
        message = "### [%s]: %s" % (addon_id,message)
        print(message)


threads        = []
sockets        = []
service_handle = None

class ExternalSocketService(threading.Thread):
    def __init__(self, quit_task, task_queue, socket_conn, messages, control, cache, debugging=False):
        super(ExternalSocketService, self).__init__(name='ExternalSocketService')
        self.quit_task       = quit_task
        self.task_queue      = task_queue
        self.socket_conn     = socket_conn
        self.message_queue   = messages
        self.controler       = control
        self.cache           = cache
        self.debugging       = debugging
        self.abort_flag      = threading.Event()
        self.abort_flag.clear()
        self.controller      = mani_controller.Controller(cache = self.cache, debugging = self.debugging)

    def add_to_event_q(self, event):
        if event:
            self.task_queue.put(event, block=False)

    def run(self):
        if self.debugging:
            _logger("Entering Main Service loop...")
        while True:
            if self.abort_flag.is_set():
                # Abort flag was set. Time to terminate this one...
                if self.debugging:
                    _logger('%s: Exiting because abort flag was set' % (self.name))
                break
            if not self.quit_task.empty():
                event = self.quit_task.get_nowait()
                if event is "STOP":
                    # STOP means shutdown
                    if self.debugging:
                        _logger('%s: Exiting because "stop" signal was recieved' % (self.name))
                    break
            # Read socket
            conn, addr = self.socket_conn.accept()
            request_data = conn.recv(10 * 1024).decode()
            try:
                # Get the json object and return response
                request_data = json.loads(request_data)
                response = 'Thank you for connecting... But the truth is, I did nothing. Please dont come back again.'
                if not "type" in request_data:
                    pass # Not a valid request
                elif request_data["type"] == "request":
                    if request_data["request"] == "STOP":
                        conn.close()
                        if self.debugging:
                            _logger('%s: Exiting because "stop" socket signal was recieved' % (self.name))
                        break
                    if self.debugging:
                        _logger('%s: Recieved request:' % (self.name), message2=request_data)
                    response = self.request(response, request_data, conn);
                    print (response)
                conn.send(response.encode())
            except Exception as e:
                if self.debugging:
                    _logger("Exception in ExternalSocketService reading socket:", message2=str(e))
            finally:
                # Close the connection
                conn.close()
            time.sleep(.1);
        if self.debugging:
            _logger("Leaving Main Service loop...")
        self.abort()

    def abort(self):
        self.abort_flag.set()
        self.add_to_event_q("STOP")

    def request(self, response, request_data, conn):
        question = request_data["request"]
        if question["type"] == "status":
            response = self.get_status(question["query"])
        elif question["type"] == "install":
            self.add_to_event_q(question)
            response = "Installing..."
        elif question["type"] in ["start", "stop", "restart"]:
            self.add_to_event_q(question)
            response = "Changing the stated of %s" % question["command"];
        elif question["type"] == "configure":
            self.add_to_event_q(question)
            response = "Configuring..."
        return response

    def get_status(self, query):
        if query["type"] == "hello_world":
            _logger("Hello World")
            return json.dumps({'hello':'world'})
        elif query["type"] == "docker_pull":
            return json.dumps(self.cache._docker_pull_progress)
        elif query["type"] == "service_status":
            self.controler.service_status(query["service"])
            return json.dumps(self.cache._docker_containers_status)
        elif query["type"] == "service_configure":
            # Return status from a running service
            result = self.controler.configure(query)
            return json.dumps(result)




class MainServiceHandle(threading.Thread):
    def __init__(self, service_handle_task, tasks, messages, control, cache, debugging=False):
        super(MainServiceHandle, self).__init__(name='MainServiceHandle')
        self.service_handle_task = service_handle_task
        self.tasks               = tasks
        self.messages            = messages
        self.controler           = control
        self.cache               = cache
        self.debugging           = debugging
        self.abort_flag          = threading.Event()
        self.abort_flag.clear()
        self.busy_flag           = threading.Event()
        self.busy_flag.clear()

    def add_to_messages_q(self, event):
        if event:
            self.messages.put(event, block=False)

    def run(self):
        if self.debugging:
            _logger("Entering Handle loop...")
        while True:
            if self.abort_flag.is_set():
                # Abort flag was set. Time to terminate this one...
                if self.debugging:
                    _logger('%s: Exiting because abort flag was set' % (self.name))
                break
            if not self.tasks.empty():
                event = self.tasks.get_nowait()
                if event is "STOP":
                    # STOP means shutdown
                    if self.debugging:
                        _logger('%s: Exiting because "stop" signal was recieved' % (self.name))
                    break
                elif isinstance(event,dict):
                    self.action_commands(event)
                else:
                    if self.debugging:
                        _logger('%s: ERROR - Unable to action event' % (self.name), message2=event)
            time.sleep(.1);
        if self.debugging:
            _logger("Leaving Handle loop...")
        self.abort()

    def abort(self):
        self.abort_flag.set()

    def busy(self, is_busy):
        if is_busy:
            self.busy_flag.set()
        else:
            self.busy_flag.clear()

    def action_commands(self, event):
        response = {"error":True}
        if event["type"] == "install":
            self.busy(True)
            import mani_installer
            installer = mani_installer.Installer(cache = self.cache, debugging = self.debugging)
            installer.install(event["command"])
            self.busy(False)
            response["error"] = False
        if event["type"] == "start":
            self.busy(True)
            self.controler.start_service(event["command"])
            self.busy(False)
            response["error"] = False
        if event["type"] == "stop":
            self.busy(True)
            self.controler.stop_service(event["command"])
            self.busy(False)
            response["error"] = False
        if event["type"] == "restart":
            self.busy(True)
            self.controler.stop_service(event["command"])
            self.controler.start_service(event["command"])
            self.busy(False)
            response["error"] = False
        if event["type"] == "configure":
            self.busy(True)
            self.controler.configure(event["command"])
            self.busy(False)
            response["error"] = False
        return response



def start_sockets(port=None, debugging=False):
    global sockets
    sockets_config = []
    # First try to create socket with file, then fall back to port
    if port:
        # If port == 'default' it will except and use SERVICE_SOCKET_PORT
        try:
            USE_PORT = int(port)
        except:
            USE_PORT = SERVICE_SOCKET_PORT
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        s.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
        s.bind((SERVICE_SOCKET_HOST, USE_PORT))
        s.listen(1)
        if debugging:
           _logger('Starting the server at host: ' + (SERVICE_SOCKET_HOST) + ' port: ' + str(USE_PORT))
        conf = {"name":"main","file":None,"host":SERVICE_SOCKET_HOST,"port":USE_PORT}
        soc  = dict(conf, **({"socket":s}))
        sockets.append(soc)
        sockets_config.append(conf)
    else:
        if os.path.exists(SERVICE_SOCKET_FILE):
            os.remove(SERVICE_SOCKET_FILE)
        s = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
        s.bind(SERVICE_SOCKET_FILE)
        s.listen(1)
        if debugging:
           _logger('Starting the server at ' + (SERVICE_SOCKET_FILE))
        conf = {"name":"main","file":SERVICE_SOCKET_FILE,"host":None,"port":None}
        soc  = dict(conf, **({"socket":s}))
        sockets.append(soc)
        sockets_config.append(conf)
    with open(SERVICE_SETTING_FILE, 'w') as outfile:
        json.dump(sockets_config, outfile)
    return s

def start_threading(args,debugging=False):
    # reload ModuleCache
    global service_handle
    global threads

    cache    = mani_cache.Cache()
    tasks    = Queue()
    messages = Queue()
    logging  = Queue()
    if debugging:
        debugging = logging

    control  = mani_controller.Controller(cache=cache, debugging=debugging)

    # Start new thread to handle messages from service
    service_handle_tasks = Queue()
    service_handle = MainServiceHandle(service_handle_tasks, tasks, messages, control, cache, logging)
    service_handle.daemon=True
    service_handle.start()
    threads.append({"thread":service_handle,"task_queue":service_handle_tasks})

    # Start new thread to handle external requests
    port = args.port
    if not args.port and not args.use_sock_file:
        port = "default"
    sockets         = start_sockets(port,debugging=debugging)
    ex_sock_tasks   = Queue()
    ex_sock_service = ExternalSocketService(ex_sock_tasks, tasks, sockets, messages, control, cache, debugging=debugging)
    ex_sock_service.daemon=True
    ex_sock_service.start()
    threads.append({"thread":ex_sock_service,"task_queue":ex_sock_tasks})

def stop_threading(timeout=1, debugging=False,quitting=False):
    # Stop Dispatcher and Executor
    _logger(str("Stopping Service"))
    for s in sockets:
        print(s)
        soc = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        soc.connect((str(s["host"]), int(s["port"])))
        soc.sendall(json.dumps({"name":"TERM", "type":"request", "request":"STOP"}).encode())
        soc.close()
        s["socket"].shutdown(socket.SHUT_WR)
        s["socket"].close()
    for t in threads:
        _logger(str(t))
        t["task_queue"].put("STOP")
    time.sleep(timeout)

def run_server(args,debugging=False):
    start_threading(args,debugging=debugging)
    while threads:
        # Check if threads are still active:
        for thread in threads:
            if not thread["thread"].isAlive():
                threads.remove(thread)
        time.sleep(.1)
    stop_threading(debugging=debugging)


class Main(object):
    def __init__(self, debugging=False):
        self.debugging = debugging

    def conn_file(self, query, file=None):
        if not file:
            file = SERVICE_SOCKET_FILE
        if self.debugging:
            _logger('Connecting to Mani Server ' + (file))
        s = socket.socket(socket.AF_UNIX, socket.SOCK_STREAM)
        s.connect(SERVICE_SOCKET_FILE)
        s.sendall(json.dumps(query).encode())
        response_data = s.recv(10 * 1024).decode()
        s.close()
        return response_data

    def conn_port(self, query, host=None, port=None):
        if not host:
            host = SERVICE_SOCKET_HOST
        if not port:
            port = SERVICE_SOCKET_PORT
        if self.debugging:
            _logger("Connecting to Mani Server at host: %s port: %s" % (host, port))
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        s.connect((host, port))
        s.sendall(json.dumps(query).encode())
        response_data = s.recv(10 * 1024).decode()
        s.close()
        return response_data

    def send_json_query(self, query):
        # Connect to socket and send request
        
        settings = None
        file     = None
        host     = None
        port     = None
        if os.path.exists(SERVICE_SETTING_FILE):
            with open(SERVICE_SETTING_FILE) as data_file:    
                settings = json.load(data_file)
        if settings:
            for setting in settings:
                if "name" in setting and setting["name"] == "main":
                    file = setting["file"]
                    host = setting["host"]
                    port = setting["port"]
        if self.debugging:
            _logger("Sending query to Mani service:", message2=query)
        if file and os.path.exists(SERVICE_SOCKET_FILE):
            response_data = self.conn_file(query, file)
        else:
            response_data = self.conn_port(query, host, port)
            if self.debugging:
                _logger(response_data)
        try:
            result = json.loads(response_data)
        except Exception as e:
            if self.debugging:
                _logger("Exception getting status from socket:", message2=str(e))
            result = False
        return result

def log_progress_status(runner,args,status_type):
    request_data = {"name":"Status", "type":"request", "request":{"type":"status","query":{"type":status_type}}};
    while True:
        time.sleep(int(args.wait))
        status = runner.send_json_query(request_data)
        if status:
            print(json.dumps(status, indent=4))
            if status["status"] and "Image is up to date" in status["status"]:
                break
        else:
            break

def action_installer(runner,services,args,debugging=False):
    for command in args.command:
        meta = services.find_service_meta(command)
        if meta:
            request_data = {"name":"Install", "type":"request", "request":{"type":args.action.lower(),"command":command}};
        if debugging:
            _logger(meta)
        runner.send_json_query(request_data);
        if args.wait > 0:
            status_type = 'docker_pull';
            log_progress_status(runner,args,status_type);

def action_starter_stopper(runner,services,args,debugging=False):
    for command in args.command:
        meta = services.find_service_meta(command)
        if meta:
            request_data = {"name":"Start/Stop", "type":"request", "request":{"type":args.action.lower(),"command":command}};
        if debugging:
            _logger(meta)
        runner.send_json_query(request_data);
        request_data = {"name":"Status", "type":"request", "request":{"type":"status","query":{"type":"service_status", "service":command}}};
        while True:
            current_state = None
            status = runner.send_json_query(request_data)
            if status:
                if "State" in status:
                    if args.action.lower() in ["start", "restart"]:
                        break_when = "running"
                    elif args.action.lower() == "stop":
                        break_when = "exited"
                    if status["State"] == break_when:
                        break
                else:
                    break
            else:
                break
            time.sleep(1)
        print(json.dumps(status, indent=4))

def run_action(args,debugging=False):
    request_data = None
    runner = Main(debugging);
    import mani_config
    services  = mani_config.Services()
    if args.action == 'install':
        action_installer(runner,services,args,debugging)
    elif args.action in ['start','stop','restart']:
        action_starter_stopper(runner,services,args,debugging)
    elif args.action == 'status':
        request_data = {"name":"Status", "type":"request", "request":{"type":"status","query":{"type":args.command[0]}}};
        count = 0
        while True:
            count += 1
            status = runner.send_json_query(request_data);
            print(json.dumps(status, indent=4))
            if args.number == count:
                break
            time.sleep(1)
    elif args.action == 'service_status':
        request_data = {"name":"Status", "type":"request", "request":{"type":"status","query":{"type":"service_status", "service":args.command[0]}}};
        count = 0
        while True:
            count += 1
            status = runner.send_json_query(request_data);
            print(json.dumps(status, indent=4))
            if args.number == count:
                break
            time.sleep(1)
    else:
        request_data = {"name":"Configure", "type":"request", "request":{"type":"configure","query":{"type":args.command[0]}}};



def parse_args():
    """Function to handle building and parsing of command line arguments"""
    description = (
        'Command line interface for manging Mani Media Server '
        '\n'
        '------------------------------------------------------------'
        '\n')

    parser = ArgParser(description=description)
    # Give optparse.OptionParser an `add_argument` method for
    # compatibility with argparse.ArgumentParser
    try:
        parser.add_argument = parser.add_option
    except AttributeError:
        pass
    parser.add_argument('-v', '--version', action='store_true',
                        help='Show the version number and exit')
    parser.add_argument('-d', '--debug', dest='debugging', action='store_true',
                        help='Run with debugging enabled', default=False)

    # Run Mani CLI Server
    parser.add_argument('-s', '--server', dest='run_server', default=False,
                        action='store_const', const=True,
                        help='Server: Start Mani service')
    parser.add_argument('-p', '--port', type=PARSER_TYPE_STR,
                        help='Server: Set the port for Mani service listning on..')
    parser.add_argument('-f', '--use-file', dest='use_sock_file', action='store_true',
                        help='Server: Use a sock file will be used instead.')

    # Run interface
    parser.add_argument('action', nargs='?', type=PARSER_TYPE_STR, default='help',
                        help='Action you want to take with Mani')
    parser.add_argument('command', nargs='*', type=PARSER_TYPE_STR, default='bar',
                        help='Command to run with Mani')
    parser.add_argument('-n', '--number', type=PARSER_TYPE_INT, default=1,
                        help='Number of times to query.')
    parser.add_argument('-w', '--wait-for-status', dest='wait', type=PARSER_TYPE_INT, default=0,
                        help='Wait for job to complete and return status.')

    options = parser.parse_args()
    if isinstance(options, tuple):
        args = options[0]
    else:
        args = options

    # Print the version and exit
    if not args.run_server and args.action == 'help':
        parser.print_help()
        sys.exit();

    return args


def run():
    args  = parse_args()
    debug = args.debugging

    if args.version:
        version()

    # Run server
    if args.run_server:
        run_server(args,debug)
    elif args.action:
        run_action(args,debug)
    else:
        runner = Main(debug);
        request_data = {"name":"App Download Status", "type":"meh", "mea":{"type":"status","status":"app_download"}};
        print (runner.send_json_query(request_data));


if __name__ == '__main__':
    run()
