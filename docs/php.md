# PHP

This page describes broadly how PHP is used in our project:

- [Main entry point](#main-entry-point)
- [Autoloading](#autoloading)
- [Service classes](#services)
- [Automatic service injection](#automatic-service-injection)
- [Syntax](#syntax)
- [Conventions / Coding Style](#conventions)

For more specific explanations of the types of classes we use, make sure to check [PHP Structure](#php-structure.md)!

## Main entry point

The main entry point for the web is `/index.php`.
That means that `/index.php` gets called whenever a `https://foodsharing.de`-request gets sent to the website.

Another entry point is `xhr.php`, which is used for routes starting with `https://foodsharing.de/xhr.php`. These are
used for our legacy API (see [Xhr](requests.md#xhr)).

The third entry point is `restApi.php`, which will be used whenever a URL starting with `https://foodsharing.de/api` is
requested. This is the route to our modern API following REST principles (see [REST-API](requests.md#rest-api)).

{% include "./php-dependencies.md" %}

{% include "./php-syntax.md" %}
