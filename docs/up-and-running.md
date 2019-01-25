# Up and Running

You must have completed the [install](./install.md) setup before doing this.

Now go and visit [localhost:18080](http://localhost:18080).

If you want a bit of seed data to play with, run:

```
./scripts/seed
```

It will give you some users you can sign in as:

| email                | password |
|----------------------|----------|
| user1@example.com    | user     |
| user2@example.com    | user     |
| userbot@example.com  | user     |
| userorga@example.com | user     |

It also generates more users and data to fill the page with life (a bit at least). If you want to modify it, look at the `SeedCommand.php` file.

To stop everything again just run:

```
./scripts/stop
```

PHPMyAdmin is also included: [localhost:18081](http://localhost:18081). Log in with:

| field | value |
|-------|-------|
| Server | db |
| Username | root |
| Password | root |

## Seeing frontend changes live
If you make changes to non-PHP frontend files (e.g. .vue, .js or .scss files), those are not direclty reflected in the docker running via the start script.
However, in addition to it, you can run 
```
./scripts/dev
```
This will start a webpack dev server which you can access at [localhost:8080](http://localhost:8080/) and which will automatically refresh whenever you make any changes to frontend files.

## Code style

We use php-cs-fixer to format the code style. The aim is to make it use the same style as phpstorm does by default.
It is based on the @Symfony ruleset, with a few changes.

To format all files you can run:

```
vendor/bin/php-cs-fixer fix --show-progress=estimating --verbose
```

For convenience, you can and should add the code style fix as a pre-commit hook, so you will never commit/push any PHP code that does not
follow the code style rules.

There are two possibilities:

### Using local PHP

When PHP >= 7.0 is installed locally and the vendor folder is in place (by having used the automated tests or the dev environment), you can use
your computers PHP to check/fix the codestyle, as this is the fastest option:

```
./scripts/fix-codestyle-local
```

Adding this to `.git/hooks/pre-commit` could look like that:

```
#!/bin/sh
HASH_BEFORE=`git diff | sha1sum`
./scripts/fix-codestyle-local
HASH_AFTER=`git diff | sha1sum`

if [ "$HASH_AFTER" != "$HASH_BEFORE" ]; then
  echo "PHP Codestyle was fixed. Please readd your changes and retry commit."
  exit 1;
fi
```

### Using docker PHP

Executing the following script will use the dev environment to run the codestyle check. As it currently always runs a new container using docker-compose, it will take some seconds to execute:

```
./scripts/fix-codestyle
```

## Helper scripts

There are a number of helper scripts available. Most of them obey the `FS_INT` env var. Default is `dev`, you can also set it to `test`.

| script | purpose |
|--------|---------|
| ./scripts/build-assets | builds the static assets |
| ./scripts/watch-assets | builds the static assets on change |
| ./scripts/dev | run webpack dev server for doing js dev |
| ./scripts/composer | run php composer |
| ./scripts/docker-compose | docker-compose with the correct options set for the env |
| ./scripts/dropdb | drop the database |
| ./scripts/clean | remove anything add by start/test commands |
| ./scripts/initdb | create the database and run migrations |
| ./scripts/mkdirs | create directories that need to be present |
| ./scripts/mysql | run mysql command in correct context: ./scripts/mysql foodsharing "select * from fs_foodsaver" |
| ./scripts/mysqldump | run mysqldump command in correct context |
| ./scripts/npm | run npm in the chat server context |
| ./scripts/rm | shutdown and cleanup all containers |
| ./scripts/seed | run seed scripts in `scripts/seeds/*.sql` |
| ./scripts/start| start everything! initializing anything if needed |
| ./scripts/stop | stop everything, but leave it configured |
| ./scripts/test | run tests |
| ./scripts/test-rerun | run tests without recreating db |
