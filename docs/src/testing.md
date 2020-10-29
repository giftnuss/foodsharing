# Testing

## Running tests

Run the tests with:

```
./scripts/test
```

You will need to have initialized everything once (with `./scripts/start`), but you do not need to have the main containers running to run the tests as it uses it's own cluster of docker containers.

After running the test, you can stop it with ```FS_ENV=test ./scripts/stop```. If you don't, the docker containers keep running and need resources.
With this, you can set the FS_ENV environment variable to test, so they operate on the test environment.
Also it is possible to add this in the config file. Maybe some day this info gets added. :-)

After you have run the tests once, you can use `./scripts/test-rerun` which will run faster. It assumes that the containers have already been created and initialized, but otherwise is the same.

So far, end to end testing is working nicely (called acceptance tests in codeception).
They run with a headless Firefox and Selenium inside the Docker setup, they are run on CI build too.

We are working on [restructing the code](https://gitlab.com/foodsharing-dev/foodsharing/issues/68) to enable unit testing.

The test contains stay around after running, and you can visit the test app
[in your browser](http://localhost:28080/), and it has
[it's own phpmyadmin](http://localhost:28081/).

If you want to run with debug mode turned on, then use: `./scripts/test --debug`.

If you just want to run one test, then pass the path to that test as an argument, e.g. `./scripts/test tests/acceptance/LoginCept.php`.

## Writing unit tests

CodeCeption uses PHPUnitTests under the hood and therefore the [PHPUnit test documentation](https://phpunit.readthedocs.io/en/8.0/) can be helpful.

## Writing acceptance tests

The `tests` directory has much stuff in it.

You just need to care about 2 places:

`tests/seed.sql` - add any data you want to be in the database when the tests are run
`acceptance/` - copy an existing test and get started!

http://codeception.com/docs/modules/WebDriver#Actions is a very useful page, showing all the things can call on`$I`.
Please read the command descriptions carefully.

### How acceptance tests work

Tests are run through selenium on firefox.
The interaction of the test with the browser is defined by commands.
Keep in mind that this is on very high level.
Selenium does at most points not know what the browser is doing!

It is especially hard to get waits right as the blocking/waiting behaviour
of the commands may change with the test driver (PhantomJS, Firefox, Chromium, etc.).

```
$I->amOnPage
```
uses WebDriver GET command and waits for the HTML body of the page to be loaded (JavaScript onload handler fired),
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
It does also not wait for any javascript executed etc.

### HtmlAcceptanceTests

Acceptance tests using the `HtmlAcceptanceTester` class are run in *PhpBrowser*. Those tests run on a lower level then WebDriver. They can only test a page's HTML content. Therefore features like JavaScript are not available, but tests run faster.

From [Codeception documentation](https://codeception.com/docs/03-AcceptanceTests):

| | `HtmlAcceptanceTester` | `AcceptanceTester`
|-|------------------------|--------------------
|JavaScript | No | Yes
|`see`/`seeElement` checks if text is… | …present in the HTML source | …actually visible to the user
|Speed | Fast | Slow
