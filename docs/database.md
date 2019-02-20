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