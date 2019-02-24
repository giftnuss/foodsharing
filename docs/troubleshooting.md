# Troubleshooting

During development everyone knows the moments when the code shows exactly what you want but the computer just does something different.
Some strategies how to find or avoid errors are collected here.

## CSRF Exception

When working on the api one usually wants to try it out.
If you just type in the api call in the web browser while running the local webpage on [`localhost:18080`](setting-things-up.md) you probably get a [`CSRF Exception`](https://de.wikipedia.org/wiki/Cross-Site-Request-Forgery).
This is a safety feature:
- While you are logined via foodsharing.de other pages can send api calls.
- Since your browser has a session foodsharing.de usually would answer the request, the other page got data that it shouldn't get.
- Solution: foodsharing.de sends a csrf-token that the client saves as a cookie and sends with every api call. Since cookies can only be accessed by the correct web page, only the foodsharing.de-Tab can authenticate itself.
- When you just type in the api call you look like a different site/ tab and get rejected.

There are several work-arounds:
- You write tests. You should write tests anyway and since they emulate a complete session, the CSRF-Token is sent and valid.
- You add an api call in some javascript-file that gets executed. For example add the following into `/src/Modules/Dashboard/Dashboard.js`:
```
import { get } from '@/api/base'
get('/activity')
```
Make sure that you do not commit those temporary changes!
- You disable the SCRF-Check in `/src/EventListener/CsrfListener.php` by commenting the lines
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

The docker container are using a cache directory that is persistent over restarts and sometimes changes in the source files are not reflected in the running containers.
Then errors that are already fixed might still appear during experiments.
Hence sometimes it helps to remove the cache directory:
```
sudo rm -rf ./cache/dev
```
or even `sudo rm -rf cache`.

## Database access

The local website gives you database access so that you can directly view and modify what is written in the database. For the `localhost:18080` version this phpadmin can be found under `localhost:18084` while for the test containers (which are running after running `./scripts/tests` once) you find the database under `localhost:28084`.
28084: phpadmin for tests

## Logs

The server (also the local one) writes logs about a lot that happens including errors. To view those logs, run
```
./scripts/docker-compose logs -f app
```
where you can also replace `app` by other components of the application that are listed during starting them with `./scripts/start`.

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
