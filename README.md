# foodsharing

Welcome to the foodsharing code!

## Getting started

You can use the docker-compose setup if you are using one of:

- Linux
- OSX Yosemite 10.10.3 or higher
- Windows 10 Pro or higher

If you are not using one of those, then try the vagrant + docker-compose setup.

### Linux

Install
[docker CE](https://docs.docker.com/engine/installation/).

And ensure you have 
[docker-compose](https://docs.docker.com/compose/install/) (at least version 1.6.0)
installed too (often comes with docker).

If you can't connect to docker with your local user, you may want to add yourself
to the docker group:

```
sudo usermod -aG docker $USER

# then either login again to reload the groups
# or run (for each shell...)
su - $USER

# should now be able to connect without errors
docker info
```

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
cd foodsharing
./scripts/start
```

### OSX Yosemite 10.10.3 or higher

Install [Docker for Mac](https://docs.docker.com/engine/installation/mac/).

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
cd foodsharing
./scripts/start
```

### Windows 10 Pro or higher

Install [Docker for Windows](https://docs.docker.com/docker-for-windows/install/).

Our scripts are written in bash, but you should be able to get something working by
installing/enabling
[Windows Subsystem for Linux](https://msdn.microsoft.com/en-gb/commandline/wsl/install_guide)

_We do not generally use Windows, so I cannot say it works out of the box.
If you can help here please do!_

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
cd foodsharing
./scripts/start
```

### None of the above

You can try using vagrant with docker-compose:

1. install
[Virtualbox](https://www.virtualbox.org/wiki/Downloads) and
[Vagrant](https://www.vagrantup.com/downloads.html)
1. clone the repo with `git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing`

All commands in Unix (macOS / Linux / BSD) after step 1:
```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
cd foodsharing
vagrant up
vagrant up
```

#### Daily work

`vagrant up` starts the machine and foodsharing project.

`vagrant halt` stops the virtual machine

`vagrant ssh` connects to the virtual machine.

You can change the folder directly with `cd /vagrant`.
From there on you can use the helper scripts. 

Note:
`./scripts/start` will always be executed, when you start the virtual machine with `vagrant up`

### foodsharing light and API

If you want to include the new Django API and the foodsharing light frontend, then:

```
# you may have "api" and "light" directories already present, if so remove them first
git clone https://github.com/foodsharing-dev/foodsharing-light.git light
git clone https://github.com/foodsharing-dev/foodsharing-django-api.git api
./scripts/start
```

Then visit [localhost:18082](http://localhost:18082) for fs light frontend and
[localhost:18000/docs/](http://localhost:18000/docs/) for the API swagger view.

You can run the foodsharing light frontend tests and run tests on change with:

```
./scripts/docker-compose run light sh -c "xvfb-run npm run test:watch -- --browsers Firefox"
```

You can run the api tests with:

```
./scripts/docker-compose run api env/bin/python manage.py test
```

When you update or change the Django API so that it would need to run `pip-sync` or apply migrations,
this can be done with:

```
./scripts/docker-compose restart api
```

### Up and Running

Now go and visit [localhost:18080](http://localhost:18080).

If you want a bit of seed data to play with, run:

```
./scripts/seed
```

It will give you three users you can sign in as:

| email               | password |
|---------------------|----------|
| user1@example.com   | user1    |
| user2@example.com   | user2    |
| userbot@example.com | userbot  |

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

### Testing

Run the tests with:

```
./scripts/test
```

You will need to have initialized everything once (with `./scripts/start`),
but you do not need to have the main containers running to run the tests
as it uses it's own cluster of docker containers.

#### Writing acceptance tests

The `tests` directory has much stuff in it.

You just need to care about 2 places:

`tests/seed.sql` - add any data you want to be in the database when the tests are run
`acceptance/` - copy an existing test and get started!

http://codeception.com/docs/modules/WebDriver#Actions is a very useful page, showing all the things can call on`$I`.
Please read the command descriptions carefully.

##### How acceptance tests work

Tests are run through selenium on firefox.
The interaction of the test with the browser is defined by commands.
Keep in mind that this is on very high level.
Selenium does at most points not know what the browser is doing!

It is especially hard to get waits right as the blocking/waiting behaviour
of the commands may change with the test driver (PhantomJS, Firefox, Chromium, etc.).

```
$I->amOnPage
```
uses Webdriver GET command and waits for the HTML body of the page to be loaded (JavaScript onload handler fired),
but nothing else.

```
$I->click
```
just fires a click event on the given element. It does not wait for anything afterwards!
If you expect a page reload or any asynchronous requests happening, you need to wait for that before
being able to assert any content.

Even just a javascript popup, like an alert, may not be visible immediately!

```
$I->waitForPageBody()
```
can be used to wait for the static page load to be done.
It does also not wait for any javascript executed etc...

##### Some useful commands / common pitfalls

|command|Action|Pitfall|
|-------|------|-------|
|amOnPage|changes URL, loads page, waits for body visible|Do not use to assert being on a URL|
|amOnSubdomain|changes internal URL state|Does not load a page|
|amOnUrl|changes internal URL state|Does not load a page|
|click|fires JavaScript click event|Does not wait for anything to happen afterwards|
|seeCurrentUrlEquals|checks on which URL the browser is (e.g. after a redirect)||
|submitForm|fills form details and submits it via click on the submit button|does not wait for anything to happen afterwards|
|waitForElement|waits until a specific element is available in the DOM||
|waitForPageBody|waits until the page body is visible (e.g. after click is expected to load a new page)|


### Asset watching / building

To rebuild assets on change, run:

```
./scripts/watch-assets
```

# Code style

We use php-cs-fixer to format the code style. The aim is to make it use the same style as phpstorm does by default.
It is based on the @Symfony ruleset, with a few changes.

To format all files you can run:

```
vendor/bin/php-cs-fixer fix --show-progress=estimating --verbose
```

For convenience, you can and should add the code style fix as a pre-commit hook, so you will never commit/push any PHP code that does not
follow the code style rules.

There are two possibilities:

## Using local PHP

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

## Using docker PHP
Executing the following script will use the dev environment to run the codestyle check. As it currently always runs a new container using docker-compose, it will take some seconds to execute:

```
./scripts/fix-codestyle
```

# Helper scripts

There are a number of helper scripts available. Most of them obey the `FS_INT` env var. Default is `dev`, you can also set it to `test`.

| script | purpose |
|--------|---------|
| ./scripts/build-assets | builds the static assets |
| ./scripts/watch-assets | builds the static assets on change (you will need nodejs installed locally) |
| ./scripts/composer | run php composer |
| ./scripts/docker-compose | docker-compose with the correct options set for the env |
| ./scripts/dropdb | drop the database |
| ./scripts/clean | remove anything add by start/test commands |
| ./scripts/initdb | create the database and run migrations |
| ./scripts/mkdirs | create directories that need to be present |
| ./scripts/mysql | run mysql command in correct context: ./scripts/mysql foodsharing "select * from fs_foodsaver" |
| ./scripts/mysqldump | run mysqldump command in correct context |
| ./scripts/npm | run npm in the chat server context |
| ./scripts/rebuild-container [name] | rebuilds and restarts a single container, useful if changing nginx config for example |
| ./scripts/rm | shutdown and cleanup all containers |
| ./scripts/seed | run seed scripts in `scripts/seeds/*.sql` |
| ./scripts/start| start everything! initializing anything if needed |
| ./scripts/stop | stop everything, but leave it configured |
| ./scripts/test | run tests |
| ./scripts/test-rerun | run tests without recreating db |

## Deployment

Ensure you set env var `FS_ENV=prod` (e.g. in php-fpm pool defintion)
and create a `config.inc.prod.php` file.
