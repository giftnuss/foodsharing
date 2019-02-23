# Helper scripts

There are a number of helper scripts available. Most of them obey the `FS_INT` env var. Default is `dev`, you can also set it to `test`.

| Script | Purpose |
|--------|---------|
| ./scripts/build-assets | Builds the static assets |
| ./scripts/watch-assets | Builds the static assets on change |
| ./scripts/dev | Run webpack dev server for doing js dev (obsolete, included in `./scripts/start`) |
| ./scripts/composer | Run php composer |
| ./scripts/docker-compose | Docker-compose with the correct options set for the env |
| ./scripts/dropdb | Drop the database |
| ./scripts/clean | Remove anything added by `start`/`test` commands |
| ./scripts/initdb | Create the database and run migrations |
| ./scripts/mkdirs | Create directories that need to be present |
| ./scripts/mysql | Run `mysql` command in correct context: `./scripts/mysql foodsharing "select * from fs_foodsaver"` |
| ./scripts/mysqldump | Run `mysqldump` command in correct context |
| ./scripts/npm | Run `npm` in the chat server context |
| ./scripts/rm | Shut down and clean up all containers |
| ./scripts/seed | Run seed scripts in `scripts/seeds/*.sql` |
| ./scripts/start| Start everything, initializing anything if needed |
| ./scripts/stop | Stop everything, but leave it configured |
| ./scripts/test | Run tests |
| ./scripts/test-rerun | Run tests without recreating db |

Using the `docker-compose` you can run various php-scripts, e.g.
```
./scripts/docker-compose run app php -f run.php Stats foodsaver
./scripts/docker-compose run app php -f run.php Stats betriebe
./scripts/docker-compose run app php -f run.php Stats bezirke
```
This runs the statistics scripts that are run nightly on the production server. This can be necessary to test code concerning statistics since they are usually never run locally.
