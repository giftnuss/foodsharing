## Gateways

Our concept of Gateway classes follows the [Table Data Gateway pattern](https://www.martinfowler.com/eaaCatalog/tableDataGateway.html).

One main difference to Models is that a Gateway doesn't contain the actual model of an entity, as the overall
domain logic is put into [Transactions](#transactions) while the structure lives in [Data Transfer Objects](#data-transfer-objects).

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

### Individual gateway functionality

Please refer to our list of Gateway classes below if you're looking for specific functionality:

```
ActivityGateway.php

ApplicationGateway.php
 - handles workgroup applications

BasketGateway.php
 - adding, editing, requesting food baskets, managing their availability
 - querying food basket data, listing new baskets nearby

BellGateway.php

BlogGateway.php

BuddyGateway.php
 - add someone as buddy, respond to that, get list of buddies

BusinessCardGateway.php

ContentGateway.php
 - basic CMS functionality: display, create, edit, delete pages with fixed contentId
 - some content is used in page templates and just a sentence, other content consists of full pages

DashboardGateway.php
 - just basic user info
 - we have an issue to investigate if `countStoresWithoutDistrict` & `setbezirkids` are still needed

EmailGateway.php

EventGateway.php
 - add or edit events, manage invitations (target audience) and their RSVP
 - get list of events in a region, query people who are interested
 - also has some weird event location storage

FoodsaverGateway.php
 - almost 1000 lines of code :)

FoodSharePointGateway.php

GroupGateway.php
 - groups handle functionality that is shared between both regions and workgroups
 - currently only has a few basic helper, and the complex hull closure computation

GroupFunctionGateway.php
 - group functions are currently only used for workgroups, but can attach to both regions and workgroups
 - includes adding and removing the special function groups, and querying whether they exist
 - there are 3 available functions right now; see `Modules/Core/DBConstants/Region/WorkgroupFunction.php`

LegalGateway.php

LoginGateway.php

LookupGateway.php

MailboxGateway.php

MailsGateway.php

MaintenanceGateway.php
 - data needed for cleanup and bookkeeping executed each night (see `MaintenanceControl.php`)

MapGateway.php

MessageGateway.php

MigrateGateway.php

PassportGeneratorGateway.php

ProfileGateway.php

PushNotificationGateway.php

QuizGateway.php

QuizSessionGateway.php

ForumGateway.php

ForumFollowerGateway.php

RegionGateway.php

WorkGroupGateway.php

ReportGateway.php
 - currently unused

SearchGateway.php

SettingsGateway.php

StatisticsGateway.php

StatsGateway.php

StoreGateway.php

TeamGateway.php

UploadsGateway.php

VotingGateway.php

WallPostGateway.php
```
