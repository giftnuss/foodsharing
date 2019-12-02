# Remote debugging with XDebug

[Xdebug](https://xdebug.org) can be used to remotely debug the PHP code


## Using a GET request

Edit your `docker\conf\app\php.dev.ini`
```
; Defaults
xdebug.default_enable=1
xdebug.remote_enable=1
xdebug.remote_port=9000

; The MacOS way
xdebug.remote_connect_back=0
xdebug.remote_host=<HERE IS YOUR LOCAL IP ADDRESS>

; idekey value
;xdebug.idekey=<THE IDE's XDEBUG KEY>
;xdebug.idekey=netbeans-xdebug
xdebug.idekey=ECLIPSE_DBGP
```

Later your IDE will start a debug session by sending a POST request with a key. Then it will wait for the server to response with this key to establish the remote debug session. The request will look like: `http://localhost:18080/?XDEBUG_SESSION_START=netbeans-xdebug`.

Depending on your IDE that key can be `xdebug-atom`, `netbeans-xdebug`, `ECLIPSE_DBGP`, or something else.

**Restart Docker** after editing the `php.dev.ini` file.

### Netbeans configuration

Disable option _Tools > Options > PHP > Debugging > Stop at first line_.
If this option is enabled the debugger will stop at the first line of a file in every new session even without a breakpoint.

Project Properties' _Run configuration_

- Run As: **Local Web Site**
- Project URL: **http://localhost:18080/**

### Eclipse configuration

Make sure you have the [Eclipse PHP Development Tools](https://www.eclipse.org/pdt/) plugin installed. It is preinstalled in _Eclipse for PHP Developers_.

Eclipse Preferences:
- _PHP > Debug > Debuggers_ > **Configure** Xdebug
  - Debug Port: 9000
  - **Disable** Use Proxy
  - Accept remote session (JIT): **localhost**
- _PHP > Server_ > **Edit** default
  - Server
    - Base URL: **http://localhost:18080**
    - Document Root: **<PROJECT PATH>**
  - Debugger
    - Debugger: **Xdebug**
    - Port: 9000
    - **Disable** Use Proxy
- _PHP > Debug_
  - PHP Server: **Default...**
  - Debugger must show Xdebug
  - **Disable** Break at First Line

_Run > Debug Configurations..._:
- New PHP Web Application
  - _Server_
    - PHP Server: **Default...**
    - File: **/foodsharing/index.php**
    - **Disable** Auto Generate
  - _Debugger_
    - Debugger: **Xdebug**
