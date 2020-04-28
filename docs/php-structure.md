# php Code structure
This article describes some parts of the `/src` directory.

## Modules

A lot of code is sorted into modules in the `/src/Modules` directory.
This is a sorting by topic: each module contains files for one topic.
That can be a [gateway](#gateway-classes),
a controller, a view, javascript, css, (old) [XHR](requests.md#xhr),
(old) [models](#deprecated-code-structure).

The [Rest api controllers](requests.md#rest-api) do not go into
their respective module directory but into the `/src/Controller`
directory. This is due to the Symfony framework: The framework's
default conventions propose a very technical project structure
having all controllers live in one directory/namespace. It is
possible to reconfigure it, however, that might make it harder
to follow Symfony manuals/guides. 


### Deprecated module structure

Since legacy code is still widespread through the repository it is important to understand it, too.

The (php) code is roughly structured with [Model - View - Controller](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller).

The communication with the database is found in Model classes.
For example we can find `sql`-commands to manipulate a foodsaver in `/src/Modules/Foodsaver/FoodsaverModel.php`.
Those are executed with the functions inherited from the `Db` class (see `use Foodsharing\Lib\Db\Db;`, for example `$this->q(...)` where `q` stands for `query`.

### Newer module structure

Instead of Model classes, that hold both, data query logic and application logic, we move towards splitting these up
into Gateway classes and Transaction classes.

#### Gateway classes

Our concept of Gateway classes follows the [Table Data Gateway pattern](https://www.martinfowler.com/eaaCatalog/tableDataGateway.html).

One main difference to Models is that a Gateway doesn't contain the actual model of an entity, as the overall
domain logic is put into [Transactions](#transaction-classes) while the structure lives in [Data Transfer Objects](#data-transfer-objects).

The purpose of a Gateway is to provide functionality to *query* instances of a certain entity type. If you are familiar
with ORM based architectures, you might compare the Gateway's responsibility to the one of a Repository.

As methods to be found on a Gateway class have the job to perform queries, they should be named in a way that
portrays this. They should not pretend to perform domain-related business logic. A method name suitable for a
Gateway class would be `selectResponsibleFoodsavers()` or `insertFetcher()`. A method not suitable would be
`addFetcher()`, as this implies that the method took care of the whole transaction of adding a fetcher to a store
pickup.

Another difference to models regarding the implementation of SQL queries is that the functions to communicate with the
database are not directly in the Gateway class by inheritance but encapsulated in the attribute 
`db` (`$this->db-><functioncall>`, defined in `/src/Modules/Core/Database.php`).

Gateways inherit from `BaseGateway` (`/src/Modules/Core/BaseGateway.php`)

Often requesting information from the database uses `sql` calls via the functions at the end of the Database class, see 
 > // === methods that accept SQL statements ===

If possible, rather use the functions at the beginning that build the `sql` commands, see
 > // === high-level methods that build SQL internally ===

Those functions are well-documented in `/src/Modules/Core/Database.php`.

#### Transaction classes

All modules have certain business rules/domain logic to follow when their data is modified. After all, there are always
certain operations that have to be executed together to ensure that the data keeps being consistent according to the
the rules that apply to them in reality. We implement these transactions of operations executed together as methods on
Transaction classes. 

*Note: Currently, all our Transaction classes are called "Service" and live in the `Foodsharing\Service` 
namespace. When they get renamed and moved to their modules, this note can be removed.* 

For example, when someone wants to join a store pickup, it's not enough to just insert this information into the
database. We also have to be check if the user has the rights to join without a confirmation, and if not, we have to
make sure that the store owner gets notified that they should confirm or deny it.

This is why joining a pickup is implemented in the `joinPickup()` method on the corresponding Transaction class. All
controllers should use this transaction if they want to make a user join a pickup, because only if all steps of the
transaction are executed, the pickup joining is complete. 

#### Data Transfer Objects
Currently, data is often represented differently. 
For further structuring  [Data Transfer Objects](https://en.wikipedia.org/wiki/Data_transfer_object) (DTO) can be used. An example can be found in the Bell module, currently in [merge request !1457](https://gitlab.com/foodsharing-dev/foodsharing/-/merge_requests/1457). 

TODO: agree on naming conventions and add them here.

DTOs help with clearing up which parameters are expected when and what types they have. 

In addition to the above mentioned classes, Permission classes are used to organize what actions are allowed for which user.

## Services

A service class is a class that doesn't represent an object in the real world,
but provides functionality. Service classes are used to structure operations 
using object oriented design patterns. 

What exactly a service is and what not, is not well defined (see Blog Post
[Services in Domain-Driven Design](http://gorodinski.com/blog/2012/04/14/services-in-domain-driven-design-ddd/)),
but there are some characteristics that are typical for services
- only one instance is created per requests and then shared by 
all classes
- services depend on other services and the non-service objects (like DTOs) they operate on
- non-service objects don't depend on services

As we don't have any entity classes, except for the DTO classes, nearly all
of our classes are services.

Because service classes only need to be instantiated once, we don't use
the `new` statement to create instances. Instead, we use the 
[Dependency Injection Pattern](https://en.wikipedia.org/wiki/Dependency_injection).
This enables us to share service instances throughout the application.

Responsible for creating and injecting the instances is the Symfony Dependency
Injection component. For more information on how we handle dependency injection,
see [PHP Dependencies](php.md#php-dependencies).

Currently, in our source code, some code that assists controllers can be found 
in classes named "Service": `/src/Services`.
Some of these classes are Transaction classes that need to be renamed, and some
of them are utility classes. `/src/Services/SanitizerService.php` is the best
example for that.

Some code in the services should rather go into the modules if they
belong to a specific module.

## Libraries

Please explain the general content of `/src/Lib` after understanding it.

## Helper classes

The content of `/src/Helpers` is a collection of code that
somehow had no better place. The word `Helper` does not say anything.
Rather try to find a suitable place for it.

