# WebSocket server (chat)

This directory contains a node.js server written in TypeScript to handle WebSocket connections to clients. These
connections are used in order to push updates to clients (e. g. browser windows or the Android app).

## Setup
### How to run yarn?
`./scripts/chat/yarn`
### How to fix the code style?
`./scripts/chat/yarn fix`
### How to run the tests?
`./scripts/chat/yarn test`
### How to get my changes running?
You don't need to. In development, we use the neat tool `ts-node-dev`, which keeps track of your TypeScript files and
updates the server on changes. No manual TypeScript build and no server restart is required.

## NABPOQ - Never asked, but probably occurring questions:

## Why node?
Node servers work very differently from most server implementations in PHP or other languages. Normally, a server
creates one process per request. Even if the requests are executed simultaneously (e. g. using `PHP-FPM`), they have
a separate place in the memory and can not e. g. use object instances created by the other requests. If these processes
want to exchange data, they need to pass it through another way, like using the file system, a relational database or an
in-memory database like Redis.

This makes handling many requests at a time very memory consuming: All request processes need their own service class
instances to be created. Also, you can't just go and tell the server to send data to all requesting clients, because
there isn't just one instance of your application running but many, and no instance of your application knows about the
other ones.

The purpose of a WebSocket server is exactly two things: Keep many connections at the same time open for a longer
period, and manage somehow to send data to many of them at the same time. This is not good regarding the limitations
classic web server implementations have.

Now what is different about node? Node, as we all know, executes JavaScript code. And JavaScript is designed for
asynchronicity, doing many things at the same time but in one instance of the program. This is not only extremely useful
for graphical user interfaces (like web sites), but also for WebSocket servers.

If you request a node server, your request may start other asynchronous tasks. Your request can be answered while
other tasks started by your request are still running, and node won't shut down unless they all ended. Because
asynchronicity is built into your application, node doesn't need to create a new instance of your application when
another client starts a request at the same time. Your application can handle it asynchronously, while still processing
the first request. That means, that your second request can use all the object instances created in your fist request.

That also means, that node needs only one instance of your application to process thousands of request. And it means
that if a third client starts a request while our first two are still ongoing, our third request can cause sending
something to the first ones, as the application knows about all of them. This is exactly what we need for WebSockets.

## Why does a user id have many sessions?
If you look into our code, you'll find that SessionIdProvider will always provide you with an array of session ids, even
if you want to know the session of one user id.

This is because the same user can be logged in across different browsers and different devices – every browser will
have their own cookies, and therefore have their own session id.

## Why does a session have many Sockets?
A session is always tied to a browser. If you log in to a site, you are still logged in on other tabs or windows,
unless it's a private one. Sockets are tied to instances of the client. Every tab or window executes an own instance
of the client. Because one browser can have many windows or tabs, one session can have many sockets.

## Why is this thing called "chat"? It also handles bell notifications and online status.
Historical reasons.
