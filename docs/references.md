# References

## Project structure

(Work in progress.)

## Helper scripts

There are a number of helper scripts available. Most of them obey the `FS_INT` env var. Default is `dev`, you can also set it to `test`.

| Script | Purpose |
|--------|---------|
| ./scripts/build-assets | Builds the static assets |
| ./scripts/watch-assets | Builds the static assets on change |
| ./scripts/dev | Run webpack dev server for doing js dev (obsolete, included in ./scripts/start) |
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

## Useful commands and common pitfalls

Useful commands for testing and common pitfalls.

| Command | Action | Pitfall |
|---|---|---|
| amOnPage | Changes URL, loads page, waits for body visible | Do not use to assert being on a URL |
| amOnSubdomain | Changes internal URL state | Does not load a page |
| amOnUrl | Changes internal URL state | Does not load a page |
| click | Fires JavaScript click event | Does not wait for anything to happen afterwards |
| seeCurrentUrlEquals | Checks on which URL the browser is (e.g. after a redirect) | |
| submitForm | Fills form details and submits it via click on the submit button | Does not wait for anything to happen afterwards |
| waitForElement | Waits until a specific element is available in the DOM | |
| waitForPageBody | Waits until the page body is visible (e.g. after click is expected to load a new page) | |
