# MySQL database

The MySQL database saves data on a hard drive.
It holds all long-term information.
Querying information is done via `sql` in `Model.php` and `Gateway.php` classes.
More detailed information about queries can be found in the [php reference](php-structure.md).

Related issue: [Extract SQL Statements To Gateways](https://gitlab.com/foodsharing-dev/foodsharing/issues/9).

# Database tables and columns

As described in [Set up](setting-things-up.md) and [Database access](troubleshooting.md#database-and-email-access) we can view the dev database in a browser via [phpmyadmin](http://localhost:18081).
Many table and column names should be self-explanatory. Many are not unfortunately.
(If you create new tables, make sure a comment is not necessary!) That's why [here](database-tables-columns.md) we start a list with
explanations to the tables and columns. Please add to this when it was not obvious to you what a table/column was representing
and you figured it out. If you found this information well described
in some file (e.g. some Gateway-php-file), please just link this location
to avoid information duplication.

Some information can also be inferred by the list of limitations and problems
listed in `/database_fixup.md` in the top level directory.

Theoretically there is also the possibility to add a comment for the column in the database, e.g. for [team status in fs_betrieb](http://localhost:18081/tbl_structure.php?server=1&db=foodsharing&table=fs_betrieb&field=team_status&change_column=1). It hasn't been established to use those and adding them later is not as easy as adding the information here since it would require [Database migration](#database-migration).

The tables also specify [indices](http://localhost:18081/tbl_relation.php?db=foodsharing&table=fs_betrieb). That are columns that are often searched. Read the [php manual](https://dev.mysql.com/doc/refman/5.7/en/mysql-indexes.html)
for more detailed explanation.

# Redis database

The Redis database saves data in memory.
It holds all short-term information and caches some of the information gotten from the MySQL database.
Information in Redis include session IDs (Who is loged in?), and email queues.

# Database Migration

We are using phinx for database migrations. If your code change requires customization of the database, 
migration templates can be created with ```./scripts/docker-compose exec app vendor/bin/phinx create MyNewMigration```

### Workaround (Here we should look for a better solution)

> Run the command ```sudo chmod 775 migrations/yourfile``` before your first edit.

You can test the migration with ```./scripts/docker-compose exec app vendor/bin/phinx migrate --dry-run``` (which "runs" all not yet applied migrations which in that case should be just your new one).

For examples you can have a look at migrations/ folder and https://book.cakephp.org/phinx/0/en/migrations.html.
