# foodsharing


## Getting started
```
git clone git@gitlab.lebensmittelretten.de:raphael_w/lmr-v1-1.git foodsharing
cd foodsharing
git checkout dev-setup
npm install
(cd chat && npm install)
```

### Docker setup

This is the recommended approach, as we can ensure the correct versions of
everything are available.

Make sure you have installed
[docker-compose](https://docs.docker.com/compose/install/) first.

Make yourself a config file:
```
cp config.inc.php.txt config.inc.php
```

Ensure you have these bits:
```
define('DB_HOST','db');
define('DB_USER','root');
define('DB_PASS','root');
define('DB_DB','foodsharing');

define('REDIS_HOST', 'redis');
define('REDIS_PORT', 6379);
```

Then run:

```
./scripts/setup
docker-compose up --build
```

It'll take some time to fetch all the docker images, so go and make a cup of tea.

Then in another terminal:

```
./scripts/initdb
./scripts/seed
npm run build-js
```

### Local setup

Install a bunch of stuff on your machine:

* mysql / mariadb
* redis
* php
* php extensions:
  * gd
  * phpredis
  * iconv
  * mcrypt
  * curl
  * zip

Make yourself a config file:
```
cp config.inc.php.txt config.inc.php
```

Ensure you have these bits:
```
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','root');
define('DB_DB','foodsharing');
```
(currently the scripts assume `root:root` for mysql)


Make sure mysql and redis are running, then run:

```
./scripts/setup
./scripts/initdb
./scripts/seed
npm run build-js
npm start
```

### Up and Running

Now go and visit [localhost:8080](http://localhost:8080).

There should be two users you can log in as:


| email             | password |
|-------------------|----------|
| usera@example.com | usera    |
| userb@example.com | userb    |

### Asset watching / building

To rebuild assets on change, run:

```
npm run watch
```

(note: currently only watches javascript files, but builds everything)
