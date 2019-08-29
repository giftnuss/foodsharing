# Helper scripts

There are a number of helper scripts available. Most of them obey the `FS_INT` env var. Default is `dev`, you can also set it to `test`.

| Script | Purpose |
|--------|---------|
| ./scripts/ci.test | |
| ./scripts/clean | Remove anything added by `start`/`test` commands |
| ./scripts/codecept | |
| ./scripts/composer | Run php composer |
| ./scripts/deploy.notifyslack.sh | |
| ./scripts/deploy.sh | |
| ./scripts/dev | Run webpack dev server for doing js dev (obsolete, included in `./scripts/start`) |
| ./scripts/docker-compose | Docker-compose with the correct options set for the environment |
| ./scripts/dropdb | Drop the database |
| ./scripts/fix | runs all fixing - code stuff (php) |
| ./scripts/fix-codestyle-local | fix php code style, [see php Code style](setting-things-up.md) |
| ./scripts/generate-revision.sh | |
| ./scripts/inc.sh | defines functions needed in other scripts |
| ./scripts/initdb | Create the database and run migrations |
| ./scripts/lint | runs all lintings scripts lint-... |
| ./scripts/lint-js | lints javascript files: prints errors etc. |
| ./scripts/lint-php | lints php files: prints errors etc. |
| ./scripts/mkdirs | Create directories that need to be present (called by other scripts) |
| ./scripts/mysql | Run `mysql` command in correct context: `./scripts/mysql foodsharing "select * from fs_foodsaver"` |
| ./scripts/mysqldump | Run `mysqldump` command in correct context |
| ./scripts/php-cs-fixer | |
| ./scripts/php-cs-fixer.ci.sh | |
| ./scripts/rebuild-all | |
| ./scripts/rebuild-container | |
| ./scripts/rebuild-test-data | |
| ./scripts/rm | Shut down and clean up all containers |
| ./scripts/run | |
| ./scripts/seed | Run seed scripts in `scripts/seeds/*.sql` |
| ./scripts/start| Start everything, initializing anything if needed, see [Setting things up](setting-things-up.md) |
| ./scripts/stop | Stop everything, but leave it configured see [Setting things up](setting-things-up.md) |
| ./scripts/test | Run tests |
| ./scripts/test-chat | Run test for the chat |
| ./scripts/test-js | Run the javascript yarn test |
| ./scripts/test-rerun | Run tests without recreating db (faster that test) |
| ./scripts/watch-assets | Builds the static assets on change |

Using the `docker-compose` you can run various php-scripts, e.g.
```
./scripts/docker-compose run --rm --no-deps app php -f run.php Stats foodsaver
./scripts/docker-compose run --rm --no-deps app php -f run.php Stats betriebe
./scripts/docker-compose run --rm --no-deps app php -f run.php Stats bezirke
```
This runs the statistics scripts that are run nightly on the production server.
This can be necessary to test code concerning statistics since they are usually never run locally.
`--rm` removes the containers afterwards, `--no-deps` lets docker not worry about any dependendent containers. This is often useful since they are often running already.

