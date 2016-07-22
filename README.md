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
./scripts/mkdirs
./scripts/start
./scripts/composer install
./scripts/npm install
```

It'll take some time to fetch all the docker images, so go and make a cup of tea.

### Up and Running

Now go and visit [localhost:8080](http://localhost:8080).

If you want a bit of seed data to play with, run:

```
./scripts/seed
```

It will give you two users you can sign in as:

| email             | password |
|-------------------|----------|
| user1@example.com | user1    |
| user2@example.com | user2    |

### Asset watching / building

To rebuild assets on change, run:

```
npm run watch
```

(note: the npm modules will have been installed inside the docker container using version 6, it might cause problems running with a different version of node outside. It's a bit tricky as file watching doesn't work well inside containers)

# Helper scripts

There are a number of helper scripts available. Most of them obey the `FS_INT` variable. Default is `dev`, you can also set it to `test`.

| script | purpose |
|-|-|
| ./scripts/build-assets | builds the static assets |
| ./scripts/composer | run php composer |
| ./scripts/docker-compose | docker-compose with the correct options set for the env |
| ./scripts/dropdb | drop the database |
| ./scripts/initdb | create the database and run migrations |
| ./scripts/mkdirs | create directories that need to be present |
| ./scripts/mysql | run mysql command in correct context |
| ./scripts/mysqldump | run mysqldump command in correct context |
| ./scripts/npm | run npm in the chat server context |
| ./scripts/restart-web | rebuilds and restarts web container, useful if changing nginx config |
| ./scripts/rm | shutdown and cleanup all containers |
| ./scripts/seed | run seed scripts in `scripts/seeds/*.sql` |
| ./scripts/test | run tests |
| ./scripts/test-rerun | run tests without recreating db |