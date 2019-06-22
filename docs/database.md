# MySQL database

The MySQL database saves data on a hard drive.
It holds all long-term information.
Querying information is done via `sql` in `Model.php` and `Gateway.php` classes.
More detailed information about queries can be found in the [php reference](php.md).

Related issue: [Extract SQL Statements To Gateways](https://gitlab.com/foodsharing-dev/foodsharing/issues/9).

# Redis database

The Redis database saves data in memory.
It holds all short-term information and caches some of the information gotten from the MySQL database.
Information in Redis include session IDs (Who is loged in?), and email queues.

# Database Migration

If your code change requires customization of the database, create a file migrations/incremental...sql with the SQL statements without the "commit" command.

*For example: migrations/incremental-20161101-remove-autokennzeichen.sql*
```DROP TABLE fs_autokennzeichen;
ALTER TABLE fs_foodsaver
  DROP COLUMN autokennzeichen_id;
ALTER TABLE fs_foodsaver_archive
  DROP COLUMN autokennzeichen_id;
```