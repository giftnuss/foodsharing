# Tests

We use [Codeception](https://codeception.com/docs/01-Introduction#) for testing, especially testing the php code.

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
