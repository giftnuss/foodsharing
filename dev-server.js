var http = require('http');
var httpProxy = require('http-proxy');

var PORT = 8080;

var SOCKETIO_PATH_RE = /^\/chat\/socket\.io\//;

var PHP_BACKEND= 'http://localhost:9000';
var SOCKETIO_SERVER = 'http://localhost:1337';

function rewriteSocketIOPath(path) {
  return path.replace(/^\/chat/, '');
}

var proxy = httpProxy.createProxyServer({});

var server = http.createServer(function(req, res) {
  if (SOCKETIO_PATH_RE.test(req.url)) {
    req.url = rewriteSocketIOPath(req.url);
    proxy.web(req, res, { target: SOCKETIO_SERVER });
  } else {
    proxy.web(req, res, { target: PHP_BACKEND });
  }
});

server.on('upgrade', function (req, socket, head) {
  req.url = rewriteSocketIOPath(req.url);
  proxy.ws(req, socket, head, { target: SOCKETIO_SERVER });
});

server.listen(PORT, function(){
  console.log('listening on port', PORT);
});

proxy.on('error', function(err, req, res){
  res.writeHead(500, {
    'Content-Type': 'text/plain'
  });
  res.end('error during proxy call - ' + err);
});
