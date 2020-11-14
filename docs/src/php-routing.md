## Routing

For the new REST API, Routing happens completely through Symfony, using `@Route` annotations in the respective controllers.
(also, see `config/routes/api.yaml`)

Everything else (the website, xhr and xhrapp) uses GET parameters to determine the controller and action to call.
See `src/Entrypoint` for the implementations,
and `src/Lib/Routing.php` for how the `page=` GET parameter corresponds with controller class names. 

Last, there are some special routes that consist of:
- a `location` and a `try_files` directive in the web server's config
    For the development environment, you can find them here: https://gitlab.com/foodsharing-dev/images/-/blob/master/web/foodsharing.conf
- Symfony routes to make symfony call the correct entrypoint for all possible URI forms in `config/routes/special.yaml`.
