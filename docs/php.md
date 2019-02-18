# PHP

Here we collect the parts of language php that are used in this project.
As I write this I already got some experiences in other languages and therefore only include short explanations of surprising syntax.

## Deprecated code structure

Since legacy code is still widespread through the repository it is important to understand it, too.

The (php) code is roughly structured with [Model - view - controller](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller).

The communication with the database is found in Model classes.
For example we can find `sql`-commands to manipulate a foodsaver in `/src/Modules/Foodsaver/FoodsaverModel.php`.
Those are executed with the functions inherited from the `Db` class (see `use Foodsharing\Lib\Db\Db;`, for example `$this->q(...)` where `q` stands for `query`.

## Newer code structure

Instead of model classes we move towards Gateway classes.
They inherit from `BaseGateway` (`/src/Modules/Core/BaseGateway.php`)

One main difference to models is that the functions to communicate with the database are not directly in the Gateway class by inheritance but encapsulated in the attribute `db` (`$this->db->functioncall`, defined in `/src/Modules/Core/Database.php`).

Often requesting information from the database uses `sql` calls via the functions at the end of the Database class, see 
 > // === methods that accept SQL statements ===

If possible, rather use the functions at the beginning that build the `sql` commands, see
 > // === high-level methods that build SQL internally ===

Those functions are well-documented in `/src/Modules/Core/Database.php`.

## Syntax

- Variables: all occurences of variables are starting with a Dollar (`$`) symbol:
```
$out = '<ul class="linklist baskets">';
```
- `=` is a definition
- `'string'` are strings. Variables inside strings are replaced by their values.
- `.` is the concatenation operator for strings (in other languages `+`)
- `.=` adds the string to the right to the variable on the left
- `$var->member` refers to a member variable of an object (in other languages `.`)

## Conventions

- Indentation: we use one tabstop per indentation level
- Variables in strings: we do not use variables in strings but concatenate:
```
$list = 'bla'
$list .= $list . 'some text' . $someVariable . 'end text'
```
