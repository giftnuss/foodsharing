# foodsharing

This runs everything inside docker containers to have minimal impact on the local system and
allow precise versions of software to be defined to match production setup closely.

## Getting started

Make sure you have installed
[docker-compose](https://docs.docker.com/compose/install/) (at least version 1.6.0) and node/npm first.
If you're [using OSX](https://docs.docker.com/engine/installation/mac/)
you'll have a better experience with Docker for Mac rather than Docker Toolbox 
(files won't update properly if using Toolbox).

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
cd foodsharing
./scripts/start
```

It'll take some time the first time you run it to fetch all the docker images and 
install composer/npm etc, so go and make a cup of tea.

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

# Helper scripts

There are a number of helper scripts available. Most of them obey the `FS_INT` env var. Default is `dev`, you can also set it to `test`.

| script | purpose |
|--------|---------|
| ./scripts/build-assets | builds the static assets |
| ./scripts/watch-assets | builds the static assets on change |
| ./scripts/composer | run php composer |
| ./scripts/docker-compose | docker-compose with the correct options set for the env |
| ./scripts/dropdb | drop the database |
| ./scripts/clean | remove anything add by start/test commands |
| ./scripts/initdb | create the database and run migrations |
| ./scripts/mkdirs | create directories that need to be present |
| ./scripts/mysql | run mysql command in correct context |
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
