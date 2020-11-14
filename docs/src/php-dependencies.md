## Dependencies and Dependency Injection

### Autoloading

(Almost) no code comes without dependencies on other PHP classes.

In order to use a class defined in another file, it needs to get imported at the beginning of your file
using `use`, following a namespace.

The namespace, starting with `Foodsharing` gets interpreted by the [composer autoloader](https://getcomposer.org).
This is configured in `/composer.json` under the key `"autoload"`, where `Foodsharing` gets mapped to `/src`.
Other namespaces than `Foodsharing` link to external libraries, which have their source code somewhere inside
the `/vendor` directory.

The composer autoloader loads files corresponding to conventions specified by the [PSR-4 standard](https://www.php-fig.org/psr/psr-4/).
That means that every subnamespace following the `Foodsharing` namespace gets interpreted as subdirectory under
`/src`.

`use` is not necessary for using classes in the same namespace (roughly: same directory).

Loading a depencency via [`require`](https://secure.php.net/manual/de/function.require.php) is just used in files
like `index.php`, which are not in the `/src` directory and therefore can't use autoloading.
`require` basically executes the PHP file unter the given file name, so you can use the class declaration defined in
that file. Don't use `require` if you can use autoloading.

### Services

A service class is a class whose main purpose is not representing an object structure,
but providing functionality. Service classes are used to structure operations
using object oriented design patterns.

What exactly a service is and what not, is not well defined (see Blog Post
[Services in Domain-Driven Design](http://gorodinski.com/blog/2012/04/14/services-in-domain-driven-design-ddd/)),
but there are some characteristics that are typical for services
- only one instance is created per requests and then shared by
all classes
- services depend on other services and the non-service objects (like DTOs) they operate on
- non-service objects don't depend on services

As we don't have any entity classes, except for the [DTO classes](php-structure.md#data-transfer-objects), nearly all
of our classes are services.

Because service classes only need to be instantiated once, we don't use
the `new` statement to create instances. Instead, we use the
[Dependency Injection pattern](https://en.wikipedia.org/wiki/Dependency_injection).
This enables us to share service instances throughout the application.

### Automatic service injection

Responsible for creating and injecting the instances is the Symfony Dependency
Injection component.

If we want Symfony to inject a dependency into our service, all we need to do is mentioning
 the class in our constructor (`__construct`), e.g.
```
class ActivityXhr extends Control {
  private $mailboxGateway; /* attributes (member variables) */
  public function __construct(ActivityModel $model, MailboxGateway $mailboxGateway)
    { /* using arguments for setting attributes */ }
... }
```
Dependency injection (Symfony) then makes sure that every service we request is created and
injected when our service is instanciated.

This works because we only need at most one object of every service class. This is because,
as already mentioned, service classes are not about representing objects using instances,
but about the functionality they provide.

Symfony config is in `/config/services.yml`. This configuration makes sure that services
get created automatically. See the [Symfony docs](https://symfony.com/doc/current/service_container.html)
for further reference.
