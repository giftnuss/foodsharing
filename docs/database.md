# mysql-Database

The mysql-Database is saving data on a hard drive.
It holds all long-term information.
Querying information is done via `sql` in `Model.php` and `Gateway.php` classes.
More detailed information about queries can be found in the [php reference](php.md).

# redis-Database
The redis-Database saves data in memory.
It holds all short-term information and caches some of the information gotten from the mysql-database.
Information in redis include session-ids (Who is loged in?), e-mail queues.

