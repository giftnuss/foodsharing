# php Code structure
## Deprecated code structure

Since legacy code is still widespread through the repository it is important to understand it, too.

The (php) code is roughly structured with [Model - View - Controller](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller).

The communication with the database is found in Model classes.
For example we can find `sql`-commands to manipulate a foodsaver in `/src/Modules/Foodsaver/FoodsaverModel.php`.
Those are executed with the functions inherited from the `Db` class (see `use Foodsharing\Lib\Db\Db;`, for example `$this->q(...)` where `q` stands for `query`.

## Newer code structure

This article describes some parts of the `/src` directory.

### Modules

A lot of code is sorted into modules in the `/src/Modules` directory.
This is a sorting by topic: each module contains files for one topic.
That can be a [gateway](#gateway-classes-vs-models),
a controller, a view, javascript, css, (old) [XHR](requests.md#xhr),
(old) [models](#deprecated-code-structure). For distinguishing
some of these, see the [refactoring page](refactor.md#back-end).

The [Rest api controllers](requests.md#rest-api) do not go into
their respective module directory but into the `/src/Controller`
directory. This has mostly a legacy reason: you can sort code
by topic or by type. Since some developers tried to do one,
other did the other, leaving us in this mid-position that is explained
on this page.

### Services

Some code that assists controllers can be found as Services: `/src/Services`.
Especially code that is used in several controllers over and over again should
exist only once there. `/src/Services/SanitizerService.php` is the best
example for that.

Some code in the services should rather go into the modules if they
belong to a specific module.

### Libraries

Please explain the general content of `/src/Lib` after understanding it.

### Helper classes

The content of `/src/Helpers` is a collection of code that
somehow had no better place. The word `Helper` does not say anything.
Rather try to find a suitable place for it.

### Gateway classes vs models
Instead of model classes we move towards Gateway classes.
They inherit from `BaseGateway` (`/src/Modules/Core/BaseGateway.php`)

One main difference to models is that the functions to communicate with the database are not directly in the Gateway class by inheritance but encapsulated in the attribute `db` (`$this->db-><functioncall>`, defined in `/src/Modules/Core/Database.php`).

Often requesting information from the database uses `sql` calls via the functions at the end of the Database class, see 
 > // === methods that accept SQL statements ===

If possible, rather use the functions at the beginning that build the `sql` commands, see
 > // === high-level methods that build SQL internally ===

Those functions are well-documented in `/src/Modules/Core/Database.php`.

### Gateways vs controllers

Gateways are doing the communication between other php code and
the database concerning one topic (= their [module](#modules))
they therefore enforce consistency of the data.

On the other hand controllers use gateways and include the
'business logic'. The question where 'consistency checking' ends
and 'business logic' begins is not a clear cut.
In case you have good guidelines to distinguish these two,
please add them here.

