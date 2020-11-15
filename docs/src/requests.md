# HTTP Request

The traditional loading of a page is a `http` request,
e.g. calling the main address `https://foodsharing.de` calls `/src/Entrypoint/IndexController.php`
which uses other `php` files to answer the request.
The `php` builds `html`, `css` and `javascript` and sends them to the client.

Other ways to interact with the foodsharing platform are:
- [(Legacy) XHR](#xhr) - do not use in new code!
- [the REST API endpoints](#rest-api) - the preferred option
- [our chatserver](#nodejs-for-messages)

{{ #include requests-xhr.md }}

{{ #include requests-rest.md }}

{{ #include requests-chat.md }}
