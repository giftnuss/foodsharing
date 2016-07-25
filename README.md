# foodsharing

This `dev-setup` branch makes it easy for people to setup foodsharing on their computer
for development purposes.

It runs everything inside docker containers to have minimal impact on the local system and
allow precise versions of software to be defined to match production setup closely.

The intention would be to merge it into master, a few things should be checked/solved first
though:

* it includes `config.inc.php` in the repo now, to switch between configs based on env var, the live site would have to create a `config.inc.prod.php` file and set env var `FS_ENV=prod`
* there are many more files in the base directory that should not served up publically, perhaps all the public files can be moved into a `public` directory and nginx root changed?
* it should be checked by someone familiar with the foodsharing.de codebase/infrastructure

## Getting started

Make sure you have installed
[docker-compose](https://docs.docker.com/compose/install/) and node/npm first.

```
git clone git@gitlab.lebensmittelretten.de:raphael_w/lmr-v1-1.git foodsharing
cd foodsharing
git checkout dev-setup
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

It will give you two users you can sign in as:

| email             | password |
|-------------------|----------|
| user1@example.com | user1    |
| user2@example.com | user2    |

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
| ./scripts/clean | opposite of start, remove everything that was installed |
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
