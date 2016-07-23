# foodsharing

## Getting started
```
git clone git@gitlab.lebensmittelretten.de:raphael_w/lmr-v1-1.git foodsharing
cd foodsharing
git checkout dev-setup
```

### Docker setup

This is the only supported approach, as we can ensure the correct versions of
everything are available.

Make sure you have installed
[docker-compose](https://docs.docker.com/compose/install/) first.

```
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

There are a number of helper scripts available. Most of them obey the `FS_INT` variable. Default is `dev`, you can also set it to `test`.

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
| ./scripts/restart-web | rebuilds and restarts web container, useful if changing nginx config |
| ./scripts/rm | shutdown and cleanup all containers |
| ./scripts/seed | run seed scripts in `scripts/seeds/*.sql` |
| ./scripts/start| start everything! initialing anything if needed |
| ./scripts/stop | stop everything, but leave it configured |
| ./scripts/test | run tests |
| ./scripts/test-rerun | run tests without recreating db |
