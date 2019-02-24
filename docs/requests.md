# HTTP Request

The traditional loading of a page is a `http` request,
e.g. calling the main address `https://foodsharing.de` calls `/index.php`
which uses other `php` files to answer the request.
The `php` builds `html`, `css` and `javascript` and sends them to the client.

# XHR

XHR is widely used throughout the project but should be replaced. So do not implement new features with XHR!

We use XHR ([XMLHttpRequest](https://en.wikipedia.org/wiki/XMLHttpRequest))
for information transferred from the server to the client which is not a complete new page but javascript-initiated.
For example, the Update-Übersicht on the Dashboard is loaded by an XHR that gets a json file with the information of the updates.
The javascript is found in `/client/src/activity.js`,
it calls addresses like `http://foodsharing.de/xhrapp.php?app=basket&m=infobar`.
This requests an answer by `/xhrapp.php` which in turn calls the correct `php` file based on the options that are given after the `?` in the url.
For example, the `activity.js` requests are answered by
`/src/Modules/Activity/ActivityXHR.php`.
In this case, the database is queried for the information via the `ActivityModel.php` which in turn uses the `/src/Modules/Activity/ActivityGateway.php`.

XHR-request answers contain a status and data and <!-- todo --> ? and always sends the HTTP-status 200. So errors are not recognizable by the HTTP-status but by a status in the sent json data.

# nodejs for messages

The chats have a separate way of communication between client and server.
For all other pages the initiative starts at the client,
the client sends a request to the (`php`) server and gets an answer.
Each request uses one connection that is closed afterwards.
This is not useful for the chats since the server knows that there are new messages and has to tell the client.

For this case there exists a separate `nodejs` server (on the same machine as the `php` server but separate). This holds an open connection to each user that has `foodsharing.de` open on their device. Each time a message arrives at the php server, it sends this information to the `nodejs` server via a websocket
<!-- TODO: explanation what a websocket is, on which server is it, is it the right place to explain it? -->
which uses the connection to the client to send the message.
Note that there can be several connections to each session, of which there can be several for each user. `nodejs` sends the message to all connections of all addressed users.

The code for the `nodejs` server is found in `/chat/server.js` and other files in `/chat`
chat/socket.io -> nodejs server, in chat/server.js. There is documentation for all used parts in `/chat/node_modules/<modulename>/Readme.md`. All `nodejs`-documentation is found on [their webpage](https://nodejs.org/en/docs/).
  <!-- - php server tells websocket that there is a new message -->
  <!-- - nodejs-server sends message to all open connections of all sessions of all users -->

## REST API

The more modern way to build our api is a [REST api](https://symfony.com/doc/master/bundles/FOSRestBundle/index.html) by FOS (friends of symfony).
<!-- TODO: good link to intro/ tutorial -->

The javascript code that sends REST API requests is found under `/client/src/api` and is used by other javascript by [import](javascript.md).

All php classes working with REST requests are found in [`/src/Modules/Controllers/<..>RestController.php`](https://symfony.com/doc/current/controller.html).
This is configured in [`/config/routes/routing.yml`](https://symfony.com/doc/master/bundles/FOSRestBundle/5-automatic-route-generation_single-restful-controller.html).
There it is also configured, that calls to `/api/` are interpreted by the REST api, e.g.
```
https://foodsharing.de/api/conversations/<conversationid>
```
This is being called when you click on a conversation on the „Alle Nachrichten“ page.

REST is configured via [annotations](https://symfony.com/doc/master/bundles/FOSRestBundle/annotations-reference.html) in comments in functions.
  - `@Rest\Get("subsite")` specifies the address to access to start this Action: `https://foodsharing.de/api/subsite"
  - `@Rest\QueryParam(name="optionname")` specifies which options can be used. These are found behind the `?` in the url: `http://foodsharing.de/api/conversations/687484?messagesLimit=1` only sends one message.
  - Both `Get` and `QueryParam` can enforce limitations on the sent data with `requirement="<some regular expression>"`.

Functions need to have special names for symfony to use them: the end with `Action`.
They start with a permission check, throw a `HttpException(401)` if the action is not permitted.
Then they somehow react to the request, usually with a Database query via the appropriate Model or Gateway classes.

During running php, the comments get translated to convoluted php code.
REST also takes care of the translation from php data structures to json.
This json contains data. Errors use the error codes of http-requests.

While reading and writing code a (basic) [manual](https://symfony.com/doc/master/bundles/FOSRestBundle/index.html)
and an [annotation overview](https://symfony.com/doc/master/bundles/FOSRestBundle/annotations-reference.html) will help.

## Services

In `/src/Services` we have services.

## Sockets

In `/src/Sockets`, we have sockets. We use them to reduce the use of [`func.inc.php`](php.md).
