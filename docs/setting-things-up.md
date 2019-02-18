# Setting things up

You must have completed the [install](./running-the-code.md) setup before doing this.

Now go and visit [localhost:18080](http://localhost:18080).

If you want a bit of seed data to play with, then run:

```
./scripts/seed
```

It will give you some dummy users you can use to sign in:

| Email                | Password |
|----------------------|----------|
| user1@example.com    | user     |
| user2@example.com    | user     |
| userbot@example.com  | user     |
| userorga@example.com | user     |

It also generates more dummy users and dummy data to fill the page with life (a bit at least). If you want to modify it, then look at the `SeedCommand.php` file.

If you make changes to non-PHP frontend files (e.g. .vue, .js or .scss files), then those are direclty reflected in the running docker.

To stop everything again, just run:

```
./scripts/stop
```

PHPMyAdmin is also included: [localhost:18081](http://localhost:18081). Log in with:

| Field | Value |
|-------|-------|
| Server | db |
| Username | root |
| Password | root |

## Code style

We use php-cs-fixer to format the code style. The aim is to make it use the same style as phpstorm does by default.
It is based on the @Symfony ruleset, with a few changes.

To format all files, you can run:

```
vendor/bin/php-cs-fixer fix --show-progress=estimating --verbose
```

For convenience, you can and should add the code style fix as a pre-commit hook. So you will never commit/push any PHP code that does not
follow the code style rules.

There are two possibilities:

### Using local PHP

When PHP >= 7.0 is installed locally and the vendor folder is in place (by having used the automated tests or the dev environment), you can use your computers PHP to check/fix the codestyle, as this is the fastest option:

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
