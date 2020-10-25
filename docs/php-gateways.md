## Gateways

Our concept of Gateway classes follows the [Table Data Gateway pattern](https://www.martinfowler.com/eaaCatalog/tableDataGateway.html).

One main difference to Models is that a Gateway doesn't contain the actual model of an entity, as the overall
domain logic is put into [Transactions](#transaction-classes) while the structure lives in [Data Transfer Objects](#data-transfer-objects).

The purpose of a Gateway is to provide functionality to *query* instances of a certain entity type from the database. If
you are familiar with ORM based architectures, you might compare the Gateway's responsibility to the one of a
Repository.

As methods to be found on a Gateway class have the job to perform queries, they should be named in a way that
portrays this. They should not pretend to perform domain-related business logic. A method name suitable for a
Gateway class would be `selectResponsibleFoodsavers()` or `insertFetcher()`. A method not suitable would be
`addFetcher()`, as this implies that the method took care of the whole transaction of adding a fetcher to a store
pickup.
In particular permission checks are not to be found in Gateways.

Another difference to models regarding the implementation of SQL queries is that the functions to communicate with the
database are not directly in the Gateway class by inheritance but encapsulated in the attribute
`db` (`$this->db-><functioncall>`) of class `Database` defined in `/src/Modules/Core/Database.php`.

Gateways inherit from `BaseGateway` (`/src/Modules/Core/BaseGateway.php`), which provides them with the `$db` attribute.

If possible, use semantic methods like `$db->fetch()` or `$db->insert()` to build your queries.
Often, requesting information from the database uses `sql` calls via the functions at the end of the Database class, like
`$db->execute()` - don't use these unless you can't build your query otherwise.

All of those functions are well-documented in `/src/Modules/Core/Database.php`.
