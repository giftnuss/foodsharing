# PHP

## Code structure

For the structure of the php code, see the subpage [php Code structure](php-structure.md).

Currently, data is often represented differently. 
For further structuring  [Data Transfer Objects](https://en.wikipedia.org/wiki/Data_transfer_object) (DTO) can be used. An example can be found in the Bell module, currently in [merge request !1457](https://gitlab.com/foodsharing-dev/foodsharing/-/merge_requests/1457). 

TODO: agree on naming conventions and add them here.

DTOs help with clearing up which parameters are expected when and what types they have. 

In addition to the above mentioned classes, Permission classes are used to organize what actions are allowed for which user.

## Main entry point

The main entry point for the web is `/index.php`.
That means that `/index.php` gets called whenever a `https://foodsharing.de`-request gets sent to the website.

Another entry point is `xhr.php`, which is used for routes starting with `https://foodsharing.de/xhr.php`. These are 
used for our legacy API (see [Xhr](requests.md#xhr)).

The third entry point is `restApi.php`, which will be used whenever a URL starting with `https://foodsharing.de/api` is
requested. This is the route to our modern API following REST principles (see [REST-API](requests.md#rest-api)).

## Dependencies and Dependency Injection

### Autoloading

(Almost) no code comes without dependencies on other `php`-classes.

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

<!-- TODO: when does [Symfony](https://symfony.com/doc) work? -->

## Syntax

Here we collect the parts of language [php](https://secure.php.net/docs.php) that are used in this project.
As I write this I already got some experiences in other languages and therefore only include short explanations of surprising syntax.

- Variables: all occurences of variables are starting with a Dollar (`$`) symbol:
```
$out = '<ul class="linklist baskets">';
```
- `=` is an assignment
- `'string'` and `"string"` are strings. Variables inside double-quoted string literals get replaced by their values.
- `.` is the concatenation operator for strings (in other languages `+`)
- `.=` adds the string to the right to the variable on the left
- `$var->member` refers to a member variable or method of an object (in other languages `.`)
- [`ClassName::member`](https://secure.php.net/manual/de/language.oop5.paamayim-nekudotayim.php) refers to a (static) member variable or function of a _class_

## Conventions

- Do not use global variables (those indicated by the keyword `global`, can be found in legacy code)
- Indentation: we use one tabstop per indentation level
- Variables in strings: we do not use variables in strings but concatenate:
```
$list = 'bla'
$list .= $list . 'some text' . $someVariable . 'end text'
```
- When (re)writing php code add types in arguments and return types of functions. If you work with existing code it is not always trivial and is therefore not enforced. In general:
  -  as a parameter, something should be either the type (e.g. `string`, `int`) OR `null` if okay to not be set. `string $blub = null` works as a parameter definition to allow that.
  - as a return type, something should always ever be one type, if possible. Throw an exception otherwise. Especially an empty array `[]` is fine to say that there is no data when otherwise data would have been returned as an array.
