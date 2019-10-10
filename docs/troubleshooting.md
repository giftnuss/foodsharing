# Troubleshooting

During development everyone knows the moments when the code shows exactly what you want but the computer just does something different.
Some strategies how to find or avoid errors are collected here.

## Test artifacts

During the Gitlab CI tests are run at every push.
These builds and tests can be found under the menu item [CI/CD](https://gitlab.com/foodsharing-dev/foodsharing/pipelines) (the rocket).
There you can already see, which stages (build, test, deploy) passed.
In order to get the detailed test results, go to the job `test` in the suitable pipeline (also reacheable via the corresponding MR if existing), click „Browse“ and navigate to `tests/_output/_output/report.html` (`https://gitlab.com/foodsharing-dev/foodsharing/-/jobs/<job number>/artifacts/browse/tests/_output/_output/report.html`).

## CSRF Exception

When working on the API one usually wants to try it out.
If you just type in the API call in the web browser while running the local webpage on [`localhost:18080`](setting-things-up.md) you probably get a [`CSRF Exception`](https://de.wikipedia.org/wiki/Cross-Site-Request-Forgery).
This is a safety feature:
- While you are logined via foodsharing.de other pages can send API calls.
- Since your browser has a session foodsharing.de usually would answer the request, the other page got data that it shouldn't get.
- Solution: foodsharing.de sends a CSRF-token that the browser saves as a cookie and the client reads from the cookie and sends the token as a header with every API call. Since cookies can only be accessed by the correct web page, only the foodsharing.de site can make requests.
- When you just type in the API call the headers including the CSRF-token are not set and you are rejected.

There are several work-arounds:
- You write tests. You should write tests anyway and since they emulate a complete session, the CSRF-Token is sent and valid.
- You add an API call in some javascript-file that gets executed. For example add the following into `/src/Modules/Dashboard/Dashboard.js`:
```
import { get } from '@/api/base'
get('/activity')
```
Make sure that you do not commit those temporary changes!
- You disable the CSRF-Check in `/src/EventListener/CsrfListener.php` by commenting the lines
```
// if (!$this->session->isValidCsrfHeader()) {
//  throw new SuspiciousOperationException('CSRF Failed: CSRF token missing or incorrect.');
//}
```
Make sure that you do not commit those temporary changes!

## Restart

Sometimes the docker container get into some weird state. It might help to restart them:
```
./scripts/stop
sudo ./scripts/clean # sudo necessary since the container run with root privileges and therefore create directories with root ownership
./scripts/start
```
But it takes quite a while.

## Cache

Symfony that is running inside docker container are using a cache directory that is persistent over docker restarts and sometimes changes in the source files are not reflected in the running containers.
Then errors that are already fixed might still appear during experiments.
Hence sometimes it helps to remove the cache directory:
```
sudo rm -rf ./cache/dev
```
or even `sudo rm -rf cache`.

## Restart, clean and delete cache - Quick and dirty ;-)

 `./scripts/stop && sudo rm -rf cache && sudo ./scripts/clean && ./scripts/start` 

## Database and email access

The local website gives you database access so that you can directly view and modify what is written in the database.
Access to the e-mails that are sent via the website can also be found.

| dev | test |
--- | --- | --- |
Website | [`localhost:18080`](localhost:18080) | |
phpadmin (database access) | [`localhost:18081`](localhost:18081) | [`localhost:18080`](localhost:28081) |
smtp (outgoing email) | [`localhost:18084`](localhost:18084) | [`localhost:28084`](localhost:28084)

Those ports are configured in `/docker/docker-compose.*.yml`.

## Logs

The server (also the local one) writes logs about a lot that happens including errors. To view those logs, run
```
./scripts/docker-compose logs -f app
```
where you can also replace `app` by other components of the application that are listed by `./scripts/docker-compose ps` or just remove it to show all logs.

`docker-compose` also respects the variable `FS_ENV` that can be set to `dev` or to `test` for running either the `localhost` (dev) containers or the testing containers.

In order to print specific information in the logs, you can print them in your `php`-code.
In order to do so, add a `LoggerInterface` in the constructor `__construct`:
```
use Psr/Log/LoggerInterface;
...
  private $logger;
...
  public function __construct(<other params>, LoggerInterface $logger) {
...
    $this->logger = $logger;
  }
...
// somewhere in your tested, executed code:
    $this->logger->error('some error text');
// especially useful if put into an except clause that catches all errors and reraises them after printing some informative message
```
